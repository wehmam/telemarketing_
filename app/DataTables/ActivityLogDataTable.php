<?php

namespace App\DataTables;

use App\Models\ActivityLog;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Services\DataTable;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Builder as HtmlBuilder;

class ActivityLogDataTable extends DataTable
{
    protected $userId;

    public function setUserContext($userId)
    {
        $this->userId = $userId;
        return $this;
    }

    /**
     * Build the DataTable class.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('user_id', fn($log) => $log->user?->name ?? '—')
            ->editColumn('method', fn($log) => $log->method)
            ->editColumn('url', fn($log) => $log->url)
            ->editColumn('status_code', fn($log) => $log->status_code ?? '-')
            ->editColumn('ip_address', fn($log) => $log->ip_address ?? '-')
            ->editColumn('description', fn($log) => $log->description ?? '-')
            ->editColumn('created_at', fn($log) => $log->created_at->format('d M Y, h:i A'))
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(ActivityLog $model): QueryBuilder
    {
        $query = $model->newQuery()->with('user');

        if ($this->userId) {
            $query->where('user_id', $this->userId);
        }

        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('activity-logs-table')
            ->columns($this->getColumns())
            ->minifiedAjax(route('user-management.users.logs.data', $this->userId))
            ->dom('rt<"row"<"col-sm-12 col-md-5"l><"col-sm-12 col-md-7"p>>')
            ->addTableClass('table align-middle table-row-dashed fs-6 gy-5')
            ->setTableHeadClass('text-start text-muted fw-bold fs-7 text-uppercase gs-0')
            ->orderBy(6, 'desc'); // order by created_at
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::make('user_id')->title('User'),
            Column::make('method')->title('Method'),
            Column::make('url')->title('URL'),
            Column::make('status_code')->title('Status'),
            Column::make('ip_address')->title('IP Address'),
            Column::make('description')->title('Description'),
            Column::make('created_at')->title('Date'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'ActivityLogs_' . date('YmdHis');
    }
}
