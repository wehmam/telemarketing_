<?php

namespace App\Http\Controllers;

use App\DataTables\MembersDataTable;
use App\DataTables\MemberTransactionsDataTable;
use App\DataTables\TransactionFollowupDataTable;
use App\Helpers\ActivityLogger;
use App\Models\Members;
use App\Models\Team;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use Maatwebsite\Excel\Facades\Excel;

class MemberController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(MembersDataTable $dataTable)
    {
        $teams = \App\Models\Team::orderBy('id', 'asc')->get();
        $marketings = \App\Models\User::whereHas('roles', function ($query) {
            $query->where('name', 'marketing');
        })->orderBy('id', 'asc')->get();

        ActivityLogger::log("View List Members", 200);
        return $dataTable->render('pages.apps.members.index', compact('teams', 'marketings'));
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
    public function show(string $id, MemberTransactionsDataTable $memberTransactions, TransactionFollowupDataTable $transactionFollowup)
    {
        $member             = Members::withTrashed()->findOrFail($id);
        $membersTable       = $memberTransactions->setMemberContext($member->id, $member->name);
        $followupsTable     = $transactionFollowup->setMemberContext($member->id);

        $totalTransactions = $member->transactions()->sum('amount');
        $lastTransaction = $member->transactions()
            ->latest('transaction_date')
            ->first();
        ActivityLogger::log("View Member {$member->name} Detail", 200);
        return view('pages.apps.members.show', [
            'member'         => $member,
            'transactionsTable' => $membersTable->html(),
            'followupsTable'    => $followupsTable->html(),
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
            // 'username'      => 'required|string|max:100|unique:members,username,' . $member->id,
            'phone'         => 'required|string|max:20',
            'nama_rekening' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            $errorMsg = collect($validator->errors())->flatten()->implode(' ');
            return response()->json(responseCustom(false, $errorMsg, errors: $validator->errors()), 422);
        }

        $member->update([
            'name'          => $request->name,
            // 'username'      => $request->username,
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
        // $member = Members::findOrFail($id);
        // $member->delete();

        // ActivityLogger::log("Delete Member {$member->name}", 200);
        // return response()->json([
        //     'status'  => true,
        //     'message' => 'Member deleted successfully'
        // ]);
        return DB::transaction(function () use ($id) {
            $member = Members::findOrFail($id);

            // Delete related transactions
            $member->transactions()->forceDelete();

            // Delete related followups
            $member->followups()->forceDelete();

            // Finally delete member
            $member->forceDelete();

            ActivityLogger::log("Delete Member {$member->name}, transactions, and followups", 200);

            return response()->json([
                'status'  => true,
                'message' => 'Member, transactions, and followups permanently deleted successfully'
            ]);
        });
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

    public function followupsData(Members $member, TransactionFollowupDataTable $dataTable)
    {
        return $dataTable->setMemberContext($member->id)->ajax();
    }

    // public function import(Request $request)
    // {
    //     try {
    //         $validator = Validator::make($request->all(), [
    //             'file' => 'required|mimes:xlsx,xls,csv', // max 2MB
    //         ]);

    //         if ($validator->fails()) {
    //             $errorMsg = collect($validator->errors())->flatten()->implode(' ');
    //             return response()->json(responseCustom(false, "Validation Failed : " . $errorMsg, errors: $validator->errors()), 422);
    //         }

    //         $user = auth()->user();
    //         $file = $request->file('file');
    //         $handle = fopen($file->getRealPath(), "r");
    //         $faker = Faker::create();

    //         fgetcsv($handle, 1000, ",");

    //         DB::beginTransaction();
    //         $countImport = 0;
    //         $countNewMembers = 0;
    //         while (($row = fgetcsv($handle, 0, ",")) !== false) {
    //             $countImport++;
    //             if ($countImport > 3000) {
    //                 break; // ✅ stop after 3000 rows
    //             }

    //             $tgl        = $row[1];
    //             $tim        = $row[2];
    //             $marketing  = $row[3];
    //             $namaPlayer = $row[4];
    //             $username   = strtolower(preg_replace('/\s+/', '', trim($row[5]))); // USERNAME → lowercase & hapus spasi
    //             $nominal    = (float) str_replace([",", "."], "", $row[6]);
    //             $phone      = '62' . $faker->numerify('8##########');


    //             $member = Members::where('username', $username)->first();
    //             if (!$member) {
    //                 $teamId = $tim ? Team::where('name', $tim)->value('id') : null;
    //                 $marketingId = $marketing ? User::where('name', $marketing)->value('id') : null;

    //                 $member = Members::create([
    //                     'name'          => ucwords(strtolower($namaPlayer)),
    //                     'username'      => strtolower($username),
    //                     'phone'         => $phone,
    //                     'nama_rekening' => null,
    //                     'marketing_id'  => $teamId && $marketingId ? $marketingId : null,
    //                     'team_id'       => $teamId && $marketingId ? $teamId : null,
    //                     'created_at'    => \Carbon\Carbon::parse($tgl)->format('Y-m-d H:i:s'),
    //                 ]);
    //                 $countNewMembers++;

    //                 // Kalau nominal > 0 → bikin transaction
    //                 if ((float)$nominal > 0) {
    //                     Transaction::create([
    //                         'id'               => \Illuminate\Support\Str::uuid(),
    //                         'member_id'        => $member->id,
    //                         'user_id'          => $teamId && $marketingId ? $marketingId : auth()->id(),
    //                         'amount'           => $nominal,
    //                         'transaction_date' => \Carbon\Carbon::parse($tgl)->format('Y-m-d'),
    //                         'type'             => 'DEPOSIT',
    //                         'username'         => strtolower($username),
    //                         'phone'            => $member->phone,
    //                         'nama_rekening'    => $member->nama_rekening,
    //                     ]);
    //                 }
    //             } else {
    //                 // kalau sudah ada → skip
    //                 continue;
    //             }
    //         }

    //         DB::commit();
    //         fclose($handle);

    //         if ($countNewMembers === 0) {
    //             return response()->json(responseCustom(true, "No new members were added. All usernames already exist in the system."));
    //         }

    //         return response()->json(responseCustom(true, "✅ Import success, total processed: {$countImport}, new members added: {$countNewMembers}"));
    //     } catch (\Throwable $th) {
    //         if (isset($pdo) && $pdo->inTransaction()) {
    //             $pdo->rollBack();
    //         }
    //         return response()->json(responseCustom(false, "❌ Import failed: " . $th->getMessage()), 500);
    //     }
    // }

    public function import(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'file' => 'required|mimes:xlsx,xls,csv',
            ]);

            if ($validator->fails()) {
                $errorMsg = collect($validator->errors())->flatten()->implode(' ');
                return response()->json(responseCustom(false, "Validation Failed : " . $errorMsg, errors: $validator->errors()), 422);
            }

            $file = $request->file('file');
            $handle = fopen($file->getRealPath(), "r");
            $faker = Faker::create();

            fgetcsv($handle, 1000, ",");

            $existingUsernames = Members::pluck('username')->map(fn($u) => strtolower($u))->toArray();
            $existingUsernames = array_flip($existingUsernames);

            DB::beginTransaction();

            // === Generate batch code for members ===
            $today = now()->format('Y-m-d');
            $countToday = DB::table('transactions')
                ->whereDate('created_at', $today)
                ->distinct('batch_code')
                ->count('batch_code');
            $nextNumber = $countToday + 1;
            $batchCode = "BATCH_MEMBERS_{$today}_{$nextNumber}";

            $countImport = 0;
            $countNewMembers = 0;
            $newMembers = [];
            $newTransactions = [];

            while (($row = fgetcsv($handle, 0, ",")) !== false) {
                $countImport++;
                if ($countImport > 5000) break; // ✅ stop after 5000 rows

                $tgl        = $row[1];
                $marketing  = $row[2];
                $namaPlayer = $row[3];
                $username   = strtolower(preg_replace('/\s+/', '', trim($row[4])));
                // $nominal    = (float) str_replace([",", "."], "", $row[6]);

                // new format excel last send
                $nominal = formatRupiah($row[6]) ?: 0;

                // $phone      = $row[5] ?: '62' . $faker->numerify('8##########');
                $phone = !empty($row[5]) ? ltrim($row[5], '+') : null;

                // ✅ Cek array
                if (isset($existingUsernames[$username])) {
                    continue; // skip kalau sudah ada
                }

                // $teamId = $tim ? Team::where('name', $tim)->value('id') : null;
                $marketingUser = $marketing ? User::where('name', $marketing)->first() : null;
                $marketingId = $marketingUser?->id;
                $teamId      = $marketingUser?->team_id;

                $newMembers[] = [
                    'name'          => ucwords(strtolower($namaPlayer)),
                    'username'      => strtolower($username),
                    'phone'         => $phone,
                    'nama_rekening' => null,
                    'marketing_id'  => $teamId && $marketingId ? $marketingId : null,
                    'team_id'       => $teamId && $marketingId ? $teamId : null,
                    'created_at'    => \Carbon\Carbon::parse($tgl)->format('Y-m-d H:i:s'),
                    'updated_at'    => now(),
                ];

                $existingUsernames[$username] = true;
                $countNewMembers++;

                if ($nominal > 0) {
                    $newTransactions[] = [
                        'id'               => (string) \Illuminate\Support\Str::uuid(),
                        'member_id'        => null,
                        'user_id'          => $teamId && $marketingId ? $marketingId : auth()->id(),
                        'amount'           => $nominal,
                        'transaction_date' => \Carbon\Carbon::parse($tgl)->format('Y-m-d'),
                        'type'             => 'DEPOSIT',
                        'username'         => strtolower($username),
                        'phone'            => $phone,
                        'nama_rekening'    => null,
                        'batch_code'       => $batchCode,
                        'created_at'       => now(),
                        'updated_at'       => now(),
                    ];
                }
            }

            // ✅ Bulk insert members
            if (!empty($newMembers)) {
                Members::insert($newMembers);

                // ambil mapping username → id
                $inserted = Members::whereIn('username', array_column($newMembers, 'username'))
                            ->pluck('id', 'username')
                            ->toArray();

                // update member_id di transaksi
                foreach ($newTransactions as &$trx) {
                    $trx['member_id'] = $inserted[$trx['username']] ?? null;
                }

                // ✅ Bulk insert transaksi
                if (!empty($newTransactions)) {
                    Transaction::insert($newTransactions);
                }
            }

            DB::commit();
            fclose($handle);

            return response()->json(responseCustom(true, "✅ Import success, total processed: {$countImport}, new members added: {$countNewMembers}"));
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(responseCustom(false, "❌ Import failed: " . $th->getMessage()), 500);
        }
    }

    /**
    * Export to Excel or CSV.
    */
    public function export(Request $request, $type)
    {
        ActivityLogger::log("Exported Members data file.");
        return Excel::download(
            new \App\Exports\MembersExport($request->all()),
            'members-' . now()->format('YmdHis') . '.xlsx'
        );
    }


}
