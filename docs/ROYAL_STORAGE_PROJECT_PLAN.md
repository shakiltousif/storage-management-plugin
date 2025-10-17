# Royal Storage Management Plugin - Project Plan

**Project:** WordPress Plugin for Storage & Parking Rental Management  
**Client:** Royal Storage (Storage Management Business)  
**Status:** [ ] Planning Phase  
**Last Updated:** 2025-10-16

---

## 📋 Executive Summary

A comprehensive WordPress/WooCommerce plugin for managing storage box and parking space rentals with:
- Multi-unit inventory management (Storage: M, L, XL + Parking)
- Automated booking system with availability management
- Bilingual support (Serbian + English)
- Payment integration with Serbian bank plugin
- Customer portal with self-service features
- Admin dashboard with reporting & analytics
- Serbian VAT (PDV 20%) compliance

---

## 🎯 Project Phases

### Phase 1: Foundation & Setup [ ]
- [ ] **1.1** Create plugin structure and architecture
  - Plugin main file with proper headers
  - Autoloader and namespace setup
  - Database schema design
  - Custom post types (Storage Unit, Parking Space, Booking, Invoice)
  - Custom taxonomies (Unit Size, Status)
  - **Estimated:** 4-5 hours

- [ ] **1.2** Database Schema & Custom Post Types
  - Storage units CPT with meta fields (size, dimensions, amenities, price)
  - Parking spaces CPT with meta fields (spot number, height limit, price)
  - Bookings CPT with meta fields (customer, unit, dates, status, payment)
  - Invoices CPT with meta fields (booking, amount, VAT, payment status)
  - **Estimated:** 3-4 hours

- [ ] **1.3** Admin Settings & Configuration
  - Settings page for business info (location, currency, VAT rate)
  - Pricing configuration (daily, weekly, monthly rates)
  - Email notification settings
  - Payment gateway configuration
  - **Estimated:** 3 hours

---

### Phase 2: Backend - Core Functionality [ ]

- [ ] **2.1** Unit Management System
  - CRUD operations for storage units and parking spaces
  - Availability tracking and status management
  - Pricing rules (daily, weekly, monthly)
  - Bulk operations for admin
  - **Estimated:** 5-6 hours

- [ ] **2.2** Booking Engine
  - Booking creation with automatic unit assignment (first available)
  - Date validation and prorating logic
  - Availability checking and conflict prevention
  - Booking status workflow (pending, confirmed, active, expired, cancelled)
  - **Estimated:** 6-7 hours

- [ ] **2.3** Pricing & Calculation Engine
  - Price calculation with prorating for mid-month starts
  - Discount application (coupon codes: 3+1, 4+1, 5+1, 10+2)
  - VAT calculation (20% PDV)
  - Late fee calculation for overdue bookings
  - **Estimated:** 4-5 hours

- [ ] **2.4** Payment Processing
  - WooCommerce integration for payment handling
  - Bank plugin integration (3D Secure support)
  - Payment status tracking
  - Invoice generation with Serbian legal format
  - Proforma invoice generation for "pay later" option
  - **Estimated:** 6-7 hours

- [ ] **2.5** Invoice & Document Management
  - Invoice generation with Serbian PDV format
  - Invoice numbering following Serbian regulations
  - PDF generation and storage
  - Proforma invoice system
  - Invoice status tracking
  - **Estimated:** 5-6 hours

---

### Phase 3: Backend - Admin Features [ ]

- [ ] **3.1** Admin Dashboard
  - Occupancy rate metrics
  - Free vs. reserved vs. overdue units display
  - Upcoming expiries widget
  - Overdue accounts widget
  - Quick stats (total revenue, active bookings, etc.)
  - **Estimated:** 4-5 hours

- [ ] **3.2** Reporting & Export
  - CSV export by object type (storage/parking)
  - CSV export by date range
  - CSV export by customer
  - CSV export by payment status
  - Report filtering and customization
  - **Estimated:** 4-5 hours

- [ ] **3.3** Manual Booking Management
  - Admin ability to create bookings for walk-in clients
  - Manual unit assignment
  - Manual payment recording
  - Booking modification and cancellation
  - **Estimated:** 3-4 hours

- [ ] **3.4** Customer Management
  - Customer list with search and filtering
  - Customer detail view with booking history
  - Customer contact information management
  - Customer communication history
  - **Estimated:** 3-4 hours

- [ ] **3.5** Notification Management
  - Email notification templates
  - Notification trigger configuration
  - Email log and history
  - Resend notification functionality
  - **Estimated:** 3-4 hours

---

### Phase 4: Frontend - Customer Portal [ ]

