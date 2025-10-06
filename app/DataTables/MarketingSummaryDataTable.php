<?php

namespace App\DataTables;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Facades\DataTables;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;

class MarketingSummaryDataTable extends DataTable
{
        // public function dataTable($query)
        // {
        //     return DataTables::of($query)
        //         ->editColumn('member_daftar', fn($row) => $row->member_daftar)
        //         ->editColumn('total_deposit_amount', fn($row) => "Rp." . number_format($row->total_deposit_amount))
        //         ->editColumn('total_redeposit_amount', fn($row) => "Rp." . number_format($row->total_redeposit_amount))
        //         ->editColumn('total_deposit_transactions', fn($row) => $row->total_deposit_transactions)
        //         ->editColumn('total_redeposit_transactions', fn($row) => $row->total_redeposit_transactions)
        //         ->setRowId('marketing');
        // }
    public function dataTable($query)
    {
        $dataTable = DataTables::of($query)
            ->editColumn('member_daftar', fn($row) => $row->member_daftar)
            ->editColumn('total_deposit_amount', fn($row) => "Rp." . number_format($row->total_deposit_amount))
            ->editColumn('total_redeposit_amount', fn($row) => "Rp." . number_format($row->total_redeposit_amount))
            ->editColumn('total_deposit_transactions', fn($row) => $row->total_deposit_transactions)
            ->editColumn('total_redeposit_transactions', fn($row) => $row->total_redeposit_transactions)
            ->setRowId('marketing');

        $rows = collect(DB::table(DB::raw("({$query->toSql()}) as sub"))
            ->mergeBindings($query)
            ->get());

        $totals = [
            'totalAmount'          => $rows->sum('total_deposit_amount') + $rows->sum('total_redeposit_amount'),
            'totalMember'          => $rows->sum('member_daftar'),
            'totalDeposit'         => $rows->sum('total_deposit_transactions'),
            'totalRedeposit'       => $rows->sum('total_redeposit_transactions'),
            'totalMemberDeposit'   => $rows->sum('total_deposit_amount'),
            'totalMemberRedeposit' => $rows->sum('total_redeposit_amount'),
        ];

        return $dataTable->with($totals);
    }


