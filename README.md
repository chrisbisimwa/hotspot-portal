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

3. **Database setup**
   ```bash
   touch database/database.sqlite
   php artisan migrate
   ```

4. **Build assets and start development**
   ```bash
   npm run dev
   php artisan serve
   ```

## Development Roadmap

### ✅ Étape 1: Base technique (Current)
- [x] Dependencies setup (Composer + NPM)
- [x] AdminLTE 3 + Bootstrap 4.6 integration
- [x] Vite configuration
- [x] Base layouts and partials
- [x] Route structure with admin protection
- [x] Environment configuration
- [x] Basic testing setup

### 🚧 Étape 2: Migrations & Enums (Next)
- [ ] User profiles migration
- [ ] Hotspot users migration  
- [ ] Sessions tracking migration
- [ ] Orders and payments migrations
- [ ] Notification system migrations
- [ ] Enum classes for user roles, payment status, etc.

### 🔜 Étape 3: Models & Relationships
- [ ] User model extensions
- [ ] HotspotUser model
- [ ] Session model
- [ ] Order and Payment models
- [ ] Notification model
- [ ] Model relationships and factories

### 🔜 Étape 4: Seeders & Sample Data
- [ ] Role and permission seeder
- [ ] Admin user seeder
- [ ] Sample hotspot profiles
- [ ] Demo data for testing

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