<?php
namespace App\Exports;
use App\Models\Customer;
use Maatwebsite\Excel\Concerns\{FromCollection, WithHeadings, WithStyles, WithColumnWidths, ShouldAutoSize};
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\{Fill, Font, Color, Alignment};
class CustomersExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize {
    public function collection() {
        return Customer::where('is_active', true)->orderBy('last_name')->get()->map(fn($c) => [
            $c->id, $c->first_name, $c->last_name, $c->company ?? '', $c->email,
            $c->phone ?? '', $c->city ?? '', $c->active_subscriptions_count,
            $c->created_at->format('d/m/Y'),
        ]);
    }
    public function headings(): array {
        return ['ID','Nome','Cognome','Azienda','Email','Telefono','Città','Abbonamenti Attivi','Cliente dal'];
    }
    public function styles(Worksheet $sheet) {
        $sheet->getStyle('A1:I1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 12],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1A1A1A']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $last = $sheet->getHighestRow();
        for ($i = 2; $i <= $last; $i++) {
            if ($i % 2 === 0) {
                $sheet->getStyle("A{$i}:I{$i}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF2F2F2');
            }
        }
    }
}
