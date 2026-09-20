# Enterprise Vehicle & Fleet Management System (VFMS) — Tasks List

**Project:** Vehicle & Fleet Management System (VFMS)  
**Conglomerate Context:** Multi-Unit Knit Composite & Textile Group in Bangladesh  
**Stack:** Laravel 13.x | Livewire 4.x | Filament 5.x | NativePHP Mobile v4 | SQLite/MySQL/PostgreSQL  
**Repository:** `https://github.com/sayem-hub/vfms.git`  
**Last Updated:** September 19, 2026  

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

- [x] **ERP Requisition & Gate Pass Workflow Realignment (Maintenance & Trip Booking)**
  - [x] **Elimination of Handwritten Paper Memos**: Digital Pre-Requisition (`MPR-202609-xxx`) created by Transport Incharge directly within VFMS
  - [x] **Admin Head Digital Approval**: Admin Head reviews and digitally approves/rejects pre-requisitions in VFMS (`APPROVED_BY_ADMIN`)
  - [x] **Central Store ERP Requisition Tagging**: Store concern creates formal ERP requisitions (Service Requisitions `SRQ` e.g. `NAZBL-SRQ-26-00389` or Parts Requisitions `RQSN` e.g. `NAZBL-RQSN-26-02116`), and Transport concern tags the ERP requisition number & document copy in VFMS
  - [x] **Vendor Repair Gate Pass**: Returnable / Non-Returnable Gate Pass tracking (`GP-2026-xxx`) for parts or assemblies sent outside to vendors for repair/lathe work
  - [x] **Central Store Scrap Surrender**: Store Officer acknowledges old replaced parts before payment status is unlocked to `READY_FOR_PAYMENT`
  - [x] **PO Tracking Excluded**: Head Office Baridhara DOHS SCM work is deliberately kept outside VFMS per instructions
  - [x] **Trip Booking Domain Refactoring**: Renamed all trip booking entities from "Requisition" to "Request" (`TripRequest`, `trip_requests`, `request_no`), and purged misplaced ERP requisition/gatepass fields from trip booking

- [x] **Fixed & Dedicated Movement (IN-OUT) System, Digital Daily Logbook & Fuel Quota Tracker**
  - [x] **Database Schema & Migrations**: `fixed_routes` (routes, stops, schedules, assigned vehicle/driver), `vehicle_gate_logs` (daily movement logbook), and `vehicles` table updates (`usage_category`, `dedicated_to_official`)
  - [x] **Eloquent Domain Models**: `FixedRoute` and `VehicleGateLog` models with relations; `Vehicle` quota helper methods (`monthlyFuelConsumedLiters`, `monthlyFuelQuotaRemaining`, `monthlyFuelQuotaUsagePercent`, `activeGateLog`)
  - [x] **Dual-Tab Gate Pass Terminal** ([`GatePassTerminal.php`](../app/Livewire/Portal/GatePassTerminal.php) & view):
    - [x] Tab 1: On-Demand Trip Requests with automated distance anomaly audit
    - [x] Tab 2: Fixed & Dedicated Vehicles express punch with real-time factory presence status (🟢 IN / 🟡 OUT)
    - [x] One-click Gate Out (records departure odometer, driver, route/official, destination, purpose; sets vehicle `ON_TRIP`)
    - [x] One-click Gate In (records return odometer, automatically calculates total KM run, sets vehicle `AVAILABLE` and updates odometer)
    - [x] Today's live fixed movement register
  - [x] **Driver Field Portal Quota Integration** ([`DriverPortal.php`](../app/Livewire/Portal/DriverPortal.php) & view):
    - [x] Real-time Monthly Fuel Quota Tracker widget (Quota vs Consumed vs Remaining, color-coded visual progress bar, quota exceeded alert)
    - [x] Direct fuel refill logging for dedicated & fixed vehicles without requiring a trip request
  - [x] **Filament v5 Back-Office Resources**:
    - [x] `FixedRouteResource` (Commute bus routes, schedules, stops, assigned vehicle & driver)
    - [x] `VehicleGateLogResource` (Daily Digital Vehicle Logbook / দৈনিক ডিজিটাল লগবই)
    - [x] `VehicleResource` enhanced with `usage_category` and `dedicated_to_official`
  - [x] **Conglomerate Seeder**: Seeded MD Camry (`GA-21-9988`), Director HiAce (`CHA-53-4412`), Staff Bus (`BA-11-2345`), Joydebpur & Uttara routes, today's gate logs, and monthly quota fuel refills in [`DatabaseSeeder.php`](../database/seeders/DatabaseSeeder.php)
  - [x] **Bilingual Localization**: Added Bengali & English dictionary keys for fixed routes, daily logbook, gate punches, and fuel quotas in [`lang/bn/vfms.php`](../lang/bn/vfms.php) and [`lang/en/vfms.php`](../lang/en/vfms.php)

