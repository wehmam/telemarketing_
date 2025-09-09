<?php

namespace App\Http\Controllers;

use App\DataTables\MembersDataTable;
use App\DataTables\MemberTransactionsDataTable;
use App\Helpers\ActivityLogger;
use App\Models\Members;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(MembersDataTable $dataTable)
    {
        ActivityLogger::log("View List Members", 200);
        return $dataTable->render('pages.apps.members.index');
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
            'name'          => 'required|string|max:255',
            'username'      => 'required|string|max:100|unique:members,username',
            'phone'         => 'required|string|max:20',
            'nama_rekening' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            $errorMsg = collect($validator->errors())->flatten()->implode(' ');
            return response()->json(responseCustom(false, "Validation Failed : " . $errorMsg, errors: $validator->errors()), 422);
        }

        $user = Auth::user();
        $teamId = $user->team_id;
        if (!$teamId) {
            return response()->json(responseCustom(false, "You must be part of a team to add members., please contact your team leader!"), 422);
        }

        $member = Members::create([
            'name'              => ucwords($request->name),
            'username'          => strtolower($request->username),
            'phone'             => $request->phone,
            'nama_rekening'     => strtoupper($request->nama_rekening),
            'marketing_id'      => $user->id,
            'team_id'           => $teamId
        ]);
        ActivityLogger::log("Add Member {$member->name}", 201);

        return response()->json(responseCustom(true, "Success Add New Member", $member));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id, MemberTransactionsDataTable $memberTransactions)
    {
        $member = Members::withTrashed()->findOrFail($id);
        $membersTable      = $memberTransactions->setMemberContext($member->id, $member->name);

        $totalTransactions = $member->transactions()->sum('amount');
        $lastTransaction = $member->transactions()
            ->latest('transaction_date')
            ->first();
        ActivityLogger::log("View Member {$member->name} Detail", 200);
        return view('pages.apps.members.show', [
            'member'         => $member,
            'transactionsTable' => $membersTable->html(),
            'totalTransactions'  => $totalTransactions,
            'lastTransaction'    => $lastTransaction?->transaction_date,
            // 'logsTable'    => $logsTable->html(),
        ]);
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
        $member = Members::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name'          => 'required|string|max:255',
            'username'      => 'required|string|max:100|unique:members,username,' . $member->id,
            'phone'         => 'required|string|max:20',
            'nama_rekening' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            $errorMsg = collect($validator->errors())->flatten()->implode(' ');
            return response()->json(responseCustom(false, $errorMsg, errors: $validator->errors()), 422);
        }

        $member->update([
            'name'          => $request->name,
            'username'      => $request->username,
            'phone'         => $request->phone,
            'nama_rekening' => $request->nama_rekening,
        ]);

        ActivityLogger::log("Update Member {$member->name}", 200);
        return response()->json([
            'status'  => true,
            'message' => 'Member updated successfully',
            'data'    => $member
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $member = Members::findOrFail($id);
        $member->delete();

        ActivityLogger::log("Delete Member {$member->name}", 200);
        return response()->json([
            'status'  => true,
            'message' => 'Member deleted successfully'
        ]);
    }

    /**
     * Restore a soft-deleted member.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function restore($id)
    {
        $member = Members::withTrashed()->find($id);

        if (!$member) {
            return response()->json(responseCustom(false, "Member not found"), 404);
        }

        if ($member->trashed()) {
            $member->restore();

            ActivityLogger::log("Restore Member {$member->name} Detail", 200);
            return response()->json(responseCustom(true, "Members successfully restored", $member));
        } else {
            return response()->json(responseCustom(false, "Member is still active"));
        }
    }

    public function transactionsData(Members $member, MemberTransactionsDataTable $dataTable)
    {
        return $dataTable->setMemberContext($member->id, $member->name, $member->team?->name ?? 'N/A')->ajax();

    }
}
