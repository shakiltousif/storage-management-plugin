# Phase 2 Implementation Guide - Backend Core Functionality

**Date**: 2025-10-16  
**Status**: ✅ COMPLETE

---

## 📚 New Classes Overview

### Model Classes

#### 1. StorageUnit Model
**File**: `includes/Models/class-storage-unit.php`

```php
// Create new unit
$unit = new \RoyalStorage\Models\StorageUnit();
$unit->set_size('L');
$unit->set_status('available');
$unit->set_price_per_day(100);
$unit->set_price_per_week(600);
$unit->set_price_per_month(2000);
$unit->save();

// Load existing unit
$unit = new \RoyalStorage\Models\StorageUnit(1);
echo $unit->get_size(); // Output: L

// Check availability
$is_available = $unit->is_available('2025-11-01', '2025-12-01');
```

#### 2. ParkingSpace Model
**File**: `includes/Models/class-parking-space.php`

```php
// Create new parking space
$space = new \RoyalStorage\Models\ParkingSpace();
$space->set_spot_number('A-01');
$space->set_status('available');
$space->set_price_per_day(50);
$space->set_price_per_month(1000);
$space->save();

// Check availability
$is_available = $space->is_available('2025-11-01', '2025-12-01');
```

#### 3. Booking Model
**File**: `includes/Models/class-booking.php`

```php
// Create new booking
$booking = new \RoyalStorage\Models\Booking();
$booking->set_customer_id(1);
$booking->set_unit_id(1);
$booking->set_start_date('2025-11-01');
$booking->set_end_date('2025-12-01');
$booking->set_status('pending');
$booking->set_total_price(2000);
$booking->save();

// Load existing booking
$booking = new \RoyalStorage\Models\Booking(1);
echo $booking->get_days(); // Output: 30
```

---

### Engine Classes

#### 1. PricingEngine
**File**: `includes/class-pricing-engine.php`

```php
$pricing = new \RoyalStorage\PricingEngine();

// Calculate price
$result = $pricing->calculate_price(
    2000,           // base price
    '2025-11-01',   // start date
    '2025-12-01',   // end date
    'monthly'       // period
);
// Returns: ['subtotal' => 2000, 'vat' => 400, 'total' => 2400, 'days' => 30]

// Apply discount
$discounted = $pricing->apply_discount(2400, 10); // 10% discount

// Calculate late fee
$late_fee = $pricing->calculate_late_fee(100, 5); // 5 days overdue

// Format price
echo $pricing->format_price(2400); // Output: 2,400.00 RSD

// Get price breakdown
$breakdown = $pricing->get_price_breakdown(2000, '2025-11-01', '2025-12-01', 'monthly');
```

#### 2. BookingEngine
**File**: `includes/class-booking-engine.php`

```php
$booking_engine = new \RoyalStorage\BookingEngine();

// Create booking
$booking = $booking_engine->create_booking([
    'customer_id' => 1,
    'unit_id'     => 1,
    'start_date'  => '2025-11-01',
    'end_date'    => '2025-12-01',
    'period'      => 'monthly'
]);

// Calculate price
$price = $booking_engine->calculate_booking_price([
    'unit_id'    => 1,
    'start_date' => '2025-11-01',
    'end_date'   => '2025-12-01',
    'period'     => 'monthly'
]);

// Get available units
$units = $booking_engine->get_available_units('2025-11-01', '2025-12-01');

// Get available spaces
$spaces = $booking_engine->get_available_spaces('2025-11-01', '2025-12-01');

// Cancel booking
$booking_engine->cancel_booking(1);

// Renew booking
$booking_engine->renew_booking(1, '2026-01-01');
```

#### 3. InvoiceGenerator
**File**: `includes/class-invoice-generator.php`

```php
$invoice_gen = new \RoyalStorage\InvoiceGenerator();

// Create invoice
$invoice_id = $invoice_gen->create_invoice([
    'booking_id'   => 1,
    'customer_id'  => 1,
    'amount'       => 2000,
    'vat_amount'   => 400,
    'total_amount' => 2400,
    'status'       => 'pending'
]);

// Get invoice
$invoice = $invoice_gen->get_invoice($invoice_id);

// Update status
$invoice_gen->update_invoice_status($invoice_id, 'paid');

// Generate HTML
$html = $invoice_gen->generate_invoice_html($invoice_id);

// Get customer invoices
$invoices = $invoice_gen->get_customer_invoices(1);

// Get invoices by status
$paid_invoices = $invoice_gen->get_invoices_by_status('paid');
```

