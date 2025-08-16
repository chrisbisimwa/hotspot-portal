# HotspotPortal

A comprehensive hotspot management portal built with Laravel 11 and modern web technologies.

## Description

HotspotPortal is a web-based management system for Wi-Fi hotspot services. It provides administrators with tools to manage users, monitor connections, handle payments, and configure hotspot settings through an intuitive interface.

## Tech Stack

- **Backend**: Laravel 12 (PHP 8.2+)
- **Frontend**: Livewire 3, AdminLTE 3, Bootstrap 4.6
- **Authentication**: Laravel Sanctum
- **Permissions**: Spatie Laravel Permission
- **Router Integration**: MikroTik RouterOS API
- **Payment Gateway**: SerdiPay Integration
- **Build Tools**: Vite
- **Testing**: Pest PHP
- **Code Style**: Laravel Pint (PSR-12)

## Quick Installation

1. **Clone and install dependencies**
   ```bash
   git clone <repository-url>
   cd hotspot-portal
   composer install
   npm install
   ```

2. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Database setup and seeding**
   ```bash
   touch database/database.sqlite
   php artisan migrate:fresh --seed
   ```

   This will create the database structure and seed it with:
   - Default roles (admin, agent, user)
   - Super admin user (email: `admin@demo.test`, password: `password`)
   - Sample user profiles (2H, 1DAY, 1WEEK packages)

4. **Build assets and start development**
   ```bash
   npm run dev
   php artisan serve
   ```

## Seeding

The application includes comprehensive seeders for setting up initial data:

### Available Seeders

1. **RolesAndPermissionsSeeder** - Creates basic user roles
   - `admin` - Full system access
   - `agent` - Limited administrative access  
   - `user` - Standard user access

2. **AdminUserSeeder** - Creates super admin account
   - Email: `admin@demo.test`
   - Password: `password`
   - Automatically assigned admin role

3. **UserProfilesSeeder** - Creates sample internet packages
   - `2H` - 2 hours access (1.50 CDF)
   - `1DAY` - 1 day access (3.00 CDF) 
   - `1WEEK` - 1 week access (12.00 CDF)

4. **DemoUsersSeeder** - Creates demo users (local environment only)
   - 5 sample users for testing
   - All assigned user role

### Running Seeders

```bash
# Fresh database with all seeders
php artisan migrate:fresh --seed

# Run specific seeders
php artisan db:seed --class=RolesAndPermissionsSeeder

# Development helper (local only)
php artisan dev:rebuild
```

### Testing Seeders

Run the seeder tests to ensure everything works correctly:
```bash
./vendor/bin/pest tests/Feature/SeedersTest.php
```

## Development Roadmap

### ✅ Étape 1: Base technique (Completed)
- [x] Dependencies setup (Composer + NPM)
- [x] AdminLTE 3 + Bootstrap 4.6 integration
- [x] Vite configuration
- [x] Base layouts and partials
- [x] Route structure with admin protection
- [x] Environment configuration
- [x] Basic testing setup

### ✅ Étape 2: Migrations & Enums (Completed)
- [x] User profiles migration
- [x] Hotspot users migration  
- [x] Sessions tracking migration
- [x] Orders and payments migrations
- [x] Notification system migrations
- [x] Enum classes for user roles, payment status, etc.

### ✅ Étape 3: Seeders & Factories (Completed)
- [x] Role and permission seeder (admin, agent, user)
- [x] Admin user seeder (admin@demo.test)
- [x] User profiles seeder (2H, 1DAY, 1WEEK packages)
- [x] Factory classes for all models
- [x] Test validation for seeders
- [x] Development helper command (dev:rebuild)

### ✅ Étape 4: Domain Services (Completed)
- [x] MikroTik RouterOS API integration service layer
- [x] SerdiPay payment gateway service layer
- [x] Fake mode support for development and testing
- [x] Domain-driven design structure (Contracts, DTOs, Services, Exceptions)
- [x] Unit tests for all services using Pest
- [x] Payment status transition management
- [x] Service provider with dependency injection

### 🔜 Étape 5: Authentication & Authorization
- [ ] Sanctum API authentication
- [ ] Role-based access control
- [ ] User registration flow
- [ ] Password reset functionality

### ✅ Étape 6: Scheduler & Monitoring (Completed)
- [x] **Scheduled Jobs Implementation**
  - [x] SyncMikrotikUsersJob (sync RouterOS users)
  - [x] SyncActiveSessionsJob (track active sessions)
  - [x] UpdateExpiredHotspotUsersJob (mark expired users)
  - [x] ReconcilePaymentsJob (verify payment status)
  - [x] DispatchPendingNotificationsJob (send queued notifications)
  - [x] PruneOldLogsJob (cleanup old logs)
- [x] **Artisan Commands**
  - [x] `hotspot:sync-users` - Manual Mikrotik user sync
  - [x] `hotspot:sync-sessions` - Manual session sync
  - [x] `hotspot:expire-users` - Manual user expiration check
  - [x] `billing:reconcile-payments` - Manual payment reconciliation
  - [x] `notifications:dispatch-pending` - Manual notification dispatch
  - [x] `logs:prune` - Manual log cleanup
  - [x] `monitoring:print-metrics` - Display system metrics
