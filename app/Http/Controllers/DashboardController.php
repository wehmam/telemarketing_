<?php

namespace App\Http\Controllers;

use App\Models\Members;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        addVendors(['amcharts', 'amcharts-maps', 'amcharts-stock']);

        $totalDeposit   = \App\Models\Transaction::where('type', 'DEPOSIT')->sum('amount');
        $totalRedeposit = \App\Models\Transaction::where('type', 'REDEPOSIT')->sum('amount');
        $totalMembers = \App\Models\Members::count();
        $pendingFollowUps = \App\Models\Transaction::doesntHave('followups')
            ->distinct('member_id')
            ->count('member_id');

        $topMembers = Members::withSum('transactions', 'amount')
            ->orderByDesc('transactions_sum_amount')
            ->take(3)
            ->get();

        $topEmployees = User::withCount('members') // count members per user
            ->has('members')                     // only employees with at least 1 member
            ->orderByDesc('members_count')       // sort by highest member count
            ->take(3)                            // top 3
            ->get();

        $percentagePending = $totalMembers > 0 ? ($pendingFollowUps / $totalMembers) * 100: 0;

        return view('pages.dashboards.index', compact('totalDeposit', 'totalRedeposit', 'totalMembers', 'topMembers', 'topEmployees', 'pendingFollowUps', 'percentagePending'));
    }
}
