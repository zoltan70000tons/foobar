# Changelog

All notable changes to the Backend and SPA Admin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.2.1] - 2025-11-24

### Added
- Custom badge color management for agent users
- Badge color selection interface in user settings
- Avatar and badge color fields in user details
- Command to fix user details avatar data
- Badge color display in booking history and team member lists

### Changed
- Enhanced user selector modal to display agent badge colors
- Updated team member view to show custom badge colors
- Modified user details model to support avatar customization

## [1.1.0] - 2025-11-18

### Added
- Tag priority system for sorted display
- Priority field to tags model with validation (0-10 range)
- Database migration for tags priority column
- Priority management in tag creation and edit forms
- Rating component for priority selection in UI
- Global scope for automatic tag sorting by priority (DESC NULLS LAST) then by name (ASC)
- Priority column in tags index view

### Changed
- Tags are now automatically sorted by priority across the application
- Removed manual ordering in repositories and controllers (now handled by model scope)
- Enhanced tag management UI with priority rating controls

### Technical Details
- Added `priority` field as `unsignedTinyInteger` (nullable) to tags table
- Implemented global scope in Tag model for consistent ordering
- Updated validation rules to include priority field (nullable, numeric, min:0, max:10)
- Modified tag-related components to support priority display and editing

## [1.0.0] - Initial Release

### Added
- Initial booking engine admin system
- User authentication and authorization
- Cabin management system
- Event management
- Customer management
- Tag system for categorization
- Booking management interface
- Payment processing integration
- Role-based access control
- Laravel-based backend with Inertia.js frontend
- React/TypeScript frontend components
- Database migrations and seeders
- API endpoints for booking operations

### Features
- Multi-event support
- Cabin type management
- Customer data management
- Booking history tracking
- Tag-based categorization system
- Responsive admin interface
- Real-time updates with Laravel Echo
- Export functionality
- Search and filtering capabilities

---

## Version Format

This project uses [Semantic Versioning](https://semver.org/):
- **MAJOR** version for incompatible API changes
- **MINOR** version for backwards-compatible functionality additions  
- **PATCH** version for backwards-compatible bug fixes

## Categories

- `Added` for new features
- `Changed` for changes in existing functionality
- `Deprecated` for soon-to-be removed features
- `Removed` for now removed features
- `Fixed` for any bug fixes
- `Security` for vulnerability fixes