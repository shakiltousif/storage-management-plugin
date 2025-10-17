# Phase 3 Quick Reference - Admin Features

## 🎯 Admin Features Overview

### Dashboard
**Location**: `wp-admin/admin.php?page=royal-storage-dashboard`

**What it shows**:
- Total storage units
- Occupied units
- Available units
- Occupancy rate (%)
- Overdue bookings count
- Bookings expiring in 7 days
- Monthly revenue
- Pending payments count

**Quick Actions**:
- Create Booking
- View Reports
- Manage Customers
- Settings

---

### Booking Management
**Location**: `wp-admin/admin.php?page=royal-storage-bookings`

**Features**:
- View all bookings with pagination
- Create new booking
- View booking details
- Cancel booking
- Filter by status
- Filter by payment status

**Columns**:
- ID
- Customer Name
- Unit/Space
- Start Date
- End Date
- Total Price
- Status (badge)
- Payment Status (badge)
- Actions

**Status Types**:
- Pending (orange)
- Confirmed (blue)
- Active (green)
- Cancelled (red)
- Expired (gray)

**Payment Status**:
- Paid (green)
- Unpaid (red)
- Pending (red)
- Failed (dark red)
- Refunded (gray)

---

### Reports
**Location**: `wp-admin/admin.php?page=royal-storage-reports`

**Features**:
- Date range filtering
- Revenue summary
- Revenue by date
- Payment status breakdown
- CSV export

**Reports Available**:
1. **Revenue Summary**
   - Total revenue for period
   - Date range display

2. **Revenue by Date**
   - Date
   - Number of bookings
   - Revenue amount

3. **Payment Status**
   - Payment status
   - Count of bookings
   - Total amount

**Export**:
- Click "Export to CSV"
- Downloads bookings data
- Includes all booking details

---

### Customer Management
**Location**: `wp-admin/admin.php?page=royal-storage-customers`

**Features**:
- View all customers with pagination
- View customer details
- Customer statistics
- Booking history

**Customer List Columns**:
- Customer ID
- Name
- Email
- Total Spent
- Overdue Amount (highlighted if > 0)
- Active Bookings
- Actions (View)

**Customer Details**:
- Name
- Email
- Phone
- Total Spent
- Overdue Amount
- Active Bookings
- Complete booking history

**Booking History**:
- Booking ID
- Start Date
- End Date
- Total Price
- Status
- Payment Status

---

### Notifications
**Location**: `wp-admin/admin.php?page=royal-storage-notifications`

**Features**:
- View pending notifications
- Send notifications manually
- Notification statistics
- Automated scheduling info

**Pending Notifications**:
- Booking ID
- Type (Expiry/Overdue)
- Customer Name
- Details
- Send Now button

**Notification Types**:
- **Expiry** (orange): Sent 7 days before booking expires
- **Overdue** (red): Sent for expired unpaid bookings

**Automated Reminders**:
- Expiry reminders: Daily at scheduled time
- Overdue reminders: Daily at scheduled time

---

## 🔧 Admin Class Methods

### Dashboard Class
```php
$dashboard = new \RoyalStorage\Admin\Dashboard();

// Get metrics
$total = $dashboard->get_total_units();
$occupied = $dashboard->get_occupied_units();
$available = $dashboard->get_available_units();
$rate = $dashboard->get_occupancy_rate();
$overdue = $dashboard->get_overdue_bookings_count();
$expiring = $dashboard->get_upcoming_expiries(7);
$revenue = $dashboard->get_monthly_revenue();
$pending = $dashboard->get_pending_payments_count();
```

### Bookings Class
```php
$bookings = new \RoyalStorage\Admin\Bookings();

// Get bookings
$all = $bookings->get_bookings($limit, $offset);
$one = $bookings->get_booking($id);
$count = $bookings->get_bookings_count();

// Create/Cancel
$bookings->handle_create_booking();
$bookings->handle_cancel_booking();
```

### Reports Class
```php
$reports = new \RoyalStorage\Admin\Reports();

// Get reports
$revenue = $reports->get_revenue_report($start, $end);
$occupancy = $reports->get_occupancy_report($start, $end);
$payment = $reports->get_payment_report($start, $end);
$total = $reports->get_total_revenue($start, $end);

// Export
$reports->handle_export_csv();
```

