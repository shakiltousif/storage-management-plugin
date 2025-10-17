<?php
/**
 * Demo Customer Portal Page
 * 
 * This file demonstrates how to use the Royal Storage customer portal.
 * You can include this in any WordPress page or post using shortcodes.
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// This is a demo file - in production, you would use shortcodes in your pages
?>

<!DOCTYPE html>
<html>
<head>
    <title>Royal Storage - Customer Portal Demo</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .demo-container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .demo-header { text-align: center; margin-bottom: 30px; }
        .demo-section { margin: 20px 0; padding: 20px; background: #f9f9f9; border-radius: 5px; }
        .code-block { background: #2d3748; color: #e2e8f0; padding: 15px; border-radius: 5px; font-family: monospace; margin: 10px 0; }
        .shortcode { background: #667eea; color: white; padding: 5px 10px; border-radius: 3px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="demo-container">
        <div class="demo-header">
            <h1>🏪 Royal Storage - Customer Portal Demo</h1>
            <p>How to use the customer frontend portal functionality</p>
        </div>

        <div class="demo-section">
            <h2>📋 Available Shortcodes</h2>
            <p>The Royal Storage plugin provides several shortcodes for customer functionality:</p>
            
            <h3>1. Customer Portal</h3>
            <p>Main customer portal with dashboard, bookings, invoices, and account management:</p>
            <div class="code-block">
                [royal_storage_portal]
            </div>
            
            <h3>2. Login Form</h3>
            <p>Login form for customers to access their portal:</p>
            <div class="code-block">
                [royal_storage_login]
            </div>
            
            <h3>3. Booking Form</h3>
            <p>Booking form for customers to create new bookings:</p>
            <div class="code-block">
                [royal_storage_booking]
            </div>
        </div>

        <div class="demo-section">
            <h2>🎯 How to Use</h2>
            
            <h3>Step 1: Create Pages</h3>
            <p>Create the following pages in your WordPress admin:</p>
            <ul>
                <li><strong>Customer Portal:</strong> Add <span class="shortcode">[royal_storage_portal]</span> shortcode</li>
                <li><strong>Login Page:</strong> Add <span class="shortcode">[royal_storage_login]</span> shortcode</li>
                <li><strong>Booking Page:</strong> Add <span class="shortcode">[royal_storage_booking]</span> shortcode</li>
            </ul>

            <h3>Step 2: Customer Experience</h3>
            <ol>
                <li>Customer visits the login page</li>
                <li>Customer logs in with their WordPress account</li>
                <li>Customer is redirected to the portal page</li>
                <li>Customer can view their dashboard, bookings, and invoices</li>
                <li>Customer can manage their bookings (renew, cancel)</li>
                <li>Customer can update their account information</li>
            </ol>
        </div>

        <div class="demo-section">
            <h2>🔧 Portal Features</h2>
            
            <h3>Dashboard Tab</h3>
            <ul>
                <li>📊 Active bookings count</li>
                <li>💰 Total amount spent</li>
                <li>📄 Unpaid invoices count</li>
                <li>⚠️ Unpaid amount</li>
                <li>🚀 Quick action buttons</li>
            </ul>

            <h3>Bookings Tab</h3>
            <ul>
                <li>📋 List of all customer bookings</li>
                <li>📅 Booking dates and details</li>
                <li>💳 Payment status</li>
                <li>🔄 Renew booking functionality</li>
                <li>❌ Cancel booking functionality</li>
                <li>💳 Pay now button for unpaid bookings</li>
            </ul>

            <h3>Invoices Tab</h3>
            <ul>
                <li>📄 List of customer invoices</li>
                <li>💰 Invoice amounts and status</li>
                <li>📥 Download invoice functionality</li>
                <li>💳 Pay invoice functionality</li>
            </ul>

            <h3>Account Tab</h3>
            <ul>
                <li>👤 Customer profile information</li>
                <li>📧 Email and contact details</li>
                <li>🔐 Password change</li>
                <li>📱 Phone number management</li>
            </ul>
        </div>

        <div class="demo-section">
            <h2>🎨 Styling</h2>
            <p>The portal includes comprehensive CSS styling:</p>
            <ul>
                <li>📱 Responsive design for mobile devices</li>
                <li>🎨 Modern gradient header</li>
                <li>📊 Card-based layout for statistics</li>
                <li>🔘 Interactive buttons and forms</li>
                <li>📋 Clean table layouts</li>
                <li>🎯 Status indicators with colors</li>
            </ul>
        </div>

        <div class="demo-section">
            <h2>🔐 Security Features</h2>
            <ul>
                <li>🛡️ User authentication required</li>
                <li>🔒 Nonce verification for all actions</li>
                <li>👤 User capability checks</li>
                <li>🚫 SQL injection prevention</li>
                <li>🧹 Data sanitization</li>
            </ul>
        </div>

        <div class="demo-section">
            <h2>📝 Implementation Notes</h2>
            <p><strong>Frontend Classes:</strong></p>
            <ul>
                <li><code>Frontend\Frontend</code> - Main frontend controller</li>
                <li><code>Frontend\Portal</code> - Customer portal functionality</li>
                <li><code>Frontend\Bookings</code> - Booking management</li>
                <li><code>Frontend\Checkout</code> - Payment processing</li>
                <li><code>Frontend\Invoices</code> - Invoice management</li>
                <li><code>Frontend\Account</code> - Account management</li>
            </ul>
            
            <p><strong>Templates:</strong></p>
            <ul>
                <li><code>templates/frontend/portal-dashboard.php</code></li>
                <li><code>templates/frontend/portal-bookings.php</code></li>
                <li><code>templates/frontend/portal-invoices.php</code></li>
                <li><code>templates/frontend/portal-account.php</code></li>
            </ul>
        </div>

        <div class="demo-section">
            <h2>🚀 Next Steps</h2>
            <ol>
                <li>Create a page with <span class="shortcode">[royal_storage_portal]</span> shortcode</li>
                <li>Test the portal with a logged-in user</li>
                <li>Add sample data using the admin dashboard</li>
                <li>Customize the styling in <code>assets/css/portal.css</code></li>
                <li>Configure payment settings in the admin</li>
            </ol>
        </div>
    </div>
</body>
</html>
