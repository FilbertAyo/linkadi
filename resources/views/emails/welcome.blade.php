<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Linkadi</title>
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
        .feature-list {
            background-color: #f8f9fa;
            border-left: 4px solid #8e1616;
            padding: 20px;
            margin: 25px 0;
        }
        .feature-list ul {
            margin: 0;
            padding-left: 20px;
        }
        .feature-list li {
            margin-bottom: 10px;
            font-size: 15px;
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
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="email-header">
            <h1>Welcome to Linkadi! 🎉</h1>
        </div>

        <!-- Body -->
        <div class="email-body">
            <h2>Hi {{ $user->name }},</h2>
            
            <p>Thank you for joining <strong>Linkadi</strong> - your all-in-one digital profile platform!</p>
            
            <p>We're excited to have you on board. With Linkadi, you can create stunning digital profiles, share your information seamlessly, and connect with others like never before.</p>

            <div class="feature-list">
                <strong>Here's what you can do with Linkadi:</strong>
                <ul>
                    <li>Create and customize multiple digital profiles</li>
                    <li>Design beautiful, professional-looking profile pages</li>
                    <li>Generate QR codes for easy sharing</li>
                    <li>Order custom NFC cards for instant profile sharing</li>
                    <li>Track profile views and engagement</li>
                    <li>Update your information in real-time</li>
                </ul>
            </div>

            <p style="text-align: center;">
                <a href="{{ config('app.url') }}/dashboard" class="cta-button">Get Started Now</a>
            </p>

            <div class="divider"></div>

            <p><strong>Need help getting started?</strong></p>
            <p>Check out our dashboard to create your first profile. If you have any questions, our support team is here to help!</p>

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
                <small>You received this email because you registered an account at Linkadi.</small>
            </p>
        </div>
    </div>
</body>
</html>
