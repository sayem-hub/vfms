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

- [ ] **Filament v5 Panel Installation & Setup**
  - [ ] Install Filament v5 admin panel provider
  - [ ] Configure bilingual topbar language switcher (English / বাংলা)
  - [ ] Configure role-based access control (Admin, Transport Manager, Accounts, Store Officer)

- [ ] **Fleet & Compliance Resources**
  - [ ] `VehicleResource` (Registration, specs, ownership profile, fuel capacity, current status)
  - [ ] `DriverResource` (Office ID card, profile photo, license expiry badge, assigned unit)
  - [ ] `VehicleComplianceResource` (BRTA expiry countdown badges: 60d, 30d, 15d, expired)
  - [ ] Automated scheduled command for BRTA document expiry alerts

- [ ] **Trip & Requisition Management**
  - [ ] `TripRequisitionResource` (Approval workflow: Submitted $\rightarrow$ Approved $\rightarrow$ Dispatched $\rightarrow$ In-Trip $\rightarrow$ Completed)
  - [ ] ERP verification fields & document previewer modal (`erp_requisition_copy`, `erp_gatepass_copy`)
  - [ ] Distance anomaly banner & justification approval UI

- [ ] **Petty Cash & Fuel Log Audit Resources**
  - [ ] `FuelLogResource` (3-point photo inspection modal: Dispenser + Odometer + Cash Memo)
  - [ ] Fuel burn-rate anomaly warning highlights
  - [ ] `TripExpenseSettlementResource` (Advance cash vs itemized expenses, cash balance settlement button for cashier)

- [ ] **Maintenance & Scrap Parts Store Chain**
  - [ ] `MaintenanceRecordResource` (Work orders, workshop quotation, ERP PR/PO attachment)
  - [ ] Old parts surrender status indicator (Blocks payment approval until scrap is acknowledged)
  - [ ] `ScrapPartsSurrenderResource` (Store officer scrap intake screen with photo of old parts)

---

## Phase 3: Front-Office & Field Portals (Livewire v4)

- [ ] **Employee Trip Requisition Portal**
  - [ ] Simple mobile-friendly Livewire form for factory staff to request vehicles
  - [ ] Destination auto-complete with map coordinates
  - [ ] Approval notification engine for HODs

- [ ] **Factory Security Gate-Pass Terminal**
  - [ ] Quick security guard screen at factory gate (Gazipur, Savar, Narayanganj, etc.)
  - [ ] Scan Vehicle QR / Enter Gate Pass No
  - [ ] Validate active trip status before recording `GATE_OUT` and `GATE_IN` timestamps and odometers

- [ ] **Driver Expense Submission Portal**
  - [ ] Driver interface completely in Bengali (বাংলা)
  - [ ] Log fuel refill with live camera snapshot
  - [ ] Log tolls, parking, food allowance, and submit trip settlement

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
