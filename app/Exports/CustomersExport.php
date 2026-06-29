<?php
namespace App\Exports;
use App\Models\Customer;
use Maatwebsite\Excel\Concerns\{FromCollection, WithHeadings, WithStyles, ShouldAutoSize};
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\{Fill, Alignment};

class CustomersExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize {
    public function collection() {
        return Customer::with(['subscriptions' => fn($q) => $q->where('status','active')->with('licenseType')])
            ->where('is_active', true)->orderBy('last_name')->get()->flatMap(function($c) {
                $subs = $c->subscriptions;
                if ($subs->isEmpty()) {
                    return [[
                        $c->id, $c->first_name, $c->last_name, $c->company ?? '',
                        $c->email, $c->phone ?? '', $c->city ?? '',
                        '', '', '', '', $c->created_at->format('d/m/Y'),
                    ]];
                }
                return $subs->map(fn($s) => [
                    $c->id, $c->first_name, $c->last_name, $c->company ?? '',
                    $c->email, $c->phone ?? '', $c->city ?? '',
                    $s->licenseType->name,
                    $s->quantity,
                    $s->end_date->format('d/m/Y'),
                    $s->billing_cycle === 'yearly' ? 'Annuale' : 'Mensile',
                    $c->created_at->format('d/m/Y'),
                ]);
            });
    }
    public function headings(): array {
        return ['ID','Nome','Cognome','Azienda','Email','Telefono','Città',
                'Licenza','Quantità','Scadenza','Ciclo','Cliente dal'];
    }
    public function styles(Worksheet $sheet) {
        $sheet->getStyle('A1:L1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 12],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1A1A1A']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $last = $sheet->getHighestRow();
        for ($i = 2; $i <= $last; $i++) {
            if ($i % 2 === 0) {
                $sheet->getStyle("A{$i}:L{$i}")->getFill()
                    ->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF2F2F2');
            }
        }
    }
}
