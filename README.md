# HotspotPortal

A comprehensive hotspot management portal built with Laravel 12, designed for managing Mikrotik-based hotspot services with integrated payment processing.

## Tech Stack

- **Backend**: Laravel 12 (PHP 8.3)
- **Frontend**: Livewire 3 + AdminLTE 3 + Bootstrap 4.6
- **Authentication**: Laravel Sanctum
- **Permissions**: Spatie Laravel Permission
- **UI Framework**: AdminLTE 3.2.0
- **CSS Framework**: Bootstrap 4.6.2
- **JavaScript**: jQuery, Chart.js, SweetAlert2
- **RouterOS Integration**: EvilFreelancer RouterOS API PHP
- **Payment Gateway**: SerdiPay
- **Testing**: Pest PHP

## Quick Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd hotspot-portal
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install Node.js dependencies**
   ```bash
   npm install
   ```

4. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Database setup**
   ```bash
   touch database/database.sqlite
   php artisan migrate
   ```

6. **Build assets**
   ```bash
   npm run dev
   ```

7. **Start development server**
   ```bash
   php artisan serve
   ```

## Development Roadmap

### ✅ Stage 1: Technical Foundation (Current)
- [x] Laravel 12 base setup
- [x] AdminLTE 3 integration
- [x] Bootstrap 4.6 configuration
- [x] Livewire 3 setup
- [x] Basic layouts and partials
- [x] Vite configuration for SCSS/JS
- [x] Composer and NPM dependencies
- [x] PSR-12 compliance (.editorconfig)
- [x] Basic routing structure
- [x] Environment configuration

### 🔄 Stage 2: Database & Models (Next)
- [ ] User profiles migration
- [ ] Hotspot users migration
- [ ] Sessions tracking migration
- [ ] Orders and payments migrations
- [ ] Notifications migration
- [ ] System settings migration
- [ ] Create Eloquent models
- [ ] Define relationships
- [ ] Create enums for status fields

### 📋 Stage 3: Authentication & Authorization
- [ ] Implement Laravel Sanctum
- [ ] Setup Spatie Permission roles and permissions
- [ ] Create admin seeder
- [ ] Authentication UI (login/register)
- [ ] Role-based middleware
- [ ] API token management

### 🏗️ Stage 4: Core Services
- [ ] Mikrotik RouterOS API integration
- [ ] User profile management service
- [ ] Hotspot user provisioning service
- [ ] Session monitoring service
- [ ] Payment processing with SerdiPay
- [ ] Notification service (SMS/Email)

### 🎨 Stage 5: Admin Interface (Livewire Components)
- [ ] Dashboard with statistics
- [ ] User management CRUD
- [ ] Hotspot user management
- [ ] Session monitoring
- [ ] Order management
- [ ] Payment tracking
- [ ] System monitoring
- [ ] Settings management

### 📱 Stage 6: User Portal
- [ ] User registration/login
- [ ] Profile management
- [ ] Package selection
- [ ] Payment interface
- [ ] Usage monitoring
- [ ] Session history

### 🔧 Stage 7: Advanced Features
- [ ] API endpoints for mobile apps
- [ ] Queue processing with Horizon
- [ ] Advanced reporting
- [ ] Backup and restore
- [ ] Multi-language support
- [ ] Advanced monitoring

## TODO List

### High Priority
- [ ] **Authentication System**: Implement Laravel Sanctum for API authentication
- [ ] **Authorization**: Setup role-based permissions with Spatie Permission
- [ ] **Mikrotik Integration**: Implement RouterOS API for hotspot user provisioning
- [ ] **Payment Gateway**: Integrate SerdiPay for payment processing
- [ ] **Database Design**: Create migrations for all business entities

### Medium Priority
- [ ] **Monitoring System**: Real-time access point monitoring
- [ ] **Notification System**: SMS/Email notifications for users and admins
- [ ] **Reporting**: Generate usage and financial reports
- [ ] **API Documentation**: Document all API endpoints

### Low Priority
- [ ] **Mobile App API**: Prepare APIs for future mobile applications
- [ ] **Advanced Analytics**: Detailed usage analytics and insights
- [ ] **Multi-tenancy**: Support for multiple hotspot locations

## Configuration

### Mikrotik Setup
Configure your Mikrotik router details in `.env`:
```env
MIKROTIK_HOST=192.168.88.1
MIKROTIK_PORT=8728
MIKROTIK_USERNAME=admin
MIKROTIK_PASSWORD=secret
MIKROTIK_USE_SSL=false
```

### SerdiPay Integration
Add SerdiPay credentials to `.env`:
```env
SERDIPAY_BASE_URL=https://api.serdipay.com
SERDIPAY_PUBLIC_KEY=your_public_key
SERDIPAY_SECRET_KEY=your_secret_key
SERDIPAY_WEBHOOK_SECRET=webhook_secret
```

## Contributing

1. Follow PSR-12 coding standards
2. Run tests before submitting: `composer test`
3. Lint code with Pint: `composer lint`
4. Write tests for new features

## License

This project is licensed under the MIT License.