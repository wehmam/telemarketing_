<?php

namespace App\DataTables;

use App\Models\Transaction;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Services\DataTable;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\Html\Builder as HtmlBuilder;

class MemberTransactionsDataTable extends DataTable
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
            ->editColumn('amount', fn($transaction) => number_format($transaction->amount, 2))
            ->editColumn('transaction_date', fn($transaction) => $transaction->transaction_date->format('Y-m-d'))
            ->addColumn('followups', function ($transaction) {
                if ($transaction->followups->isEmpty()) {
                    return '<span class="badge bg-danger">Not Followed Up</span>';
                }

                return $transaction->followups->map(function ($f) {
                    return $f->user->name . ' (' . $f->followed_up_at->format('Y-m-d H:i') . ')';
                })->implode('<br>');
            })
            ->rawColumns(['followups'])
            ->setRowId('id');
    }

    public function query(Transaction $model): QueryBuilder
    {
        return $model->newQuery()
            ->with(['followups.user'])
            ->where('member_id', $this->memberId);
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('transactions-table')
            ->columns($this->getColumns())
            ->minifiedAjax(route('members.transactions.data', $this->memberId))
            ->dom('rt' . "<'row'<'col-sm-12 col-md-5'l><'col-sm-12 col-md-7'p>>")
            ->addTableClass('table align-middle table-row-dashed fs-6 gy-5 dataTable no-footer text-gray-600 fw-semibold')
            ->setTableHeadClass('text-start text-muted fw-bold fs-7 text-uppercase gs-0')
            ->orderBy(2); // order by transaction_date
    }

    public function getColumns(): array
    {
        return [
            Column::make('id')->title('ID'),
            Column::make('amount')->title('Amount'),
            Column::make('transaction_date')->title('Date'),
            Column::make('type')->title('Type'),
            Column::make('username')->title('Username'),
            Column::make('phone')->title('Phone'),
            Column::computed('followups')->title('Follow Ups'),
        ];
    }

    protected function filename(): string
    {
        return "Transactions_{$this->memberName}_" . date('YmdHis');
    }
}
