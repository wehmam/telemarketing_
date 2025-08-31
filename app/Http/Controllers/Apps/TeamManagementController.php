<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;

class TeamManagementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $teams = Team::all();
        $leaders = User::role('Leader')->get();
        $marketings = User::role('Marketing')->get();

        return view("pages.apps.user-management.teams.list", compact('teams', 'leaders', 'marketings'));
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
            $validated = $request->validate([
                'team_name' => 'required|string|unique:teams,name',
                'team_leader' => 'required|exists:users,id',
                'team_members'   => 'required|array|min:1',
                'team_members.*' => 'exists:users,id',
            ]);

            $team = Team::create([
                'name' => $validated['team_name'],
                'leader_id' => $validated['team_leader']
            ]);

            $team->members()->sync($validated['team_members']);

            return response()->json(responseCustom(true, "Success create new team", $team));

        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors   = collect($e->errors())->flatten()->toArray();
            $messages = collect($e->errors())->flatten()->implode(' ');

            return response()->json(responseCustom(false, "Validation failed : $messages", errors: $errors), 422);
        } catch (\Exception $e) {
            return response()->json(responseCustom(false, "Something went wrong", $e->getMessage()), 500);
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
