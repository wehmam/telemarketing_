<?php

namespace App\Http\Controllers;

use App\DataTables\MemberAssignTransactionDataTable;
use App\Models\Members;
use App\Models\Transaction;
use App\Models\TransactionAssignLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TransactionAssignController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(MemberAssignTransactionDataTable $dataTable)
    {
        // $fromMembers = \App\Models\Members::whereHas('transactions')
        //     ->orderBy('name')
        //     ->get();
        $fromMembers = \App\Models\Members::orderBy('name')
            ->get();

        $toUsers = \App\Models\User::whereHas('roles')   // hanya user yg punya role
            ->whereHas('team')               // hanya user yg punya team
            ->with(['roles','team'])         // eager load
            ->orderBy('name')
            ->get();

        $teams = \App\Models\Team::orderBy('name')->get();
        $marketings = \App\Models\User::whereHas('roles', function ($query) {
            $query->where('name', 'marketing');
        })->orderBy('id', 'asc')->get();

        return $dataTable->render('pages.apps.transaction-assign.index', [
            'fromMembers' => $fromMembers,
            'toUsers'   => $toUsers,
            'teams'     => $teams,
            'marketings' => $marketings
        ]);
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
        $validator = Validator::make($request->all(), [
            'from_member_ids'   => 'required|array',
            'from_member_ids.*' => 'exists:members,id',
            'to_user_id'        => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            $errorMsg = collect($validator->errors())->flatten()->implode(' ');
            return responseCustom(false, $errorMsg, 422, errors: $validator->errors());
        }

        try {
            $toUser = User::with('team')->findOrFail($request->to_user_id);
            if (!$toUser->team_id) {
                return response()->json(responseCustom(false, 'Selected user must belong to a team.'), 400);
            }
            $logs = [];

            DB::transaction(function () use ($request, $toUser, &$logs) {
                foreach ($request->from_member_ids as $memberId) {
                    $member = Members::findOrFail($memberId);
                    $fromMarketing = $member->marketing_id;

                    Transaction::where('member_id', $member->id)
                        ->update(['user_id' => $toUser->id]);

                    $member->marketing_id = $toUser->id;
                    $member->team_id = $toUser->team_id;
                    $member->save();
                    $movedCount = Transaction::where('member_id', $member->id)->count();

                    $log = TransactionAssignLog::create([
                        'action_by'      => auth()->id(),
                        'from_member_id' => $member->id,
                        'from_user_id'   => $fromMarketing,
                        'to_user_id'     => $toUser->id,
                        'moved_count'    => $movedCount,
                    ]);

                    $logs[] = $log;
                }
            });

            return response()->json(responseCustom(true, 'Transactions successfully reassigned.'));
        } catch (\Exception $e) {
            return response()->json(responseCustom(false, 'Failed to assign transactions. ' . $e->getMessage()), 500);
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
