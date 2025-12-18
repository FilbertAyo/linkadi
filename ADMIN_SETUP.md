# Admin Setup Instructions

## Initial Setup

### 1. Run Migrations and Seeders

```bash
php artisan migrate
php artisan db:seed --class=RolePermissionSeeder
```

### 2. Create Your First Admin User

You can create an admin user using Laravel Tinker:

```bash
php artisan tinker
```

Then run:

```php
$user = App\Models\User::create([
    'name' => 'Admin User',
    'email' => 'admin@example.com',
    'password' => Hash::make('your-secure-password'),
    'email_verified_at' => now(),
]);

$user->assignRole('admin');
```

Or use the admin interface after creating a regular user and then assign the admin role.

### 3. Access Admin Dashboard

Once you have an admin user, log in and navigate to:
- `/admin/dashboard` - Admin dashboard
- `/admin/users` - User management
- `/admin/profiles` - Profile management

## Security Features Implemented

1. **Role-Based Access Control (RBAC)**
   - Admin, Moderator, and User roles
   - Granular permissions for different actions
   - Admin role bypasses all permission checks

2. **Middleware Protection**
   - `AdminMiddleware` - Protects admin routes
   - `PermissionMiddleware` - Checks specific permissions
   - Rate limiting on admin routes (60 requests/minute)

3. **Policy-Based Authorization**
   - `UserPolicy` - Controls user management actions
   - `ProfilePolicy` - Controls profile management actions
   - Prevents self-deletion and unauthorized access

4. **Audit Logging**
   - All admin actions are logged
   - Tracks user, action, model changes, IP address, and user agent
   - Stored in `audit_logs` table

5. **Input Validation**
   - All forms have proper validation
   - CSRF protection on all forms
   - SQL injection protection via Eloquent ORM

6. **Security Best Practices**
   - Passwords are hashed using bcrypt
   - Email verification support
   - Secure session management
   - XSS protection via Blade templating

## Available Permissions

- `view users` - View user list
- `create users` - Create new users
- `edit users` - Edit existing users
- `delete users` - Delete users
- `manage user roles` - Assign/remove roles
- `view profiles` - View profiles
- `view all profiles` - View all profiles (admin)
- `edit profiles` - Edit profiles
- `delete profiles` - Delete profiles
- `view social links` - View social links
- `edit social links` - Edit social links
- `delete social links` - Delete social links
- `view analytics` - View analytics
- `view reports` - View reports
- `manage settings` - Manage system settings
- `view settings` - View settings
- `manage roles` - Manage roles and permissions
- `view roles` - View roles
- `assign roles` - Assign roles to users

## Roles

- **admin** - Full access to all features
- **moderator** - Limited admin access (view users, profiles, analytics)
- **user** - Regular user with no admin permissions

## Notes

- Admins cannot delete themselves
- Admins cannot remove their own admin role
- All admin actions are logged for audit purposes
- Rate limiting prevents abuse of admin endpoints

