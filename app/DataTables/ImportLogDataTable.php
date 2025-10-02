<?php

namespace App\DataTables;

use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Facades\DataTables;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Builder as HtmlBuilder;

class ImportLogDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     */
    public function dataTable($query)
    {
        return DataTables::of($query)
            ->editColumn('batch_code', fn($row) => $row->batch_code ?? '-')
            ->editColumn('latest_date', fn($row) =>
                $row->latest_date ? date('d M Y H:i', strtotime($row->latest_date)) : '-'
            )
            ->addColumn('action', function ($row) {
                return view('pages.apps.import-logs.components._actions', [
                    'batch_code' => $row->batch_code,
                    'latest_date' => $row->latest_date
                ]);
            })
            ->setRowId('batch_code');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query()
    {
        return DB::table(DB::raw("(
            SELECT batch_code, MAX(import_at) AS latest_date
            FROM (
                SELECT batch_code, import_at FROM starterkit.members WHERE import_at IS NOT NULL
                UNION ALL
                SELECT batch_code, import_at FROM starterkit.transactions WHERE import_at IS NOT NULL
            ) AS all_batches
            GROUP BY batch_code
        ) as q"));
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('import-logs-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->dom('rt' . "<'row'<'col-sm-12 col-md-5'l><'col-sm-12 col-md-7'p>>")
            ->addTableClass('table align-middle table-row-dashed fs-6 gy-5 dataTable no-footer text-gray-600 fw-semibold')
            ->setTableHeadClass('text-start text-muted fw-bold fs-7 text-uppercase gs-0')
            ->orderBy(1, 'desc')
            ->drawCallback("function() {" . file_get_contents(resource_path('views/pages/apps/import-logs/components/_draw-scripts.js')) . "}");
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::make('batch_code')->title('Batch Code'),
            Column::make('latest_date')->title('Latest Import At'),
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
        return 'ImportLogs_' . date('YmdHis');
    }
}
