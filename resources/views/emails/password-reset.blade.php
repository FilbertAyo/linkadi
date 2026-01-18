<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Your Password</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333333;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 0;
        }
        .email-header {
            background: #8e1616;
            padding: 40px 20px;
            text-align: center;
            color: #ffffff;
        }
        .email-header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
        }
        .email-body {
            padding: 40px 30px;
        }
        .email-body h2 {
            color: #333333;
            font-size: 24px;
            margin-bottom: 20px;
        }
        .email-body p {
            margin-bottom: 15px;
            font-size: 16px;
            line-height: 1.8;
        }
        .cta-button {
            display: inline-block;
            background: #8e1616;
            color: #ffffff;
            text-decoration: none;
            padding: 15px 35px;
            border-radius: 5px;
            font-weight: 600;
            margin: 20px 0;
            font-size: 16px;
        }
        .cta-button:hover {
            opacity: 0.9;
        }
        .alert-box {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 25px 0;
            border-radius: 4px;
        }
        .alert-box p {
            margin: 0;
            font-size: 14px;
            color: #856404;
        }
        .email-footer {
            background-color: #f8f9fa;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #e9ecef;
        }
        .email-footer p {
            margin: 5px 0;
            font-size: 14px;
            color: #6c757d;
        }
        .divider {
            height: 1px;
            background-color: #e9ecef;
            margin: 30px 0;
        }
        .security-note {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            margin-top: 20px;
        }
        .security-note p {
            margin: 5px 0;
            font-size: 14px;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="email-header">
            <h1>🔒 Password Reset Request</h1>
        </div>

        <!-- Body -->
        <div class="email-body">
            <h2>Hi there,</h2>
            
            <p>We received a request to reset the password for your Linkadi account.</p>
            
            <p>Click the button below to choose a new password. This link will expire in {{ config('auth.passwords.'.config('auth.defaults.passwords').'.expire') }} minutes for security reasons.</p>

            <p style="text-align: center;">
                <a href="{{ $url }}" class="cta-button">Reset Your Password</a>
            </p>

            <div class="alert-box">
                <p><strong>⚠️ Security Notice:</strong> If you didn't request a password reset, please ignore this email. Your password will remain unchanged.</p>
            </div>

            <div class="divider"></div>

            <p><strong>Having trouble with the button?</strong></p>
            <p>Copy and paste this link into your browser:</p>
            <p style="word-break: break-all; color: #8e1616; font-size: 14px;">{{ $url }}</p>

            <div class="security-note">
                <p><strong>Security Tips:</strong></p>
                <p>• Never share your password with anyone</p>
                <p>• Use a strong, unique password</p>
                <p>• Consider using a password manager</p>
            </div>

            <p style="margin-top: 30px;">
                Best regards,<br>
                <strong>The Linkadi Team</strong>
            </p>
        </div>

        <!-- Footer -->
        <div class="email-footer">
            <p><strong>Linkadi</strong></p>
            <p>{{ config('app.url') }}</p>
            <p style="margin-top: 15px;">
                <small>This is an automated security email from Linkadi.</small>
            </p>
        </div>
    </div>
</body>
</html>
