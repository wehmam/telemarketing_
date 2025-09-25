<?php

namespace App\DataTables;

use App\Models\Transaction;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Services\DataTable;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\Html\Builder as HtmlBuilder;

class TransactionDataTable extends DataTable
{
    protected ?int $memberId   = null;
    protected ?string $memberName = null;

    public function setMemberContext($memberId, $memberName): self
    {
        $this->memberId   = $memberId;
        $this->memberName = $memberName;
        return $this;
    }

    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->filter(function ($query) {
                if ($keyword = request()->get('search')['value'] ?? null) {
                    $query->where(function($q) use ($keyword) {
                        $q->whereHas('member', function($q2) use ($keyword) {
                            $q2->where('name', 'like', "%{$keyword}%")
                            ->orWhere('username', 'like', "%{$keyword}%")
                            ->orWhereHas('team', fn($q3) => $q3->where('name', 'like', "%{$keyword}%"));
                        })
                        ->orWhereHas('user', fn($q2) => $q2->where('name', 'like', "%{$keyword}%"))
                        ->orWhere('type', 'like', "%{$keyword}%")
                        ->orWhere('id', 'like', "%{$keyword}%");
                    });
                }
            })
            ->editColumn('member_id', fn($trx) => $trx->member?->name ?? '—')
            ->addColumn('username', fn($trx) => $trx->member?->username ?? '—')
            // ->editColumn('user_id', fn($trx) => $trx->user?->name ?? '—')
            ->editColumn('amount', fn($transaction) => number_format($transaction->amount, 2))
            // ->editColumn('transaction_date', fn($trx) => $trx->transaction_date->format('Y-m-d'))
            ->editColumn('transaction_date', function ($trx) {
                return $trx->transaction_date
                    ? \Carbon\Carbon::parse($trx->transaction_date)->format('Y-m-d')
                    : '—';
            })
            ->addColumn('marketing', fn($trx) => $trx->member?->marketing?->name ?? 'WA')
            ->addColumn('team', fn($trx) => $trx->member?->team?->name ?? 'WA')
            ->addColumn('followups', function ($trx) {
                $last = $trx->followups->sortByDesc('followed_up_at')->first();
                return $last
                    ? $last->user->name . '<br> (' . \Carbon\Carbon::parse($last->followed_up_at)->format('d-m-Y H:i') . ')'
                    : '<span class="badge badge-danger">Not Followed Up</span>';
            })
            ->addColumn('action', function (Transaction $transaction) {
                return view('pages.apps.transactions.components._actions', compact('transaction'));
            })
            ->rawColumns(['followups'])
            ->setRowId('id')
            ->with([
                'totalAmount' => (clone $query)->sum('amount'),
                'totalMember' => (clone $query)->distinct('member_id')->count('member_id'),
                'totalMemberDeposit' => (clone $query)->where('type', 'DEPOSIT')->sum("amount"),
                'totalMemberRedeposit' => (clone $query)->where('type', 'REDEPOSIT')->sum("amount"),
                'totalDeposit' => (clone $query)->where('type', 'DEPOSIT')->count(),
                'totalRedeposit' => (clone $query)->where('type', 'REDEPOSIT')->count(),
            ]);
    }

    public function query(Transaction $model): QueryBuilder
    {
        $query = $model->newQuery()
            ->with(['member.marketing', 'member.team', 'user', 'followups.user']);
            // ->with(['followups.user']);

        $status = request('s_status');
        $username = request('s_username');
        $phone = request('s_phone');
        $namaRekening = request('s_nama_rekening');
        $lastDepositRange = request('s_last_deposit');
        $amountDeposit = request('s_amount_deposit');
        $marketingId = request('s_marketing');
        $teamId = request('s_team');

        // Filter by amount deposit
        if ($amountDeposit && is_numeric($amountDeposit)) {
            $query->where('amount', '=', (float)$amountDeposit);
        }

        // Filter by marketing context
        if ($marketingId) {
            if ($marketingId === 'WA') {
                $query->whereHas('member', fn($q) => $q->whereNull('marketing_id'));
            } else {
                $query->whereHas('member', fn($q) => $q->where('marketing_id', $marketingId));
            }
        }

        // Filter by team context
        if ($teamId) {
            if ($teamId === 'WA') {
                $query->whereHas('member', fn($q) => $q->whereNull('team_id'));
            } else {
                $query->whereHas('member', fn($q) => $q->where('team_id', $teamId));
            }
        }

        // Filter by member context
        if ($this->memberId) {
            $model->where('member_id', $this->memberId);
        }

        if ($namaRekening) {
            $query->whereHas('member', fn($q) => $q->where('nama_rekening', 'like', '%' . $namaRekening . '%'));
        }

        if ($username) {
            $query->whereHas('member', fn($q) => $q->where('username', 'like', '%' . $username . '%'));
        }

        if ($phone) {
            $query->whereHas('member', fn($q) => $q->where('phone', 'like', '%' . $phone . '%'));
        }

        if (in_array($status, ['DEPOSIT', 'REDEPOSIT'])) {
            $query->where('type', $status);
        } elseif( $status === 'DELETED') {
            $query->onlyTrashed();
        }

        if ($lastDepositRange) {
            $dates = explode(' to ', $lastDepositRange);
            if (count($dates) === 2) {
                $startDate = \Carbon\Carbon::createFromFormat('d-m-Y', trim($dates[0]))->format('Y-m-d');
                $endDate   = \Carbon\Carbon::createFromFormat('d-m-Y', trim($dates[1]))->format('Y-m-d');

                $query->whereBetween(\DB::raw('CAST(transaction_date AS DATE)'), [$startDate, $endDate]);
            } elseif (count($dates) === 1) {
                $date = \Carbon\Carbon::createFromFormat('d-m-Y', trim($dates[0]))->format('Y-m-d');
                $query->whereDate('transaction_date', $date);
            }
        }

        return $query;
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('transactions-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->dom('rt' . "<'row'<'col-sm-12 col-md-5'l><'col-sm-12 col-md-7'p>>")
            ->addTableClass('table align-middle table-row-dashed fs-6 gy-5 dataTable no-footer text-gray-600 fw-semibold')
            ->setTableHeadClass('text-start text-muted fw-bold fs-7 text-uppercase gs-0')
            ->orderBy(2) // order by transaction_date
            ->drawCallback("function() {" . file_get_contents(resource_path('views/pages/apps/transactions/components/_draw-scripts.js')) . "}");

    }

    public function getColumns(): array
    {
        return [
            Column::make('id')->title('Transaction ID'),
            Column::make('member_id')->title('Member'),
            Column::computed('username')->title('Username'),
            Column::make('amount')->title('Amount'),
            Column::make('transaction_date')->title('Date'),
            Column::make('type')->title('Type'),
            // Column::make('user_id')->title('Insert By'),
            Column::computed('marketing')->title('Marketing'),
            Column::computed('team')->title('Team'),
            Column::computed('followups')->title('Last Follow Up')->addClass('text-center'),
            Column::computed('action')
                ->addClass('text-end text-nowrap')
                ->exportable(false)
                ->printable(false)
                ->width(100),
        ];
    }

    protected function filename(): string
    {
        return "Transactions_{$this->memberName}_" . date('YmdHis');
    }
}
