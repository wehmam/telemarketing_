<?php

namespace App\DataTables;

use App\Models\TransactionFollowup;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Services\DataTable;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Builder as HtmlBuilder;

class TransactionFollowupDataTable extends DataTable
{
    protected $memberId;

    public function setMemberContext($memberId)
    {
        $this->memberId = $memberId;
        return $this;
    }

    /**
     * Build the DataTable class.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('user_id', fn($followup) => $followup->user?->name ?? '—')
            ->editColumn('transaction_id', fn($followup) => $followup->transaction?->id ?? '—')
            ->editColumn('note', fn($followup) => $followup->note ?? '-')
            ->editColumn('followed_up_at', fn($followup) => \Carbon\Carbon::parse($followup->followed_up_at)->format('d M Y, h:i A'))
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(TransactionFollowup $model): QueryBuilder
    {
        $query = $model->newQuery()
            ->with(['user', 'transaction']);

        if ($this->memberId) {
            // Only follow-ups for transactions belonging to this member
            $query->whereHas('transaction', fn($q) => $q->where('member_id', $this->memberId));
        }

        return $query->orderBy('followed_up_at', 'desc');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('followups-table')
            ->columns($this->getColumns())
            ->minifiedAjax(route('members.followups.data', $this->memberId))
            ->dom('rt<"row"<"col-sm-12 col-md-5"l><"col-sm-12 col-md-7"p>>')
            ->addTableClass('table align-middle table-row-dashed fs-6 gy-5')
            ->setTableHeadClass('text-start text-muted fw-bold fs-7 text-uppercase gs-0')
            ->orderBy(3, 'desc'); // order by followed_up_at
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::make('transaction_id')->title('Transaction ID'),
            Column::make('user_id')->title('Followed Up By'),
            Column::make('note')->title('Note'),
            Column::make('followed_up_at')->title('Follow Up Date'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'TransactionFollowups_' . date('YmdHis');
    }
}
