# Podcast Finder - Updated Project Documentation

## Table of Contents
1. [Project Overview](#project-overview)
2. [Technology Stack](#technology-stack)
3. [Project Structure](#project-structure)
4. [Implemented Features](#implemented-features)
5. [Architecture Improvements](#architecture-improvements)
6. [Security Implementation](#security-implementation)
7. [Testing Strategy](#testing-strategy)
8. [API Endpoints](#api-endpoints)
9. [Database Schema](#database-schema)
10. [Deployment Configuration](#deployment-configuration)
11. [Performance Criteria Implementation Status](#performance-criteria-implementation-status)
12. [Issue Resolution Summary](#issue-resolution-summary)
13. [Final Deployment Steps](#final-deployment-steps)

## Project Overview

The Podcast Finder is a Laravel-based API application that allows users to create, manage, and search podcasts and episodes. The system implements role-based access control with three user types: Utilisateur (regular user), Animateur (podcast creator), and Administrateur (system administrator).

## Technology Stack

- **Backend Framework**: Laravel 10
- **PHP Version**: 8.1 or higher
- **Database**: MySQL
- **Authentication**: Laravel Sanctum
- **File Storage**: Cloudinary (for audio files) and Local Storage (for images)
- **Testing**: PHPUnit
- **API Documentation**: JSON-based RESTful API

## Project Structure

```
app/
├── Console/
├── Exceptions/
├── Http/
│   ├── Controllers/
│   │   ├── Api/
│   │   │   ├── BaseApiController.php
│   │   │   ├── EpisodeController.php
│   │   │   ├── PodcastController.php
│   │   │   └── UserController.php
│   │   └── Controller.php
│   ├── Middleware/
│   │   ├── Authenticate.php
│   │   ├── CanDeleteUser.php
│   │   ├── CanManageEpisode.php
│   │   ├── CanManagePodcast.php
│   │   ├── CanUpdateUser.php
│   │   ├── RoleMiddleware.php
│   │   └── ...
│   ├── Requests/
│   │   ├── StoreEpisodeRequest.php
│   │   └── StorePodcastRequest.php
│   └── Kernel.php
├── Models/
│   ├── User.php
│   ├── Podcast.php
│   └── Episode.php
├── Services/
│   └── FileUploadService.php
├── Providers/
└── ...
database/
├── factories/
├── migrations/
└── seeders/
routes/
├── api.php
└── web.php
tests/
├── Feature/
│   └── Api/
│       ├── EpisodeControllerTest.php
│       └── PodcastControllerTest.php
└── Unit/
```

## Implemented Features

### 1. User Management
- User registration and authentication via Laravel Sanctum
- Role-based access control (Utilisateur, Animateur, Administrateur)
- User profile management (update own profile)
- User role management (Administrateur only)
- User deletion (self and by admin)

### 2. Podcast Management
- Create, read, update, and delete podcasts (CRUD)
- Image upload for podcast covers
- Search functionality for podcasts

### 3. Episode Management
- Create, read, update, and delete episodes (CRUD)
- Audio file upload to Cloudinary
- Search functionality for episodes

### 4. Authorization System
- Role-based middleware for API route protection
- Ownership verification for content modification
- Fine-grained access control with custom middleware

## Architecture Improvements

### Clean Controllers Implementation
Controllers were refactored to be lightweight and follow the Single Responsibility Principle:

**After:**
- Created [BaseApiController](file:///c%3A/Users/HP/Desktop/simplon/semaine9/Podcast-finder/app/Http/Controllers/Api/BaseApiController.php) for shared functionality
- Implemented [FileUploadService](file:///c%3A/Users/HP/Desktop/simplon/semaine9/Podcast-finder/app/Services/FileUploadService.php) to handle all file operations
- Used dependency injection for service classes
- Applied Form Request validation consistently

### Service Layer Implementation
Created a dedicated service class for file operations to eliminate code duplication:

``php
class FileUploadService
{
    public function uploadImage(UploadedFile $file, string $folder = 'images'): string
    {
        $imagePath = $file->store($folder, 'public');
        return '/storage/' . $imagePath;
    }

    public function uploadAudio(UploadedFile $file, string $folder = 'podcast_episodes'): string
    {
        $uploadedFileUrl = Cloudinary::upload($file->getRealPath(), [
            'folder' => $folder,
            'timeout' => 120,
            'resource_type' => 'video'
        ])->getSecurePath();
        
        return $uploadedFileUrl;
    }
}
```

### Global Exception Handling
Enhanced the exception handler to provide meaningful JSON error responses for API endpoints:

- Added specific handling for 404, 403, and validation errors
- Implemented proper exception rendering for API requests
- Maintained consistency with Laravel's exception handling patterns

### Form Request Validation
Updated Form Request classes to remove unnecessary validation rules:

- Removed `user_id` requirement from [StorePodcastRequest](file:///c%3A/Users/HP/Desktop/simplon/semaine9/Podcast-finder/app/Http/Requests/StorePodcastRequest.php) as it's set automatically
- Removed `podcast_id` requirement from [StoreEpisodeRequest](file:///c%3A/Users/HP/Desktop/simplon/semaine9/Podcast-finder/app/Http/Requests/StoreEpisodeRequest.php) as it's passed in the URL

## Security Implementation

### Sanctum Authentication
- Properly configured Sanctum guard in [auth.php](file:///c%3A/Users/HP/Desktop/simplon/semaine9/Podcast-finder/config/auth.php)
- Authentication middleware returns null for API requests to prevent redirects
- Secure token-based authentication for all API endpoints

### Role-Based Access Control
- Custom [RoleMiddleware](file:///c%3A/Users/HP/Desktop/simplon/semaine9/Podcast-finder/app/Http/Middleware/RoleMiddleware.php) for permission management
- Fine-grained custom middleware for specific operations:
  - [CanUpdateUser](file:///c%3A/Users/HP/Desktop/simplon/semaine9/Podcast-finder/app/Http/Middleware/CanUpdateUser.php) - Controls user profile updates
  - [CanDeleteUser](file:///c%3A/Users/HP/Desktop/simplon/semaine9/Podcast-finder/app/Http/Middleware/CanDeleteUser.php) - Controls user deletion
  - [CanManageEpisode](file:///c%3A/Users/HP/Desktop/simplon/semaine9/Podcast-finder/app/Http/Middleware/CanManageEpisode.php) - Controls episode management
  - [CanManagePodcast](file:///c%3A/Users/HP/Desktop/simplon/semaine9/Podcast-finder/app/Http/Middleware/CanManagePodcast.php) - Controls podcast management
- Three distinct roles with appropriate permissions:
  - **Utilisateur**: Read-only access to podcasts and episodes, can update/delete own profile
  - **Animateur**: Can create and manage their own podcasts and episodes
  - **Administrateur**: Full access to all content and user management

### Content Ownership Verification
- Implemented authorization logic to ensure users can only modify their own content
- Administrators have override permissions for all content
- Centralized authorization in custom middleware classes

## Testing Strategy

### Unit Testing
Comprehensive test suites were created for both controllers:

#### PodcastControllerTest
- Tests for listing podcasts
- Tests for showing individual podcasts
- Tests for creating podcasts (with role validation)
- Tests for preventing unauthorized podcast creation
- Tests for validation requirements

#### EpisodeControllerTest
- Tests for listing episodes by podcast
- Tests for showing individual episodes
- Tests for creating episodes (with role validation)
- Tests for preventing unauthorized episode creation
- Tests for validation requirements

### Mocking External Services
Implemented proper mocking for Cloudinary service to ensure reliable tests:

```php
// Mock the FileUploadService
$mockFileUploadService = Mockery::mock(FileUploadService::class);
$mockFileUploadService->shouldReceive('uploadAudio')
    ->andReturn('https://example.com/audio.mp3');

$this->app->instance(FileUploadService::class, $mockFileUploadService);
```

### Test Database Management
- Used Laravel's RefreshDatabase trait for test isolation
- Created model factories for consistent test data
- Implemented proper test data cleanup

## API Endpoints

### Authentication Endpoints
- `POST /api/register` - User registration
- `POST /api/login` - User login
- `POST /api/logout` - User logout (authenticated)

### User Management Endpoints
- `PUT /api/users/{user}` - Update user profile (user or admin)
- `DELETE /api/users/{user}` - Delete user (user or admin)
- `GET /api/users` - List all users (Administrateur only)
- `PUT /api/users/{user}/role` - Update user role (Administrateur only)

### Podcast Endpoints
- `GET /api/podcasts` - List all podcasts
- `GET /api/podcasts/{id}` - Show specific podcast
- `POST /api/podcasts` - Create new podcast (Animateur/Administrateur)
- `PUT /api/podcasts/{podcast}` - Update podcast (owner/Administrateur)
- `DELETE /api/podcasts/{podcast}` - Delete podcast (owner/Administrateur)
- `GET /api/search/podcasts?q={query}` - Search podcasts

### Episode Endpoints
- `GET /api/podcasts/{podcast_id}/episodes` - List episodes for a podcast
- `GET /api/episodes/{id}` - Show specific episode
- `POST /api/podcasts/{podcast_id}/episodes` - Create new episode (Animateur/Administrateur)
- `PUT /api/episodes/{episode}` - Update episode (owner/Administrateur)
- `DELETE /api/episodes/{episode}` - Delete episode (owner/Administrateur)
- `GET /api/search/episodes?q={query}` - Search episodes

## Database Schema

### Users Table
```php
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email')->unique();
    $table->timestamp('email_verified_at')->nullable();
    $table->string('password');
    $table->string('role')->default('Utilisateur'); // Utilisateur, Animateur, Administrateur
    $table->rememberToken();
    $table->timestamps();
});
```

### Podcasts Table
```php
Schema::create('podcasts', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->text('description')->nullable();
    $table->string('image')->nullable();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->timestamps();
});
```

### Episodes Table
```php
Schema::create('episodes', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->text('description')->nullable();
    $table->string('audio_file')->nullable();
    $table->foreignId('podcast_id')->constrained()->onDelete('cascade');
    $table->timestamps();
});
```

## Deployment Configuration

### Environment Variables
Required environment variables for Cloudinary integration:
```
CLOUDINARY_URL=cloudinary://API_KEY:API_SECRET@CLOUD_NAME
CLOUDINARY_UPLOAD_PRESET=your_upload_preset
CLOUDINARY_NOTIFICATION_URL=your_notification_url
CLOUDINARY_TIMEOUT=120
CLOUDINARY_CONNECT_TIMEOUT=30
```

### Storage Configuration
- Podcast images stored in `storage/app/public/images` and served via `/storage/images`
- Audio files stored in Cloudinary in the `podcast_episodes` folder
- Symbolic link required: `php artisan storage:link`

### Sanctum Configuration
```php
// config/auth.php
'guards' => [
    'web' => [
        'driver' => 'session',
        'provider' => 'users',
    ],
    'sanctum' => [
        'driver' => 'sanctum',
        'provider' => 'users',
    ],
],
```

## Performance Criteria Implementation Status

### ✅ Authentification Sanctum opérationnelle
- Sanctum configured correctly in [auth.php](file:///c%3A/Users/HP/Desktop/simplon/semaine9/Podcast-finder/config/auth.php) with proper guard setup
- User model uses `HasApiTokens` trait
- Authentication middleware properly handles API requests

### ✅ Gestion correcte des rôles et permissions
- Role field properly defined in User model
- Custom middleware for permission control ([RoleMiddleware](file:///c%3A/Users/HP/Desktop/simplon/semaine9/Podcast-finder/app/Http/Middleware/RoleMiddleware.php), [CanUpdateUser](file:///c%3A/Users/HP/Desktop/simplon/semaine9/Podcast-finder/app/Http/Middleware/CanUpdateUser.php), etc.)
- Role enforcement in API routes with appropriate middleware
- Valid roles ('Utilisateur', 'Animateur', 'Administrateur') used consistently

### ✅ Relations Eloquent fonctionnelles
- User ↔ Podcast ↔ Episode relationships established
- Proper Eloquent methods used ([belongsTo](file:///c%3A/Users/HP/Desktop/simplon/semaine9/Podcast-finder/app/Models/episode.php#L14-L15), [hasMany](file:///c%3A/Users/HP/Desktop/simplon/semaine9/Podcast-finder/app/Models/User.php#L47-L48))
- Relationships eager-loaded where appropriate

### ✅ Contrôleurs propres et légers
- Created [BaseApiController](file:///c%3A/Users/HP/Desktop/simplon/semaine9/Podcast-finder/app/Http/Controllers/Api/BaseApiController.php) for shared functionality
- Implemented [FileUploadService](file:///c%3A/Users/HP/Desktop/simplon/semaine9/Podcast-finder/app/Services/FileUploadService.php) to eliminate duplication
- Used dependency injection and Form Requests consistently
- Removed duplicated authorization logic

### ✅ Form Request pour validation
- Dedicated Form Request classes for Episode and Podcast creation
- Proper validation rules defined with appropriate constraints
- Removed unnecessary validation requirements

### ✅ Sécurisation des routes avec middlewares
- Sanctum middleware on protected routes
- Role-based middleware on restricted routes
- Middleware registered correctly in [Kernel.php](file:///c%3A/Users/HP/Desktop/simplon/semaine9/Podcast-finder/app/Http/Kernel.php)
- Fine-grained custom middleware for specific operations

### ✅ Gestion des erreurs (Handler global)
- Enhanced exception handler with specific error type handling
- Proper JSON responses for different exception scenarios
- Maintained consistency with Laravel's exception handling patterns

### ✅ Respect du principe DRY et des bonnes pratiques Laravel
- Eliminated code duplication with service classes
- Used proper inheritance with base controller
- Applied dependency injection for services
- Used Form Requests for validation

### ✅ Gestion claire des exceptions (try/catch, App\Exceptions)
- Enhanced exception handler with specific error types
- Added proper JSON responses for different exception scenarios
- Maintained consistency with Laravel's exception handling patterns

### ✅ Tests unitaires valides et endpoints fonctionnels
- Created comprehensive test suites for both controllers
- Implemented tests for all major functionality including authorization and validation
- Used mocking for external services (Cloudinary) to ensure reliable tests
- All tests pass successfully

## Issue Resolution Summary

Throughout the development process, several critical issues were identified and resolved:

### Authentication and Authorization Issues
- Fixed route method mismatches (POST vs PUT)
- Resolved authentication token handling
- Implemented proper role-based access control with custom middleware
- Fixed user ID verification for self-management vs admin operations

### File Upload and Cloudinary Integration
- Resolved Cloudinary upload timeout issues by adding proper timeout configurations
- Fixed audio file URL not appearing in responses
- Implemented proper error handling for file uploads
- Added file size validation to prevent oversized uploads

### API Endpoint and Route Management
- Fixed duplicate routes and method conflicts
- Resolved PUT method issues with file uploads by using POST with _method parameter
- Implemented proper route model binding in middleware
- Organized routes with appropriate middleware groups

### Postman Testing and API Consumption
- Fixed form-data submission issues
- Resolved environment variable configuration problems
- Created comprehensive Postman collections and environments
- Provided clear instructions for API testing

### Middleware and Model Binding
- Fixed "Attempt to read property on string" errors in middleware
- Implemented proper model resolution in custom middleware
- Added missing model imports in middleware classes
- Ensured route parameters are correctly converted to model instances

### Performance and Configuration
- Added timeout configurations for Cloudinary uploads
- Implemented proper error handling to prevent server hangs
- Configured environment variables for optimal performance
- Restarted development server to clear hanging processes

Each issue was systematically identified, diagnosed, and resolved with appropriate code changes to ensure the podcast finder application works correctly with all CRUD operations, proper authentication, authorization, and file handling.

## Final Deployment Steps

To deploy the final version of the Podcast Finder project to a GitHub repository, execute the following commands:

```bash
# Check the current status of the repository
git status

# Add all files to git tracking
git add .

# Commit all changes with a descriptive message
git commit -m "Final implementation of Podcast Finder project with complete CRUD operations, authentication, authorization, Cloudinary integration, and comprehensive documentation"

# Add the remote repository (if not already set up)
git remote add origin https://github.com/emmy-na/Podcast-Finder.git

# Push all code to the master branch on GitHub
git push -u origin master
```

After executing these commands, the complete project will be deployed to the GitHub repository and can be accessed at: https://github.com/emmy-na/Podcast-Finder.git

The repository will contain all application code, database migrations, API controllers, middleware, unit tests, Postman collections, and documentation files.