- [x] **Monitoring System**
  - [x] MetricsService for system statistics
  - [x] Admin monitoring endpoints (/admin/monitoring/metrics, /admin/monitoring/interfaces)
  - [x] Memory usage, queue status, interface load tracking
- [x] **Scheduler Configuration**
  - [x] Laravel 12 scheduler setup in routes/console.php
  - [x] Configurable CRON expressions via environment variables
  - [x] Job retry policies and tags
- [x] **Testing**
  - [x] Pest feature tests for all jobs
  - [x] Monitoring service tests
  - [x] Protected route access tests
- [x] **Configuration Files**
  - [x] config/scheduler.php (CRON expressions)
  - [x] config/logging_extra.php (log retention)
  - [x] config/billing.php (reconciliation batch size)
  - [x] Enhanced config/notifications.php (dispatch batch size)

### ✅ Étape 7: API REST v1 (Completed)
- [x] **API Infrastructure**
  - [x] Versioned routes (api/v1) with clean URL structure
  - [x] Consistent JSON response format with ApiResponse trait
  - [x] Custom exception handler for API-specific error formatting
  - [x] Role-based rate limiting (admin: 600/min, agent: 300/min, user: 120/min, auth: 30/min)
- [x] **Authentication Endpoints**
  - [x] POST /auth/login (email or phone + password)
  - [x] POST /auth/logout (revoke current token)
  - [x] GET /me (current user profile)
  - [x] Sanctum token-based authentication with role-based abilities
