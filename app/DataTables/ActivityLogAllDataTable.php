<?php

namespace App\DataTables;

use App\Models\ActivityLog;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;

class ActivityLogAllDataTable extends DataTable
{
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

        $activityDate = request()->get('s_activity_date');
        $marketingId   = request()->get('s_marketing');
        $teamId        = request()->get('s_team');

        if ($activityDate) {
            $dates = explode(' to ', $activityDate);
            if (count($dates) === 2) {
                $startDate = \Carbon\Carbon::createFromFormat('d-m-Y', trim($dates[0]))->format('Y-m-d');
                $endDate   = \Carbon\Carbon::createFromFormat('d-m-Y', trim($dates[1]))->format('Y-m-d');

                $query->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
            } elseif( count($dates) === 1 ) {
                $activityDate = \Carbon\Carbon::createFromFormat('d-m-Y', trim($dates[0]))->format('Y-m-d');
                $query->whereDate('created_at', $activityDate);
            }
        }

        if ($marketingId) {
            $query->where("user_id", $marketingId);
        }

        if ($teamId) {
            $query->whereHas('user.teams', function ($q) use ($teamId) {
                $q->where('teams.id', $teamId);
            });
        }

        return $query;
    }

    /**
     * Optional HTML builder.
     */
    public function html()
    {
        return $this->builder()
            ->setTableId('activity-logs-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->dom('rt' . "<'row'<'col-sm-12 col-md-5'l><'col-sm-12 col-md-7'p>>")
            ->addTableClass('table align-middle table-row-dashed fs-6 gy-5 dataTable no-footer text-gray-600 fw-semibold')
            ->setTableHeadClass('text-start text-muted fw-bold fs-7 text-uppercase gs-0')
            ->orderBy(0)
            ->drawCallback("function() {" . file_get_contents(resource_path('views/pages/apps/activity-logs/components/_draw-scripts.js')) . "}");
    }

    /**
     * Get columns.
     */
    protected function getColumns(): array
    {
        return [
            Column::make('user_id')->title('User'),
            Column::make('method')->title('Method'),
            Column::make('url')->title('URL'),
            Column::make('status_code')->title('Status'),
            Column::make('ip_address')->title('IP Address'),
            Column::make('description')->title('Description'),
            Column::make('created_at')->title('Executed At'),
        ];
    }
}
