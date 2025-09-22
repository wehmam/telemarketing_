<?php

namespace App\Http\Controllers;

use App\Exports\ReportExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ExportReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('pages.apps.export.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $typeReport = $request->input('type_report');
            $periode = $request->periode;
            $dates = explode(" to ", $periode);

            $start = trim($dates[0]);
            $end = isset($dates[1]) ? trim($dates[1]) : $start; // kalau cuma 1 tanggal, pakai start juga

            $startDate = \Carbon\Carbon::createFromFormat('d-m-Y', $start)
                        ->startOfDay()
                        ->format('Y-m-d');

            $endDate = \Carbon\Carbon::createFromFormat('d-m-Y', $end)
                        ->endOfDay()
                        ->format('Y-m-d');
            $report = collect();

            // Choose query based on type
            switch ($typeReport) {
                case 'summary_employee':
                    $sql = "
                        SELECT
                            CASE
                                WHEN marketing_id IS NULL THEN 'WA'
                                ELSE marketing_name
                            END AS marketing,
                            team_name,
                            MIN(start_kerja) AS start_kerja,

                            -- jumlah member yang daftar pada periode
                            COALESCE(SUM(member_in_period),0) AS member_daftar,

                            -- total nominal deposit
                            COALESCE(SUM(deposit_amount),0) AS total_deposit_amount,

                            -- jumlah transaksi deposit
                            COALESCE(SUM(deposit_count),0) AS total_deposit_transactions

                        FROM (
                            -- gabungkan semua marketing + WA
                            SELECT
                                m.marketing_id,
                                u.name AS marketing_name,
                                t_team.name AS team_name,
                                u.created_at AS start_kerja,
                                CASE WHEN m.created_at BETWEEN '$startDate 00:00:00' AND '$endDate 23:59:59' THEN 1 ELSE 0 END AS member_in_period,
                                0 AS deposit_amount,
                                0 AS deposit_count
                            FROM members m
                            LEFT JOIN users u ON u.id = m.marketing_id
                            LEFT JOIN (
                                SELECT tm.user_id, MIN(tn.name) AS name
                                FROM team_members tm
                                JOIN teams tn ON tn.id = tm.team_id
                                GROUP BY tm.user_id
                            ) AS t_team ON t_team.user_id = m.marketing_id

                            UNION ALL

                            SELECT
                                m.marketing_id,
                                u.name AS marketing_name,
                                t_team.name AS team_name,
                                0 AS member_in_period,
                                u.created_at AS start_kerja,
                                CASE WHEN t.type = 'DEPOSIT' THEN t.amount ELSE 0 END AS deposit_amount,
                                CASE WHEN t.type = 'DEPOSIT' THEN 1 ELSE 0 END AS deposit_count
                            FROM transactions t
                            LEFT JOIN members m ON m.id = t.member_id
                            LEFT JOIN users u ON u.id = m.marketing_id
                            LEFT JOIN (
                                SELECT tm.user_id, MIN(tn.name) AS name
                                FROM team_members tm
                                JOIN teams tn ON tn.id = tm.team_id
                                GROUP BY tm.user_id
                            ) AS t_team ON t_team.user_id = m.marketing_id
                            WHERE t.transaction_date BETWEEN '$startDate' AND '$endDate'
                        ) AS combined

                        GROUP BY marketing_id, marketing_name, team_name
                        ORDER BY marketing;
                    ";

                    $report = collect(DB::select($sql));
                    break;

                case 'redeposit':
                    $report = DB::table('transactions as t')
                        ->select(
                            't.id',
                            'm.name as member_name',
                            'u.name as marketing_name',
                            't.amount',
                            't.created_at as deposit_date'
                        )
                        ->join('members as m', 't.member_id', '=', 'm.id')
                        ->join('users as u', 'm.marketing_id', '=', 'u.id')
                        ->where('t.type', '=', 'deposit')
                        ->whereBetween('t.created_at', [$startDate, $endDate])
                        ->get();
                    break;

                default:
                    throw new \Exception("Unknown report type: {$typeReport}");
            }

            return Excel::download(new ReportExport($report, $typeReport, $startDate, $endDate), $typeReport . '_report.xlsx');
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
