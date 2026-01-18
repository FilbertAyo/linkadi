# Quick Email Setup - 5 Minutes ⚡

Follow these simple steps to enable email functionality for user registration and password resets.

## Step 1: Create Email Account (2 min)

1. Log in to your **cPanel** at your hosting provider
2. Go to **Email Accounts** section
3. Click **"Create"** button
4. Fill in the details:
   - **Email:** `noreply`
   - **Domain:** `linkadi.co.tz` (should be pre-selected)
   - **Password:** Create a strong password and SAVE IT
   - **Storage:** 250 MB (default is fine)
5. Click **"Create"**

✅ You now have: `noreply@linkadi.co.tz`

## Step 2: Update .env File (1 min)

Open your `.env` file and add or update these lines:

```env
MAIL_MAILER=smtp
MAIL_HOST=mail.linkadi.co.tz
MAIL_PORT=465
MAIL_USERNAME=noreply@linkadi.co.tz
MAIL_PASSWORD=PUT_YOUR_PASSWORD_HERE
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=noreply@linkadi.co.tz
MAIL_FROM_NAME="Linkadi"
QUEUE_CONNECTION=database
```

**Important:** Replace `PUT_YOUR_PASSWORD_HERE` with the password you created in Step 1.

## Step 3: Run Database Migration (30 sec)

If you haven't already, run these commands:

```bash
php artisan queue:table
php artisan migrate
```

This creates the necessary database tables for queuing emails.

## Step 4: Start Queue Worker (30 sec)

Run this command to process email queue:

```bash
php artisan queue:work
```

**Note:** Keep this terminal window open. In production, you should use a process manager like Supervisor to keep this running.

## Step 5: Test It! (1 min)

### Test Registration Email:
1. Go to your registration page
2. Create a new test account
3. Check the email inbox for the welcome email

### Test Password Reset:
1. Go to the "Forgot Password" page
2. Enter an email address
3. Check the inbox for the password reset email

## ✅ Done!

Your email system is now configured and working!

---

## 🔧 Troubleshooting

**Email not received?**
- Check spam/junk folder
- Check `storage/logs/laravel.log` for errors
- Make sure queue worker is running (`php artisan queue:work`)

**Connection refused?**
- Verify email account password is correct
- Check if port 465 is open on your server
- Try using port 587 with TLS instead

**For Production:**
Set up a supervisor to keep the queue worker running continuously. See `EMAIL_SETUP_GUIDE.md` for details.

---

## 📧 Email Templates

Two beautiful email templates have been created:

1. **Welcome Email** - Sent when users register
   - Location: `resources/views/emails/welcome.blade.php`
   
2. **Password Reset Email** - Sent when users request password reset
   - Location: `resources/views/emails/password-reset.blade.php`

You can customize these templates to match your branding!

---

**Need more details?** Check `EMAIL_SETUP_GUIDE.md` for comprehensive documentation.