- [x] **Automated Test Suite for Portals & Operations**
  - [x] 24 Pest tests passing with 106 assertions (`php artisan test`)
  - [x] Dedicated test suite [`tests/Feature/FixedMovementAndLogbookTest.php`](../tests/Feature/FixedMovementAndLogbookTest.php)
  - [x] Laravel Pint code styling & PSR-12 formatting applied cleanly (`pint --test` passed)


---

## Phase 4: NativePHP Mobile v4 Application (Android/iOS) & Mobile Field Engine

- [x] **NativePHP Mobile Environment & Configuration**
  - [x] Mobile configuration ([`config/nativephp.php`](../config/nativephp.php)) with App ID `com.applifebd.vfms`
  - [x] Hardware permissions: Camera, Fine/Coarse GPS Location, Network State, WakeLock, Storage
  - [x] Local SQLite database initialization for offline client storage (`mobile_offline_cache.sqlite`)
  - [x] Official factory and logistics hub GPS coordinates:
    - `HO`: Corporate Head Office, Baridhara DOHS, Dhaka (`23.8197, 90.4143`)
    - `GZP`: BK Bari Factory Plant, Gazipur (`24.1036, 90.3991`)
    - `CGZP`: CAKL, Bhobanipur, Gazipur (`24.1483, 90.4224`)
    - `AGZP`: BIDC Road, Joydebpur, Gazipur (`23.9310, 90.2690`)
    - `CTG`: Chittagong Port Off-Dock Depot (`22.3167, 91.8000`)
    - `AIR`: Dhaka Airport Cargo Village (`23.8433, 90.4030`)
    - `YGZP`: NAZ Yarn Store, Rajabari, Gazipur (`24.1044, 90.4961`)

- [x] **Offline-First Data Synchronization Engine**
  - [x] Database migration & Eloquent Model: [`MobileSyncOutbox.php`](../app/Models/MobileSyncOutbox.php) (`mobile_sync_outbox` table)
  - [x] Idempotency deduplication mechanism preventing duplicate entries on network retry
  - [x] Domain sync service: [`OfflineSyncService.php`](../app/Services/Mobile/OfflineSyncService.php) handling batch actions (`GATE_OUT`, `GATE_IN`, `FUEL_REFILL`, `EXPENSE_LOG`, `TRIP_START`, `TRIP_END`, `GPS_BREADCRUMB`)
  - [x] Automatic background sync on network reconnect via `window.addEventListener('online')`

- [x] **Hardware Sensors, Tamper-Evident Watermarking & Geofence Engine**
  - [x] Direct native camera capture with anti-gallery lock (`capture="environment"`)
  - [x] PHP GD Tamper-Evident Visual & Metadata Watermarking Engine ([`ImageWatermarkService.php`](../app/Services/Mobile/ImageWatermarkService.php)) burning timestamp, vehicle registration, driver name, GPS coordinates, and security seal onto photos
  - [x] Background GPS ping & breadcrumb recording: [`VehicleGpsPing.php`](../app/Models/VehicleGpsPing.php)
  - [x] Geofence Verification Service ([`GeofenceVerificationService.php`](../app/Services/Mobile/GeofenceVerificationService.php)) with Haversine distance, factory boundary checks, and mock/fake GPS detection

- [x] **Mobile RESTful APIs & Field Terminal UI**
  - [x] RESTful API endpoints in [`routes/api.php`](../routes/api.php):
    - `POST /api/v1/mobile/sync/push`: Ingest offline outbox batch
    - `GET /api/v1/mobile/sync/pull`: Offline bootstrap data pull
    - `POST /api/v1/mobile/gps/ping`: Live GPS coordinate ping
    - `POST /api/v1/mobile/photo/watermark`: Live camera photo upload with auto-watermarking
    - `GET /api/v1/mobile/geofences`: Official location reference
  - [x] Mobile Terminal Livewire component ([`MobileTerminal.php`](../app/Livewire/Portal/MobileTerminal.php) & [`mobile-terminal.blade.php`](../resources/views/livewire/portal/mobile-terminal.blade.php)) with touchscreen controls, offline status indicator, and outbox manager
  - [x] Navigation bar integration in [`layouts/app.blade.php`](../resources/views/layouts/app.blade.php)

- [x] **Automated Test Suite & Code Quality**
  - [x] Dedicated test suite [`tests/Feature/MobileOfflineSyncAndHardwareTest.php`](../tests/Feature/MobileOfflineSyncAndHardwareTest.php) (5 tests, 68 assertions)
  - [x] Total project test suite: **33 tests passing with 230 assertions (100%)**
  - [x] Clean PSR-12 code formatted via Laravel Pint

---

## Phase 5: Financial Management, CPK, TCO & Executive Dashboards

