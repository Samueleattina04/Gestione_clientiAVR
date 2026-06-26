<?php
namespace App\Imports;

use App\Models\{Customer, LicenseType, Subscription};
use Maatwebsite\Excel\Concerns\{ToCollection, WithHeadingRow, SkipsEmptyRows};
use Illuminate\Support\Collection;
use Carbon\Carbon;

class CustomersImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    private int $customersCreated  = 0;
    private int $customersSkipped  = 0;
    private int $subscriptionsCreated = 0;
    private int $licensesCreated   = 0;

    private array $colMap = [
        'first_name'    => ['nome','first_name','first name','name','firstname'],
        'last_name'     => ['cognome','last_name','last name','surname','lastname'],
        'email'         => ['email','e-mail','mail'],
        'company'       => ['azienda','company','societa','ragione_sociale','ragione sociale'],
        'phone'         => ['telefono','phone','tel','cellulare'],
        'city'          => ['citta','city','comune'],
        'fiscal_code'   => ['codice_fiscale','codice fiscale','fiscal_code','cf'],
        'vat_number'    => ['partita_iva','partita iva','vat','p_iva','piva'],
        'address'       => ['indirizzo','address'],
        'notes'         => ['note','notes'],
        // Abbonamento
        'license_name'  => ['licenza','license','licenza_microsoft','microsoft_license','piano','plan','prodotto','product'],
        'quantity'      => ['quantita','quantity','qty','seats','licenze','num_licenze'],
        'start_date'    => ['data_inizio','start_date','data inizio','inizio'],
        'end_date'      => ['data_scadenza','end_date','data scadenza','scadenza','expiry','expiry_date'],
        'billing_cycle' => ['ciclo','billing_cycle','fatturazione','billing','frequenza'],
        'custom_price'  => ['prezzo','price','custom_price','costo','importo'],
    ];

    private function findCol(array $row, string $field): ?string
    {
        foreach ($this->colMap[$field] as $v) {
            if (array_key_exists($v, $row)) return $v;
        }
        return null;
    }

    private function val(array $row, string $field): string
    {
        $col = $this->findCol($row, $field);
        return $col ? trim((string)($row[$col] ?? '')) : '';
    }

    public function collection(Collection $rows): void
    {
        foreach ($rows as $rawRow) {
            $row = array_change_key_case($rawRow->toArray(), CASE_LOWER);

            $email = $this->val($row, 'email');
            $first = $this->val($row, 'first_name');
            if (!$email || !$first) continue;

            // ── Cliente: crea o recupera ──────────────────────────
            $customer = Customer::where('email', $email)->first();

            if (!$customer) {
                $customer = Customer::create([
                    'first_name'  => $first,
                    'last_name'   => $this->val($row, 'last_name'),
                    'email'       => $email,
                    'company'     => $this->val($row, 'company'),
                    'phone'       => $this->val($row, 'phone'),
                    'city'        => $this->val($row, 'city'),
                    'fiscal_code' => $this->val($row, 'fiscal_code'),
                    'vat_number'  => $this->val($row, 'vat_number'),
                    'address'     => $this->val($row, 'address'),
                    'notes'       => $this->val($row, 'notes'),
                    'is_active'   => true,
                ]);
                $this->customersCreated++;
            } else {
                $this->customersSkipped++;
            }

            // ── Abbonamento: solo se c'è la licenza ──────────────
            $licenseName = $this->val($row, 'license_name');
            if (!$licenseName) continue;

            // Cerca la licenza (case-insensitive), altrimenti creala
            $license = LicenseType::whereRaw('LOWER(name) = ?', [strtolower($licenseName)])->first();
            if (!$license) {
                $price = $this->val($row, 'custom_price');
                $license = LicenseType::create([
                    'name'          => $licenseName,
                    'category'      => 'Microsoft 365',
                    'price_monthly' => is_numeric($price) ? (float)$price : 0,
                    'price_yearly'  => is_numeric($price) ? (float)$price * 12 : 0,
                    'is_active'     => true,
                ]);
                $this->licensesCreated++;
            }

            // Calcola date
            $startRaw = $this->val($row, 'start_date');
            $endRaw   = $this->val($row, 'end_date');

            $startDate = $this->parseDate($startRaw) ?? now();
            $endDate   = $this->parseDate($endRaw);

            // Ciclo di fatturazione
            $cycleRaw = strtolower($this->val($row, 'billing_cycle'));
            $cycle = str_contains($cycleRaw, 'ann') || str_contains($cycleRaw, 'year') ? 'yearly' : 'monthly';

            // Se non c'è end_date, la calcola in base al ciclo
            if (!$endDate) {
                $endDate = $cycle === 'yearly'
                    ? $startDate->copy()->addYear()
                    : $startDate->copy()->addMonth();
            }

            $qty = (int)($this->val($row, 'quantity') ?: 1);
            $customPrice = $this->val($row, 'custom_price');

            // Evita duplicati: stesso cliente + stessa licenza + stessa scadenza
            $exists = Subscription::where('customer_id', $customer->id)
                ->where('license_type_id', $license->id)
                ->where('end_date', $endDate->toDateString())
                ->exists();

            if (!$exists) {
                Subscription::create([
                    'customer_id'     => $customer->id,
                    'license_type_id' => $license->id,
                    'quantity'        => $qty > 0 ? $qty : 1,
                    'start_date'      => $startDate->toDateString(),
                    'end_date'        => $endDate->toDateString(),
                    'billing_cycle'   => $cycle,
                    'custom_price'    => is_numeric($customPrice) && $customPrice > 0 ? (float)$customPrice : null,
                    'status'          => $endDate->isPast() ? 'expired' : 'active',
                ]);
                $this->subscriptionsCreated++;
            }
        }
    }

    private function parseDate(string $value): ?Carbon
    {
        if (!$value) return null;
        // Numero seriale Excel
        if (is_numeric($value)) {
            return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float)$value));
        }
        $formats = ['d/m/Y', 'Y-m-d', 'd-m-Y', 'd.m.Y', 'm/d/Y', 'Y/m/d'];
        foreach ($formats as $fmt) {
            try {
                return Carbon::createFromFormat($fmt, $value);
            } catch (\Exception $e) {}
        }
        try { return Carbon::parse($value); } catch (\Exception $e) {}
        return null;
    }

    public function getStats(): array
    {
        return [
            'customers_created'    => $this->customersCreated,
            'customers_skipped'    => $this->customersSkipped,
            'subscriptions_created'=> $this->subscriptionsCreated,
            'licenses_created'     => $this->licensesCreated,
        ];
    }
}