#### 4. PaymentHandler
**File**: `includes/class-payment-handler.php`

```php
$payment = new \RoyalStorage\PaymentHandler();

// Process payment
$payment->process_payment(1, 'online'); // or 'pay_later'

// Confirm payment
$payment->confirm_payment(1);

// Handle failure
$payment->handle_payment_failure(1, 'Card declined');

// Get status
$status = $payment->get_payment_status(1);

// Refund
$payment->refund_payment(1);
```

#### 5. EmailManager
**File**: `includes/class-email-manager.php`

```php
$email = new \RoyalStorage\EmailManager();

// Send booking confirmation
$email->send_booking_confirmation(1);

// Send payment confirmation
$email->send_payment_confirmation(1);

// Send expiry reminder
$email->send_expiry_reminder(1);

// Send overdue reminder
$email->send_overdue_reminder(1);
```

---

## 🔄 Workflow Examples

### Complete Booking Workflow

```php
// 1. Create booking
$booking_engine = new \RoyalStorage\BookingEngine();
$booking = $booking_engine->create_booking([
    'customer_id' => 1,
    'unit_id'     => 1,
    'start_date'  => '2025-11-01',
    'end_date'    => '2025-12-01',
    'period'      => 'monthly'
]);

// 2. Send confirmation email
$email = new \RoyalStorage\EmailManager();
$email->send_booking_confirmation($booking->get_id());

// 3. Process payment
$payment = new \RoyalStorage\PaymentHandler();
$payment->process_payment($booking->get_id(), 'online');

// 4. Confirm payment
$payment->confirm_payment($booking->get_id());

// 5. Send payment confirmation
$email->send_payment_confirmation($booking->get_id());
```

### Pricing Calculation Workflow

```php
$pricing = new \RoyalStorage\PricingEngine();

// Get base price
$unit = new \RoyalStorage\Models\StorageUnit(1);
$base_price = $unit->get_price_per_month();

// Calculate price
$price_data = $pricing->calculate_price(
    $base_price,
    '2025-11-01',
    '2025-12-01',
    'monthly'
);

// Apply discount if applicable
if ($has_discount) {
    $total = $pricing->apply_discount($price_data['total'], 10);
} else {
    $total = $price_data['total'];
}

// Get breakdown for display
$breakdown = $pricing->get_price_breakdown(
    $base_price,
    '2025-11-01',
    '2025-12-01',
    'monthly'
);
```

---

## 📊 Database Queries

### Check Availability
```php
$unit = new \RoyalStorage\Models\StorageUnit(1);
$is_available = $unit->is_available('2025-11-01', '2025-12-01');
```

### Get Customer Bookings
```php
$invoice_gen = new \RoyalStorage\InvoiceGenerator();
$invoices = $invoice_gen->get_customer_invoices(1);
```

### Get Overdue Invoices
```php
$invoice_gen = new \RoyalStorage\InvoiceGenerator();
$overdue = $invoice_gen->get_invoices_by_status('pending');
```

---

## 🎯 Integration Points

### With WooCommerce
- Payment processing creates WooCommerce orders
- Order IDs are stored in booking meta

### With Email System
- Booking confirmations sent automatically
- Payment confirmations sent on payment
- Reminders sent via cron jobs

### With Admin Dashboard
- Booking data displayed in admin
- Invoice data displayed in admin
- Payment status tracked in admin

---

## 🔐 Security Considerations

- All database queries use prepared statements
- Input validation on all booking data
- User authentication checks
- Capability checks for admin functions
- Output escaping in email templates

---

## 📝 Notes

- All classes use PSR-4 autoloading
- Models follow standard getter/setter pattern
- Engines handle business logic
- Email templates are HTML-based
- All prices in RSD (Serbian Dinar)
- VAT rate is 20% (Serbian PDV)

---

**Status**: ✅ Phase 2 Complete  
**Next**: Phase 3 - Admin Features

