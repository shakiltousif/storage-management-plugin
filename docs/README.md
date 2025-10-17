# Royal Storage Management Plugin

A comprehensive WordPress plugin for managing storage box and parking space rentals.

## Features

- **Storage Unit Management**: Manage storage units in different sizes (M, L, XL)
- **Parking Space Management**: Manage outdoor parking spaces with automatic spot assignment
- **Booking System**: Complete booking system with availability checking
- **Pricing Engine**: Flexible pricing with daily, weekly, and monthly rates
- **Payment Processing**: WooCommerce integration with bank plugin support
- **Invoice Management**: Automatic invoice generation with Serbian VAT compliance
- **Customer Portal**: Self-service portal for customers to manage bookings
- **Admin Dashboard**: Comprehensive dashboard with metrics and reporting
- **Email Notifications**: Automated email notifications for bookings and reminders
- **Multilingual Support**: Serbian and English language support
- **Security**: GDPR compliance, reCAPTCHA integration, and security hardening

## Requirements

- WordPress 6.0 or higher
- PHP 8.0 or higher
- WooCommerce 5.0 or higher
- MySQL 5.7 or higher

## Installation

1. Upload the `royal-storage` folder to `/wp-content/plugins/`
2. Activate the plugin through the WordPress admin panel
3. Navigate to Royal Storage > Settings to configure the plugin

## Configuration

### Business Settings
- Business name and contact information
- Location details
- Currency (RSD)
- VAT rate (20%)

### Pricing Settings
- Daily rates for storage units and parking
- Weekly rates
- Monthly rates
- Discount codes (3+1, 4+1, 5+1, 10+2)

### Email Settings
- SMTP configuration
- Email templates
- Notification triggers

### Payment Settings
- Bank plugin configuration
- 3D Secure settings
- Payment gateway credentials

## Usage

### For Administrators

1. **Manage Units**: Add and manage storage units and parking spaces
2. **View Dashboard**: Monitor occupancy rates and metrics
3. **Manage Bookings**: Create, modify, or cancel bookings
4. **Generate Reports**: Export booking and revenue data
5. **Configure Settings**: Set up business rules and pricing

### For Customers

1. **Browse Units**: View available storage units and parking spaces
2. **Make Bookings**: Select dates and book units
3. **Manage Portal**: View active bookings and payment history
4. **Renew Bookings**: Extend existing bookings
5. **Download Invoices**: Access and download invoices

## Database Schema

### Tables Created

- `wp_royal_storage_units`: Storage unit information
- `wp_royal_parking_spaces`: Parking space information
- `wp_royal_bookings`: Booking records
- `wp_royal_invoices`: Invoice records

## Custom Post Types

- `rs_storage_unit`: Storage unit CPT
- `rs_parking_space`: Parking space CPT
- `rs_booking`: Booking CPT
- `rs_invoice`: Invoice CPT

## Custom Taxonomies

- `rs_unit_size`: Unit size (M, L, XL)
- `rs_booking_status`: Booking status
- `rs_payment_status`: Payment status

## REST API Endpoints

- `GET /wp-json/royal-storage/v1/units`: Get available units
- `POST /wp-json/royal-storage/v1/bookings`: Create booking
- `GET /wp-json/royal-storage/v1/availability`: Check availability

## Shortcodes

- `[royal_storage_booking]`: Display booking form
- `[royal_storage_portal]`: Display customer portal

## Hooks and Filters

### Actions

- `royal_storage_booking_created`: Fired when booking is created
- `royal_storage_payment_processed`: Fired when payment is processed
- `royal_storage_send_expiry_reminders`: Cron job for expiry reminders
- `royal_storage_send_overdue_reminders`: Cron job for overdue reminders

### Filters

- `royal_storage_booking_price`: Filter booking price calculation
- `royal_storage_email_subject`: Filter email subject
- `royal_storage_email_body`: Filter email body

## File Structure

```
royal-storage/
├── royal-storage.php              # Main plugin file
├── includes/
│   ├── class-autoloader.php       # PSR-4 autoloader
│   ├── class-plugin.php           # Main plugin class
│   ├── class-database.php         # Database operations
│   ├── class-post-types.php       # Custom post types
│   ├── class-activator.php        # Plugin activation
│   ├── class-deactivator.php      # Plugin deactivation
│   ├── Admin/
│   │   ├── class-admin.php
│   │   ├── class-dashboard.php
│   │   ├── class-settings.php
│   │   ├── class-reports.php
│   │   ├── class-bookings.php
│   │   ├── class-customers.php
│   │   └── class-notifications.php
│   ├── Frontend/
│   │   ├── class-frontend.php
│   │   ├── class-portal.php
│   │   ├── class-booking.php
│   │   └── class-checkout.php
│   └── API/
│       └── class-api.php
├── assets/
│   ├── css/
│   │   ├── admin.css
│   │   └── frontend.css
│   └── js/
│       ├── admin.js
│       └── frontend.js
├── languages/
│   ├── royal-storage.pot
│   ├── royal-storage-sr_RS.po
│   └── royal-storage-en_US.po
└── README.md
```

## Support

For support, please contact the development team or visit https://royalstorage.rs

## License

This plugin is licensed under the GPL v2 or later.

## Changelog

### Version 1.0.0
- Initial release
- Core functionality for storage and parking management
- Booking system with availability checking
- Payment processing integration
- Customer portal
- Admin dashboard
- Email notifications
- Multilingual support

## Development

This plugin follows WordPress coding standards and best practices:
- PSR-4 autoloading
- Proper nonce verification
- Input sanitization and output escaping
- Capability checks
- Database prepared statements

## Future Enhancements

- SMS notifications
- Barrier/gate access integration
- Accounting system integration
- CRM integration
- Mobile app
- Advanced reporting
- Seasonal pricing
- Insurance add-ons

