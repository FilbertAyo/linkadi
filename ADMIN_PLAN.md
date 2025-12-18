# Admin Side Implementation Plan

## Overview
This document outlines the step-by-step plan to implement the admin side of Linkadi with roles and permissions using Laravel Sanctum and Spatie Laravel Permission package.

## Step 1: Install Required Packages

### 1.1 Install Spatie Laravel Permission
```bash
composer require spatie/laravel-permission
```

### 1.2 Install Laravel Sanctum (if not already installed)
```bash
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
```

### 1.3 Publish Spatie Permission migrations and config
```bash
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan migrate
```

## Step 2: Database Structure

### 2.1 Create Migration for Admin Role Assignment
- Add `is_admin` boolean field to users table (optional, for quick checks)
- Or rely solely on Spatie roles

### 2.2 Update Users Migration
- Ensure users table has all necessary fields

## Step 3: Model Setup

### 3.1 Update User Model
- Add `HasRoles` trait from Spatie
- Add `HasApiTokens` trait from Sanctum
- Add helper methods: `isAdmin()`, `hasPermission()`, etc.

### 3.2 Create Role and Permission Seeders
- Create default roles: `admin`, `moderator`, `user`
- Create default permissions:
  - `view users`
  - `create users`
  - `edit users`
  - `delete users`
  - `view profiles`
  - `edit profiles`
  - `delete profiles`
  - `view social links`
  - `manage settings`
  - `view analytics`

## Step 4: Middleware and Authorization

### 4.1 Create Middleware
- `AdminMiddleware` - Check if user has admin role
- `PermissionMiddleware` - Check specific permissions
- Register middleware in `bootstrap/app.php`

### 4.2 Create Policy Classes
- `UserPolicy` - For user management
- `ProfilePolicy` - For profile management
- `SocialLinkPolicy` - For social link management

## Step 5: Admin Routes

### 5.1 Create Admin Route Group
- `/admin` prefix
- `admin.` route name prefix
- Protected by `auth` and `role:admin` middleware

### 5.2 Admin Routes Structure
```
/admin
  /dashboard          - Admin dashboard (overview stats)
  /users              - User management (list, view, edit, delete)
  /profiles           - Profile management
  /social-links       - Social links management
  /analytics          - Analytics and reports
  /settings           - System settings
  /roles              - Role and permission management
```

## Step 6: Admin Controllers

### 6.1 Create Admin Controllers
- `Admin/DashboardController` - Dashboard overview
- `Admin/UserController` - User CRUD operations
- `Admin/ProfileController` - Profile management
- `Admin/SocialLinkController` - Social link management
- `Admin/AnalyticsController` - Analytics and reports
- `Admin/SettingsController` - System settings
- `Admin/RoleController` - Role and permission management

## Step 7: Admin Views

### 7.1 Create Admin Layout
- `resources/views/layouts/admin.blade.php`
- Similar to dashboard layout but with admin-specific sidebar
- Include admin navigation menu

### 7.2 Admin Dashboard Views
- `admin/dashboard.blade.php` - Main admin dashboard
- `admin/users/index.blade.php` - User list
- `admin/users/show.blade.php` - User details
- `admin/users/edit.blade.php` - Edit user
- `admin/profiles/index.blade.php` - Profile list
- `admin/profiles/show.blade.php` - Profile details
- `admin/analytics/index.blade.php` - Analytics dashboard
- `admin/settings/index.blade.php` - Settings page
- `admin/roles/index.blade.php` - Roles and permissions management

## Step 8: Livewire Components (Optional)

### 8.1 Admin Livewire Components
- `Admin/UserTable` - User listing with filters and search
- `Admin/ProfileTable` - Profile listing
- `Admin/StatsCards` - Dashboard statistics
- `Admin/RoleManager` - Role assignment interface

## Step 9: Features to Implement

### 9.1 Admin Dashboard
- Total users count
- Total profiles count
- Active profiles count
- Recent registrations
- Recent profile updates
- System statistics

### 9.2 User Management
- List all users with pagination
- Search and filter users
- View user details
- Edit user information
- Delete users
- Assign/remove roles
- Activate/deactivate users

### 9.3 Profile Management
- List all profiles
- View profile details
- Edit profiles
- Delete profiles
- Toggle profile visibility
- View profile analytics

### 9.4 Analytics
- User growth charts
- Profile creation trends
- Most popular social links
- Profile views statistics
- User engagement metrics

### 9.5 Settings
- General settings
- Email settings
- Feature flags
- System configuration

### 9.6 Role & Permission Management
- List all roles
- Create/edit roles
- Assign permissions to roles
- Assign roles to users
- Create custom permissions

## Step 10: API Endpoints (Sanctum)

### 10.1 Admin API Routes
- `/api/admin/*` - Admin API endpoints
- Protected by Sanctum and role middleware

### 10.2 API Resources
- `UserResource` - User API response
- `ProfileResource` - Profile API response
- `RoleResource` - Role API response

## Step 11: Testing

### 11.1 Create Tests
- Admin authentication tests
- Role and permission tests
- Admin controller tests
- Middleware tests

## Step 12: Security Considerations

### 12.1 Security Measures
- CSRF protection on all forms
- Rate limiting on admin routes
- Audit logging for admin actions
- Two-factor authentication (optional)
- IP whitelisting (optional)

## Implementation Order

1. ✅ Install packages (Step 1)
2. ✅ Database migrations (Step 2)
3. ✅ Model setup and seeders (Step 3)
4. ✅ Middleware and policies (Step 4)
5. ✅ Basic admin routes and controllers (Step 5-6)
6. ✅ Admin layout and dashboard view (Step 7)
7. ✅ User management (Step 9.2)
8. ✅ Profile management (Step 9.3)
9. ✅ Analytics (Step 9.4)
10. ✅ Settings (Step 9.5)
11. ✅ Role management (Step 9.6)
12. ✅ API endpoints (Step 10)
13. ✅ Testing (Step 11)

## Notes

- Use Spatie Laravel Permission for role-based access control
- Use Laravel Sanctum for API authentication
- Follow Laravel best practices for controllers and views
- Use Livewire/Volt for interactive components
- Maintain consistent UI with existing dashboard design
- Implement proper error handling and validation
- Add proper logging for admin actions

