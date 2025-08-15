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

### 🔜 Étape 4: Models & Relationships (Next)
- [ ] User model extensions
- [ ] HotspotUser model
- [ ] Session model
- [ ] Order and Payment models
- [ ] Notification model
- [ ] Model relationships validation

### 🔜 Étape 5: Authentication & Authorization
- [ ] Sanctum API authentication
- [ ] Role-based access control
- [ ] User registration flow
- [ ] Password reset functionality

### 🔜 Étape 6: Core Services
- [ ] MikroTik RouterOS integration
- [ ] User provisioning service
- [ ] Session monitoring service
- [ ] Payment processing (SerdiPay)

### 🔜 Étape 7: Admin Interface (Livewire)
- [ ] Dashboard with statistics
- [ ] User management interface
- [ ] Session monitoring
- [ ] Payment management
- [ ] System configuration

### 🔜 Étape 8: User Interface
- [ ] User dashboard
- [ ] Profile management
- [ ] Package selection
- [ ] Payment interface

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

## Configuration

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

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Run tests: `composer test`
5. Check code style: `composer lint`
6. Submit a pull request

## License

This project is open-sourced software licensed under the [MIT license](LICENSE).