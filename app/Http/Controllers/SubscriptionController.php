<?php
namespace App\Http\Controllers;
use App\Models\{Subscription, Customer, LicenseType};
use App\Services\EmailReminderService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SubscriptionsExport;

class SubscriptionController extends Controller {
    public function index(Request $request) {
        $status = $request->get('status', 'active');
        $q      = $request->get('q', '');
        $query  = Subscription::with(['customer', 'licenseType'])
            ->where('status', $status);
        if ($q) {
            $query->whereHas('customer', fn($qb) => $qb
                ->where('first_name', 'like', "%$q%")
                ->orWhere('last_name',  'like', "%$q%")
                ->orWhere('email',      'like', "%$q%")
            )->orWhereHas('licenseType', fn($qb) => $qb->where('name', 'like', "%$q%"));
        }
        $subscriptions = $query->orderBy('end_date')->paginate(25)->withQueryString();
        return view('subscriptions.index', compact('subscriptions', 'status', 'q'));
    }

    public function create(Request $request) {
        $customers = Customer::where('is_active', true)->orderBy('last_name')->get();
        $licenses  = LicenseType::where('is_active', true)->orderBy('name')->get();
        $preCustomer = $request->integer('customer_id');
        return view('subscriptions.create', compact('customers', 'licenses', 'preCustomer'));
    }

    public function store(Request $request) {
        $data = $request->validate([
            'customer_id'                => 'required|exists:customers,id',
            'license_type_id'            => 'required|exists:license_types,id',
            'quantity'                   => 'required|integer|min:1',
            'start_date'                 => 'required|date',
            'end_date'                   => 'required|date|after:start_date',
            'billing_cycle'              => 'required|in:monthly,yearly',
            'custom_price'               => 'nullable|numeric|min:0',
            'microsoft_tenant_id'        => 'nullable|string|max:100',
            'microsoft_subscription_id'  => 'nullable|string|max:100',
            'notes'                      => 'nullable|string',
        ]);
        Subscription::create($data);
        return redirect()->route('subscriptions.index')->with('success', 'Abbonamento aggiunto!');
    }

    public function edit(Subscription $subscription) {
        $customers = Customer::where('is_active', true)->orderBy('last_name')->get();
        $licenses  = LicenseType::where('is_active', true)->orderBy('name')->get();
        return view('subscriptions.edit', compact('subscription', 'customers', 'licenses'));
    }

    public function update(Request $request, Subscription $subscription) {
        $data = $request->validate([
            'customer_id'                => 'required|exists:customers,id',
            'license_type_id'            => 'required|exists:license_types,id',
            'quantity'                   => 'required|integer|min:1',
            'start_date'                 => 'required|date',
            'end_date'                   => 'required|date|after:start_date',
            'billing_cycle'              => 'required|in:monthly,yearly',
            'custom_price'               => 'nullable|numeric|min:0',
            'status'                     => 'required|in:active,expired,cancelled,suspended',
            'microsoft_tenant_id'        => 'nullable|string|max:100',
            'microsoft_subscription_id'  => 'nullable|string|max:100',
            'notes'                      => 'nullable|string',
        ]);
        $data['auto_renew'] = $request->boolean('auto_renew');
        $subscription->update($data);
        return redirect()->route('subscriptions.index')->with('success', 'Abbonamento aggiornato!');
    }

    public function destroy(Subscription $subscription) {
        $subscription->update(['status' => 'cancelled']);
        return redirect()->route('subscriptions.index')->with('info', 'Abbonamento annullato.');
    }

    public function sendReminder(Subscription $subscription) {
        $days    = $subscription->days_to_expiry;
        $months  = max(1, (int) round($days / 30));
        $service = new EmailReminderService;
        $results = $service->sendReminder($subscription, $months);
        $ok = collect($results)->where('success', true)->count();

        // Aggiorna il flag corrispondente così l'automatico non rimanda
        if ($ok > 0) {
            if ($days >= 150) {
                $subscription->update(['reminder_6m_sent' => true]);
            } elseif ($days >= 14) {
                $subscription->update(['reminder_1m_sent' => true]);
            } else {
                $subscription->update(['reminder_1w_sent' => true]);
            }
        }

        $msg = "Promemoria inviato a $ok/" . count($results) . " destinatari.";
        return back()->with($ok > 0 ? 'success' : 'danger', $msg);
    }

    public function export() {
        return Excel::download(new SubscriptionsExport, 'avr_abbonamenti_' . now()->format('Ymd_His') . '.xlsx');
    }
}
