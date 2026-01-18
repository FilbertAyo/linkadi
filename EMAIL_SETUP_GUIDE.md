# Email Setup Guide for Linkadi

This guide will help you configure email functionality for user registration and password reset features.

## 📧 Email Address Recommendation

For automated system emails, we recommend using: **`noreply@linkadi.co.tz`**

This is standard practice for:
- Welcome emails when users register
- Password reset emails
- System notifications

Alternative options:
- `info@linkadi.co.tz` - if you want users to be able to reply
- `support@linkadi.co.tz` - for support-related emails

## 🔧 Configuration Steps

### Step 1: Update Your `.env` File

Copy the mail configuration from `.env.example` and update your `.env` file with your actual credentials:

```env
MAIL_MAILER=smtp
MAIL_HOST=mail.linkadi.co.tz
MAIL_PORT=465
MAIL_USERNAME=noreply@linkadi.co.tz
MAIL_PASSWORD=your_actual_password_here
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=noreply@linkadi.co.tz
MAIL_FROM_NAME="Linkadi"
```

**Important:** Replace `your_actual_password_here` with the actual password for your email account.

### Step 2: Mail Server Settings Explained

Based on your cPanel mail settings:

| Setting | Value |
|---------|-------|
| **Incoming Server** | mail.linkadi.co.tz |
| **IMAP Port** | 993 (not used by Laravel) |
| **POP3 Port** | 995 (not used by Laravel) |
| **Outgoing Server (SMTP)** | mail.linkadi.co.tz |
| **SMTP Port** | 465 |
| **Encryption** | SSL/TLS |
| **Authentication** | Required |

Laravel only uses SMTP (outgoing) settings to send emails.

### Step 3: Create the Email Account in cPanel

1. Log in to your cPanel
2. Go to **Email Accounts**
3. Click **Create**
4. Set up the account:
   - Email: `noreply`
   - Domain: `linkadi.co.tz`
   - Password: Choose a strong password
   - Mailbox quota: 250 MB (sufficient for automated emails)
5. Save the password - you'll need it for the `.env` file

### Step 4: Configure Queue for Better Performance

Email sending can be slow. Using queues allows emails to be sent in the background:

1. Make sure your `.env` has:
   ```env
   QUEUE_CONNECTION=database
   ```

2. Run the queue table migration (if not already done):
   ```bash
   php artisan queue:table
   php artisan migrate
   ```

3. Process queued jobs:
   ```bash
   php artisan queue:work
   ```

   **For Production:** Set up a supervisor to keep the queue worker running:
   ```bash
   php artisan queue:work --daemon
   ```

### Step 5: Test Email Configuration

Test if emails are working:

```bash
php artisan tinker
```

Then run:
```php
Mail::raw('Test email from Linkadi', function ($message) {
    $message->to('your-email@example.com')
            ->subject('Test Email');
});
```

Check your inbox to verify the email was received.

## 📨 Email Features Implemented

### 1. Welcome Email (Registration)
- ✅ Automatically sent when a user registers
- ✅ Beautiful HTML template with branding
- ✅ Includes quick start guide
- ✅ Queued for better performance

**Location:** `app/Mail/WelcomeEmail.php`
**Template:** `resources/views/emails/welcome.blade.php`
**Listener:** `app/Listeners/SendWelcomeEmail.php`

### 2. Password Reset Email
- ✅ Sent when user requests password reset
- ✅ Secure token-based reset link
- ✅ Custom branded template
- ✅ Expires after 60 minutes
- ✅ Queued for better performance

**Notification:** `app/Notifications/ResetPasswordNotification.php`
**Template:** `resources/views/emails/password-reset.blade.php`

## 🔒 Security Recommendations

1. **Use strong passwords** for your email accounts
2. **Enable two-factor authentication** in cPanel if available
3. **Monitor email logs** for suspicious activity
4. **Keep credentials secure** - never commit `.env` file
5. **Use environment-specific emails** (different for dev/staging/production)

## 🧪 Testing During Development

For local development, you can use `log` driver to avoid sending real emails:

```env
MAIL_MAILER=log
```

Emails will be logged to `storage/logs/laravel.log` instead of being sent.

## 🚀 Production Deployment Checklist

- [ ] Create `noreply@linkadi.co.tz` email account in cPanel
- [ ] Update production `.env` with correct mail credentials
- [ ] Set `MAIL_MAILER=smtp` in production `.env`
- [ ] Verify `APP_URL` is set correctly in `.env`
- [ ] Test email sending in production
- [ ] Set up queue worker with supervisor
- [ ] Monitor email delivery logs

## 🐛 Troubleshooting

### Emails not sending

1. **Check credentials**: Verify username and password are correct
2. **Check port**: Make sure MAIL_PORT=465 for SSL
3. **Check firewall**: Ensure port 465 is not blocked
4. **Check logs**: `tail -f storage/logs/laravel.log`
5. **Test connection**:
   ```bash
   telnet mail.linkadi.co.tz 465
   ```

### SSL Certificate Issues

If you get SSL errors, try:
```env
MAIL_ENCRYPTION=tls
MAIL_PORT=587
```

### Queue not processing

Make sure the queue worker is running:
```bash
php artisan queue:work
```

Check failed jobs:
```bash
php artisan queue:failed
```

Retry failed jobs:
```bash
php artisan queue:retry all
```

## 📧 Alternative Email Addresses

You may want to create additional email addresses for different purposes:

- `welcome@linkadi.co.tz` - For welcome emails
- `security@linkadi.co.tz` - For password resets
- `notifications@linkadi.co.tz` - For system notifications
- `support@linkadi.co.tz` - For user support emails

To use different addresses, update the mailable classes and notification classes accordingly.

## 📚 Additional Resources

- [Laravel Mail Documentation](https://laravel.com/docs/mail)
- [Laravel Queue Documentation](https://laravel.com/docs/queues)
- [Laravel Notifications Documentation](https://laravel.com/docs/notifications)

## ✅ Verification

Once configured, test the following:

1. **Registration Flow**:
   - Register a new user
   - Check email inbox for welcome email
   - Verify email formatting and links work

2. **Password Reset Flow**:
   - Click "Forgot Password" on login page
   - Enter email and submit
   - Check email for reset link
   - Verify link works and password can be reset

3. **Queue Processing**:
   - Check that `php artisan queue:work` is processing jobs
   - Monitor `storage/logs/laravel.log` for email sending logs

---

**Need Help?** If you encounter any issues, check the logs or contact your system administrator.
