# Enterprise Vehicle & Fleet Management System (VFMS) — Tasks List

**Project:** Vehicle & Fleet Management System (VFMS)  
**Conglomerate Context:** Multi-Unit Knit Composite & Textile Group in Bangladesh  
**Stack:** Laravel 13.x | Livewire 4.x | Filament 5.x | NativePHP Mobile v4 | SQLite/MySQL/PostgreSQL  
**Repository:** `https://github.com/sayem-hub/vfms.git`  
**Last Updated:** September 17, 2026  

---

## Phase 1: Architecture, Localization & Data Foundation

- [x] **System Architecture & Domain Blueprint**
  - [x] Multi-ownership vehicle & driver financial responsibility matrix
  - [x] Petty cash advance & trip expense settlement workflow design
  - [x] Custom ERP cross-reference integration strategy (`erp_requisition_no`, `erp_gatepass_no`, etc.)
  - [x] Comprehensive architectural document created at [`docs/architecture_and_implementation_plan.md`](./architecture_and_implementation_plan.md)

- [x] **Bilingual Localization Engine (বাংলা ও ইংরেজি)**
  - [x] English translation dictionary created at [`lang/en/vfms.php`](../lang/en/vfms.php)
  - [x] Complete Bengali (Bangla) translation dictionary created at [`lang/bn/vfms.php`](../lang/bn/vfms.php)
  - [x] Session & user preferred locale middleware [`SetLocaleMiddleware`](../app/Http/Middleware/SetLocaleMiddleware.php)
  - [x] Dynamic `/locale/{lang}` toggling route in [`routes/web.php`](../routes/web.php)
  - [x] Feature tests for English/Bengali translation switching [`tests/Feature/LocalizationTest.php`](../tests/Feature/LocalizationTest.php)

- [x] **Database Migrations & Schema Engine**
  - [x] `companies` and `factory_units` migration (geo-coordinates, unit codes)
  - [x] `vehicles` migration (ownership types, fuel baselines, current odometer, capacity)
  - [x] `drivers` migration (including `office_id_card`, `photo`, `factory_unit_id`, license classifications)
  - [x] `vehicle_compliances` migration (BRTA Fitness, Tax Token, Route Permit, Insurance, 5-Yr CNG cylinder test)
  - [x] `trip_requisitions` migration (scheduled/actual dates, odometers, ERP cross-reference fields, distance anomaly flags)
  - [x] `export_shipment_details` migration (covered van carton quantities, buyer export LC, port off-dock waiting & demurrage)
  - [x] `fuel_logs` migration (3-point photos: dispenser, odometer, cash memo; KM/L calculation & siphon anomaly flags)
  - [x] `trip_expense_settlements` migration (cash advance vs actual expenses, net balance refund/payable)
  - [x] `maintenance_records` migration (workshop types, work order no, ERP PR/PO cross-references, parts surrender requirements)
  - [x] `scrap_parts_surrenders` migration (factory scrap warehouse receipt, old replaced parts photo, store officer acknowledgement)
  - [x] Successfully executed migrations against database (`php artisan migrate`)

- [x] **Eloquent Domain Models & Relationships**
  - [x] `Company` & `FactoryUnit` models with one-to-many fleet relations
  - [x] `Driver` model with factory unit, vehicle, and settlement relationships
  - [x] `Vehicle` model with compliance, requisition, and fuel log relations
  - [x] `VehicleCompliance` model with `isExpired()` and `daysRemaining()` methods
  - [x] `TripRequisition` model with requester, driver, vehicle, and ERP data casts
  - [x] `ExportShipmentDetail` model for garment export shipping logistics
  - [x] `FuelLog` model with photo attachments and efficiency verification
  - [x] `TripExpenseSettlement` model with `recalculate()` math method
  - [x] `MaintenanceRecord` & `ScrapPartsSurrender` models with scrap workflow
  - [x] `User` model updated with `preferred_locale` attribute

