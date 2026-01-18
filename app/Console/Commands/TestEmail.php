<?php

namespace App\Console\Commands;

use App\Mail\WelcomeEmail;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:test {email? : The email address to send test email to}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test email configuration by sending a test email';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');

        // If no email provided, ask for it
        if (!$email) {
            $email = $this->ask('What email address should we send the test email to?');
        }

        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Invalid email address!');
            return 1;
        }

        $this->info('Testing email configuration...');
        $this->newLine();

        // Display current mail configuration
        $this->info('📧 Mail Configuration:');
        $this->table(
            ['Setting', 'Value'],
            [
                ['Mailer', config('mail.default')],
                ['Host', config('mail.mailers.smtp.host')],
                ['Port', config('mail.mailers.smtp.port')],
                ['Username', config('mail.mailers.smtp.username')],
                ['From Address', config('mail.from.address')],
                ['From Name', config('mail.from.name')],
            ]
        );

        $this->newLine();

        // Ask what type of email to test
        $type = $this->choice(
            'What type of email would you like to test?',
            ['Simple', 'Welcome Email Template'],
            0
        );

        try {
            if ($type === 'Simple') {
                // Send simple test email
                Mail::raw('This is a test email from Linkadi! If you received this, your email configuration is working correctly. 🎉', function ($message) use ($email) {
                    $message->to($email)
                            ->subject('Test Email from Linkadi');
                });

                $this->newLine();
                $this->info('✅ Simple test email queued successfully!');
            } else {
                // Send welcome email template
                $testUser = new User([
                    'name' => 'Test User',
                    'email' => $email,
                ]);

                Mail::to($email)->send(new WelcomeEmail($testUser));

                $this->newLine();
                $this->info('✅ Welcome email template queued successfully!');
            }

            $this->newLine();
            $this->warn('⚠️  Email has been QUEUED. Make sure you have a queue worker running!');
            $this->info('Run: php artisan queue:work');
            
            $this->newLine();
            $this->info("📬 Check your inbox at: {$email}");
            $this->info("💡 Don't forget to check spam/junk folder if you don't see it in inbox.");

            return 0;

        } catch (\Exception $e) {
            $this->newLine();
            $this->error('❌ Failed to send email!');
            $this->error('Error: ' . $e->getMessage());
            
            $this->newLine();
            $this->warn('Common issues:');
            $this->line('  1. Check your .env mail credentials are correct');
            $this->line('  2. Make sure MAIL_MAILER is set to "smtp"');
            $this->line('  3. Verify port 465 is open on your server');
            $this->line('  4. Check storage/logs/laravel.log for detailed errors');
            
            return 1;
        }
    }
}
