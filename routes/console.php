<?php
use Illuminate\Support\Facades\Schedule;
Schedule::command('subscriptions:check-expiring')->dailyAt('08:00');
