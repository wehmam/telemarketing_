<?php

namespace App\DataTables;

use App\Models\Members;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;

class MembersFollowUpDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('team_id', fn($member) => $member->team?->name ?? '—')
            ->editColumn('marketing_id', fn($member) => $member->marketing?->name ?? '—')
            ->addColumn('total_transactions', fn($member) => $member->transactions()->count())
            ->addColumn('last_transaction_date', function ($member) {
                $last = $member->transactions()
                    ->orderByDesc('transaction_date')
                    ->orderByDesc('created_at')
                    ->first();
                return $last ? \Carbon\Carbon::parse($last->transaction_date)->format("d F Y") : '—';
            })
            ->addColumn('total_followups', fn($member) => $member->followups()->count())
            ->addColumn('last_followup_by', function ($member) {
                $last = $member->followups()
                    ->orderByDesc('followed_up_at')
                    ->orderByDesc('created_at')
                    ->first();
                return $last ? $last->user->name : '—';
            })
            ->addColumn('total_deposit', fn($member) => "Rp. " . number_format( ($member->transactions()->sum("amount") ?? 0) , 0, ",", "." ))
            ->addColumn('last_deposit_amount', fn($member) => "Rp. " . number_format(($member->lastTransaction?->amount ?? '0')  , 0, ",", "."))
            ->addColumn('action', fn($member) => view('pages.apps.followup-member.components._actions', compact('member')))
            ->rawColumns(['action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Members $model): QueryBuilder
    {
        $query = $model->newQuery()->with(['team', 'marketing', 'transactions', 'followups.user']);

        // Optional filtering
        if ($username = request('s_username')) {
            $query->where('username', 'like', "%{$username}%");
        }
        if ($phone = request('s_phone')) {
            $query->where('phone', 'like', "%{$phone}%");
        }
        if ($namaRekening = request('s_nama_rekening')) {
            $query->where('nama_rekening', 'like', "%{$namaRekening}%");
        }

        if ($teamId = request('s_team')) {
            $query->where('team_id', $teamId);
        }

        if ($marketingId = request('s_marketing')) {
            $query->where('marketing_id', $marketingId);
        }

        return $query->orderBy('id', 'asc');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('members-followup-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->dom('rt' . "<'row'<'col-sm-12 col-md-5'l><'col-sm-12 col-md-7'p>>")
            ->addTableClass('table align-middle table-row-dashed fs-6 gy-5 dataTable no-footer text-gray-600 fw-semibold')
            ->setTableHeadClass('text-start text-muted fw-bold fs-7 text-uppercase gs-0')
            ->orderBy(0)
            ->drawCallback("function() {" . file_get_contents(resource_path('views/pages/apps/followup-member/components/_draw-scripts.js')) . "}");
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::make('name')->title('Member Name'),
            Column::make('team_id')->title('Team'),
            Column::make('marketing_id')->title('Marketing'),
            Column::computed('total_transactions')->title('Total Transactions'),
            Column::computed('total_deposit')->title('Total Deposit'),
            Column::computed('last_transaction_date')->title('Last Deposit Date'),
            Column::computed('last_deposit_amount')->title('Last Deposit Amount'),
            Column::computed('total_followups')->title('Total Followups'),
            Column::computed('last_followup_by')->title('Last Followup By'),
            Column::computed('action')
                ->addClass('text-end text-nowrap')
                ->exportable(false)
                ->printable(false)
                ->width(100),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'MembersFollowUp_' . date('YmdHis');
    }
}
