# Changelog

All notable changes to the Backend and SPA Admin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.10.2](https://github.com/70000TONS-IT/booking-engine-admin/pull/822) - 2026-01-05

### Changed

- Refactored Booking Stepper

## [1.10.1](https://github.com/70000TONS-IT/booking-engine-admin/pull/821) - 2026-01-08

### Added
- Potential survivor matches button tooltips
- Fix for the constant re-render

## [1.10.0] 2026-01-07(https://github.com/70000TONS-IT/booking-engine-admin/pull/824)

### Added

- `CabinInventoryIntegrityService` to validate cabin inventory integrity with validation rules covering private and single-ticket cabin scenarios
- `CabinInventoryIntegrityCheck` console command for running integrity checks and sending daily reports
- `CabinInventoryIntegrityReport` mailable for emailing integrity reports with issues grouped by event
- Email template for cabin inventory integrity reports with formatted issue tables
- `CABIN_INVENTORY_INTEGRITY_REPORT_RECIPIENTS` environment variable for configuring report recipients
- `runIntegrityCheck()` method in `CabinsController` to handle manual integrity check requests
- `CABIN_INVENTORY_CHECK_TRIGGERED` log action to `LogActionCabin` enum
- Scheduled Job to run cabin inventory integrity check at 01:00 PST
- Integrity check button in cabin management UI to run manually

## [1.9.6] 2026-01-08

### Changed

- Email template cabin-inventory-integrity-report with white background color inline

## [1.9.5] 2026-01-07(https://github.com/70000TONS-IT/booking-engine-admin/pull/825)

### Changed

- Improve error response for events

## [1.9.4](https://github.com/70000TONS-IT/booking-engine-admin/pull/820) - 2026-01-05

### Changed

- Refactored `PassengerRepository::create()` to return an array of all created passengers instead of just the lead passenger
- Refactored `PassengerRepository::fillAditionalSeats()` to return an array of created passengers instead of a boolean
- Moved installment creation logic from `BookingRepository` to `PassengerRepository` for better separation of concerns
- Ensured all passengers (lead and additional seats) have installments created consistently

### Fixed

- Issue where the booking was being created even if the pax and installment creation failed
- Ensured minimum of 1 installment is created even when payment plan is PAY_IN_FULL

## [1.9.3](https://github.com/70000TONS-IT/booking-engine-admin/pull/816) - 2026-01-01

### Changed

- Standarized Repo format

## [1.9.2](https://github.com/70000TONS-IT/booking-engine-admin/pull/814) - 2026-01-01

### Fixed

- Validate event ID in EventController

## [1.9.1](https://github.com/70000TONS-IT/booking-engine-admin/pull/814) - 2026-01-01

### Added

- `ApiException` class for standardized error handling across the entire application
- `NOT_ENOUGH_TIME_TO_CREATE_INSTALLMENTS` error code to ErrorCode enum

### Changed

- Proper error response for installment validation failures in PaymentService
- Updated `PaymentService` to throw `ApiException` with specific error codes instead of generic exceptions

### Fixed

- Fixed "Undefined array key 'booking'" error when exceptions occurred during booking creation

## [1.9.0](https://github.com/70000TONS-IT/booking-engine-admin/pull/804)- 2025-12-31

### Added

- Booking Action Rules endpoint to retrieve rules by event and action code
- Support for action rules including blocking behavior and additional fees
- Validation logic to determine when an action requires confirmation

## [1.8.6](https://github.com/70000TONS-IT/booking-engine-admin/pull/813) - 2025-12-29

### Added

- validate reset password token

### Changed

- Reset password form received correct param

## [1.8.5](https://github.com/70000TONS-IT/booking-engine-admin/pull/812) - 2025-12-26

### Fixed

- Undefined key issue in BookingsController when creating bookings
- Validation for installment count in PaymentService to account for Pay in Full

### Removed

- Legacy booking log write operations from BookingsController (writeOnBooking method calls and interface/repository implementation)

## [1.8.4](https://github.com/70000TONS-IT/booking-engine-admin/pull/808/) - 2025-12-26

### Added

- Booking Action Rules endpoint to retrieve rules by event and action code
- Support for action rules including blocking behavior and additional fees
- Fee-related fields in action rules response (fee_amount, fee_currency)
- Validation logic to determine when an action requires confirmation

### Changed

- Standardized action rule response structure for frontend consumption
- Improved rule evaluation logic to ensure consistent behavior across actions

## [1.8.2](https://github.com/70000TONS-IT/booking-engine-admin/pull/803) - 2025-12-19

### Added

- Password visibility toggle and input field icons to authentication login form
- Icons to action buttons in passenger edit modal with descriptive tooltips

### Changed

- Reorganized action buttons in edit passenger modal to top header with better layout and tooltips

### Fixed

- Incorrect JavaScript condition checks for empty objects in SplitPaymentModal and SplitPaymentModal2 (changed from `=== {}` to `Object.keys().length === 0`)
- Guest Authentication error message now uses translated feedback key for passenger not found errors
- Button URLs in navigation menu for "Check Booking" and "Make Payment" routes

## [1.8.1](https://github.com/70000TONS-IT/booking-engine-admin/pull/798) - 2025-12-18

### Added

- Introduce resources for api responses.
- Data masker for specific field types

### Changed

- Removed unnecesery fields from response, removed duplicates

## [1.8.0](https://github.com/70000TONS-IT/booking-engine-admin/pull/783) - 2025-12-15

### Added

- Cabin inventory matrix tab in the Cabin menu to check cabin inventory

## [1.7.7](https://github.com/70000TONS-IT/booking-engine-admin/pull/801) - 2025-12-16

### Changed

- Made Survivor Number field read-only in passenger edit form to prevent manual modifications
- Improved error messages to include "Survivor Number" when email or survivor number is already used
- Extracted sync attempts reset logic into reusable helper method

### Fixed

- Reset survivor sync attempts when passenger is added or identity (name/DOB) is modified
- Added `survivor_sync_attempts` to non-tracked fields list to prevent unnecessary update logs

## [1.7.6](https://github.com/70000TONS-IT/booking-engine-admin/pull/800) - 2025-12-16

### Changed

- Standardized payment log entries to include currency amounts and passenger order
- Payment transfer notes use passenger order instead of passenger ID
- Improved passenger observer to skip tracking timestamp-only updates

### Removed

- Unused survivor number sync logging method from PassengerObserver
- Duplicated logging in NotificationController for system transactions

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
