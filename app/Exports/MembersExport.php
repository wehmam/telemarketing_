<?php

namespace App\Exports;

use App\Models\Members;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;

class MembersExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithColumnFormatting
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = Members::with(['marketing', 'team', 'transactions']);

        // Apply filters (same as DataTable)
        if (!empty($this->filters['s_status'])) {
            if ($this->filters['s_status'] === 'wa') {
                $query->whereNull('marketing_id')->orWhereNull('team_id');
            } elseif ($this->filters['s_status'] === 'has_team') {
                $query->whereNotNull('marketing_id')->whereNotNull('team_id');
            } else {
                $query->withTrashed();
            }
        }

        if (!empty($this->filters['s_username'])) {
            $query->where('username', 'like', '%' . $this->filters['s_username'] . '%');
        }

        if (!empty($this->filters['s_phone'])) {
            $query->where('phone', 'like', '%' . $this->filters['s_phone'] . '%');
        }

        if (!empty($this->filters['s_nama_rekening'])) {
            $query->where('nama_rekening', 'like', '%' . $this->filters['s_nama_rekening'] . '%');
        }

        if (!empty($this->filters['s_last_deposit'])) {
            $dates = explode(' to ', $this->filters['s_last_deposit']);
            if (count($dates) === 2) {
                $startDate = \Carbon\Carbon::createFromFormat('d-m-Y', trim($dates[0]))->format('Y-m-d');
                $endDate   = \Carbon\Carbon::createFromFormat('d-m-Y', trim($dates[1]))->format('Y-m-d');

                $query->whereHas('transactions', function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('transaction_date', [$startDate, $endDate]);
                });
            } else {
                $date = \Carbon\Carbon::createFromFormat('d-m-Y', trim($dates[0]))->format('Y-m-d');
                $query->whereHas('transactions', fn($q) => $q->whereDate('transaction_date', $date));
            }
        }

        if (!empty($this->filters['s_marketing'])) {
            if ($this->filters['s_marketing'] === 'WA') {
                $query->whereNull('marketing_id');
            } else {
                $query->where('marketing_id', $this->filters['s_marketing']);
            }
        }

        if (!empty($this->filters['s_team'])) {
            if ($this->filters['s_team'] === 'WA') {
                $query->whereNull('team_id');
            } else {
                $query->where('team_id', $this->filters['s_team']);
            }
        }

        return $query->orderBy('id', 'asc');
    }

    public function headings(): array
    {
        return [
            'ID',
            'Member Name',
            'Rekening Name',
            'Username',
            'Phone',
            'Marketing',
            'Team',
            'Last Deposit',
            'Total Transactions',
            'Nominal Total Transactions',
        ];
    }

    public function map($member): array
    {
        $lastDeposit = $member->transactions()->latest('transaction_date')->first();

        return [
            $member->id,
            $member->name,
            $member->nama_rekening,
            $member->username,
            " " . $member->phone,
            $member->marketing?->name ?? 'WA',
            $member->team?->name ?? 'WA',
            $lastDeposit ? \Carbon\Carbon::parse($lastDeposit->transaction_date)->format('Y-m-d') : '—',
            $member->transactions()->count() ?? 0,
            $member->transactions()->sum("amount") ?? 0,
        ];
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function styles(Worksheet $sheet)
    {
        $lastColumn = $sheet->getHighestColumn();
        $lastRow = $sheet->getHighestRow();


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

        // $sheet->getStyle("E2:E{$lastRow}")
        //     ->getNumberFormat()
        //     ->setFormatCode(NumberFormat::FORMAT_TEXT);

        $sheet->getStyle("J2:J{$lastRow}")
            ->getNumberFormat()
            ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

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

    public function columnFormats(): array
    {
        return [
            'E' => NumberFormat::FORMAT_TEXT, // Kolom E (Phone) jadi text asli
        ];
    }
}
