<?php
namespace App\Http\Controllers;
use App\Models\{Customer, LicenseType, Subscription};
use Illuminate\Http\Request;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CustomersExport;
use App\Imports\CustomersImport;

class CustomerController extends Controller {
    public function index(Request $request) {
        $q = $request->get('q', '');
        $query = Customer::where('is_active', true);
        if ($q) {
            $query->where(fn($qb) => $qb
                ->where('first_name', 'like', "%$q%")
                ->orWhere('last_name',  'like', "%$q%")
                ->orWhere('email',      'like', "%$q%")
                ->orWhere('company',    'like', "%$q%")
            );
        }
        $customers = $query->orderBy('last_name')->paginate(20)->withQueryString();
        return view('customers.index', compact('customers', 'q'));
    }

    public function create() {
        $licenses = LicenseType::where('is_active', true)->orderBy('name')->get();
        return view('customers.create', compact('licenses'));
    }

    public function store(Request $request) {
        $data = $request->validate([
            'first_name'  => 'required|string|max:100',
            'last_name'   => 'required|string|max:100',
            'email'       => 'required|email|max:120',
            'phone'       => 'nullable|string|max:20',
            'company'     => 'nullable|string|max:150',
            'fiscal_code' => 'nullable|string|max:16',
            'vat_number'  => 'nullable|string|max:20',
            'address'     => 'nullable|string|max:255',
            'city'        => 'nullable|string|max:100',
            'notes'       => 'nullable|string',
        ]);
        $customer = Customer::create($data);

        if ($request->filled('license_type_id') && $request->filled('start_date') && $request->filled('end_date')) {
            Subscription::create([
                'customer_id'      => $customer->id,
                'license_type_id'  => $request->license_type_id,
                'quantity'         => $request->integer('quantity', 1),
                'start_date'       => $request->start_date,
                'end_date'         => $request->end_date,
                'billing_cycle'    => $request->billing_cycle ?? 'yearly',
                'custom_price'     => $request->filled('custom_price') ? $request->custom_price : null,
                'notes'            => $request->sub_notes,
            ]);
        }

        return redirect()->route('customers.show', $customer)->with('success', "Cliente {$customer->full_name} aggiunto con successo!");
    }

    public function show(Customer $customer) {
        $customer->load(['subscriptions.licenseType']);
        return view('customers.show', compact('customer'));
    }

    public function edit(Customer $customer) {
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer) {
        $data = $request->validate([
            'first_name'  => 'required|string|max:100',
            'last_name'   => 'required|string|max:100',
            'email'       => 'required|email|max:120',
            'phone'       => 'nullable|string|max:20',
            'company'     => 'nullable|string|max:150',
            'fiscal_code' => 'nullable|string|max:16',
            'vat_number'  => 'nullable|string|max:20',
            'address'     => 'nullable|string|max:255',
            'city'        => 'nullable|string|max:100',
            'notes'       => 'nullable|string',
        ]);
        $customer->update($data);
        return redirect()->route('customers.show', $customer)->with('success', 'Cliente aggiornato!');
    }

    public function destroy(Customer $customer) {
        $customer->update(['is_active' => false]);
        return redirect()->route('customers.index')->with('info', "Cliente {$customer->full_name} archiviato.");
    }

    public function export() {
        return Excel::download(new CustomersExport, 'avr_clienti_' . now()->format('Ymd_His') . '.xlsx');
    }

    public function importForm() {
        return view('customers.import');
    }

    public function import(Request $request) {
        $request->validate(['file' => 'required|mimes:xlsx,xls|max:10240']);
        $import = new CustomersImport;
        Excel::import($import, $request->file('file'));
        $s = $import->getStats();
        $msg = "Import completato: {$s['customers_created']} clienti creati, {$s['customers_skipped']} già presenti, {$s['subscriptions_created']} abbonamenti importati";
        if ($s['licenses_created'] > 0) {
            $msg .= ", {$s['licenses_created']} licenze create automaticamente";
        }
        return redirect()->route('customers.index')->with('success', $msg . '.');
    }
}