    public function query(): \Illuminate\Database\Query\Builder
    {
        $start = Carbon::now()->startOfMonth()->format('Y-m-d');
        $end   =  Carbon::now()->endOfMonth()->format('Y-m-d');
        $rangeDate = request("s_date");
        if ($rangeDate) {
            $dates = explode(' to ', $rangeDate);
            if (count($dates) === 2) {
                $start  = \Carbon\Carbon::createFromFormat('d-m-Y', trim($dates[0]))->format('Y-m-d');
                $end    = \Carbon\Carbon::createFromFormat('d-m-Y', trim($dates[1]))->format('Y-m-d');
            } elseif (count($dates) === 1) {
                $start = \Carbon\Carbon::createFromFormat('d-m-Y', trim($dates[0]))->format('Y-m-d');
                $end   = $start;
            }
        }

        $marketingId = request("s_marketing");
        $teamId      = request("s_team");

        if ($marketingId === 'WA') {
            $marketingId = null;
        }

        if ($teamId === 'WA') {
            $teamId = null;
        }

        // $filterMarketing = $marketingId ? " AND m.marketing_id = '$marketingId' " : "";
        // $filterTeam      = $teamId ? " AND t_team.team_id = '$teamId' " : "";

        return DB::table(DB::raw("(
            SELECT
                CASE WHEN marketing_id IS NULL THEN 'WA' ELSE marketing_name END AS marketing,
                team_name,
                MAX(start_kerja) AS start_kerja,
                COALESCE(SUM(member_in_period),0) AS member_daftar,
                COALESCE(SUM(deposit_amount),0) AS total_deposit_amount,
                COALESCE(SUM(deposit_count),0) AS total_deposit_transactions,
                COALESCE(SUM(redeposit_amount),0) AS total_redeposit_amount,
                COALESCE(SUM(redeposit_count),0) AS total_redeposit_transactions,
                COALESCE(SUM(total_followups),0) AS total_followups
            FROM (
                -- === Bagian members ===
                SELECT
                    m.marketing_id,
                    u.name AS marketing_name,
                    t_team.name AS team_name,
                    m.created_at AS start_kerja,
                    CASE
                        WHEN m.created_at BETWEEN '$start' AND '$end' THEN 1
                        ELSE 0
                    END AS member_in_period,
                    0 AS deposit_amount,
                    0 AS deposit_count,
                    0 AS redeposit_amount,
                    0 AS redeposit_count,
                    0 AS total_followups
                FROM members m
                LEFT JOIN users u ON u.id = m.marketing_id
                LEFT JOIN (
                    SELECT tm.user_id, MIN(tn.name) AS name
                    FROM team_members tm
                    JOIN teams tn ON tn.id = tm.team_id
                    GROUP BY tm.user_id
                ) AS t_team ON t_team.user_id = m.marketing_id

                UNION ALL

                -- === Bagian transaksi ===
                SELECT
                    m.marketing_id,
                    u.name AS marketing_name,
                    t_team.name AS team_name,
                    m.created_at AS start_kerja,
                    0 AS member_in_period,
                    CASE WHEN t.type = 'DEPOSIT' THEN t.amount ELSE 0 END AS deposit_amount,
                    CASE WHEN t.type = 'DEPOSIT' THEN 1 ELSE 0 END AS deposit_count,
                    CASE WHEN t.type = 'REDEPOSIT' THEN t.amount ELSE 0 END AS redeposit_amount,
                    CASE WHEN t.type = 'REDEPOSIT' THEN 1 ELSE 0 END AS redeposit_count,
                    0 AS total_followups
                FROM transactions t
                LEFT JOIN members m ON m.id = t.member_id
                LEFT JOIN users u ON u.id = m.marketing_id
                LEFT JOIN (
                    SELECT tm.user_id, MIN(tn.name) AS name
                    FROM team_members tm
                    JOIN teams tn ON tn.id = tm.team_id
                    GROUP BY tm.user_id
                ) AS t_team ON t_team.user_id = m.marketing_id
                WHERE t.transaction_date BETWEEN '$start' AND '$end'


                UNION ALL

                -- === Bagian FOLLOWUP ===
                SELECT
			        m.marketing_id,
			        u.name AS marketing_name,
			        t_team.name AS team_name,
			        m.created_at AS start_kerja,
			        0 AS member_in_period,
			        0 AS deposit_amount,
			        0 AS deposit_count,
			        0 AS redeposit_amount,
			        0 AS redeposit_count,
			        1 AS total_followups
			    FROM transaction_followups tf
			    LEFT JOIN transactions t ON t.id = tf.transaction_id
			    LEFT JOIN members m ON m.id = t.member_id
			    LEFT JOIN users u ON u.id = m.marketing_id
			    LEFT JOIN (
			        SELECT tm.user_id, MIN(tn.name) AS name
			        FROM team_members tm
			        JOIN teams tn ON tn.id = tm.team_id
			        GROUP BY tm.user_id
			    ) AS t_team ON t_team.user_id = m.marketing_id
			    WHERE tf.followed_up_at BETWEEN '$start 00:00:00' AND '$end 23:59:00'
            ) AS combined
            GROUP BY marketing_id, marketing_name, team_name
        ) as summary"));
    }


    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('marketing-summary-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->dom('rt' . "<'row'<'col-sm-12 col-md-5'l><'col-sm-12 col-md-7'p>>")
            ->addTableClass('table align-middle table-row-dashed fs-6 gy-5 dataTable no-footer text-gray-600 fw-semibold')
            ->setTableHeadClass('text-start text-muted fw-bold fs-7 text-uppercase gs-0')
            ->orderBy(5)
            ->drawCallback("function() {" . file_get_contents(resource_path('views/pages/apps/summary/components/_draw-scripts.js')) . "}");
    }

    public function getColumns(): array
    {
        return [
            Column::make('marketing')->title('Marketing'),
            Column::make('team_name')->title('Team'),
            Column::make('start_kerja')->title('Start Work'),
            Column::make('member_daftar')->title('Member Register'),
            Column::make('total_deposit_amount')->title('Amount Deposit'),
            Column::make('total_deposit_transactions')->title('Count Deposit'),
            Column::make('total_redeposit_amount')->title('Amount Redeposit'),
            Column::make('total_redeposit_transactions')->title('Count Redeposit'),
            Column::make('total_followups')->title('Total Followup'),
        ];
    }

    protected function filename(): string
    {
        return 'Marketing_Summary_' . date('YmdHis');
    }
}
