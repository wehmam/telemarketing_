<?php

namespace App\Http\Controllers;

use App\DataTables\ActivityLogAllDataTable;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(ActivityLogAllDataTable $dataTable)
    {
        $teams = \App\Models\Team::orderBy('id', 'asc')->get();
        $marketings = \App\Models\User::whereHas('roles', function ($query) {
            $query->where('name', 'marketing');
        })->orderBy('id', 'asc')->get();

        return $dataTable->render('pages.apps.activity-logs.index', compact('teams', 'marketings'));
    }
}
