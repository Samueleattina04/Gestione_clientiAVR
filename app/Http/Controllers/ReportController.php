<?php
namespace App\Http\Controllers;
use App\Models\Subscription;
use Illuminate\Http\Request;
class ReportController extends Controller {
    public function expiring(Request $request) {
        $days = $request->integer('days', 180);
        $subs = Subscription::with(['customer', 'licenseType'])
            ->where('status', 'active')
            ->whereDate('end_date', '>=', now())
            ->whereDate('end_date', '<=', now()->addDays($days))
            ->orderBy('end_date')
            ->get();
        return view('reports.expiring', compact('subs', 'days'));
    }
}
