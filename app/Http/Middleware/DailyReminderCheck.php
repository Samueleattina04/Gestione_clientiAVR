<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Services\EmailReminderService;
use App\Http\Controllers\AdminSettingsController;

class DailyReminderCheck
{
    public function handle(Request $request, Closure $next)
    {
        $key = 'reminder_check_' . now()->toDateString();

        AdminSettingsController::applySmtpFromDb();

        if (!Cache::has($key)) {
            Cache::put($key, true, now()->endOfDay());
            try {
                app(EmailReminderService::class)->checkAndSendExpiring();
            } catch (\Exception $e) {
                // Non bloccare la richiesta se l'email fallisce
            }
        }

        return $next($request);
    }
}
