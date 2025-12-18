<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $query = User::with('roles')->latest();

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if ($request->has('role') && $request->role) {
            $query->role($request->role);
        }

        $users = $query->paginate(15)->withQueryString();
        $roles = Role::all();

        return view('admin.users.index', compact('users', 'roles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', User::class);

        $roles = Role::all();
        return view('admin.users.create', compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', User::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'roles' => ['sometimes', 'array'],
            'roles.*' => ['exists:roles,id'],
        ]);

        $oldValues = [];
        $newValues = $validated;
        unset($newValues['password']); // Don't log password
        $newValues['password'] = '[HIDDEN]';

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // Assign roles
        if ($request->has('roles')) {
            $roleIds = $request->input('roles');
            $roles = Role::whereIn('id', $roleIds)->pluck('name')->toArray();
            $user->assignRole($roles);
            $newValues['roles'] = $roles;
        }

        // Audit log
        AuditLog::log('user.created', $user, $oldValues, $newValues, "User created by admin");

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        $this->authorize('view', $user);

        $user->load(['roles', 'profile', 'profile.socialLinks']);
        $auditLogs = AuditLog::where('model_type', User::class)
            ->where('model_id', $user->id)
            ->with('user')
            ->latest()
            ->take(20)
            ->get();

        return view('admin.users.show', compact('user', 'auditLogs'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $this->authorize('update', $user);

        $roles = Role::all();
        $user->load('roles');

        return view('admin.users.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $oldValues = $user->only(['name', 'email']);
        $oldRoles = $user->roles->pluck('name')->toArray();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'roles' => ['sometimes', 'array'],
            'roles.*' => ['exists:roles,id'],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        if ($request->filled('password')) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        // Update roles
        if ($request->has('roles')) {
            $roleIds = $request->input('roles');
            $roles = Role::whereIn('id', $roleIds)->pluck('name')->toArray();
            $user->syncRoles($roles);
        } else {
            $user->syncRoles([]);
        }

        $newValues = $user->only(['name', 'email']);
        $newRoles = $user->fresh()->roles->pluck('name')->toArray();
        $newValues['roles'] = $newRoles;
        $oldValues['roles'] = $oldRoles;

        // Audit log
        AuditLog::log('user.updated', $user, $oldValues, $newValues, "User updated by admin");

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        // Prevent self-deletion
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You cannot delete your own account.');
        }

        $oldValues = $user->only(['name', 'email', 'id']);
        $oldRoles = $user->roles->pluck('name')->toArray();
        $oldValues['roles'] = $oldRoles;

        $userEmail = $user->email;
        $user->delete();

        // Audit log
        AuditLog::log('user.deleted', null, $oldValues, [], "User {$userEmail} deleted by admin");

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }
}
