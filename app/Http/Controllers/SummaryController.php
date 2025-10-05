<?php

namespace App\Http\Controllers;

use App\DataTables\MarketingSummaryDataTable;
use App\Exports\ExportSummary;
use Illuminate\Http\Request;
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

    public function export(Request $request, MarketingSummaryDataTable $dataTable)
    {
        $start = now()->startOfMonth()->format('Y-m-d');
        $end = now()->endOfMonth()->format('Y-m-d');

        // Ambil filter date dari request
        $rangeDate = $request->get('s_date');
        $fileName = 'marketing_summary_' . $start . '.xlsx';
        if ($rangeDate) {
            $dates = explode(' to ', $rangeDate);
            if (count($dates) === 2) {
                $start = \Carbon\Carbon::createFromFormat('d-m-Y', trim($dates[0]))->format('Y-m-d');
                $end = \Carbon\Carbon::createFromFormat('d-m-Y', trim($dates[1]))->format('Y-m-d');
                $fileName = 'marketing_summary_' . $start . '_to_' . $end . '.xlsx';
            } elseif (count($dates) === 1) {
                $start = \Carbon\Carbon::createFromFormat('d-m-Y', trim($dates[0]))->format('Y-m-d');
                $end = $start;
                $fileName = 'marketing_summary_' . $start . '.xlsx';
            }
        }

        // Ambil data berdasarkan query datatable
        $report = $dataTable->query()->get();
         $report = $report->map(function($row) {
            $row->member_daftar = (int) $row->member_daftar;
            $row->total_deposit_transactions = (int) $row->total_deposit_transactions;
            $row->total_redeposit_transactions = (int) $row->total_redeposit_transactions;
            return $row;
        });

        // return Excel::download(new ExportSummary($report, $start, $end), $fileName);
        return \Maatwebsite\Excel\Facades\Excel::download( new \App\Exports\ExportSummary($report, $start, $end), $fileName );
    }
}
