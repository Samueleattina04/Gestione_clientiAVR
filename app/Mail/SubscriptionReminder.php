<?php
namespace App\Mail;
use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
class SubscriptionReminder extends Mailable {
    use Queueable, SerializesModels;
    public function __construct(
        public Subscription $subscription,
        public string $recipientType,
        public int $monthsLeft
    ) {}
    public function envelope(): \Illuminate\Mail\Mailables\Envelope {
        $lic = $this->subscription->licenseType->name;
        return new \Illuminate\Mail\Mailables\Envelope(
            subject: "[A.V.R. Informatica] Scadenza {$lic} — {$this->monthsLeft} mes" . ($this->monthsLeft === 1 ? 'e' : 'i')
        );
    }
    public function content(): \Illuminate\Mail\Mailables\Content {
        return new \Illuminate\Mail\Mailables\Content(
            view: 'emails.subscription_reminder'
        );
    }
}
