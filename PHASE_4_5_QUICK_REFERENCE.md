# Phase 4 & 5 - Quick Reference Guide

---

## 🚀 Quick Start

### Enable Portal
```php
// In your theme or plugin
do_shortcode( '[royal_storage_portal]' );
```

### Enable Checkout
```php
// In your theme or plugin
do_shortcode( '[royal_storage_checkout]' );
```

---

## 📚 Frontend Classes

### Bookings Class
```php
use RoyalStorage\Frontend\Bookings;

$bookings = new Bookings();

// Get customer bookings
$all_bookings = $bookings->get_customer_bookings( $customer_id );

// Get active bookings
$active = $bookings->get_customer_active_bookings( $customer_id );

// Get stats
$stats = $bookings->get_customer_stats( $customer_id );

// Get single booking
$booking = $bookings->get_booking( $booking_id );

// Renew booking
$bookings->renew_booking( $booking_id, 30 ); // 30 days

// Cancel booking
$bookings->cancel_booking( $booking_id );

// Get status label
$label = Bookings::get_status_label( 'active' );
```

### Invoices Class
```php
use RoyalStorage\Frontend\Invoices;

$invoices = new Invoices();

// Get customer invoices
$all_invoices = $invoices->get_customer_invoices( $customer_id );

// Get unpaid invoices
$unpaid = $invoices->get_unpaid_invoices( $customer_id );

// Get single invoice
$invoice = $invoices->get_invoice( $invoice_id );

// Get invoice items
$items = $invoices->get_invoice_items( $invoice_id );

// Get total unpaid
$total = $invoices->get_total_unpaid( $customer_id );
```

### Account Class
```php
use RoyalStorage\Frontend\Account;

$account = new Account();

// Get account stats
$stats = $account->get_account_stats( $customer_id );

// Get customer info
$info = $account->get_customer_info( $customer_id );

// Update profile
$account->update_profile( $customer_id, [
    'name' => 'John Doe',
    'phone' => '+381123456789',
    'address' => '123 Main St',
    'city' => 'Belgrade',
    'postal_code' => '11000',
    'country' => 'Serbia',
    'company' => 'My Company',
    'tax_id' => '123456789'
] );

// Change password
$account->change_password( $customer_id, 'new_password' );
```

---

## 💳 Payment Classes

### WooCommerce Integration
```php
use RoyalStorage\WooCommerceIntegration;

$wc = new WooCommerceIntegration();

// Create product
$product_id = $wc->create_product( $booking_id, 100.00 );

// Create order
$order_id = $wc->create_order( $booking_id, $customer_id, 100.00 );

// Get order by booking
$order = $wc->get_order_by_booking( $booking_id );

// Get settings
$settings = $wc->get_payment_gateway_settings();

// Validate amount
$valid = $wc->validate_payment_amount( 100.00 );
```

### Payment Processor
```php
use RoyalStorage\PaymentProcessor;

$processor = new PaymentProcessor();

// Create payment record
$payment_id = $processor->create_payment_record(
    $booking_id,
    100.00,
    'card',
    'pending'
);

// Get payment records
$payments = $processor->get_payment_records( $booking_id );

// Get total paid
$paid = $processor->get_total_paid( $booking_id );

// Get remaining balance
$balance = $processor->get_remaining_balance( $booking_id );

// Refund payment
$processor->refund_payment( $payment_id, 50.00 );
```

---

## 🎨 Frontend Templates

### Portal Dashboard
```php
// Displays:
// - 4 stat cards
// - Quick action buttons
// - Welcome information
```

### Portal Bookings
```php
// Displays:
// - Bookings list
// - Renew/cancel buttons
// - Status indicators
// - Empty state
```

### Portal Invoices
```php
// Displays:
// - Invoices table
// - Download/pay buttons
// - Status indicators
// - Responsive design
```

### Portal Account
```php
// Displays:
// - Profile form
// - Password change form
// - Form validation
// - Responsive layout
```

---

## 🎯 AJAX Endpoints

### Booking Actions
```javascript
// Renew booking
$.ajax({
    url: ajaxUrl,
    type: 'POST',
    data: {
        action: 'royal_storage_renew_booking',
        nonce: nonce,
        booking_id: bookingId,
        days: 30
    }
});

// Cancel booking
$.ajax({
    url: ajaxUrl,
    type: 'POST',
    data: {
        action: 'royal_storage_cancel_booking',
        nonce: nonce,
        booking_id: bookingId
    }
});
```

### Invoice Actions
```javascript
// Download invoice
$.ajax({
    url: ajaxUrl,
    type: 'POST',
    data: {
        action: 'royal_storage_download_invoice',
        nonce: nonce,
        invoice_id: invoiceId
    }
});

// Pay invoice
$.ajax({
    url: ajaxUrl,
    type: 'POST',
    data: {
        action: 'royal_storage_pay_invoice',
        nonce: nonce,
        invoice_id: invoiceId
    }
});
```

### Account Actions
```javascript
// Update profile
$.ajax({
    url: ajaxUrl,
    type: 'POST',
    data: {
        action: 'royal_storage_update_profile',
        nonce: nonce,
        name: 'John Doe',
        phone: '+381123456789',
        // ... other fields
    }
});

// Change password
$.ajax({
    url: ajaxUrl,
    type: 'POST',
    data: {
        action: 'royal_storage_change_password',
        nonce: nonce,
        current_password: 'old_pass',
        new_password: 'new_pass'
    }
});
```

### Payment Actions
```javascript
// Process payment
$.ajax({
    url: ajaxUrl,
    type: 'POST',
    data: {
        action: 'royal_storage_process_payment',
        nonce: nonce,
        booking_id: bookingId,
        amount: 100.00,
        payment_method: 'card'
    }
});
```

---

## 🗄️ Database Queries

### Get Customer Bookings
```sql
SELECT * FROM wp_royal_bookings 
WHERE customer_id = %d 
ORDER BY created_at DESC
```

### Get Customer Invoices
```sql
SELECT * FROM wp_royal_invoices 
WHERE customer_id = %d 
ORDER BY created_at DESC
```

### Get Payment Records
```sql
SELECT * FROM wp_royal_payments 
WHERE booking_id = %d 
ORDER BY created_at DESC
```

---

## 🔐 Security Checklist

- ✅ Always verify nonce on AJAX requests
- ✅ Always check user capabilities
- ✅ Always sanitize user input
- ✅ Always escape output
- ✅ Always use prepared statements
- ✅ Always validate payment amounts
- ✅ Always verify user ownership

---

## 🐛 Common Issues

### Portal Not Showing
- Check if shortcode is added to page
- Check if user is logged in
- Check browser console for errors

### Payment Not Processing
- Check WooCommerce is installed
- Check payment gateway is configured
- Check payment amount is valid
- Check browser console for errors

### AJAX Not Working
- Check nonce is correct
- Check user is logged in
- Check AJAX URL is correct
- Check browser console for errors

---

## 📞 Support

For issues:
1. Check error logs
2. Check browser console
3. Review code comments
4. Check documentation

---

**Version**: 1.0.0
**Date**: October 16, 2025