### Customers Class
```php
$customers = new \RoyalStorage\Admin\Customers();

// Get customers
$all = $customers->get_customers($limit, $offset);
$info = $customers->get_customer_info($id);
$bookings = $customers->get_customer_bookings($id);
$count = $customers->get_customers_count();

// Get stats
$spent = $customers->get_customer_total_spent($id);
$overdue = $customers->get_customer_overdue_amount($id);
$active = $customers->get_customer_active_bookings_count($id);
```

### Notifications Class
```php
$notifications = new \RoyalStorage\Admin\Notifications();

// Get notifications
$pending = $notifications->get_pending_notifications();
$count = $notifications->get_pending_notifications_count();

// Send
$notifications->handle_send_notification();
$notifications->send_expiry_reminders();
$notifications->send_overdue_reminders();

// Schedule
$notifications->schedule_cron_jobs();
$notifications->clear_cron_jobs();
```

---

## 📊 Database Queries

### Get Total Units
```sql
SELECT COUNT(*) FROM wp_royal_storage_units
```

### Get Occupied Units
```sql
SELECT COUNT(DISTINCT unit_id) FROM wp_royal_bookings 
WHERE status IN ('confirmed', 'active')
```

### Get Overdue Bookings
```sql
SELECT * FROM wp_royal_bookings 
WHERE end_date < CURDATE() 
AND status != 'cancelled' 
AND payment_status IN ('unpaid', 'pending')
```

### Get Monthly Revenue
```sql
SELECT SUM(total_price) FROM wp_royal_bookings 
WHERE MONTH(created_at) = MONTH(CURDATE()) 
AND YEAR(created_at) = YEAR(CURDATE()) 
AND payment_status = 'paid'
```

---

## 🎨 UI Elements

### Status Badges
```html
<span class="status-badge status-pending">Pending</span>
<span class="status-badge status-confirmed">Confirmed</span>
<span class="status-badge status-active">Active</span>
<span class="status-badge status-cancelled">Cancelled</span>
```

### Payment Badges
```html
<span class="payment-badge payment-paid">Paid</span>
<span class="payment-badge payment-unpaid">Unpaid</span>
<span class="payment-badge payment-pending">Pending</span>
```

### Metric Cards
```html
<div class="metric-card">
  <div class="metric-icon">📦</div>
  <div class="metric-content">
    <h3>Total Units</h3>
    <p class="metric-value">50</p>
  </div>
</div>
```

---

## 🔐 Security

### Nonce Verification
All forms include nonce verification:
```php
wp_verify_nonce($_POST['nonce'], 'royal_storage_action')
```

### Capability Checks
All admin actions require `manage_options`:
```php
current_user_can('manage_options')
```

### Input Sanitization
All inputs are sanitized:
```php
sanitize_text_field(wp_unslash($_POST['field']))
```

### Output Escaping
All outputs are escaped:
```php
esc_html($value)
esc_attr($value)
esc_url($url)
```

---

## 📈 Workflow Examples

### Create a Booking
1. Go to Bookings page
2. Click "Create New Booking"
3. Fill in booking details
4. Click "Create"
5. Email sent to customer
6. Booking appears in list

### Export Reports
1. Go to Reports page
2. Select date range
3. Click "Filter"
4. Review data
5. Click "Export to CSV"
6. File downloads

### Send Notification
1. Go to Notifications page
2. Find pending notification
3. Click "Send Now"
4. Email sent to customer
5. Notification removed from list

### View Customer Details
1. Go to Customers page
2. Click on customer name
3. View customer info
4. See booking history
5. Check statistics

---

## 🚀 Tips & Tricks

### Dashboard
- Bookmark the dashboard for quick access
- Check alerts regularly
- Use quick actions for common tasks

### Bookings
- Use pagination for large lists
- Filter by status to find specific bookings
- Cancel bookings to free up units

### Reports
- Export monthly for records
- Use date range to analyze trends
- Check payment status regularly

### Customers
- View customer details for context
- Track overdue amounts
- Monitor active bookings

### Notifications
- Send manual reminders as needed
- Check pending notifications daily
- Verify automated reminders are working

---

## 📞 Support

For issues or questions:
1. Check the documentation
2. Review the code comments
3. Check WordPress error logs
4. Contact support

---

**Phase 3 Admin Features Quick Reference**  
**Version**: 1.0.0  
**Date**: 2025-10-16

