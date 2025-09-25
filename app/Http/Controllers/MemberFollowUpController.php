<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\DataTables\MembersFollowUpDataTable;
use App\Helpers\ActivityLogger;
use App\Models\Members;
use App\Models\TransactionFollowup;

// use App\DataTables\MembersDataTable;


class MemberFollowUpController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(MembersFollowUpDataTable $dataTable)
    {
        $teams = \App\Models\Team::orderBy('id', 'asc')->get();
        $marketings = \App\Models\User::whereHas('roles', function ($query) {
            $query->where('name', 'marketing');
        })->orderBy('id', 'asc')->get();

        return $dataTable->render('pages.apps.followup-member.index', compact('teams', 'marketings'));
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
            $currentUser = auth()->user();
            $member = Members::find($request->member_id);
            if (!$member) {
                return response()->json(responseCustom(false, "Member not found."));
            }

            if ($member->phone === null || $member->phone === '') {
                return response()->json(responseCustom(false, "Member phone number is empty, cannot follow up."));
            }

            if (empty($member->marketing_id) && !$currentUser->hasRole(['administrator', 'leader'])) {
                return response()->json(responseCustom(false, "Member does not have an assigned marketing, cannot follow up."));
            }

            $created = 0;
            foreach ($member->transactions as $transaction) {
                $member->followups()->create([
                    'member_id'     => $member->id,
                    'transaction_id'=> $transaction->id,
                    'user_id'       => $currentUser->id,
                    'followed_up_at'=> now(),
                    'notes'         => 'Follow up by ' . $currentUser->name,
                ]);
                $created++;
            }

            if ($member->transactions->isEmpty()) {
                TransactionFollowup::create([
                    'member_id'     => $member->id,
                    'user_id'       => $currentUser->id,
                    'followed_up_at'=> now(),
                    'notes'         => 'Follow up by ' . $currentUser->name,
                ]);
                $created++;
            }

            $waLink = "https://wa.me/{$member->phone}";
            ActivityLogger::log("Followed up member {$member->name} via WhatsApp link.");
            return response()->json(responseCustom(true, "Follow-up recorded successfully.", [
                'redirectUrl' => $waLink
            ]));
        } catch (\Throwable $th) {
            return response()->json(responseCustom(false, "Failed to record follow-up: " . $th->getMessage()));
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
