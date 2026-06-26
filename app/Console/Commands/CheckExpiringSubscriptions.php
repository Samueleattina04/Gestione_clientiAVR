<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use App\Services\EmailReminderService;
class CheckExpiringSubscriptions extends Command {
    protected $signature   = 'subscriptions:check-expiring';
    protected $description = 'Controlla scadenze e invia promemoria email';
    public function handle(): void {
        $this->info('Controllo scadenze abbonamenti...');
        (new EmailReminderService)->checkAndSendExpiring();
        $this->info('Completato.');
    }
}