- [x] **Core API Endpoints**
  - [x] GET /user-profiles (public, active profiles only)
  - [x] POST /orders, GET /orders, GET /orders/{order}
  - [x] GET /payments/{payment}, POST /payments/{order}/initiate
  - [x] GET /hotspot-users, GET /hotspot-users/{user}, GET /hotspot-users/{user}/sessions
  - [x] GET /sessions (user's sessions across all hotspot users)
  - [x] GET /notifications, GET /notifications/{notification}
- [x] **Admin Endpoints** (role:admin required)
  - [x] GET /admin/metrics (system metrics using MetricsService)
  - [x] GET /admin/orders (global order listing)
  - [x] GET /admin/payments (global payment listing)
- [x] **Callback Endpoints**
  - [x] POST /payments/callback/serdipay (signature verification, no auth)
- [x] **Data Validation & Security**
  - [x] Form request validation with French error messages
  - [x] Authorization policies for data access control
  - [x] Ownership checks (users can only access their own data)
  - [x] Admin bypass for all policies
- [x] **API Resources & Documentation**
  - [x] Complete API resources for all models (User, Order, Payment, etc.)
  - [x] Pagination support with metadata
  - [x] OpenAPI v1 stub documentation (docs/openapi-v1.yaml)
- [x] **Testing Coverage**
  - [x] Authentication tests (login, logout, profile access)
  - [x] Order flow tests (create order, initiate payment)
  - [x] Policy enforcement tests (ownership validation)
  - [x] Callback processing tests (SerdiPay webhook)
  - [x] Rate limiting tests
  - [x] Basic API functionality tests

### ✅ Étape 8: Interface Web (Completed)

L'interface web Livewire a été implémentée avec un système complet d'administration et un portail utilisateur utilisant AdminLTE.

#### Composants Livewire Génériques
- [x] **MetricCard** - Cartes de métriques réutilisables avec icônes et diff optionnel
- [x] **DataTable** - Table de données avec tri, recherche, pagination et filtres
- [x] **StatusBadge** - Badges colorés pour statuts avec mapping automatique
- [x] **SearchBar** - Barre de recherche avec debounce et événements

#### Tableaux de Bord
- [x] **Admin Dashboard** (`/admin/dashboard`) - Métriques globales, graphiques placeholder, actions rapides
- [x] **User Dashboard** (`/dashboard`) - Résumé personnel, commandes récentes, notifications

#### Interface Admin
- [x] **Navigation** - Menu latéral dynamique basé sur les rôles (admin/user)
- [x] **Gestion des commandes** - Liste complète avec filtres et tri
- [x] **Pages liste** - Pattern réutilisable pour Orders, Payments, HotspotUsers, etc.

#### Portail Utilisateur  
- [x] **Dashboard personnel** - Métriques utilisateur, commandes récentes
- [x] **Navigation adaptée** - Menu simplifié pour fonctionnalités utilisateur
- [x] **Mes Commandes** - Vue restreinte aux données de l'utilisateur connecté

#### Architecture & Sécurité
- [x] **Layouts** - app.blade.php (général), admin.blade.php, user.blade.php
- [x] **StatusColor Helper** - Mapping centralisé couleurs/statuts par domaine
- [x] **Policies** - Vérification autorisations dans mount() des composants
- [x] **Navigation sidebar** - Component réutilisable avec highlight route active

#### Configuration & Utilitaires
- [x] **config/ui.php** - Configuration pagination, thème, formats dates
- [x] **Tests** - AdminDashboard, UserDashboard, StatusColor avec Pest
- [x] **Composants réutilisables** - Architecture modulaire pour extensions futures

#### Fonctionnalités Techniques
- [x] **Performance** - Eager loading, pagination server-side, cache
- [x] **UX** - Loading indicators, skeletons, flash messages session
- [x] **Accessibilité** - aria-label, navigation clavier, contraste couleurs
- [x] **AdminLTE Integration** - Thématisation cohérente, responsive design

#### TODO Futurs (Étape 9)
- [ ] Graphiques temps réel avec Chart.js/ApexCharts  
- [ ] Export CSV/PDF des données
- [ ] Édition profils utilisateur
- [ ] Notifications push WebSocket
- [ ] Permissions granulaires avancées
- [ ] Dark mode toggle complet

### 🔜 Étape 9: Reporting avancé & Charts temps réel

## TODO List

### Authentication & Security
- [ ] Implement custom authentication UI
- [ ] Set up Sanctum for API authentication
- [ ] Configure Spatie Permission roles and permissions
- [ ] Add two-factor authentication

### Hotspot Integration
- [ ] MikroTik RouterOS API integration
- [ ] User provisioning automation
- [ ] Session monitoring and control
- [ ] Bandwidth management

### Payment Integration
- [ ] SerdiPay payment gateway integration
- [ ] Webhook handling for payment notifications
- [ ] Invoice generation
- [ ] Payment history tracking

### Monitoring & Analytics
- [ ] Real-time session monitoring
- [ ] Usage analytics dashboard
- [ ] Performance metrics
- [ ] Alert system for issues

### Notifications
- [ ] SMS notification system
- [ ] Email notifications
- [ ] In-app notifications
- [ ] Webhook notifications for third-party services

## Domain Services (Étape 4)

The application implements a Domain-Driven Design approach with dedicated service layers for Mikrotik RouterOS integration and payment processing.

### Mikrotik Domain (`app/Domain/Hotspot/`)

**Interface**: `MikrotikApiInterface` provides methods for:
- User management (create, remove, suspend, resume)
- Session control (get active sessions, disconnect users)
- System monitoring (AP interface load, ping)

**Service**: `MikrotikApiService` implements the interface using the `evilfreelancer/routeros-api-php` package with:
- Real RouterOS API integration
- Fake mode for development (`MIKROTIK_FAKE=true` or `host=demo`)
- Comprehensive error handling and logging
- Automatic connection management

**DTOs**: 
- `HotspotUserProvisionData` - User creation parameters
- `MikrotikUserResult` - User creation results

### Billing Domain (`app/Domain/Billing/`)

**Interface**: `PaymentGatewayInterface` provides methods for:
- Payment initiation
- Transaction verification  
- Webhook callback parsing

**Services**:
- `SerdiPayGateway` - SerdiPay API integration with fake mode support
- `PaymentService` - Orchestrates payment workflows with status transitions

**DTOs**:
- `InitiatePaymentResponse` - Payment initiation results
- `VerifyPaymentResult` - Transaction verification results
- `CallbackParseResult` - Webhook callback parsing results

### Fake Mode Support

Both domains support fake mode for development and testing:
- **Mikrotik**: Set `MIKROTIK_FAKE=true` or use `host=demo`
- **SerdiPay**: Set `SERDIPAY_FAKE=true` (default)

Fake mode returns realistic simulated data without making external API calls.

### Testing

Unit tests are included for all services using Pest PHP:
```bash
# Run all domain tests
php artisan test --filter=Domain

# Run specific domain tests
php artisan test tests/Unit/Domain/Hotspot/
php artisan test tests/Unit/Domain/Billing/
```

## Configuration

### Étape 5 – Provisioning & Events

The application implements a complete hotspot provisioning system with event-driven architecture:

#### Provisioning Flow
```
Payment SUCCESS → PaymentSucceeded Event → OnPaymentSucceededProvisionOrder Listener
                                                            ↓
Order Status: processing → Create HotspotUsers → HotspotUserProvisioned Events
                                                            ↓
                    Credentials sent via NotificationService → OrderCompleted Event
```

#### Components

**Core Services:**
- `HotspotProvisioningService`: Main provisioning logic for creating hotspot users from orders
- `NotificationService`: Abstract notification service supporting SMS, Email, and WhatsApp channels

**Events & Listeners:**
- `PaymentSucceeded` → `OnPaymentSucceededProvisionOrder`: Triggers provisioning when payment succeeds
- `HotspotUserProvisioned` → `OnHotspotUserProvisionedSendCredentials`: Sends user credentials
- `OrderCompleted` → `OnOrderCompletedSendSummary`: Sends order completion summary (placeholder)

**Key Features:**
- Configurable username patterns (default: `HS{timestamp}{index}`)
- Secure password generation (10 chars alphanum by default)
- Partial failure handling with metadata
- Mikrotik API integration with fake mode support
- Multiple notification channels (SMS, Email)

#### Configuration
```bash
# Provisioning settings
HOTSPOT_USERNAME_PREFIX=HS
HOTSPOT_PASSWORD_LENGTH=10

# Notification settings  
NOTIFY_DEFAULT_CHANNEL=sms
```

#### Future Commands (TODO)
```bash
# Reprovision a specific order
php artisan hotspot:provision-order {orderId}
```

#### Testing
```bash
# Run provisioning-related tests
php artisan test --filter=Provisioning

# Test the complete flow
php artisan migrate:fresh --seed
php artisan test tests/Feature/ProvisioningFlowTest.php
```

### MikroTik Setup
Configure your MikroTik router settings in `.env`:
```env
MIKROTIK_HOST=192.168.88.1
MIKROTIK_PORT=8728
MIKROTIK_USERNAME=admin
MIKROTIK_PASSWORD=secret
MIKROTIK_USE_SSL=false
```

### SerdiPay Setup
Configure your SerdiPay credentials in `.env`:
```env
SERDIPAY_BASE_URL=https://api.serdipay.com
SERDIPAY_PUBLIC_KEY=your_public_key
SERDIPAY_SECRET_KEY=your_secret
SERDIPAY_WEBHOOK_SECRET=webhook_secret_signature
```

## Étape 6 – Scheduler & Monitoring

The application implements a complete background job system with monitoring capabilities for automated hotspot management.

### Scheduled Jobs

The following background jobs run automatically via Laravel's scheduler:

#### Job Descriptions

**SyncMikrotikUsersJob** (*/10 * * * *)
- Synchronizes user data from MikroTik router with local database
- Logs unknown usernames for future mapping
- Execution time tracking and error handling

**SyncActiveSessionsJob** (*/2 * * * *)
- Retrieves active sessions from MikroTik router
- Creates/updates session records in hotspot_sessions table
- Automatically closes sessions no longer active on router
- Tracks data usage (upload/download MB)

**UpdateExpiredHotspotUsersJob** (*/15 * * * *)
- Marks hotspot users as expired based on expired_at timestamp
- Updates user status from active → expired
- Preserves user data for reporting purposes

**ReconcilePaymentsJob** (*/5 * * * *)
- Verifies pending payment status with gateway
- Processes payments in configurable batches (default: 50)
- Updates payment status and triggers provisioning

**DispatchPendingNotificationsJob** (* * * * *)
- Sends queued notifications via SMS/Email
- Processes notifications in batches (default: 50)
- Updates notification status (sent/failed)

**PruneOldLogsJob** (0 2 * * *)
- Removes old log entries from database
- Configurable retention period (default: 30 days)
- Runs daily at 2 AM

### Manual Job Execution

Execute jobs manually via artisan commands:

```bash
# Sync users from MikroTik
php artisan hotspot:sync-users

# Sync active sessions
php artisan hotspot:sync-sessions

# Mark expired users
php artisan hotspot:expire-users

# Reconcile payment status
php artisan billing:reconcile-payments --batch-size=25

# Dispatch pending notifications  
php artisan notifications:dispatch-pending --batch-size=100

# Prune old logs
php artisan logs:prune --days=7

# Display system metrics
php artisan monitoring:print-metrics
```

### Scheduler Configuration

Configure job schedules via environment variables:

```env
# CRON expressions for job scheduling
CRON_SYNC_USERS="*/10 * * * *"
CRON_SYNC_SESSIONS="*/2 * * * *"
CRON_EXPIRE_USERS="*/15 * * * *"
CRON_RECONCILE_PAYMENTS="*/5 * * * *"
CRON_DISPATCH_NOTIFICATIONS="* * * * *"
CRON_PRUNE_LOGS="0 2 * * *"

# Batch sizes for job processing
BILLING_RECONCILE_BATCH_SIZE=50
NOTIFY_DISPATCH_BATCH=50
LOG_PRUNE_AFTER_DAYS=30
```

### Running the Scheduler

Start the scheduler for continuous job processing:

```bash
# Production: Add to crontab
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1

# Development: Run worker process
php artisan schedule:work
```

### Monitoring System

The application provides comprehensive monitoring capabilities:

#### Admin Monitoring Endpoints

**GET /admin/monitoring/metrics** (Admin only)
```json
{
  "global": {
    "total_users": 150,
    "active_users": 140,
    "hotspot_users": 89,
    "active_hotspot_users": 45,
    "user_profiles_active": 3,
    "orders_last_24h": 12,
    "revenue_last_24h": 240.00,
    "active_sessions_count": 23,
    "payments_pending": 3,
    "notifications_queued": 0
  },
  "system": {
    "memory_usage": {
      "current": 33554432,
      "peak": 35651584,
      "formatted": {
        "current": "32 MB",
        "peak": "34 MB"
      }
    },
    "queue_pending": 5,
    "server_load": "TODO: implement server load monitoring"
  },
  "timestamp": "2025-08-15T18:00:00.000000Z"
}
```

**GET /admin/monitoring/interfaces** (Admin only)
```json
{
  "interfaces": [
    {
      "interface": "wlan1",
      "connected_users": 15,
      "last_sync_at": "2025-08-15T18:00:00.000000Z"
    },
    {
      "interface": "wlan2", 
      "connected_users": 8,
      "last_sync_at": "2025-08-15T18:00:00.000000Z"
    }
  ],
  "timestamp": "2025-08-15T18:00:00.000000Z"
}
```

#### Command Line Monitoring

```bash
# Display formatted system metrics
php artisan monitoring:print-metrics

# View scheduled jobs status
php artisan schedule:list

# Monitor queue status
php artisan queue:monitor redis:default --max=100
```

### Testing Jobs & Monitoring

```bash
# Run job-specific tests
php artisan test --filter=Jobs

# Run monitoring tests  
php artisan test --filter=Monitoring

# Test scheduler configuration
php artisan schedule:test

# Test specific job manually
php artisan test tests/Feature/Jobs/SyncActiveSessionsJobTest.php
```

### Troubleshooting

**Common Issues:**

1. **Jobs not running:** Verify scheduler is active (`php artisan schedule:work`)
2. **MikroTik connection errors:** Enable fake mode (`MIKROTIK_FAKE=true`) for testing
3. **Payment reconciliation fails:** Check gateway credentials and network connectivity
4. **Memory issues:** Adjust batch sizes in job configuration
5. **Permission errors:** Ensure proper file permissions for log directories

**Debugging:**

```bash
# View application logs
tail -f storage/logs/laravel.log

# Debug specific job
php artisan queue:work --timeout=300 --tries=1 --verbose

# Check queue status
php artisan queue:monitor
```

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Run tests: `composer test`
5. Check code style: `composer lint`
6. Submit a pull request

## Étape 7 – API v1

### Endpoints principaux

L'API REST v1 est accessible via le préfixe `/api/v1/` et utilise l'authentification Sanctum avec tokens Bearer.

#### Authentification
- `POST /api/v1/auth/login` - Connexion (email ou téléphone + mot de passe)
- `POST /api/v1/auth/logout` - Déconnexion (révoque le token actuel)
- `GET /api/v1/me` - Profil de l'utilisateur connecté

#### Endpoints publics
- `GET /api/v1/user-profiles` - Liste des profils actifs (aucune authentification requise)

#### Endpoints utilisateur (authentification requise)
- `GET|POST /api/v1/orders` - Gestion des commandes
- `GET /api/v1/orders/{order}` - Détails d'une commande
- `GET /api/v1/payments/{payment}` - Détails d'un paiement
- `POST /api/v1/payments/{order}/initiate` - Initier un paiement
- `GET /api/v1/hotspot-users` - Comptes hotspot de l'utilisateur
- `GET /api/v1/sessions` - Sessions de l'utilisateur
- `GET /api/v1/notifications` - Notifications de l'utilisateur

#### Endpoints admin (rôle admin requis)
- `GET /api/v1/admin/metrics` - Métriques système
- `GET /api/v1/admin/orders` - Toutes les commandes
- `GET /api/v1/admin/payments` - Tous les paiements

#### Callbacks
- `POST /api/v1/payments/callback/serdipay` - Webhook SerdiPay (aucune auth, vérification signature)

### Format de réponse

Toutes les réponses API utilisent un format JSON standardisé :

```json
{
  "success": true|false,
  "data": <données>|null,
  "meta": {
    "pagination": {...}, // si applicable
    "code": "..." // code d'erreur si applicable
  },
  "errors": null|<erreurs validation>
}
```

### Rate limiting par rôle

- **Admin** : 600 requêtes/minute
- **Agent** : 300 requêtes/minute  
- **User** : 120 requêtes/minute
- **Auth endpoints** : 30 requêtes/minute (protection brute force)

### TODO

- Versioning complet de l'API
- Documentation OpenAPI exhaustive
- Refresh token flow
- Webhooks externes complets
- Pagination avancée cursor-based

### Tests

```bash
# Tester l'API v1
php artisan test --filter=Api\V1

# Étape suivante : Étape 8 (UI Livewire admin + portail utilisateur)
```

## ✅ Étape 9: Advanced Reporting & Real-time Charts (Completed)

The advanced reporting system has been implemented with modular report builders, async exports, and real-time capabilities.

### Core Reporting Features
- [x] **Modular Report Builders** - Interface-based system with 4 initial reports
- [x] **Async Export System** - CSV/PDF generation with secure downloads
- [x] **Caching Layer** - Configurable TTL for report results
- [x] **Daily Snapshots** - Automated metric archiving with scheduler
- [x] **Broadcasting Events** - Real-time updates for exports and metrics
- [x] **Secure Downloads** - Signed URLs with permission validation

### Available Reports

#### 1. Orders Summary Report (`orders_summary`)
**Columns:** date, orders_count, total_amount, avg_amount  
**Filters:** date_from, date_to (default: last 7 days)  
**Description:** Daily aggregated summary of orders with count and financial metrics

#### 2. Payments Status Breakdown (`payments_status_breakdown`)
**Columns:** status, count, total_amount (SUCCESS sum net_amount)  
**Filters:** date_from, date_to  
**Description:** Breakdown of payments by status with count and total amounts

#### 3. Hotspot Usage Report (`hotspot_usage`)
**Columns:** user_profile, users_created, active_sessions, data_sum_mb  
**Filters:** date_from, date_to  
**Description:** Usage statistics by user profile including users and session data

#### 4. User Growth Report (`user_growth`)
**Columns:** date, new_users  
**Filters:** date_from, date_to  
**Description:** Daily user registration statistics showing growth over time

### Export System

#### Configuration
```bash
# .env configuration
REPORTING_CACHE_TTL=300           # Cache TTL in seconds
EXPORTS_RETENTION_DAYS=7          # Export file retention
EXPORTS_MAX_ROWS=50000           # Maximum rows per report
```

#### Usage Flow
```bash
# 1. Access reports interface
GET /admin/reports

# 2. View specific report with filters
GET /admin/reports/orders_summary

# 3. Request export (queues background job)
POST /admin/reports/orders_summary/export
{
  "format": "csv",
  "filters": {
    "date_from": "2024-01-01",
    "date_to": "2024-01-31"
  }
}

# 4. Download completed export (signed URL)
GET /admin/exports/{export}/download
```

#### Background Jobs & Scheduling
```bash
# Daily metric snapshots (01:05)
php artisan queue:work --job=SnapshotDailyMetricsJob

# Export processing (on-demand)
php artisan queue:work --job=ProcessExportJob

# Cleanup old exports (02:15 daily)
php artisan queue:work --job=PurgeOldExportsJob
```

### Technical Architecture

#### Domain Layer Structure
```
app/Domain/Reporting/
├── Contracts/ReportBuilderInterface.php
├── DTO/
│   ├── ReportResult.php
│   └── ExportRequestData.php
├── Services/
│   ├── AbstractReportBuilder.php
│   ├── ReportRegistry.php
│   ├── ExportService.php
│   └── ReportCacheService.php
├── Builders/
│   ├── OrdersSummaryReportBuilder.php
│   ├── PaymentsStatusBreakdownReportBuilder.php
│   ├── HotspotUsageReportBuilder.php
│   └── UserGrowthReportBuilder.php
├── Events/
│   ├── ExportCompleted.php
│   └── MetricsUpdated.php
└── Exceptions/ReportException.php
```

#### Database Schema
```sql
-- Exports tracking
CREATE TABLE exports (
    id BIGINT PRIMARY KEY,
    report_key VARCHAR(255) INDEX,
    format VARCHAR(10),
    status VARCHAR(20) INDEX,
    requested_by BIGINT FOREIGN KEY,
    filters JSON,
    total_rows INT,
    file_path VARCHAR(255),
    error_message TEXT,
    started_at TIMESTAMP,
    finished_at TIMESTAMP,
    meta JSON,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Daily metric snapshots
CREATE TABLE metric_snapshots (
    id BIGINT PRIMARY KEY,
    snapshot_date DATE,
    metric_key VARCHAR(255),
    value JSON,
    created_at TIMESTAMP,
    UNIQUE(snapshot_date, metric_key)
);
```

### Broadcasting & Real-time Updates

#### Events
- **ExportCompleted** - Notifies when export processing finishes
- **MetricsUpdated** - Broadcasts metric updates for dashboards

#### Channels
- `private-admin-exports` - Export status updates
- `private-admin-metrics` - Real-time metric updates

### Testing

```bash
# Run reporting tests
php artisan test --filter=Reporting

# Run specific test suites
php artisan test tests/Unit/Reporting/
php artisan test tests/Feature/Reporting/
php artisan test tests/Feature/Jobs/
php artisan test tests/Feature/Livewire/ReportsViewerTest.php
```

### Performance & Limitations

- **Caching:** Report results cached for 5 minutes (configurable)
- **Row Limit:** Maximum 50,000 rows per report (prevents memory issues)
- **Export Retention:** Files automatically purged after 7 days
- **Format Support:** CSV (lightweight) and PDF (formatted) exports
- **Security:** Signed download URLs with ownership validation

### Future Enhancements (TODO)

- [ ] **WebSocket Production Scaling** - Redis pub/sub for multi-server setups
- [ ] **Advanced Charts** - ApexCharts integration for dynamic visualizations  
- [ ] **Complex Filtering** - Multi-criteria joins and advanced query builders
- [ ] **Excel Export** - XLSX format support via maatwebsite/excel
- [ ] **Report Scheduling** - Automated recurring exports via email
- [ ] **Custom Report Builder** - UI for creating ad-hoc reports

### Example Usage

```php
// Register a new report builder
$registry = app(\App\Domain\Reporting\Services\ReportRegistry::class);
$registry->register(new CustomReportBuilder());

// Request an export programmatically
$exportService = app(\App\Domain\Reporting\Services\ExportService::class);
$export = $exportService->requestExport(
    'orders_summary',
    'pdf',
    ['date_from' => '2024-01-01', 'date_to' => '2024-01-31'],
    auth()->user()
);

// Manual snapshot creation
php artisan queue:dispatch "App\Jobs\SnapshotDailyMetricsJob" --arguments="2024-01-01"
```

---

## ✅ Étape 10: Alerting & Webhooks & SLA (Completed)

### Système d'Alertes Techniques

The application now includes a comprehensive alerting system with support for multiple channels and automatic incident management.

#### Architecture

```
app/Domain/Alerting/
├── Contracts/AlertChannelInterface.php
├── DTO/AlertMessage.php
├── Channels/
│   ├── SlackAlertChannel.php
│   └── EmailAlertChannel.php
└── Services/AlertDispatcher.php
```

#### Alert Channels

- **Slack Channel**: Sends rich formatted alerts to Slack webhooks with severity-based colors
- **Email Channel**: Sends structured email alerts to operations team
- **Severity Filtering**: Configurable minimum severity levels per channel

#### Configuration

```php
// config/alerting.php
return [
    'channels' => ['slack', 'email'],
    'severity_email_min' => 'high',
    'severity_slack_min' => 'medium',
    'slack_webhook_url' => env('SLACK_ALERT_WEBHOOK'),
];
```

### Webhooks Sortants

Robust webhook system for external integrations with retry logic and signature verification.

#### Supported Events

- `PaymentSucceeded` - Payment completion notifications
- `HotspotUserProvisioned` - User provisioning events
- `OrderCompleted` - Order fulfillment updates
- `ExportCompleted` - Report export completion
- `IncidentStatusChanged` - Incident lifecycle updates

#### Webhook Features

- **Signature Verification**: HMAC-SHA256 signatures in `X-Hub-Signature-Sha256` header
- **Retry Logic**: Exponential backoff (1m, 5m, 30m, 2h, 6h) with max 5 attempts
- **Payload Filtering**: Sensitive data automatically filtered from webhooks
- **Endpoint Management**: CRUD operations for webhook endpoints via admin UI

#### Configuration

```php
// config/webhooks.php
return [
    'max_retries' => 5,
    'retry_schedule_minutes' => [1, 5, 30, 120, 360],
    'timeout_seconds' => 8,
    'signature_header' => 'X-Hub-Signature-Sha256',
];
```

### SLA Metrics & Health Monitoring

#### Tracked Metrics

- `mikrotik.ping_ms` - MikroTik connectivity latency
- `payment.initiate_latency_ms` - Payment initiation performance
- `payment.failure_rate` - Payment failure rate monitoring
- `provisioning.error_rate` - Hotspot provisioning errors

#### Anomaly Detection Rules

- **Payment Failure Rate**: Alert when >40% failures over 10 minutes
- **MikroTik Unreachable**: Alert after 3 consecutive ping failures >1s
- **Provisioning Spike**: Alert when >5 partial failures in 15 minutes

#### Health Dashboard

Access via `/admin/health` - provides real-time system health overview:

- MikroTik connectivity status
- Payment success rates (24h)
- Provisioning error rates
- Open incidents count
- Pending webhook attempts

### Gestion d'Incidents

#### Automatic Incident Creation

- High/Critical severity alerts auto-create incidents
- Prevents duplicate incidents (30-minute window)
- Includes alert context and detection source

#### Incident Lifecycle

- **Status Flow**: Open → Monitoring → Mitigated → Resolved/False Positive
- **Timeline**: All status changes and updates tracked
- **Metadata**: Rich context from originating alerts

#### Admin Interface

- `/admin/incidents` - List and filter incidents
- `/admin/incidents/{incident}` - Detailed view with timeline
- Status updates and manual updates with user attribution

### Commandes Artisan

```bash
# Retry failed webhook attempts
php artisan webhooks:retry-failed --limit=50

# Filter by specific endpoint or event
php artisan webhooks:retry-failed --endpoint=1 --event=PaymentSucceeded
```

### Environment Variables

```bash
# Alerting Configuration
SLACK_ALERT_WEBHOOK=https://hooks.slack.com/services/...
ALERT_EMAIL_TO=ops@example.com
ALERT_EMAIL_FROM=alerts@example.com
ALERT_EMAIL_SUBJECT_PREFIX="[ALERT]"

# Webhook Configuration
WEBHOOKS_MAX_RETRIES=5
```

### Testing Alerting & Webhooks

```bash
# Test all alerting components
php artisan test --filter=Alerting

# Test webhook system
php artisan test --filter=Webhook

# Test incident management
php artisan test --filter=Incident
```

### Database Schema

```sql
-- Incidents tracking
CREATE TABLE incidents (
    id BIGINT PRIMARY KEY,
    title VARCHAR(255),
    slug VARCHAR(255) UNIQUE,
    status VARCHAR(30) INDEX,
    severity VARCHAR(20) INDEX,
    started_at TIMESTAMP,
    detection_source VARCHAR(255),
    meta JSON
);

-- Webhook endpoints configuration
CREATE TABLE webhook_endpoints (
    id BIGINT PRIMARY KEY,
    name VARCHAR(255),
    url VARCHAR(255),
    secret VARCHAR(255),
    is_active BOOLEAN DEFAULT true,
    event_types JSON,
    failure_count INTEGER DEFAULT 0
);

-- SLA metrics storage
CREATE TABLE sla_metrics (
    id BIGINT PRIMARY KEY,
    metric_key VARCHAR(255) INDEX,
    value DOUBLE,
    captured_at TIMESTAMP INDEX,
    meta JSON
);
```

### TODO / Extensions futures

- **Intégration PagerDuty/OpsGenie**: Escalation automatique des incidents critiques
- **OpenTelemetry**: Traces distribuées pour l'observabilité
- **Dashboard temps réel**: WebSockets pour les mises à jour live
- **Analytics avancées**: Tendances et prédictions basées sur les métriques SLA

---

## ✅ Étape 11: Observability & Scaling (Completed)

Couche d'observabilité unifiée, optimisations de performance et préparation à la montée en charge avec résilience avancée.

### 🔍 Observabilité

- **Traces & Corrélation**
  - Middleware de corrélation (`X-Request-Id`) pour traçage des requêtes
  - Configuration OpenTelemetry basique (traces OTLP + sampling configurable)  
  - Injection du contexte de trace dans les logs structurés
  - Support des attributs de span pour les services critiques

- **Métriques Prometheus**
  - Endpoint `/internal/metrics` au format Prometheus (protégé par token)
  - Métriques système : utilisateurs, commandes, revenus, mémoire, queues
  - Métriques SLA : latence MikroTik, taux de succès paiements, erreurs provisioning
  - Métriques queue : jobs en attente par queue, jobs échoués, temps de traitement

- **Logging Structuré**
  - Canal logging JSON dédié avec processeur de sanitisation
  - Helper `StructuredLog` avec filtrage automatique des données sensibles
  - Listener slow queries (seuil configurable DB_SLOW_MS)
  - Injection automatique correlation_id, user_id, trace context

### ⚡ Performance & Base de Données

- **Audit & Optimisation Index**
  - Commande `db:audit-indexes` analysant le schéma et proposant des optimisations
  - Index composites suggérés : `payments(status,created_at)`, `logs(level,created_at)`, etc.
  - Migrations prêtes à l'emploi pour les index manquants

- **Caching Multi-Couches**
  - TTL spécifiques par type : profiles_list(5m), metrics(1m), feature_flags(1m)
  - Cache warming automatique (`cache:warm`) des données critiques
  - Middleware `ApiCacheMiddleware` avec support ETag/If-None-Match
  - Invalidation ciblée lors des modifications

### 🛡️ Résilience & Scalabilité  

- **Gestion Queues Avancée**
  - Multi-queues : critical, high, default, low, reporting
  - Monitoring temps réel (`QueueLoadMonitor`) avec alertes automatiques
  - Job `MonitorQueuesJob` surveillant backlog et jobs anciens
  - Seuils configurables : QUEUE_CRITICAL_MAX, QUEUE_CRITICAL_AGE_MAX

- **Health Checks Complets**
  - `/health/live` (liveness), `/health/ready` (readiness), `/health/summary`
  - Vérifications : Database, Redis, Queue backlog, MikroTik ping, Payment latency
  - Format JSON standard pour intégration load balancer/Kubernetes

- **Feature Flags**
  - Système complet avec model FeatureFlag et façade `Feature::enabled()`
  - Gestion CLI : `feature:enable`, `feature:disable`, `feature:list`
  - Cache des flags avec invalidation automatique

### 🚦 Rate Limiting Adaptatif

- **Limites Dynamiques**
  - Calcul adaptatif basé sur charge système (queue depth, CPU, mémoire)
  - Limites de base par rôle : user(120/min), admin(600/min), guest(60/min)
  - Facteurs d'ajustement selon la charge : low(1.5x), high(0.7x), critical(0.3x)
  - Headers Retry-After dynamiques

### 🔒 Sécurité & Hardening

- **Headers Sécurité**
  - CSP baseline, X-Frame-Options, X-Content-Type-Options
  - Referrer-Policy, Permissions-Policy, HSTS (si HTTPS)
  - Protection XSS et MIME sniffing

- **Protection Brute Force** (TODO)
  - Rate limiting auth endpoints (30 req/min)
  - Stockage tentatives par IP dans Redis/cache
  - Blocage progressif selon configuration

- **Chaos Engineering**
  - Middleware injection erreurs/latence (staging uniquement)
  - Configuration probabiliste : error_rate, latency_range, timeout_rate
  - Commande `chaos:toggle` pour activation/désactivation

### 📊 Tests & Documentation

- **Tests Non-Fonctionnels**
  - Script k6 `scripts/load/basic.js` avec scénarios représentatifs
  - Profile baseline `docs/load/profile-baseline.json` exemple
  - Tests feature flags, health endpoints, queue monitoring

- **Documentation Technique**
  - Architecture observabilité complète `docs/architecture/observability.md`
  - Configuration Prometheus/Grafana (TODO placeholders)
  - Procédures de rotation secrets (TODO)

### Configuration

```bash
# Observabilité
OTEL_TRACES_ENABLED=false
OTEL_TRACES_SAMPLER_RATIO=0.2
INTERNAL_METRICS_TOKEN=changeme

# Performance  
DB_SLOW_MS=120
CACHE_WARM_ENABLED=true

# Résilience
QUEUE_CRITICAL_MAX=100
ADAPTIVE_RATE_BASE_USER=120

# Chaos (staging uniquement)
CHAOS_ENABLED=false
CHAOS_ERROR_RATE=0.05
```

### Commandes

```bash
# Cache & Performance
php artisan cache:warm
php artisan db:audit-indexes

# Feature Flags
php artisan feature:enable key --meta='{"desc":"feature"}'
php artisan feature:list

# Load Testing
k6 run scripts/load/basic.js

# Monitoring
curl -H "Authorization: Bearer token" /internal/metrics
```

### TODO Extensions Futures

- **OpenTelemetry Complet** : Instrumentation automatique HTTP/DB/Queue
- **Métriques Business** : Revenue, growth rate, SLI/SLO tracking  
- **Sécurité Avancée** : Rotation secrets, protection brute force complète
- **Scaling** : Policies auto-scaling, circuit breakers, load balancer integration

---

## Validation & Prochaines Étapes

```bash
# Pour valider : php artisan migrate --path=database/migrations/*_create_feature_flags_table.php
# Pour warm cache : php artisan cache:warm
# Pour tester : php artisan test --filter=Observability
# Étape suivante potentielle : Étape 12 (Scaling multi-région, SLO & Burn Rate, Autoscaling policies)
```

## License

This project is open-sourced software licensed under the [MIT license](LICENSE).