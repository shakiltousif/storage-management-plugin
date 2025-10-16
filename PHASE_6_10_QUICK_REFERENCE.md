# PHASE 6-10 QUICK REFERENCE GUIDE

**Royal Storage Management WordPress Plugin**

---

## PHASE 6: ADVANCED FEATURES

### Notification Manager
```php
$notification_manager = new \RoyalStorage\NotificationManager();

// Send booking confirmation
$notification_manager->send_booking_confirmation($booking_id);

// Send payment reminder
$notification_manager->send_payment_reminder($booking_id);

// Send expiry warning
$notification_manager->send_expiry_warning($booking_id);

// Get customer notifications
$notifications = $notification_manager->get_customer_notifications($customer_id);

// Get unread count
$unread = $notification_manager->get_unread_count($customer_id);

// Mark as read
$notification_manager->mark_as_read($notification_id);
```

### Subscription Manager
```php
$subscription_manager = new \RoyalStorage\SubscriptionManager();

// Create subscription
$subscription_id = $subscription_manager->create_subscription(
    $customer_id,
    $unit_id,
    'storage',
    99.99,
    'monthly'
);

// Get customer subscriptions
$subscriptions = $subscription_manager->get_customer_subscriptions($customer_id);

// Cancel subscription
$subscription_manager->cancel_subscription($subscription_id);

// Get subscription stats
$stats = $subscription_manager->get_subscription_stats();
```

---

## PHASE 7: REPORTING & ANALYTICS

### Advanced Reports
```php
$reports = new \RoyalStorage\AdvancedReports();

// Get revenue report
$revenue = $reports->get_revenue_report('2025-01-01', '2025-01-31');

// Get occupancy report
$occupancy = $reports->get_occupancy_report('2025-01-01', '2025-01-31');

// Get customer report
$customers = $reports->get_customer_report('2025-01-01', '2025-01-31');

// Get payment report
$payments = $reports->get_payment_report('2025-01-01', '2025-01-31');

// Export to CSV
$csv = $reports->export_to_csv('revenue', '2025-01-01', '2025-01-31');

// Get dashboard metrics
$metrics = $reports->get_dashboard_metrics();
```

### Analytics
```php
$analytics = new \RoyalStorage\Analytics();

// Track event
$analytics->track_event('booking_created', $customer_id, ['booking_id' => 123]);

// Get event analytics
$events = $analytics->get_event_analytics('booking_created', '2025-01-01', '2025-01-31');

// Get customer journey
$journey = $analytics->get_customer_journey($customer_id);

// Get conversion funnel
$funnel = $analytics->get_conversion_funnel('2025-01-01', '2025-01-31');

// Get top performing units
$top_units = $analytics->get_top_performing_units(10);

// Get customer lifetime value
$ltv = $analytics->get_customer_lifetime_value($customer_id);

// Get average CLV
$avg_ltv = $analytics->get_average_customer_lifetime_value();

// Get churn rate
$churn = $analytics->get_churn_rate(30);

// Get retention rate
$retention = $analytics->get_retention_rate(30);
```

---

## PHASE 8: API DEVELOPMENT

### REST API Endpoints
```
GET  /wp-json/royal-storage/v1/bookings
GET  /wp-json/royal-storage/v1/bookings/{id}
POST /wp-json/royal-storage/v1/bookings
GET  /wp-json/royal-storage/v1/invoices
GET  /wp-json/royal-storage/v1/invoices/{id}
GET  /wp-json/royal-storage/v1/reports/revenue
GET  /wp-json/royal-storage/v1/reports/occupancy
GET  /wp-json/royal-storage/v1/analytics/dashboard
```

### Webhook Handler
```php
$webhook_handler = new \RoyalStorage\API\WebhookHandler();

// Send webhook
\RoyalStorage\API\WebhookHandler::send_webhook(
    'payment_completed',
    ['booking_id' => 123, 'amount' => 99.99]
);
```

---

## PHASE 9: PERFORMANCE OPTIMIZATION

### Cache Manager
```php
$cache = new \RoyalStorage\CacheManager();

// Get cached data
$data = $cache->get('key');

// Set cached data
$cache->set('key', $value, 3600);

// Delete cached data
$cache->delete('key');

// Clear all cache
$cache->clear_all();

// Cache booking
$cache->cache_booking($booking_id);

// Get cached booking
$booking = $cache->get_cached_booking($booking_id);

// Cache unit availability
$cache->cache_unit_availability($unit_id);

// Get cached unit availability
$unit = $cache->get_cached_unit_availability($unit_id);

// Cache dashboard metrics
$cache->cache_dashboard_metrics();

// Get cached dashboard metrics
$metrics = $cache->get_cached_dashboard_metrics();

// Invalidate booking cache
$cache->invalidate_booking_cache($booking_id);

// Invalidate unit cache
$cache->invalidate_unit_cache($unit_id);
```

