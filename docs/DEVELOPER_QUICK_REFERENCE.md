# 👨‍💻 Developer Quick Reference - Phase 2 Classes

**Quick lookup guide for Phase 2 implementation**

---

## 🎯 Quick Class Reference

### StorageUnit Model
```php
$unit = new \RoyalStorage\Models\StorageUnit($id);
$unit->get_size();              // Get unit size (M, L, XL)
$unit->get_status();            // Get status (available, occupied, maintenance)
$unit->get_price_per_day();     // Get daily rate
$unit->get_price_per_week();    // Get weekly rate
$unit->get_price_per_month();   // Get monthly rate
$unit->is_available($start, $end); // Check availability
$unit->save();                  // Save unit
$unit->delete();                // Delete unit
```

### ParkingSpace Model
```php
$space = new \RoyalStorage\Models\ParkingSpace($id);
$space->get_spot_number();      // Get spot number
$space->get_status();           // Get status
$space->get_price_per_day();    // Get daily rate
$space->get_price_per_month();  // Get monthly rate
$space->is_available($start, $end); // Check availability
$space->save();                 // Save space
$space->delete();               // Delete space
```

### Booking Model
```php
$booking = new \RoyalStorage\Models\Booking($id);
$booking->get_customer_id();    // Get customer ID
$booking->get_unit_id();        // Get unit ID
$booking->get_space_id();       // Get space ID
$booking->get_start_date();     // Get start date
$booking->get_end_date();       // Get end date
$booking->get_status();         // Get status
$booking->get_total_price();    // Get total price
$booking->get_access_code();    // Get access code
$booking->get_days();           // Get number of days
$booking->save();               // Save booking
$booking->delete();             // Delete booking
```

---

## 💰 PricingEngine

```php
$pricing = new \RoyalStorage\PricingEngine();

// Calculate price
$result = $pricing->calculate_price(
    2000,           // base price
    '2025-11-01',   // start date
    '2025-12-01',   // end date
    'monthly'       // period: daily, weekly, monthly
);
// Returns: ['subtotal' => 2000, 'vat' => 400, 'total' => 2400, 'days' => 30]

// Apply discount
$discounted = $pricing->apply_discount(2400, 10); // 10% discount

// Calculate late fee
$late_fee = $pricing->calculate_late_fee(100, 5); // 5 days overdue

// Format price
echo $pricing->format_price(2400); // Output: 2,400.00 RSD

// Get price breakdown
$breakdown = $pricing->get_price_breakdown(
    2000, '2025-11-01', '2025-12-01', 'monthly'
);
```

---

## 📅 BookingEngine

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

---

## 📄 InvoiceGenerator

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

---

## 💳 PaymentHandler

```php
$payment = new \RoyalStorage\PaymentHandler();

// Process payment
$payment->process_payment(1, 'online');     // Online payment
$payment->process_payment(1, 'pay_later');  // Pay later

// Confirm payment
$payment->confirm_payment(1);

// Handle failure
$payment->handle_payment_failure(1, 'Card declined');

// Get status
$status = $payment->get_payment_status(1);

// Refund
$payment->refund_payment(1);
```

---

## 📧 EmailManager

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

## 🔄 Common Workflows

### Create Booking & Process Payment
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

// 2. Send confirmation
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

### Check Availability & Get Price
```php
$booking_engine = new \RoyalStorage\BookingEngine();
$pricing = new \RoyalStorage\PricingEngine();

// Get available units
$units = $booking_engine->get_available_units('2025-11-01', '2025-12-01');

// Get price for first unit
if (!empty($units)) {
    $unit = $units[0];
    $price = $pricing->calculate_price(
        $unit->get_price_per_month(),
        '2025-11-01',
        '2025-12-01',
        'monthly'
    );
    echo "Total: " . $price['total'] . " RSD";
}
```

---

## 🗄️ Database Tables

### wp_royal_storage_units
- id, size, status, price_per_day, price_per_week, price_per_month, created_at, updated_at

### wp_royal_parking_spaces
- id, spot_number, status, price_per_day, price_per_week, price_per_month, created_at, updated_at

### wp_royal_bookings
- id, customer_id, unit_id, space_id, start_date, end_date, status, total_price, payment_status, access_code, created_at, updated_at

### wp_royal_invoices
- id, booking_id, customer_id, invoice_number, amount, vat_amount, total_amount, status, paid_date, created_at, updated_at

---

## 🔐 Security Notes

- All queries use prepared statements
- Input validation on all methods
- Output escaping in templates
- User authentication checks
- Capability checks for admin functions

---

## 📝 Constants

```php
ROYAL_STORAGE_VERSION      // Plugin version
ROYAL_STORAGE_DIR          // Plugin directory path
ROYAL_STORAGE_URL          // Plugin URL
ROYAL_STORAGE_TEXT_DOMAIN  // Text domain for translations
```

---

## 🎯 Status Values

**Booking Status**: pending, confirmed, active, cancelled, expired  
**Payment Status**: pending, paid, failed, refunded  
**Invoice Status**: pending, paid, overdue, cancelled  
**Unit Status**: available, occupied, maintenance  
**Space Status**: available, occupied, maintenance  

---

## 📞 Error Handling

All classes return false on error or throw exceptions. Always check return values:

```php
$booking = $booking_engine->create_booking($data);
if (!$booking) {
    // Handle error
    error_log('Booking creation failed');
}
```

---

**Last Updated**: 2025-10-16  
**Version**: 1.0.0  
**Status**: ✅ Phase 2 Complete

