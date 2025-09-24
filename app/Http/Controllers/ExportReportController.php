<?php

namespace App\Http\Controllers;

use App\Exports\BackupExport;
use App\Exports\ReportExport;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
    public function backupAndDeleteTransactions()
    {

        $beforeDate = Carbon::now()->subYear()->toDateString(); // data lebih dari 1 tahun

        // 1. Backup ke CSV
        $fileName = "transactions_backup_" . now()->format('Ymd_His') . ".csv";
        Excel::store(new BackupExport($beforeDate), $fileName, 'local');
        // file ada di storage/app

        // 2. Delete data lama
        DB::transaction(function () use ($beforeDate) {
            // Transaction::where('transaction_date', '<', $beforeDate)->delete();
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Backup & delete done',
            'file' => $fileName
        ]);
    }

    // backup transactions older than 1 year and delete them from database
    // public function backupTransactions(Request $request)
    // {
    //     $beforeDate = Carbon::now()->subYear()->toDateString();

    //     return response()->streamDownload(function () use ($beforeDate) {
    //         $handle = fopen('php://output', 'w');

    //         // Header CSV
    //         fputcsv($handle, [
    //             'ID','Transaction Date','Amount','Type','Username',
    //             'Phone','Nama Rekening','Member Name','Team Name',
    //             'Marketing Name','Created At','Updated At'
    //         ]);

    //         // Backup pakai chunk supaya hemat memory
    //         Transaction::with(['member.marketing','member.team','user'])
    //             ->where('transaction_date', '<', $beforeDate)
    //             ->orderBy('id')
    //             ->chunk(1000, function ($transactions) use ($handle) {
    //                 foreach ($transactions as $trx) {
    //                     fputcsv($handle, [
    //                         $trx->id,
    //                         $trx->transaction_date,
    //                         $trx->amount,
    //                         $trx->type,
    //                         $trx->username,
    //                         $trx->phone,
    //                         $trx->nama_rekening,
    //                         optional($trx->member)->name,
    //                         optional($trx->member->team)->name ?? "WA",
    //                         optional($trx->member->marketing)->name ?? "WA",
    //                         $trx->created_at,
    //                         $trx->updated_at,
    //                     ]);
    //                 }
    //             });

    //         fclose($handle);

    //         // Setelah backup selesai → hapus data
    //         DB::transaction(function () use ($beforeDate) {
    //             Transaction::where('transaction_date', '<', $beforeDate)
    //                 ->with('followups')
    //                 ->chunkById(1000, function ($transactions) {
    //                     foreach ($transactions as $trx) {
    //                         // hapus followups permanen
    //                         $trx->followups()->forceDelete();

    //                         // hapus transaction permanen
    //                         $trx->forceDelete();
    //                     }
    //                 });
    //             // Transaction::where('transaction_date', '<', $beforeDate)->forceDelete();
    //         });
    //     }, "backup_transactions_" . now()->format('Ymd_His') . ".csv");
    // }


    public function backupTransactions(Request $request)
    {
        $periode = $request->periode;
        $startDate = null;
        $endDate = null;

        if ($periode) {
            $dates = explode(" to ", $periode);
            $start = trim($dates[0]);
            $end = isset($dates[1]) ? trim($dates[1]) : $start;

            $startDate = \Carbon\Carbon::createFromFormat('d-m-Y', $start)
                        ->startOfDay()
                        ->format('Y-m-d');

            $endDate = \Carbon\Carbon::createFromFormat('d-m-Y', $end)
                        ->endOfDay()
                        ->format('Y-m-d');
        }

        // Default → 1 tahun lalu (kalau user tidak kasih tanggal)
        if (!$startDate && !$endDate) {
            $endDate   = Carbon::now()->subYear()->toDateString();
        }

        if ($startDate && $endDate) {
            $query = Transaction::with(['member.marketing','member.team','user'])
                ->whereBetween('transaction_date', [$startDate, $endDate]);
        } else {
            $query = Transaction::with(['member.marketing','member.team','user'])
                ->where('transaction_date', '<=', $endDate);
        }

        return response()->streamDownload(function () use ($query, $startDate, $endDate) {
            $handle = fopen('php://output', 'w');

            // Header CSV
            fputcsv($handle, [
                'ID','Transaction Date','Amount','Type','Username',
                'Phone','Nama Rekening','Member Name','Team Name',
                'Marketing Name','Created At','Updated At'
            ]);

            // ✅ Backup pakai chunk
            $query->orderBy('id')->chunk(1000, function ($transactions) use ($handle) {
                foreach ($transactions as $trx) {
                    fputcsv($handle, [
                        $trx->id,
                        $trx->transaction_date,
                        $trx->amount,
                        $trx->type,
                        $trx->username,
                        $trx->phone,
                        $trx->nama_rekening,
                        optional($trx->member)->name,
                        optional($trx->member->team)->name ?? "WA",
                        optional($trx->member->marketing)->name ?? "WA",
                        $trx->created_at,
                        $trx->updated_at,
                    ]);
                }
            });

            fclose($handle);

            // // ✅ Delete data setelah backup
            // DB::transaction(function () use ($query) {
            //     $query->with('followups')->chunkById(1000, function ($transactions) {
            //         foreach ($transactions as $trx) {
            //             $trx->followups()->forceDelete();
            //             $trx->forceDelete();
            //         }
            //     });
            // });
        }, "backup_transactions_" . now()->format('Ymd_His') . ".csv");
    }



    public function deleteOldTransactions(Request $request)
    {
        $periode = $request->periode;
        $startDate = null;
        $endDate = null;

        if ($periode) {
            $dates = explode(" to ", $periode);
            $start = trim($dates[0]);
            $end = isset($dates[1]) ? trim($dates[1]) : $start;

            $startDate = \Carbon\Carbon::createFromFormat('d-m-Y', $start)
                        ->startOfDay()
                        ->format('Y-m-d');

            $endDate = \Carbon\Carbon::createFromFormat('d-m-Y', $end)
                        ->endOfDay()
                        ->format('Y-m-d');
        }


        // Default → 1 tahun lalu (kalau user tidak kasih tanggal)
        if (!$startDate && !$endDate) {
            $endDate   = Carbon::now()->subYear()->toDateString();
        }

        if ($startDate && $endDate) {
            $query = Transaction::with(['member.marketing','member.team','user'])
                ->whereBetween('transaction_date', [$startDate, $endDate]);
        } else {
            $query = Transaction::with(['member.marketing','member.team','user'])
                ->where('transaction_date', '<=', $endDate);
        }

        $deletedCount = 0;

        DB::transaction(function () use ($query, &$deletedCount) {
            $query->with('followups')->chunkById(1000, function ($transactions) use (&$deletedCount) {
                foreach ($transactions as $trx) {
                    $trx->followups()->delete();
                    $trx->delete();
                    $deletedCount++;
                }
            });
        });

        return response()->json([
            'success' => true,
            'message' => "Success deleting {$deletedCount} transactions",
        ]);
    }
}
