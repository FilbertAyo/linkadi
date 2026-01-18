# 📧 Email Setup - Quick Start

## 🎯 What You Need to Know

Your Laravel application now has **complete email functionality** for:
- ✅ **Welcome emails** when users register
- ✅ **Password reset emails** when users forget their password

## ⚡ Quick Setup (5 Minutes)

### 1. Create Email Account

In your **cPanel**:
- Go to Email Accounts
- Create: `noreply@linkadi.co.tz`
- Save the password!

### 2. Update .env File

Add these lines to your `.env` file:

```env
MAIL_MAILER=smtp
MAIL_HOST=mail.linkadi.co.tz
MAIL_PORT=465
MAIL_USERNAME=noreply@linkadi.co.tz
MAIL_PASSWORD=your_actual_password_here
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=noreply@linkadi.co.tz
MAIL_FROM_NAME="Linkadi"
QUEUE_CONNECTION=database
```

Replace `your_actual_password_here` with your actual email password.

### 3. Setup Database Queue

```bash
php artisan queue:table
php artisan migrate
```

### 4. Start Queue Worker

```bash
php artisan queue:work
```

Keep this running in a terminal (or use Supervisor in production).

### 5. Test It!

```bash
php artisan email:test your@email.com
```

Or register a new user and check for the welcome email!

## 📖 Full Documentation

- **Quick Guide:** `QUICK_EMAIL_SETUP.md`
- **Detailed Guide:** `EMAIL_SETUP_GUIDE.md`
- **Implementation Summary:** `EMAIL_IMPLEMENTATION_SUMMARY.md`

## 🎨 Email Templates

Beautiful HTML email templates are located at:
- Welcome Email: `resources/views/emails/welcome.blade.php`
- Password Reset: `resources/views/emails/password-reset.blade.php`

Customize these to match your brand!

## 🐛 Troubleshooting

**Emails not sending?**
```bash
# Check logs
tail -f storage/logs/laravel.log

# Verify queue is working
php artisan queue:work --verbose
```

**Common issues:**
- Wrong password → Check .env file
- Port blocked → Verify port 465 is open
- Queue not running → Run `php artisan queue:work`

## ✅ You're All Set!

Once configured, emails will automatically send when:
1. A new user registers → Welcome email
2. User forgets password → Reset email

---

**Need help?** Check the documentation files or Laravel logs for more details.