- [x] **Pluggable Routing Engine & Anti-Fraud Core Services**
  - [x] `RoutingServiceInterface` & `RouteResult` DTO contract
  - [x] `OsrmRoutingService` (Zero-cost OpenStreetMap routing with Haversine fallback)
  - [x] `GoogleMapsRoutingService` (Plug-and-play adapter for Google Maps Distance Matrix API)
  - [x] `RoutingManager` (Dynamic driver switcher via `config/vfms.php` and `.env`)
  - [x] `DistanceAuditService` (Odometer vs Route distance comparison; flags $>15\%$ variance)
  - [x] `FuelEfficiencyService` (Computes real-time KM/L; flags $>20\%$ drop from vehicle baseline)
  - [x] `TripExpenseSettlementService` (Calculates net balance: Advance Cash - Total Actual Expenses)

- [x] **Automated Quality Assurance & Pest Test Suite**
  - [x] 12 test cases with 38 assertions passing (`php artisan test`)
  - [x] Laravel Pint code formatting & linting applied cleanly

---

## Phase 2: Administrative Back-Office (Filament v5 & TALL Stack)

- [x] **Filament v5 Panel Installation & Setup**
  - [x] Install Filament v5 admin panel provider (`AdminPanelProvider.php`)
  - [x] Configure bilingual topbar language switcher (English / বাংলা MenuItem)
  - [x] Configure brand styling (Emerald theme, brand title) and register `SetLocaleMiddleware`
  - [x] Real-time executive dashboard widget `FleetOverviewWidget` (Active fleet, drivers, cost anomalies, BRTA radar)

- [x] **Fleet & Compliance Resources**
  - [x] `VehicleResource` (Registration, specs, ownership profile, fuel capacity, current status)
  - [x] `DriverResource` (Office ID card, circular photo ImageColumn, license expiry badge, assigned unit)
  - [x] `VehicleComplianceResource` (BRTA expiry countdown badges: 60d, 30d, 15d, expired color-coded alerts)
  - [x] Automated scheduled command for BRTA document expiry alerts (`vfms:check-compliances` scheduled daily in `routes/console.php`)

- [x] **Trip & Requisition Management**
  - [x] `TripRequisitionResource` (Approval workflow: Submitted $\rightarrow$ Approved $\rightarrow$ Dispatched $\rightarrow$ In-Trip $\rightarrow$ Completed)
  - [x] ERP verification fields & file upload controls (`erp_requisition_no`, `erp_requisition_copy`, `erp_gatepass_no`, `erp_gatepass_copy`)
  - [x] Distance anomaly banner with one-click "Audit Distance" row action

- [x] **Petty Cash & Fuel Log Audit Resources**
  - [x] `FuelLogResource` (3-point photo inspection: Dispenser + Odometer + Cash Memo)
  - [x] Fuel burn-rate anomaly warning highlights with one-click "Audit Fuel" row action
  - [x] `TripExpenseSettlementResource` (Advance cash vs itemized expenses, cash balance settlement button for cashier)

- [x] **Maintenance & Scrap Parts Store Chain**
  - [x] `MaintenanceRecordResource` (Work orders, workshop quotation, ERP PR/PO attachment)
  - [x] Old parts surrender status indicator (Blocks payment approval until scrap is acknowledged) with one-click "Store Acknowledge" action
  - [x] `ScrapPartsSurrenderResource` (Store officer scrap intake screen with photo of old parts)

- [x] **Database Seeders & Conglomerate Demo Data**
  - [x] `DatabaseSeeder` with Admin (`admin@vfms.com` / `password`), Cashier, Apex Knit Composite, Echo Spinning, Toyota HiAce, Isuzu Covered Van, Noah Ambulance, Drivers with office ID cards, and test BRTA compliance expiring in 12 days.

---

## Phase 3: Front-Office & Field Portals (Livewire v4)

- [x] **Shared Portal Layout & Navigation**
  - [x] Responsive portal layout ([`resources/views/layouts/app.blade.php`](../resources/views/layouts/app.blade.php)) with Figtree & Hind Siliguri typography
  - [x] Sticky topbar with direct links to Requisitions, Gate Pass, Driver Portal, and Admin
  - [x] Bilingual instant language switcher (English $\leftrightarrow$ বাংলা)

- [x] **Employee Trip Requisition Portal**
  - [x] Livewire v4 component [`TripRequisitionPortal.php`](../app/Livewire/Portal/TripRequisitionPortal.php) & view
  - [x] Quick preset routes for composite mills (Gulshan HO, Mawna Gazipur, Kachpur Narayanganj, DEPZ, Chittagong Port)
  - [x] Custom ERP cross-reference inputs (`erp_requisition_no` and `erp_requisition_copy` file upload)
  - [x] Automated map routing distance calculation via `RoutingManager` (OSRM)
  - [x] Recent requisitions table with real-time status tracking badges

