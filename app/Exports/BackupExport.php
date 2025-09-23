<?php

namespace App\Exports;

use App\Models\Transaction;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\Exportable;

class BackupExport implements FromQuery, WithHeadings, WithMapping, WithChunkReading
{
    use Exportable;

    protected $beforeDate;

    public function __construct($beforeDate)
    {
        $this->beforeDate = $beforeDate;
    }

    public function query()
    {
        return Transaction::with(['member.marketing', 'member.team', 'user'])
            ->where('transaction_date', '<', $this->beforeDate);
    }

    public function map($trx): array
    {
        return [
            $trx->id,
            $trx->transaction_date,
            $trx->amount,
            $trx->type,
            $trx->username,
            $trx->phone,
            $trx->nama_rekening,
            $trx->member?->name ?? '—',
            $trx->member?->username ?? '—',
            $trx->user?->name ?? '—',
            $trx->member?->marketing?->name ?? '—',
            $trx->member?->team?->name ?? '—',
            $trx->created_at,
            $trx->updated_at,
        ];
    }

    public function headings(): array
    {
        return [
            'ID',
            'Transaction Date',
            'Amount',
            'Type',
            'Username',
            'Phone',
            'Nama Rekening',
            'Member Name',
            'Member Username',
            'Inserted By',
            'Marketing',
            'Team',
            'Created At',
            'Updated At',
        ];
    }

    public function chunkSize(): int
    {
        return 100; // proses per 10 ribu biar aman untuk data besar
    }
}
