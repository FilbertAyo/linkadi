<?php

namespace App\Listeners;

use App\Mail\WelcomeEmail;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendWelcomeEmail
{
    // Removed ShouldQueue to send emails immediately
    // use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Registered $event): void
    {
        // Send welcome email to the newly registered user
        Mail::to($event->user->email)->send(new WelcomeEmail($event->user));
    }

    /**
     * Handle a job failure.
     */
    public function failed(Registered $event, \Throwable $exception): void
    {
        // Log the error or handle the failure
        \Log::error('Failed to send welcome email', [
            'user_id' => $event->user->id,
            'error' => $exception->getMessage()
        ]);
    }
}
