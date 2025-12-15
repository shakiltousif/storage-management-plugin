# Royal Storage - Pay Later Payment Gateway Setup

## Overview
The Pay Later payment gateway allows customers to book storage units and receive an invoice via email, giving them time to complete payment later. This is fully integrated with WooCommerce.

## Features
- ✅ Customers can book storage units without immediate payment
- ✅ Automatic invoice generation and email delivery
- ✅ Professional HTML email template with complete booking details
- ✅ Configurable payment due date (default: 7 days)
- ✅ Payment link included in invoice email
- ✅ Order status set to "On Hold" until payment received
- ✅ Fully integrated with WooCommerce order system

## Setup Instructions

### Step 1: Activate the Payment Gateway

1. Go to **WooCommerce → Settings → Payments**
2. You will see **Royal Storage - Pay Later** in the payment methods list
3. Click on **Royal Storage - Pay Later** to configure
4. Check the box to **Enable** the payment gateway

### Step 2: Configure Settings

Configure the following options:

- **Title**: The payment method name shown to customers (default: "Pay Later")
- **Description**: Text displayed during checkout (e.g., "Reserve your storage unit now and pay later. An invoice will be sent to your email.")
- **Instructions**: Text shown on thank you page and in emails
- **Payment Due Days**: Number of days customers have to pay (default: 7)

### Step 3: Save Settings

Click **Save changes** to activate the payment gateway.

## How It Works

### For Customers:

1. Customer selects a storage unit and proceeds to checkout
2. Customer chooses **"Pay Later"** as payment method
3. Customer completes booking without payment
4. System automatically:
   - Creates WooCommerce order (status: On Hold)
   - Generates professional invoice
   - Sends invoice email to customer's email address
   - Includes payment link in email

### Invoice Email Contains:

- Order number and date
- Customer details
- Itemized booking details (unit type, dates, duration)
- Subtotal, tax, and total amount
- Payment due date
- Direct payment link
- Professional HTML formatting

### For Admin:

1. Order appears in WooCommerce → Orders with status "On Hold"
2. Order notes show:
   - "Payment pending. Invoice sent to customer."
   - Payment due date
3. When customer pays, order status updates to "Processing" or "Completed"

## Email Configuration

### To Ensure Emails Are Delivered:

1. **Install SMTP Plugin** (recommended):
   - Install "WP Mail SMTP" or "Easy WP SMTP"
   - Configure with your email service (Gmail, SendGrid, etc.)

2. **Test Email Delivery**:
   - Create a test booking with Pay Later
   - Check if invoice email is received
   - Check spam folder if not received

### Email Customization:

The invoice email template is located in:
```
/wp-content/plugins/royal-storage/includes/class-pay-later-gateway.php
```

Look for the `get_invoice_email_content()` method to customize the HTML template.

## Order Management

### Order Statuses:

- **On Hold**: Booking created, awaiting payment
- **Processing**: Payment received, booking being processed
- **Completed**: Booking confirmed and active

### To Manually Mark as Paid:

1. Go to WooCommerce → Orders
2. Find the order
3. Change status from "On Hold" to "Processing"
4. Customer receives confirmation email

## Payment Link

Customers can pay using the link in their invoice email, which takes them to:
- WooCommerce payment page
- Shows order details
- Allows payment with other available payment methods

## Testing

### Test the Pay Later Gateway:

1. Create a test booking on the frontend
2. Select "Pay Later" at checkout
3. Complete the booking
4. Check:
   - Order created in WooCommerce
   - Email sent to customer
   - Payment link works
   - Order appears as "On Hold"

### Test Email Template:

1. Use a test email address
2. Create booking with Pay Later
3. Verify invoice email:
   - Professional formatting
   - Correct order details
   - Working payment link
   - Payment due date displayed

## Troubleshooting

### Email Not Received:

1. Check WordPress email settings
2. Install SMTP plugin for reliable delivery
3. Check server email logs
4. Verify customer email address is correct
5. Check order notes for email sending status

### Payment Gateway Not Showing:

1. Ensure WooCommerce is activated
2. Go to WooCommerce → Settings → Payments
3. Enable "Royal Storage - Pay Later"
4. Clear cache if using caching plugin

### Orders Not Creating:

1. Check PHP error log
2. Ensure Royal Storage plugin is activated
3. Check WooCommerce is properly configured
4. Test with WooCommerce default products first

## Integration with Royal Storage Booking

The Pay Later gateway works seamlessly with the Royal Storage booking system:

1. Customer books storage unit through Royal Storage interface
2. Booking creates WooCommerce product automatically
3. WooCommerce checkout process begins
4. Customer selects Pay Later
5. Invoice sent, booking reserved

## Customization

### Change Payment Due Days:

1. Go to WooCommerce → Settings → Payments
2. Click on Royal Storage - Pay Later
3. Change "Payment Due Days" value
4. Save changes

### Customize Email Template:

Edit the HTML in `get_invoice_email_content()` method to match your branding:
- Change colors
- Add logo
- Modify text
- Add payment instructions

### Add Payment Methods:

Customers can pay via:
- Payment link in email → WooCommerce checkout
- My Account → Orders → Pay
- Any WooCommerce payment gateway you have enabled

## Support

For issues or questions:
1. Check WordPress debug log
2. Check WooCommerce system status
3. Verify email delivery with SMTP plugin
4. Test with default WooCommerce theme

## Notes

- Orders remain "On Hold" until payment received
- Automatic reminders can be set up with WooCommerce Follow-Up Emails plugin
- Stock is reserved when order is created
- Payment link is valid until order is cancelled

---

**Created by Claude Code for Royal Storage Plugin**