### Database Optimizer
```php
$optimizer = new \RoyalStorage\DatabaseOptimizer();

// Optimize all tables
$results = $optimizer->optimize_all_tables();

// Add indexes
$results = $optimizer->add_indexes();

// Analyze tables
$results = $optimizer->analyze_tables();

// Get table statistics
$stats = $optimizer->get_table_statistics();

// Clean old data
$count = $optimizer->clean_old_data(90);

// Repair tables
$results = $optimizer->repair_tables();
```

---

## PHASE 10: DEPLOYMENT & SECURITY

### Security Manager
```php
$security = new \RoyalStorage\SecurityManager();

// Get security logs
$logs = $security->get_security_logs(100);

// Get security report
$report = $security->get_security_report();

// Verify data integrity
$integrity = $security->verify_data_integrity();

// Clean security logs
$count = $security->clean_security_logs(90);
```

### Deployment Manager
```php
$deployment = new \RoyalStorage\DeploymentManager();

// Get deployment checklist
$checklist = $deployment->get_deployment_checklist();

// Get system health
$health = $deployment->get_system_health();

// Create backup
$backup_file = $deployment->create_backup();

// Get backup files
$backups = $deployment->get_backup_files();

// Get deployment status
$status = $deployment->get_deployment_status();
```

---

## DATABASE TABLES

### royal_bookings
- id, customer_id, unit_id, unit_type, start_date, end_date, total_price, payment_status, status, created_at

### royal_storage_units
- id, name, size, price, status, location, created_at

### royal_parking_spaces
- id, name, price, status, location, created_at

### royal_invoices
- id, booking_id, customer_id, amount, status, created_at

### royal_payments
- id, booking_id, amount, method, status, created_at

### royal_notifications
- id, customer_id, type, title, message, booking_id, is_read, created_at

### royal_subscriptions
- id, customer_id, unit_id, unit_type, price, frequency, status, next_billing, created_at

### royal_events
- id, event_type, customer_id, data, created_at

### royal_security_logs
- id, event_type, data, created_at

---

## COMMON TASKS

### Send Notification
```php
$notification_manager = new \RoyalStorage\NotificationManager();
$notification_manager->send_booking_confirmation($booking_id);
```

### Create Subscription
```php
$subscription_manager = new \RoyalStorage\SubscriptionManager();
$subscription_id = $subscription_manager->create_subscription(
    $customer_id, $unit_id, 'storage', 99.99, 'monthly'
);
```

### Get Revenue Report
```php
$reports = new \RoyalStorage\AdvancedReports();
$revenue = $reports->get_revenue_report('2025-01-01', '2025-01-31');
```

### Track Event
```php
$analytics = new \RoyalStorage\Analytics();
$analytics->track_event('booking_created', $customer_id, ['booking_id' => 123]);
```

### Cache Data
```php
$cache = new \RoyalStorage\CacheManager();
$cache->set('key', $value, 3600);
```

### Optimize Database
```php
$optimizer = new \RoyalStorage\DatabaseOptimizer();
$optimizer->optimize_all_tables();
```

### Create Backup
```php
$deployment = new \RoyalStorage\DeploymentManager();
$backup_file = $deployment->create_backup();
```

---

## HOOKS & FILTERS

### Actions
- `wp_scheduled_event_royal_storage_notifications` - Process notifications
- `wp_scheduled_event_royal_storage_subscriptions` - Process subscriptions

### Filters
- `royal_storage_notification_email` - Filter notification email
- `royal_storage_report_data` - Filter report data
- `royal_storage_api_response` - Filter API response

---

## SHORTCODES

```
[royal_storage_portal] - Display customer portal
[royal_storage_checkout] - Display checkout page
[royal_storage_login] - Display login form
```

---

## AJAX HANDLERS

```
wp_ajax_royal_storage_renew_booking
wp_ajax_royal_storage_cancel_booking
wp_ajax_royal_storage_download_invoice
wp_ajax_royal_storage_pay_invoice
wp_ajax_royal_storage_update_profile
wp_ajax_royal_storage_change_password
wp_ajax_royal_storage_process_payment
```

---

## CONFIGURATION OPTIONS

```php
// Webhook settings
get_option('royal_storage_webhook_url');
get_option('royal_storage_webhook_secret');

// Payment settings
get_option('royal_storage_payment_gateway');
get_option('royal_storage_payment_methods');

// Email settings
get_option('royal_storage_email_from');
get_option('royal_storage_email_template');

// Notification settings
get_option('royal_storage_notifications_enabled');
get_option('royal_storage_notification_frequency');
```

---

## TROUBLESHOOTING

### Notifications not sending
1. Check email configuration
2. Check notification manager logs
3. Verify cron jobs running
4. Check security logs

### API not working
1. Check REST API enabled
2. Verify authentication
3. Check webhook signature
4. Review error logs

### Performance issues
1. Check cache status
2. Optimize database
3. Review slow queries
4. Check server resources

### Security issues
1. Review security logs
2. Check failed logins
3. Verify data integrity
4. Review access logs

---

**For more information, see the complete documentation files.**