- [ ] **4.1** Customer Authentication & Account
  - Customer registration/login
  - Account profile management
  - Password reset functionality
  - Account security settings
  - **Estimated:** 3-4 hours

- [ ] **4.2** Booking Search & Selection
  - Unit type selection (Storage M/L/XL or Parking)
  - Availability calendar
  - Unit details display with images
  - Price calculation preview
  - **Estimated:** 5-6 hours

- [ ] **4.3** Checkout Process
  - Personal details form
  - Terms of Service acceptance
  - Payment method selection (online or pay later)
  - Order review and confirmation
  - Access code generation and display
  - **Estimated:** 5-6 hours

- [ ] **4.4** Customer Portal Dashboard
  - Active bookings display
  - Booking history
  - Renewal/extension functionality
  - Cancellation requests
  - Payment history
  - **Estimated:** 5-6 hours

- [ ] **4.5** Invoice & Document Management (Customer)
  - Invoice download (PDF)
  - Proforma invoice download
  - Payment history view
  - Document archive
  - **Estimated:** 3-4 hours

---

### Phase 5: Frontend - Booking Interface [ ]

- [ ] **5.1** Public Booking Page
  - Unit type selection interface
  - Date range picker
  - Availability display
  - Price calculation display
  - Call-to-action for booking
  - **Estimated:** 4-5 hours

- [ ] **5.2** Booking Form & Validation
  - Multi-step booking form
  - Form validation (client-side and server-side)
  - Error handling and user feedback
  - Progress indicator
  - **Estimated:** 4-5 hours

- [ ] **5.3** Payment Integration
  - Bank plugin payment form integration
  - 3D Secure handling
  - Payment success/failure handling
  - Payment confirmation display
  - **Estimated:** 4-5 hours

---

### Phase 6: Notifications & Communications [ ]

- [ ] **6.1** Email Notification System
  - Booking confirmation email
  - Payment confirmation email
  - Reservation confirmation (pay later)
  - 7 days before expiry reminder
  - Last paid day reminder
  - Overdue daily reminder
  - **Estimated:** 4-5 hours

- [ ] **6.2** Email Templates
  - Template creation and management
  - Variable substitution (customer name, dates, amounts, etc.)
  - HTML email formatting
  - Multilingual support (Serbian/English)
  - **Estimated:** 3-4 hours

- [ ] **6.3** Notification Scheduling
  - Cron job setup for scheduled notifications
  - Expiry reminder scheduling
  - Overdue reminder scheduling
  - Email queue management
  - **Estimated:** 3-4 hours

---

### Phase 7: Internationalization & Localization [ ]

- [ ] **7.1** i18n Setup
  - POT file generation
  - Serbian (Latin) translation file
  - English translation file
  - Translation string extraction
  - **Estimated:** 3-4 hours

- [ ] **7.2** Frontend Localization
  - Language switcher implementation
  - Dynamic language switching
  - Currency display (RSD)
  - Date/time formatting per locale
  - **Estimated:** 3-4 hours

- [ ] **7.3** Backend Localization
  - Admin interface translation
  - Email template translation
  - Report labels translation
  - **Estimated:** 2-3 hours

---

### Phase 8: Security & Compliance [ ]

- [ ] **8.1** Security Hardening
  - Nonce verification for all forms
  - Capability checks for all admin functions
  - Input sanitization and validation
  - SQL injection prevention
  - XSS protection
  - CSRF protection
  - **Estimated:** 4-5 hours

- [ ] **8.2** GDPR & Privacy
  - Privacy policy integration
  - Data export functionality
  - Data deletion functionality
  - Consent management
  - **Estimated:** 3-4 hours

- [ ] **8.3** reCAPTCHA Integration
  - reCAPTCHA v3 setup
  - Form protection (booking, contact)
  - Bot detection and prevention
  - **Estimated:** 2-3 hours

---

### Phase 9: Testing & QA [ ]

- [ ] **9.1** Unit Testing
  - Booking engine tests
  - Pricing calculation tests
  - Availability checking tests
  - Payment processing tests
  - **Estimated:** 6-8 hours

- [ ] **9.2** Integration Testing
  - WooCommerce integration tests
  - Bank plugin integration tests
  - Email notification tests
  - Database operation tests
  - **Estimated:** 5-6 hours

- [ ] **9.3** User Acceptance Testing
  - Admin workflow testing
  - Customer booking flow testing
  - Payment flow testing
  - Notification testing
  - **Estimated:** 4-5 hours

- [ ] **9.4** Performance & Security Testing
  - Load testing
  - Security vulnerability scanning
  - Performance optimization
  - Database query optimization
  - **Estimated:** 4-5 hours

---

### Phase 10: Deployment & Documentation [ ]

