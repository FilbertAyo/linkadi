<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\PackagePricingTier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PackageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Package::with('pricingTiers')->latest();

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by type
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        // Filter by status
        if ($request->has('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $packages = $query->paginate(15)->withQueryString();

        return view('admin.packages.index', compact('packages'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.packages.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:packages,slug'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'in:nfc_card,classic'],
            'image' => ['nullable', 'image', 'max:2048'],
            'is_active' => ['boolean'],
            'display_order' => ['nullable', 'integer', 'min:0'],
            'base_price' => ['nullable', 'numeric', 'min:0'],
            'subscription_renewal_price' => ['nullable', 'numeric', 'min:0'],
            'printing_fee' => ['nullable', 'numeric', 'min:0'],
            'design_fee' => ['nullable', 'numeric', 'min:0'],
            'card_colors' => ['nullable', 'array'],
            'card_colors.*' => ['string', 'max:255'],
            'features' => ['nullable', 'array'],
            'features.*' => ['string', 'max:255'],
        ]);

        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Package::generateUniqueSlug($validated['name']);
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('packages', 'public');
        }

        // Set default values
        $validated['is_active'] = $request->has('is_active') ? true : false;
        $validated['display_order'] = $validated['display_order'] ?? 0;
        
        // Clean up empty card_colors array
        if (isset($validated['card_colors']) && empty(array_filter($validated['card_colors']))) {
            $validated['card_colors'] = null;
        }

        $package = Package::create($validated);

        // For classic packages, validate and create pricing tiers
        if ($package->type === 'classic' && $request->has('pricing_tiers')) {
            $this->validatePricingTiers($request->pricing_tiers);
            $this->createPricingTiers($package, $request->pricing_tiers);
        }

        return redirect()->route('admin.packages.index')
            ->with('success', 'Package created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Package $package)
    {
        $package->load('pricingTiers', 'orders');
        return view('admin.packages.show', compact('package'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Package $package)
    {
        $package->load('pricingTiers');
        return view('admin.packages.edit', compact('package'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Package $package)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:packages,slug,' . $package->id],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'in:nfc_card,classic'],
            'image' => ['nullable', 'image', 'max:2048'],
            'is_active' => ['boolean'],
            'display_order' => ['nullable', 'integer', 'min:0'],
            'base_price' => ['nullable', 'numeric', 'min:0'],
            'subscription_renewal_price' => ['nullable', 'numeric', 'min:0'],
            'printing_fee' => ['nullable', 'numeric', 'min:0'],
            'design_fee' => ['nullable', 'numeric', 'min:0'],
            'card_colors' => ['nullable', 'array'],
            'card_colors.*' => ['string', 'max:255'],
            'features' => ['nullable', 'array'],
            'features.*' => ['string', 'max:255'],
        ]);

        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Package::generateUniqueSlug($validated['name']);
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image
            if ($package->image) {
                Storage::disk('public')->delete($package->image);
            }
            $validated['image'] = $request->file('image')->store('packages', 'public');
        }

        // Set default values
        $validated['is_active'] = $request->has('is_active') ? true : false;
        $validated['display_order'] = $validated['display_order'] ?? 0;
        
        // Clean up empty card_colors array
        if (isset($validated['card_colors']) && empty(array_filter($validated['card_colors']))) {
            $validated['card_colors'] = null;
        }

        $package->update($validated);

        // For classic packages, update pricing tiers
        if ($package->type === 'classic' && $request->has('pricing_tiers')) {
            $this->validatePricingTiers($request->pricing_tiers);
            $this->updatePricingTiers($package, $request->pricing_tiers);
        }

        return redirect()->route('admin.packages.index')
            ->with('success', 'Package updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Package $package)
    {
        // Check if package has orders
        $orderCount = $package->orders()->count();
        
        // Delete image if exists
        if ($package->image) {
            Storage::disk('public')->delete($package->image);
        }

        $package->delete();

        $message = $orderCount > 0 
            ? "Package deleted successfully. {$orderCount} order(s) associated with this package now have no package reference."
            : 'Package deleted successfully.';

        return redirect()->route('admin.packages.index')
            ->with('success', $message);
    }

    /**
     * Toggle package active status.
     */
    public function toggleActive(Package $package)
    {
        $package->update(['is_active' => !$package->is_active]);

        return redirect()->back()
            ->with('success', 'Package status updated successfully.');
    }

    /**
     * Store a new pricing tier for a package.
     */
    public function storePricingTier(Request $request, Package $package)
    {
        $validated = $request->validate([
            'min_quantity' => ['required', 'integer', 'min:1'],
            'max_quantity' => ['nullable', 'integer', 'gt:min_quantity'],
            'price_per_unit' => ['required', 'numeric', 'min:0'],
            'total_price' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['boolean'],
        ]);

        // Check for overlapping tiers
        $this->validateNoOverlap($package, $validated['min_quantity'], $validated['max_quantity']);

        $validated['package_id'] = $package->id;
        $validated['is_active'] = $request->has('is_active') ? true : false;

        PackagePricingTier::create($validated);

        return redirect()->back()
            ->with('success', 'Pricing tier added successfully.');
    }

    /**
     * Remove a pricing tier.
     */
    public function destroyPricingTier(Package $package, PackagePricingTier $tier)
    {
        if ($tier->package_id !== $package->id) {
            abort(404);
        }

        $tier->delete();

        return redirect()->back()
            ->with('success', 'Pricing tier removed successfully.');
    }

    /**
     * Validate pricing tiers array.
     */
    private function validatePricingTiers(array $tiers): void
    {
        foreach ($tiers as $tier) {
            if (empty($tier['min_quantity']) || empty($tier['price_per_unit'])) {
                throw new \Illuminate\Validation\ValidationException(
                    validator([], []),
                    ['pricing_tiers' => ['Each tier must have min_quantity and price_per_unit.']]
                );
            }
        }
    }

    /**
     * Create pricing tiers for a package.
     */
    private function createPricingTiers(Package $package, array $tiers): void
    {
        foreach ($tiers as $tier) {
            if (!empty($tier['min_quantity']) && !empty($tier['price_per_unit'])) {
                PackagePricingTier::create([
                    'package_id' => $package->id,
                    'min_quantity' => $tier['min_quantity'],
                    'max_quantity' => $tier['max_quantity'] ?? null,
                    'price_per_unit' => $tier['price_per_unit'],
                    'total_price' => $tier['total_price'] ?? null,
                    'is_active' => true,
                ]);
            }
        }
    }

    /**
     * Update pricing tiers for a package.
     */
    private function updatePricingTiers(Package $package, array $tiers): void
    {
        // Delete existing tiers
        $package->pricingTiers()->delete();

        // Create new tiers
        $this->createPricingTiers($package, $tiers);
    }

    /**
     * Validate that a tier range doesn't overlap with existing tiers.
     */
    private function validateNoOverlap(Package $package, int $min, ?int $max): void
    {
        $overlapping = $package->pricingTiers()
            ->where(function ($query) use ($min, $max) {
                // Check if new range overlaps with existing ranges
                $query->where(function ($q) use ($min, $max) {
                    // New min is within existing range
                    $q->where('min_quantity', '<=', $min)
                      ->where(function ($q2) use ($min) {
                          $q2->whereNull('max_quantity')
                             ->orWhere('max_quantity', '>=', $min);
                      });
                })->orWhere(function ($q) use ($min, $max) {
                    // New max is within existing range (if max is set)
                    if ($max !== null) {
                        $q->where('min_quantity', '<=', $max)
                          ->where(function ($q2) use ($max) {
                              $q2->whereNull('max_quantity')
                                 ->orWhere('max_quantity', '>=', $max);
                          });
                    }
                });
            })
            ->exists();

        if ($overlapping) {
            throw new \Illuminate\Validation\ValidationException(
                validator([], []),
                ['pricing_tiers' => ['Pricing tier ranges cannot overlap.']]
            );
        }
    }
}
