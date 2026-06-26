<?php
namespace App\Exports;
use App\Models\Subscription;
use Maatwebsite\Excel\Concerns\{FromCollection, WithHeadings, WithStyles, ShouldAutoSize};
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\{Fill, Alignment};
class SubscriptionsExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize {
    public function collection() {
        return Subscription::with(['customer','licenseType'])->orderBy('end_date')->get()->map(function($s) {
            $days = $s->days_to_expiry;
            return [
                $s->id, $s->customer->full_name, $s->customer->company ?? '', $s->customer->email,
                $s->licenseType->name, $s->quantity,
                $s->start_date->format('d/m/Y'), $s->end_date->format('d/m/Y'),
                $days, $s->billing_cycle === 'monthly' ? 'Mensile' : 'Annuale',
                number_format($s->effective_price, 2), ucfirst($s->status),
            ];
        });
    }
    public function headings(): array {
        return ['ID','Cliente','Azienda','Email','Licenza','Qtà','Inizio','Scadenza','Giorni','Ciclo','Prezzo €','Stato'];
    }
    public function styles(Worksheet $sheet) {
        $sheet->getStyle('A1:L1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1A1A1A']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
    }
}
