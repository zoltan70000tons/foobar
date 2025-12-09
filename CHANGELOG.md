# Changelog

All notable changes to the Backend and SPA Admin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).



## [1.4.2] - 2025-12-08

### Fixed
- Resolved an issue that allowed invitations to be canceled without enabling edit mode.

### Changed
- Improved passenger ordering logic to prevent disordered lists after canceling an invitation.

## [1.4.1] - 2025-12-08

### Fixed
- `cabin_spec_id` being used as `cabins` identifier in Manual Bookings. 

## [1.4.0] - 2025-12-03

### Added
- Optional "Choose Your Cabin" adjustment in manual booking UI
- `getBookingFinalCost` endpoint to preCalculate a Booking Price
- `SystemAdjustment` enum for type-safe adjustment code handling

### Changed
- Optimized adjustment retrieval from individual queries to single bulk query
- Refactored `PriceCalculation::calculatePricePerPassenger()` to use explicit parameters instead of array
- Improved `CartController::getCartData()` to use constructor injection and optimized adjustment fetching
- Updated `AdjustmentsRepository` to use event-aware filtering for all adjustment queries
- Simplified booking controller logic using `SystemAdjustment` enum instead of hardcoded strings

### Removed
- 7 obsolete adjustment repository methods: `getSingleTicketFeeId()`, `getPaidInFullId()`, `getTaxAdjustmentId()`, `getChooseYourCabinFeeId()`, `getCarbonOffsetFeeId()`, `getIdByCode()`, `getCarbonOffsetCodeByCabinCategorySpecId()`

## [1.3.2] - 2025-12-03

### Fixed
- Email invitation error response handling to correctly use `errorMessage` field instead of `message`
- Passenger invitation error display in SendingStep component
- Modal error handling for failed invitation requests

### Changed
- Updated email invitation API endpoint from `/api/my-booking/add-passenger-via-email` to `/api/manage-passengers/add-passenger-via-email`
- Improved error handling flow in ModalSendInvitation component

## [1.3.1] - 2025-12-01

### Added
- Error response helper for standardized API error handling
- Cart empty error code for improved error tracking

### Fixed
- Reservation expiration handling to prevent booking expired cabins
- Cart validation logic to properly verify cabin reservations before updates

## [1.3.0] - 2025-11-26

### Added
- Upgradable Categories Controller
- High Roller flag to cabin category spec to identify free perks
- Cart snapshot field to bookings table for tracking additional data

### Changed
- Modified cabin and cart controllers for upgrade flow improvements
- Cabin category seeder to support new High Roller Fields

## [1.2.2] - 2025-11-25

### Fixed
- Language parameter handling in passenger email invitation endpoint to correctly use `language` input

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
