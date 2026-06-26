<?php
namespace App\Http\Controllers;
use App\Models\{Customer, Subscription, LicenseType};
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
class DashboardController extends Controller {
    public function index() {
        $today = now()->startOfDay();
        $totalCustomers = Customer::where('is_active', true)->count();
        $totalSubs      = Subscription::where('status', 'active')->count();
        $expiring30  = Subscription::active()->expiringIn(30)->count();
        $expiring90  = Subscription::active()->expiringIn(90)->count();
        $expiring180 = Subscription::active()->expiringIn(180)->count();
        $expired     = Subscription::where('status', 'active')->where('end_date', '<', $today)->count();
        $recentSubs  = Subscription::with(['customer', 'licenseType'])
            ->where('status', 'active')
            ->orderBy('end_date')
            ->limit(10)->get();
        $activeSubs = Subscription::with('licenseType')->where('status', 'active')->get();
        $monthlyRevenue = $activeSubs->sum(fn($s) =>
            $s->billing_cycle === 'yearly' ? $s->effective_price / 12 : $s->effective_price
        );
        $yearlyRevenue = $activeSubs->sum(fn($s) =>
            $s->billing_cycle === 'yearly' ? $s->effective_price : $s->effective_price * 12
        );
        $licenseStats = DB::table('license_types')
            ->join('subscriptions', 'license_types.id', '=', 'subscriptions.license_type_id')
            ->where('subscriptions.status', 'active')
            ->select('license_types.name', DB::raw('count(*) as count'))
            ->groupBy('license_types.name')
            ->orderByDesc('count')
            ->get();
        return view('dashboard', compact('totalCustomers','totalSubs','expiring30','expiring90','expiring180','expired','recentSubs','monthlyRevenue','yearlyRevenue','licenseStats'));
    }
}
