<?php

namespace App\Exports;

use App\Models\Transaction;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class TransactionsExport implements FromQuery, WithHeadings, WithMapping, WithChunkReading, WithStyles, ShouldAutoSize
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = Transaction::with(['member.marketing', 'member.team', 'user', 'followups.user']);

        // ✅ Apply filters
        if (!empty($this->filters['s_status']) && in_array($this->filters['s_status'], ['DEPOSIT', 'REDEPOSIT'])) {
            $query->where('type', $this->filters['s_status']);
        }

        if (!empty($this->filters['s_username'])) {
            $query->whereHas('member', fn($q) => $q->where('username', 'like', '%' . $this->filters['s_username'] . '%'));
        }

        if (!empty($this->filters['s_phone'])) {
            $query->whereHas('member', fn($q) => $q->where('phone', 'like', '%' . $this->filters['s_phone'] . '%'));
        }

        if (!empty($this->filters['s_nama_rekening'])) {
            $query->whereHas('member', fn($q) => $q->where('nama_rekening', 'like', '%' . $this->filters['s_nama_rekening'] . '%'));
        }

        if (!empty($this->filters['s_last_deposit'])) {
            $dates = explode(' to ', $this->filters['s_last_deposit']);

            if (count($dates) === 2) {
                $startDate = \Carbon\Carbon::createFromFormat('d-m-Y', trim($dates[0]))->format('Y-m-d');
                $endDate   = \Carbon\Carbon::createFromFormat('d-m-Y', trim($dates[1]))->format('Y-m-d');
                $query->whereBetween(\DB::raw('CAST(transaction_date AS DATE)'), [$startDate, $endDate]);
            } elseif (count($dates) === 1) {
                $date = \Carbon\Carbon::createFromFormat('d-m-Y', trim($dates[0]))->format('Y-m-d');
                $query->whereDate('transaction_date', $date);
            }
        }

        if (!empty($this->filters['s_amount_deposit']) && is_numeric($this->filters['s_amount_deposit'])) {
            $query->where('amount', '=', (float) $this->filters['s_amount_deposit']);
        }

        if (!empty($this->filters['s_marketing'])) {
            if ($this->filters['s_marketing'] === 'WA') {
                $query->whereHas('member', fn($q) => $q->whereNull('marketing_id'));
            } else {
                $query->whereHas('member', fn($q) => $q->where('marketing_id', $this->filters['s_marketing']));
            }
        }

        if (!empty($this->filters['s_team'])) {
            if ($this->filters['s_team'] === 'WA') {
                $query->whereHas('member', fn($q) => $q->whereNull('team_id'));
            } else {
                $query->whereHas('member', fn($q) => $q->where('team_id', $this->filters['s_team']));
            }
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'Transaction ID',
            'Member',
            'Username',
            'Amount',
            'Date',
            'Type',
            'Insert By',
            'Marketing',
            'Team',
        ];
    }

    public function map($trx): array
    {
        return [
            $trx->id,
            $trx->member?->name ?? '—',
            $trx->member?->username ?? '—',
            number_format($trx->amount, 2),
            $trx->transaction_date ? \Carbon\Carbon::parse($trx->transaction_date)->format('Y-m-d') : '—',
            $trx->type,
            $trx->user?->name ?? '—',
            $trx->member?->marketing?->name ?? 'WA',
            $trx->member?->team?->name ?? 'WA',
        ];
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function styles(Worksheet $sheet)
    {
        $lastColumn = $sheet->getHighestColumn();

        // Style header row (row 1)
        $sheet->getStyle("A1:{$lastColumn}1")->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 11,
                'color' => ['rgb' => 'FFFFFF'], // white font
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '228B22'], // green (ForestGreen)
            ],
        ]);

        // Add thin borders to all cells
        $sheet->getStyle('A1:' . $sheet->getHighestColumn() . $sheet->getHighestRow())
            ->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ],
                ],
            ]);

        return [];
    }

}
