<?php

namespace App\Http\Controllers\Apps;

use App\DataTables\ActivityLogDataTable;
use App\DataTables\MemberUserDataTable;
use App\DataTables\UsersDataTable;
use App\Helpers\ActivityLogger;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserManagementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(UsersDataTable $dataTable)
    {
        ActivityLogger::log("View List Users", 200);
        return $dataTable->render('pages.apps.user-management.users.list');
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user, MemberUserDataTable $membersDataTable, ActivityLogDataTable $logDataTable)
    {
        $teamName          = $user->team->name ?? 'N/A';
        $membersTable      = $membersDataTable->setUserContext($user->id, $user->name, $teamName);
        // $transactionsTable = $memberTransactions->setMemberContext($member->id, $
        $logTable          = $logDataTable->setUserContext($user->id);
        
        ActivityLogger::log("View User {$user->name} Detail", 200);
        return view('pages.apps.user-management.users.show', [
            'user'         => $user,
            'membersTable' => $membersTable->html(),
            'logsTable'    => $logTable->html(),
            // 'transactionsTable' => $transactionsTable->html(),
            // 'logsTable'    => $logsTable->html(),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        //
    }

    public function membersData(User $user, MemberUserDataTable $dataTable)
    {
        return $dataTable->setUserContext($user->id, $user->name, $user->team?->name ?? 'N/A')->ajax();
    }

    public function logsData(User $user, ActivityLogDataTable $dataTable)
    {
        return $dataTable->setUserContext($user->id)->ajax();
    }

    public function restore(string $id)
    {
        $user = User::withTrashed()->find($id);
        if (!$user) {
            return response()->json(responseCustom(false, 'User not found.'), 404);
        }


        if (!$user->deleted_at) {
            return response()->json(responseCustom(false, 'User is not deleted.'));
        }

        try {
            $user->restore();
            ActivityLogger::log("Restore User {$user->name}", 200);
            return response()->json(responseCustom(true, 'User successfully restored.'));
        } catch (\Exception $e) {
            return response()->json(responseCustom(false, 'Failed to restore user. ' . $e->getMessage()), 500);
        }
    }
}
