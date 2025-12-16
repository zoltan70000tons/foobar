# Changelog

All notable changes to the Backend and SPA Admin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.7.5](https://github.com/70000TONS-IT/booking-engine-admin/pull/791) - 2025-12-10

### Changed

- Removed NON-REV, added CREW, PARTIAL REVENUE, SPECIAL DISCOUNT and VIP in TagSeeder

## [1.7.4](https://github.com/70000TONS-IT/booking-engine-admin/pull/796) - 2025-12-15

### Fixed

- The form submits successfully without crashing.
- The split amounts across passengers are calculated correctly and evenly.
- The sum of all split amounts must not exceed the booking’s remaining balance.
- All amounts are rounded to two decimal places, and displayed in the correct localized currency format

## [1.7.3](https://github.com/70000TONS-IT/booking-engine-admin/pull/797) - 2025-12-15

### Improved

- Manual booking customer search now finds customer by name case-insensitive

## [1.7.2](https://github.com/70000TONS-IT/booking-engine-admin/pull/795) - 2025-12-14

### Improved

- Enhance Payment component with collapsible sections for discounts, taxes, and fees.
- Highlight **PAID** in green when a passenger is fully paid.
- Fix conditional rendering for the next payment due date to avoid incorrect evaluations.

## [1.7.1](https://github.com/70000TONS-IT/booking-engine-admin/pull/793) - 2025-12-11

### Added

- Blacklisted tag

### Changed

- Blacklisted customers not show up in Manual Booking flow and flag is returned to the frontend to handle

## [1.7.0](https://github.com/70000TONS-IT/booking-engine-admin/pull/792) - 2025-12-11

### Added

- Export function for admins on the Team/Roles page

## [1.6.0](https://github.com/70000TONS-IT/booking-engine-admin/pull/785) - 2025-12-11

### Added

- More sophisticated survivor sync
- Admin UI to manage potential matches
- Enable running the command from the UI

## [1.5.5] - 2025-12-11

### Changed

- Renamed `bedConfig.ts` to `bed-config.ts` to avoid case-sensitivity issues on Forge.
- Updated all related imports and exports to match the new filename.

## [1.5.4] - 2025-12-11

### Added

- Inline customer creation support in the manual booking flow (agents can create a new customer without leaving the stepper).
- fillPassengerFromUser(user) helper to populate passenger fields from a selected or newly created customer.
- createdCustomer prop handling: automatically selects and pre-fills the stepper when a new customer is created.
- resetState() and handleClose() to fully reset the stepper state when the modal is closed.

### Changed

- Reworked prefill flow so handlePrefill reuses the new fillPassengerFromUser function.
- Prefill now sets searchQuery and selectedUser to reflect the chosen/new customer.

### Fixed

- Prevented stale user being kept between openings by resetting selectedUser, searchQuery, and all relevant state on close/unmount.

## [1.5.3] - 2025-12-10

### Added

- Added bed config to the manual booking stepper.
- Added editable bed configuration dropdown in the SPA Admin.
- Added “Update” action to save the bed configuration.
- Added backend endpoint to update the bed configuration.
- Added form validation, error handling and try/catch in the update controller.
- Added TypeScript types for bed configuration options (`BedConfigId`, `BedConfigOption`).
- Centralized `bedConfigOptions` into a shared types file.

### Fixed

- Fixed Autocomplete typing error by updating state to use `BedConfigOption | null`.

### Updated

- Refactored Autocomplete usage to rely on inferred MUI typings and avoid duplicate option definitions.

## [1.5.2] - 2025-12-09

### Added

- Route /json/events/{id}/bookings/cabin-categories
- getCabinCategories in BookingsController

### Changed

- By default we not return all categories for New Booking modal. Instead after New Booking is selected we fetch categories only, to prevent massive pull each time

## [1.5.1] - 2025-12-09

### Changed

- Standarize Error Handling on `BookingContoller`

## [1.5.0] - 2025-12-08

### Added

- Modal to edit the installment due date in the SPA Admin.
- New backend validation to ensure consistent date updates.

### Changed

- Updated installment editing flow in the SPA Admin.
- Adjusted installment controller to support manual due-date changes.
- Improved error handling when updating installment dates.

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
