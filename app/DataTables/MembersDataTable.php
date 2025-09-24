<?php

namespace App\DataTables;

use App\Models\Members;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;

class MembersDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->rawColumns(['deleted_at'])
            ->editColumn('marketing_id', function (Members $member) {
                return $member->marketing?->name ?? 'WA';
            })
            ->editColumn('team_id', function (Members $member) {
                return $member->team?->name ?? 'WA';
            })
            // ->editColumn('deleted_at', function (Members $member) {
            //     return sprintf(
            //         '<div class="badge badge-%s fw-bold">%s</div>',
            //         $member->deleted_at ? 'danger' : 'success',
            //         $member->deleted_at ? 'Not Active' : 'Active'
            //     );
            // })
            ->addColumn('last_deposit', function (Members $member) {
                $lastDeposit = $member->transactions()->latest('transaction_date')->first();
                // return $lastDeposit ? $lastDeposit->transaction_date->format('d-m-Y H:i') : '—';
                return $lastDeposit
                    ? \Carbon\Carbon::parse($lastDeposit->transaction_date)->format('Y-m-d')
                    : '—';
            })
            // ->addColumn('type', function (Members $member) {
            //     return ($member->marketing_id && $member->team_id) ? $member->team?->name ?? 'WA' : 'WA';
            // })
            ->addColumn('action', function (Members $member) {
                return view('pages.apps.members.components._actions', compact('member'));
            })
            // ->addColumn('action', function (Members $member) {
            //     return sprintf(
            //         '<div class="text-end">
            //             <a href="%s" class="btn btn-sm btn-light-primary me-1">Edit</a>
            //             <a href="%s" class="btn btn-sm btn-light-danger">Delete</a>
            //         </div>',
            //         route('members.edit', $member->id),
            //         route('members.destroy', $member->id)
            //     );
            // })
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Members $model): QueryBuilder
    {
        $query = $model->newQuery()->with(['marketing', 'team']);

        $status = request('s_status');
        $username = request('s_username');
        $phone = request('s_phone');
        $namaRekening = request('s_nama_rekening');
        $createdAtRange = request('s_created_at');

        if ($status === 'wa') {
            $query->whereNull('marketing_id')->orWhereNull('team_id');
        } elseif ($status === 'has_team') {
            $query->whereNotNull('marketing_id')->whereNotNull('team_id');
        } else {
            $query->withTrashed();
        }

        if ($username) {
            $query->where('username', 'like', '%' . $username . '%');
        }

        if ($phone) {
            $query->where('phone', 'like', '%' . $phone . '%');
        }

        if ($namaRekening) {
            $query->where('nama_rekening', 'like', '%' . $namaRekening . '%');
        }

        if ($lastDepositRange = request('s_last_deposit')) {
            $dates = explode(' to ', $lastDepositRange);
            if (count($dates) === 2) {
                $startDate = \Carbon\Carbon::createFromFormat('d-m-Y', trim($dates[0]))->format('Y-m-d');
                $endDate   = \Carbon\Carbon::createFromFormat('d-m-Y', trim($dates[1]))->format('Y-m-d');

                $query->whereHas('transactions', function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('transaction_date', [
                        $startDate,
                        $endDate
                    ]);
                });
            } else {
                $startDate = \Carbon\Carbon::createFromFormat('d-m-Y', trim($dates[0]))->format('Y-m-d');
                $query->whereHas('transactions', function ($q) use ($startDate) {
                    $q->whereDate('transaction_date', $startDate);
                });
            }
        }

        return $query->orderBy('id', 'asc');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('members-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->dom('rt' . "<'row'<'col-sm-12 col-md-5'l><'col-sm-12 col-md-7'p>>")
            ->addTableClass('table align-middle table-row-dashed fs-6 gy-5 dataTable no-footer text-gray-600 fw-semibold')
            ->setTableHeadClass('text-start text-muted fw-bold fs-7 text-uppercase gs-0')
            ->orderBy(0)
            ->drawCallback("function() {" . file_get_contents(resource_path('views/pages/apps/members/components/_draw-scripts.js')) . "}");
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::make('name')->title('Member Name'),
            Column::make('nama_rekening')->title('Rekening Name'),
            Column::make('username'),
            Column::make('phone'),
            Column::make('marketing_id')->title('Marketing'),
            Column::make('team_id')->title('Team'),
            // Column::make('deleted_at')->title('Status'),
            Column::make('last_deposit')->title('Last Deposit'),
            // Column::make('type')->title('Member Type'), // <-- added
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
        return 'Members_' . date('YmdHis');
    }
}
