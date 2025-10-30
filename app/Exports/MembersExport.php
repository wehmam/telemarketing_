<?php

namespace App\Exports;

use App\Models\Members;
use Illuminate\Database\Query\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithCustomChunkSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class MembersExport implements
    FromQuery,
    WithHeadings,
    WithMapping,
    WithChunkReading,
    WithCustomChunkSize,
    WithStyles
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        // Build minimal query with subqueries instead of relationship calls
        $query = Members::query()
            ->select([
                'members.id',
                'members.name',
                'members.nama_rekening',
                'members.username',
                'members.phone',
                'm.name as marketing_name',
                't.name as team_name',
                // use subselects instead of relationships to reduce queries
                \DB::raw('(SELECT MAX(transaction_date) FROM transactions WHERE transactions.member_id = members.id) as last_deposit'),
                \DB::raw('(SELECT COUNT(*) FROM transactions WHERE transactions.member_id = members.id) as trx_count'),
                \DB::raw('(SELECT SUM(amount) FROM transactions WHERE transactions.member_id = members.id) as trx_total')
            ])
            ->leftJoin('users as m', 'm.id', '=', 'members.marketing_id')
            ->leftJoin('teams as t', 't.id', '=', 'members.team_id');

        // apply filters only on columns that exist here (avoid relationship filters)
        if (!empty($this->filters['s_username'])) {
            $query->where('members.username', 'like', '%' . $this->filters['s_username'] . '%');
        }

        if (!empty($this->filters['s_phone'])) {
            $query->where('members.phone', 'like', '%' . $this->filters['s_phone'] . '%');
        }

        if (!empty($this->filters['s_nama_rekening'])) {
            $query->where('members.nama_rekening', 'like', '%' . $this->filters['s_nama_rekening'] . '%');
        }

        // optional filter for team or marketing
        if (!empty($this->filters['s_team'])) {
            $query->where('members.team_id', $this->filters['s_team']);
        }

        if (!empty($this->filters['s_marketing'])) {
            $query->where('members.marketing_id', $this->filters['s_marketing']);
        }

        return $query->orderBy('members.id', 'asc');
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
        return [
            $member->id,
            $member->name ?? '-',
            $member->nama_rekening ?? '-',
            $member->username ?? '-',
            " " . ($member->phone ?? '-'),
            $member->marketing_name ?? 'WA',
            $member->team_name ?? 'WA',
            $member->last_deposit ? date('Y-m-d', strtotime($member->last_deposit)) : '—',
            $member->trx_count ?? 0,
            $member->trx_total ?? 0,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:J1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '228B22'],
            ],
        ]);

        return [];
    }

    public function chunkSize(): int
    {
        return 2000; // 2000 rows per chunk
    }

    public function batchSize(): int
    {
        return 2000;
    }
}