- [x] **Cost Per Kilometer (CPK) Calculator Engine**
  - [x] Comprehensive CPK formula service ([`CostPerKmService.php`](../app/Services/Financial/CostPerKmService.php)): $(\text{Fuel} + \text{Maintenance} + \text{Driver Wages} + \text{Tolls/Operating} + \text{Fixed Costs}) / \text{Total KM}$
  - [x] Vehicle-wise and Fleet-wide CPK calculation integrating both on-demand trips and daily digital gate log distances
  - [x] Vehicle category CPK benchmarking (Sedan Car, Microbus, Staff Bus, Covered Van, Ambulance)

- [x] **Vehicle Total Cost of Ownership (TCO) Ledger & "Repair vs Replace" Advisory**
  - [x] Vehicle lifetime TCO ledger ([`TotalCostOfOwnershipService.php`](../app/Services/Financial/TotalCostOfOwnershipService.php)): Capital acquisition value, lifetime fuel, maintenance, BRTA compliances, and trip operating costs
  - [x] Lifetime fleet CPK benchmarking
  - [x] **Repair vs Replace (মেরামত বনাম নতুন গাড়ি ক্রয়) Advisory Engine**: Economic evaluation comparing trailing 12-month maintenance spend vs capital benchmark ($>35\%$ or high mileage $>300,000\text{ km} \rightarrow$ Recommend Replace, $>20\% \rightarrow$ Watchlist, otherwise Economical to Retain)

- [x] **Inter-Company Cost Allocation Engine (সিস্টার কনসার্ন খরচ বণ্টন)**
  - [x] Live monthly cost allocation matrix generator ([`CostAllocationService.php`](../app/Services/Financial/CostAllocationService.php)) cross-charging central transport costs to sister concerns (`NAZ`, `CAKL`, factory units)
  - [x] Dynamic allocation journal voucher generator with unique reference numbers (`JRN-YYYYMM-XXX-####`)
  - [x] Normalized database schema: `cost_allocations` and `cost_allocation_items` with itemized vehicle-level breakdowns

- [x] **Executive Dashboards & Back-Office Analytics Pages**
  - [x] **Financial Analytics & TCO Page** ([`FinancialAnalytics.php`](../app/Filament/Pages/FinancialAnalytics.php) & [`financial-analytics.blade.php`](../resources/views/filament/pages/financial-analytics.blade.php))
  - [x] **Inter-Company Cost Allocation Page** ([`InterCompanyAllocationPage.php`](../app/Filament/Pages/InterCompanyAllocationPage.php) & [`inter-company-allocation.blade.php`](../resources/views/filament/pages/inter-company-allocation.blade.php)) with one-click journal generation and live table
  - [x] **Executive KPI Stat Cards** ([`FleetFinancialStatsWidget.php`](../app/Filament/Widgets/FleetFinancialStatsWidget.php)): Monthly Spend, Average CPK, Total KM, Executive Fuel Quota consumption
  - [x] **Fleet Cost Breakdown Doughnut Chart** ([`FleetCostBreakdownChartWidget.php`](../app/Filament/Widgets/FleetCostBreakdownChartWidget.php))
  - [x] **Category CPK Benchmark Bar Chart** ([`CostPerKmChartWidget.php`](../app/Filament/Widgets/CostPerKmChartWidget.php))
  - [x] **Management Fuel Quota Table Widget** ([`ManagementFuelQuotaWidget.php`](../app/Filament/Widgets/ManagementFuelQuotaWidget.php))

- [x] **Authentic Fleet Seeder from Handwritten Factory Transport Roster**
  - [x] Seeded authentic vehicles and drivers from the transport department's actual log:
    - `16-0158`: ED Sir (Executive Director) - Driver Md. Robiul (`01917577119`)
    - `27-6387`: Md. Harun Sir - Driver Md. Romjan (`01643133909`)
    - `20-2755`: Next Buyer Dedicated (CA Knitwear) - Driver Md. Mushfiq (`01856158750`)
    - `75-0175`: Medical Ambulance - Driver Md. Raju (`01930898182`)
    - `11-0382`: Merchandiser Minibus - Driver Md. Monir (`01735827735`)
    - `11-0957`: Production Staff Bus - Driver Md. Shukur (`01959263011`)
    - `11-3128`: Covered Van 5T (CA Knitwear) - Driver Md. Monir (`01605978393`)
    - `11-3127`: Covered Van 5T (Export Logistics) - Driver Md. Farhad (`01912107173`)
  - [x] Seeded initial finalized monthly inter-company allocation journals for `NAZ` and `CAKL`

- [x] **Bilingual Localization & Automated Quality Assurance**
  - [x] Full Bengali (`lang/bn/vfms.php`) and English (`lang/en/vfms.php`) translations for financial terms, CPK, TCO, and journals
  - [x] Automated feature test suite [`tests/Feature/FinancialAnalyticsAndTcoTest.php`](../tests/Feature/FinancialAnalyticsAndTcoTest.php) (4 tests, 56 assertions)
  - [x] Total project test suite: **28 tests passing with 162 assertions (100%)**
  - [x] Laravel Pint PSR-12 code style verified and applied cleanly

