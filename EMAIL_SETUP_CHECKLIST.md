# ✅ Email Setup Checklist

Use this checklist to ensure your email system is properly configured and working.

## 📋 Pre-Setup Checklist

- [ ] You have access to cPanel for your hosting
- [ ] You can create email accounts
- [ ] Port 465 (SMTP) is not blocked on your server
- [ ] You have a code editor to edit `.env` file

## 🔧 Configuration Checklist

### 1. Email Account Creation
- [ ] Logged into cPanel
- [ ] Created email account: `noreply@linkadi.co.tz`
- [ ] Set a strong password
- [ ] Saved the password securely
- [ ] Verified account was created successfully

### 2. Environment Configuration
- [ ] Opened `.env` file in editor
- [ ] Added/Updated `MAIL_MAILER=smtp`
- [ ] Added/Updated `MAIL_HOST=mail.linkadi.co.tz`
- [ ] Added/Updated `MAIL_PORT=465`
- [ ] Added/Updated `MAIL_USERNAME=noreply@linkadi.co.tz`
- [ ] Added/Updated `MAIL_PASSWORD=your_actual_password`
- [ ] Added/Updated `MAIL_ENCRYPTION=ssl`
- [ ] Added/Updated `MAIL_FROM_ADDRESS=noreply@linkadi.co.tz`
- [ ] Added/Updated `MAIL_FROM_NAME="Linkadi"`
- [ ] Added/Updated `QUEUE_CONNECTION=database`
- [ ] Saved `.env` file
- [ ] Password is correct (no typos!)

### 3. Database Setup
- [ ] Ran `php artisan queue:table`
- [ ] Ran `php artisan migrate`
- [ ] Verified migrations completed successfully
- [ ] Checked database for `jobs` table
- [ ] Checked database for `failed_jobs` table

### 4. Queue Worker Setup
- [ ] Started queue worker: `php artisan queue:work`
- [ ] Queue worker is running without errors
- [ ] Kept terminal window open (or configured Supervisor for production)

## 🧪 Testing Checklist

### Basic Email Test
- [ ] Ran `php artisan email:test your@email.com`
- [ ] Command executed without errors
- [ ] Checked `storage/logs/laravel.log` for any issues
- [ ] Received test email in inbox (or spam folder)
- [ ] Email displays correctly
- [ ] Images and styling load properly

### Registration Email Test
- [ ] Opened registration page
- [ ] Filled in registration form with test data
- [ ] Submitted registration
- [ ] Registration successful
- [ ] User created in database
- [ ] Checked email inbox for welcome email
- [ ] Welcome email received
- [ ] Email displays correctly
- [ ] All links in email work
- [ ] Dashboard link redirects correctly

### Password Reset Email Test
- [ ] Opened login page
- [ ] Clicked "Forgot Password" link
- [ ] Entered valid email address
- [ ] Submitted forgot password form
- [ ] Success message displayed
- [ ] Checked email inbox for reset email
- [ ] Reset email received
- [ ] Email displays correctly
- [ ] Reset link works
- [ ] Can reset password successfully
- [ ] Reset link expires after use

## 🔍 Verification Checklist

### Queue System
- [ ] Queue worker processes jobs without errors
- [ ] Jobs are being removed from queue after processing
- [ ] No jobs stuck in "processing" status
- [ ] Failed jobs table is empty (or issues resolved)
- [ ] Logs show successful email sending

### Email Delivery
- [ ] Emails arrive within 1-2 minutes
- [ ] Emails not going to spam folder (or SPF/DKIM configured)
- [ ] Email formatting looks professional
- [ ] Images and logos display correctly
- [ ] Links are clickable and work
- [ ] Email is mobile-responsive

### Error Handling
- [ ] Tested with invalid email addresses (handled gracefully)
- [ ] Tested with non-existent users (appropriate error messages)
- [ ] Failed email jobs are logged
- [ ] Application doesn't crash if email fails

## 🚀 Production Checklist

### Before Going Live
- [ ] All tests passed successfully
- [ ] `.env` file has production email credentials
- [ ] `APP_URL` is set to production domain
- [ ] `MAIL_MAILER` is set to `smtp` (not `log`)
- [ ] Queue worker configured with Supervisor
- [ ] Supervisor configured to auto-restart queue worker
- [ ] Email templates reviewed and approved
- [ ] Email content is professional and error-free
- [ ] Contact information is correct

### DNS and Deliverability
- [ ] SPF record configured for domain
- [ ] DKIM configured for better deliverability
- [ ] DMARC policy set up
- [ ] MX records configured correctly
- [ ] Domain not blacklisted (check with email blacklist checker)

### Monitoring Setup
- [ ] Error logging configured
- [ ] Email delivery monitoring in place
- [ ] Alert system for failed emails
- [ ] Regular log review scheduled
- [ ] Backup email system considered

## 📊 Performance Checklist

- [ ] Queue worker performance is acceptable
- [ ] Email sending doesn't slow down application
- [ ] Database queue table is being cleaned regularly
- [ ] Failed jobs are monitored and handled
- [ ] Email templates load quickly

## 🔒 Security Checklist

- [ ] `.env` file not committed to version control
- [ ] Email password is strong and unique
- [ ] SMTP connection uses SSL/TLS encryption
- [ ] Password reset links expire appropriately (60 minutes)
- [ ] Email templates don't expose sensitive information
- [ ] Rate limiting configured for password resets

## 📱 Compatibility Checklist

Test emails on multiple platforms:
- [ ] Gmail (desktop)
- [ ] Gmail (mobile app)
- [ ] Outlook (desktop)
- [ ] Outlook (mobile app)
- [ ] Apple Mail (iOS)
- [ ] Apple Mail (macOS)
- [ ] Yahoo Mail
- [ ] Other major email clients

## 📚 Documentation Checklist

- [ ] Team knows where email configuration is documented
- [ ] Team knows how to troubleshoot email issues
- [ ] Team knows how to customize email templates
- [ ] Team knows how to monitor email queue
- [ ] Emergency contact for email issues identified

## 🎯 Optional Enhancements

Consider these for better email experience:
- [ ] Email verification for new users
- [ ] Unsubscribe functionality
- [ ] Email preferences management
- [ ] Email analytics (open rates, click rates)
- [ ] A/B testing for email templates
- [ ] Transactional email service (SendGrid, Mailgun)

## ✅ Final Verification

- [ ] All critical checkboxes above are checked
- [ ] At least 2 successful test emails sent
- [ ] No errors in logs
- [ ] Team briefed on email system
- [ ] Backup plan in place if emails fail
- [ ] Ready for production! 🚀

---

## 📝 Notes Section

Use this space to note any issues, customizations, or important information:

```
Date: _______________
Completed by: _______________

Notes:
_________________________________________________
_________________________________________________
_________________________________________________
_________________________________________________
```

---

## 🆘 If Something Goes Wrong

1. **Check logs first:** `tail -f storage/logs/laravel.log`
2. **Verify queue is running:** `php artisan queue:work --verbose`
3. **Check failed jobs:** `php artisan queue:failed`
4. **Retry failed jobs:** `php artisan queue:retry all`
5. **Test configuration:** `php artisan email:test`
6. **Review documentation:** Check `EMAIL_SETUP_GUIDE.md`

---

**Last Updated:** January 18, 2026
**Status:** Ready for Configuration
