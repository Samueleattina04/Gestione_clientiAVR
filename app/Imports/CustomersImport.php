<?php
namespace App\Imports;
use App\Models\Customer;
use Maatwebsite\Excel\Concerns\{ToModel, WithHeadingRow, SkipsEmptyRows};
class CustomersImport implements ToModel, WithHeadingRow, SkipsEmptyRows {
    private int $count = 0;
    private array $colMap = [
        'first_name' => ['nome','first_name','first name','name'],
        'last_name'  => ['cognome','last_name','last name','surname'],
        'email'      => ['email','e-mail','mail'],
        'company'    => ['azienda','company','societa','ragione_sociale'],
        'phone'      => ['telefono','phone','tel','cellulare'],
        'city'       => ['citta','city','comune'],
    ];
    private function findCol(array $row, string $field): ?string {
        foreach ($this->colMap[$field] as $v) {
            if (array_key_exists($v, $row)) return $v;
        }
        return null;
    }
    public function model(array $row): ?Customer {
        $row = array_change_key_case($row, CASE_LOWER);
        $emailCol = $this->findCol($row, 'email');
        $firstCol = $this->findCol($row, 'first_name');
        if (!$emailCol || !$firstCol) return null;
        $email = trim($row[$emailCol] ?? '');
        $first = trim($row[$firstCol] ?? '');
        if (!$email || !$first) return null;
        if (Customer::where('email', $email)->where('is_active', true)->exists()) return null;
        $this->count++;
        return new Customer([
            'first_name' => $first,
            'last_name'  => trim($row[$this->findCol($row,'last_name')  ?? ''] ?? ''),
            'email'      => $email,
            'company'    => trim($row[$this->findCol($row,'company')     ?? ''] ?? ''),
            'phone'      => trim($row[$this->findCol($row,'phone')       ?? ''] ?? ''),
            'city'       => trim($row[$this->findCol($row,'city')        ?? ''] ?? ''),
        ]);
    }
    public function getRowCount(): int { return $this->count; }
}
