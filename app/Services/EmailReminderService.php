<?php
namespace App\Services;
use App\Models\{Subscription, EmailLog};
use Illuminate\Support\Facades\Mail;
use App\Mail\SubscriptionReminder;

class EmailReminderService {
    public function sendReminder(Subscription $subscription, int $months): array {
        $results = [];
        $resellerEmail = config('avr.reseller_email');
        $customerEmail = $subscription->customer->email;

        foreach ([['customer', $customerEmail], ['reseller', $resellerEmail]] as [$type, $email]) {
            if (!$email) continue;
            try {
                Mail::to($email)->send(new SubscriptionReminder($subscription, $type, $months));
                $success = true; $error = null;
            } catch (\Exception $e) {
                $success = false; $error = $e->getMessage();
            }
            EmailLog::create([
                'subscription_id' => $subscription->id,
                'recipient_email' => $email,
                'recipient_type'  => $type,
                'email_type'      => "reminder_{$months}m",
                'success'         => $success,
                'error_message'   => $error,
            ]);
            $results[] = ['type' => $type, 'email' => $email, 'success' => $success];
        }
        return $results;
    }

    public function checkAndSendExpiring(): void {
        $active = Subscription::with(['customer','licenseType'])->active()->get();
        foreach ($active as $sub) {
            $d = $sub->days_to_expiry;
            if ($d < 0) { $sub->update(['status' => 'expired']); continue; }
            if ($d >= 175 && $d <= 185 && !$sub->reminder_6m_sent) {
                $this->sendReminder($sub, 6); $sub->update(['reminder_6m_sent' => true]);
            } elseif ($d >= 28 && $d <= 32 && !$sub->reminder_1m_sent) {
                $this->sendReminder($sub, 1); $sub->update(['reminder_1m_sent' => true]);
            } elseif ($d >= 5 && $d <= 8 && !$sub->reminder_1w_sent) {
                $this->sendReminder($sub, 0); $sub->update(['reminder_1w_sent' => true]);
            }
        }
    }
}