- [x] **Factory Security Gate-Pass Terminal**
  - [x] Specialized Livewire v4 terminal [`GatePassTerminal.php`](../app/Livewire/Portal/GatePassTerminal.php) for factory security guards
  - [x] Real-time search by Requisition No, ERP Gate Pass No, Vehicle Registration, or Driver
  - [x] Record **GATE OUT** (departure odometer, start timestamp, transitions vehicle to `ON_TRIP`)
  - [x] Record **GATE IN** (arrival odometer, end timestamp, transitions vehicle to `AVAILABLE`)
  - [x] Instant automated distance audit check via `DistanceAuditService`: Immediately flashes high-visibility warning banner if vehicle traveled $>15\%$ over expected route distance

- [x] **Driver Expense & Fuel Submission Portal**
  - [x] Mobile-optimized Livewire v4 field portal [`DriverPortal.php`](../app/Livewire/Portal/DriverPortal.php)
  - [x] Log fuel/gas refill with mandatory 3-point live photo upload (Dispenser Meter + Dashboard Odometer + Cash Memo)
  - [x] Real-time burn-rate efficiency calculation via `FuelEfficiencyService` (flags $>20\%$ drop)
  - [x] Itemized trip expenses (Tolls, Parking, Daily Food Allowance / DA, Emergency Repairs)
  - [x] Live petty cash settlement balance calculator: $(\text{Advance Cash} - \text{Total Actual Expenses})$ with instant refundable/payable breakdown and submission to accounts

- [x] **NZ Group Internal Enterprise Branding & Color Theme**
  - [x] Integrated `nz-group.png` corporate logo in main header (preceding "VFMS") and in the footer
  - [x] Admin Panel Provider brand logo, height, and Orange (`#FA5514` / `#ea580c`) theme configuration
  - [x] Transformed Livewire v4 portals (Trip Requisition, Gate-Pass Terminal, Driver Field Portal) to match the dark slate & NZ Group orange aesthetic
  - [x] Purged all marketing fluff/buzzwords to maintain an internal conglomerate operations tone
  - [x] Updated English and Bengali localization packages to "NZ Group - VFMS"

- [x] **Automated Test Suite for Portals**
  - [x] 18 Pest tests passing with 65 assertions (`php artisan test`)

---

## Phase 4: NativePHP Mobile v4 Application (Android/iOS)

- [ ] **NativePHP Mobile Environment Setup**
  - [ ] Configure NativePHP Mobile v4 compiler & shell
  - [ ] Local SQLite database initialization for offline cache

- [ ] **Offline-First Data Synchronization**
  - [ ] Outbox queue for offline trip starts, stops, and checkpoints
  - [ ] Automatic background sync when 4G network reconnects

- [ ] **Hardware Sensors & Device Features**
  - [ ] Native camera capture (block gallery uploads to prevent receipt manipulation)
  - [ ] Image watermarking with timestamp, GPS coordinates, and vehicle registration
  - [ ] Background GPS pinging during active trips for geofence verification

---

## Phase 5: Financial Management, TCO & Executive Dashboards

- [ ] **Cost Per Kilometer (CPK) Calculator**
  - [ ] Monthly aggregation: $(\text{Fuel} + \text{Maintenance} + \text{Driver Salary} + \text{Tolls} + \text{Rental}) / \text{Total KM}$
  - [ ] Route-wise and vehicle-wise CPK benchmarking charts

- [ ] **Vehicle Total Cost of Ownership (TCO) Ledger**
  - [ ] Vehicle lifetime ledger (Fuel, Servicing, BRTA fees, Insurance, Demurrage)
  - [ ] Replacement vs repair cost recommendation analytics

- [ ] **Inter-Company Cost Allocation Engine**
  - [ ] Monthly journal generation cross-charging central transport costs to specific sister concern units (Knitting, Dyeing, Apparel 1/2/3, Spinning)

- [ ] **Custom ERP API Sync Bridge**
  - [ ] Background job to sync cleared trip settlements with Custom ERP financial ledger
  - [ ] Sync status tracking and retry mechanism for failed API payloads