- [ ] **10.1** Staging Setup
  - Staging environment configuration
  - Database migration to staging
  - Plugin deployment to staging
  - Testing on staging environment
  - **Estimated:** 3-4 hours

- [ ] **10.2** Documentation
  - Admin user guide
  - Customer user guide
  - API documentation
  - Installation guide
  - Configuration guide
  - **Estimated:** 4-5 hours

- [ ] **10.3** Training & Handover
  - Admin training session
  - Documentation review
  - Support setup
  - **Estimated:** 2-3 hours

- [ ] **10.4** Production Deployment
  - Final testing
  - Backup creation
  - Plugin deployment
  - Post-deployment verification
  - **Estimated:** 2-3 hours

---

## 📊 Project Statistics

| Metric | Value |
|--------|-------|
| **Total Phases** | 10 |
| **Total Tasks** | 50+ |
| **Estimated Total Hours** | 180-220 hours |
| **Estimated Timeline** | 6-8 weeks (full-time) |
| **Team Size** | 1-2 developers |
| **Tech Stack** | WordPress, WooCommerce, PHP 8.x, MySQL, JavaScript |

---

## 🛠️ Technical Architecture

### Plugin Structure
```
royal-storage-plugin/
├── royal-storage-plugin.php          # Main plugin file
├── includes/
│   ├── class-plugin.php              # Main plugin class
│   ├── class-database.php            # Database operations
│   ├── class-booking-engine.php      # Booking logic
│   ├── class-pricing-engine.php      # Pricing calculations
│   ├── class-payment-handler.php     # Payment processing
│   ├── class-invoice-generator.php   # Invoice generation
│   └── class-notification-manager.php # Email notifications
├── admin/
│   ├── class-admin-dashboard.php     # Admin dashboard
│   ├── class-admin-settings.php      # Settings page
│   ├── class-admin-reports.php       # Reporting
│   └── templates/
│       ├── dashboard.php
│       ├── settings.php
│       └── reports.php
├── frontend/
│   ├── class-customer-portal.php     # Customer portal
│   ├── class-booking-form.php        # Booking form
│   └── templates/
│       ├── booking-search.php
│       ├── checkout.php
│       └── portal-dashboard.php
├── assets/
│   ├── css/
│   ├── js/
│   └── images/
├── languages/
│   ├── royal-storage.pot
│   ├── royal-storage-sr_RS.po
│   └── royal-storage-en_US.po
└── tests/
    ├── unit/
    ├── integration/
    └── fixtures/
```

### Database Schema
- **Storage Units** (CPT): ID, size, dimensions, amenities, price, status
- **Parking Spaces** (CPT): ID, spot_number, height_limit, price, status
- **Bookings** (CPT): ID, customer_id, unit_id, start_date, end_date, status, total_price, payment_status
- **Invoices** (CPT): ID, booking_id, amount, vat_amount, invoice_number, status
- **Customers** (User Meta): phone, address, company, tax_id

---

## 🔄 Development Workflow

1. **Setup** → Create plugin structure and database schema
2. **Backend Core** → Implement booking and pricing engines
3. **Admin Features** → Build admin dashboard and management tools
4. **Frontend** → Create customer portal and booking interface
5. **Integrations** → Connect WooCommerce and payment gateway
6. **Notifications** → Implement email system
7. **Localization** → Add multilingual support
8. **Testing** → Comprehensive QA
9. **Documentation** → Create user guides
10. **Deployment** → Launch to production

---

## ✅ Success Criteria

- [x] Plugin fully functional for storage and parking rentals
- [x] Admin can manage units, bookings, and customers
- [x] Customers can book units and manage reservations
- [x] Payment processing with 3D Secure
- [x] Automated email notifications
- [x] Serbian VAT compliance
- [x] Multilingual support (Serbian/English)
- [x] Comprehensive reporting and analytics
- [x] Security hardening and GDPR compliance
- [x] Full documentation and training

---

## 📝 Notes

- **Bank Plugin**: Will be provided by client (ZIP + docs)
- **SMTP Details**: To be provided by client
- **Hosting**: cPanel/SSH (to be confirmed)
- **Domain**: https://royalstorage.rs
- **Staging**: Required before production
- **Future Integrations**: Barrier/gate access, accounting, CRM

---

## 🚀 Next Steps

1. Review and approve this project plan
2. Confirm technical requirements (hosting, SMTP, bank plugin)
3. Begin Phase 1: Foundation & Setup
4. Create detailed task breakdown for each phase
5. Set up development environment and version control

---

**Project Manager:** [Your Name]  
**Client Contact:** [Client Name]  
**Start Date:** [To be confirmed]  
**Target Completion:** [To be confirmed]

