<?php

namespace App\DataTables;

use App\Models\Members;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Services\DataTable;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;

class MemberAssignTransactionDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('transactions_count', fn($member) => $member->transactions->count())
            ->addColumn('last_transaction_date', function ($member) {
                $last = $member->transactions->sortByDesc('transaction_date')->first();
                return $last
                    ? \Carbon\Carbon::parse($last->transaction_date)->format('Y-m-d')
                    : '—';
            })
            ->addColumn('marketing', fn($member) => $member->marketing?->name ?? '—')
            ->addColumn('team', fn($member) => $member->team?->name ?? '—')
            ->addColumn('action', fn($member) => view('pages.apps.transaction-assign.components._actions', compact('member')))
            ->editColumn('phone', function ($member) {
                if ($member->phone && str_starts_with($member->phone, '62')) {
                    return '0' . substr($member->phone, 2);
                }
                return $member->phone ?? '—';
            })
            ->rawColumns(['action'])
            ->setRowId('id');
    }

    public function query(Members $model): QueryBuilder
    {
        $query = $model->newQuery()
            // ->whereHas('transactions') // only members who have transactions
            ->with(['transactions', 'marketing', 'team']);

        // filters
        $username = request('s_username');
        $phone = request('s_phone');
        $namaRekening = request('s_nama_rekening');
        $team = request('s_team');
        $marketingName = request('s_marketing_name');
        $marketingId = request('s_marketing');
        $lastDepositRange = request('s_last_deposit');

        if ($namaRekening) {
            $query->where('nama_rekening', 'like', '%' . $namaRekening . '%');
        }

        if ($username) {
            $query->where('username', 'like', '%' . $username . '%');
        }

        if ($phone) {
            $query->where('phone', 'like', '%' . $phone . '%');
        }

        if ($team) {
            $query->whereHas('team', function ($q) use ($team) {
                $q->where('teams.id', $team);
            });
        }

        if ($marketingId) {
            $query->whereHas('marketing', function ($q) use ($marketingId) {
                $q->where('id', $marketingId);
            });
        }

        return $query;
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('member-transactions-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->dom('rt' . "<'row'<'col-sm-12 col-md-5'l><'col-sm-12 col-md-7'p>>")
            ->addTableClass('table align-middle table-row-dashed fs-6 gy-5 dataTable no-footer text-gray-600 fw-semibold')
            ->setTableHeadClass('text-start text-muted fw-bold fs-7 text-uppercase gs-0')
            ->orderBy(3)
            ->drawCallback("function() {" . file_get_contents(resource_path('views/pages/apps/transaction-assign/components/_draw-scripts.js')) . "}");

    }

    public function getColumns(): array
    {
        return [
            // Column::make('id')->title('Member ID'),
            Column::make('name')->title('Member Name'),
            Column::make('username')->title('Username'),
            Column::make('phone')->title('Phone'),
            Column::make('nama_rekening')->title('Nama Rekening'),
            Column::computed('transactions_count')->title('Total Deposit'),
            Column::computed('last_transaction_date')->title('Last Deposit'),
            Column::computed('marketing')->title('Marketing'),
            Column::computed('team')->title('Team'),
            Column::computed('action')
                ->addClass('text-end text-nowrap')
                ->exportable(false)
                ->printable(false)
                ->width(100),
        ];
    }

    protected function filename(): string
    {
        return "MembersWithTransactions_" . date('YmdHis');
    }
}
