<?php

namespace App\Http\Controllers;

use App\DataTables\MarketingSummaryDataTable;
use App\Exports\ExportSummary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;


class SummaryController extends Controller
{
    public function index(MarketingSummaryDataTable $dataTable)
    {
        $teams = \App\Models\Team::orderBy('id', 'asc')->get();
        $marketings = \App\Models\User::whereHas('roles', function ($query) {
            $query->where('name', 'marketing');
        })->orderBy('id', 'asc')->get();

        return $dataTable->render('pages.apps.summary.index', compact('teams', 'marketings'));
    }

    public function export(Request $request)
    {
        // $start = now()->startOfMonth()->format('Y-m-d');
        // $end = now()->endOfMonth()->format('Y-m-d');

        // // Ambil filter date dari request
        // $rangeDate = $request->get('s_date');
        // $fileName = 'marketing_summary_' . $start . '.xlsx';
        // if ($rangeDate) {
        //     $dates = explode(' to ', $rangeDate);
        //     if (count($dates) === 2) {
        //         $start = \Carbon\Carbon::createFromFormat('d-m-Y', trim($dates[0]))->format('Y-m-d');
        //         $end = \Carbon\Carbon::createFromFormat('d-m-Y', trim($dates[1]))->format('Y-m-d');
        //         $fileName = 'marketing_summary_' . $start . '_to_' . $end . '.xlsx';
        //     } elseif (count($dates) === 1) {
        //         $start = \Carbon\Carbon::createFromFormat('d-m-Y', trim($dates[0]))->format('Y-m-d');
        //         $end = $start;
        //         $fileName = 'marketing_summary_' . $start . '.xlsx';
        //     }
        // }

        // // Ambil data berdasarkan query datatable
        // $report = $dataTable->query()->get();
        //  $report = $report->map(function($row) {
        //     $row->member_daftar = (int) $row->member_daftar;
        //     $row->total_deposit_transactions = (int) $row->total_deposit_transactions;
        //     $row->total_redeposit_transactions = (int) $row->total_redeposit_transactions;
        //     return $row;
        // });

        $start = \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d');
        $end   =  \Carbon\Carbon::now()->endOfMonth()->format('Y-m-d');
        $rangeDate = request("s_date");
        $fileName = 'marketing_summary_' . $start . '.xlsx';
        if ($rangeDate) {
            $dates = explode(' to ', $rangeDate);
            if (count($dates) === 2) {
                $start  = \Carbon\Carbon::createFromFormat('d-m-Y', trim($dates[0]))->format('Y-m-d');
                $end    = \Carbon\Carbon::createFromFormat('d-m-Y', trim($dates[1]))->format('Y-m-d');
                $fileName = 'marketing_summary_' . $start . '_to_' . $end . '.xlsx';
            } elseif (count($dates) === 1) {
                $start = \Carbon\Carbon::createFromFormat('d-m-Y', trim($dates[0]))->format('Y-m-d');
                $end   = $start;
                $fileName = 'marketing_summary_' . $start . '.xlsx';
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

        $reports = DB::table(DB::raw("(
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

                -- === BAGIAN FOLLOWUP ===
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
        ) as summary"))->get();

        // return Excel::download(new ExportSummary($report, $start, $end), $fileName);
        return \Maatwebsite\Excel\Facades\Excel::download( new \App\Exports\ExportSummary($reports, $start, $end), $fileName );
    }
}
