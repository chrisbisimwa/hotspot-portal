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

## License

This project is open-sourced software licensed under the [MIT license](LICENSE).