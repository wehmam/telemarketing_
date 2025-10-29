<?php

namespace App\Http\Controllers;

use App\DataTables\ImportLogDataTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use App\Helpers\ActivityLogger;


class ImportLogController extends Controller
{
    public function index(ImportLogDataTable $dataTable)
    {
        return $dataTable->render('pages.apps.import-logs.index');
    }

    public function update(Request $request)
    {
       $validator = Validator::make($request->all(), [
            'batch_code_ref'   => 'required|string',
            'type_import'      => 'required',
            'action_type'      => 'required|in:change_date,replace_data',
            'transaction_date' => 'required',
            'file'             => 'required_if:action_type,replace_data|file|mimes:xlsx,csv',
        ]);

        if ($validator->fails()) {
            $errorMsg = collect($validator->errors())->flatten()->implode(' ');
            return response()->json(responseCustom(false, "Validation Failed: " . $errorMsg, errors: $validator->errors()), 422);
        }

        $batchCode  = $request->batch_code_ref;
        $batchType  = $request->type_import;
        $actionType = $request->action_type;

        try {
            if ($actionType === 'change_date') {
                $date = \Carbon\Carbon::createFromFormat('d-m-Y', $request->transaction_date)->format('Y-m-d');

                if ($batchType === 'Import Transactions') {
                    DB::table('transactions')->where('batch_code', $batchCode)->update(['transaction_date' => $date]);
                } else {
                    DB::table('members')->where('batch_code', $batchCode)->update(['created_at' => $date]);
                    // return response()->json(responseCustom(false, "Change date action is only applicable for transactions type."), 400);
                }

                ActivityLogger::log("Update import date for batch_code: $batchCode to $date, type: $batchType");
                return response()->json(responseCustom(true, "Import date updated successfully!"));

            } elseif ($actionType === 'replace_data') {
                // Hapus data lama
                if ($batchType === 'Import Transactions') {
                    $path = $request->file('file')->storeAs(
                        'imports',
                        'transactions_' . time() . '.' . $request->file('file')->getClientOriginalExtension()
                    );
                    $filePath = storage_path('app/' . $path);
                    $transactions = \App\Models\Transaction::with('member', 'followups')->where('batch_code', $batchCode)->get();
                    foreach ($transactions as $trx) {
                        $trx->followups()->forceDelete();

                        // if ($trx->member) {
                        //     $trx->member->forceDelete();
                        // }
                        $trx->forceDelete();
                    }

                    $today = now()->format('Y-m-d');
                    $countToday = DB::table('transactions')
                        ->whereDate('created_at', $today)
                        ->distinct()
                        ->count('batch_code');

                    $nextNumber = $countToday + 1;
                    $newBatchCode = "BATCH_TRANSACTIONS_{$today}_{$nextNumber}";
                    $transactionDate = \Carbon\Carbon::createFromFormat('d-m-Y', $request->transaction_date)->format('Y-m-d');
                    return $this->importTransactions($filePath, $transactionDate, $newBatchCode, $batchCode);
                } else {
                    $path = $request->file('file')->storeAs(
                        'imports',
                        'members_' . time() . '.' . $request->file('file')->getClientOriginalExtension()
                    );
                    $filePath = storage_path('app/' . $path);

                    $today = now()->format('Y-m-d');
                    $countToday = DB::table('members')
                        ->whereDate('import_at', $today)
                        ->distinct('batch_code')
                        ->count('batch_code');

                    $nextNumber = $countToday + 1;
                    $newBatchCode = "BATCH_MEMBERS_{$today}_{$nextNumber}";

                    // delete followup
                    \App\Models\TransactionFollowup::whereIn('transaction_id', function ($q) use ($batchCode) {
                        $q->select('id')
                        ->from('transactions')
                        ->whereIn('member_id', function ($q2) use ($batchCode) {
                            $q2->select('id')
                                ->from('members')
                                ->where('batch_code', $batchCode);
                        });
                    })->forceDelete();

                    // Delete transactions
                    \App\Models\Transaction::whereIn('member_id', function ($q) use ($batchCode) {
                        $q->select('id')
                        ->from('members')
                        ->where('batch_code', $batchCode);
                    })->forceDelete();

                    // Delete members
                    \App\Models\Members::where('batch_code', $batchCode)->forceDelete();

                    return $this->importMember($filePath, null, $newBatchCode, $batchCode);
                }

                return response()->json(responseCustom(true, "No Action Process!"));
            } else {
                return response()->json(responseCustom(false, "Invalid action type."), 400);
            }

        } catch (\Exception $e) {
            return response()->json(responseCustom(false, $e->getMessage()), 500);
        }
    }

    private function importTransactions($filePath, $transactionDate, $newBatchCode , $oldBatchCode = null)
    {
        try {
            $user = auth()->user();
            // DB connection
            $host = trim(config('app.connect_info.host'));
            $db   = config('database.connections.mysql.database');
            $username = config('database.connections.mysql.username');
            $pass = config('database.connections.mysql.password');

            $pdo = new \PDO("mysql:host=$host;dbname=$db", $username, $pass, [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            ]);

            $batchCode = $newBatchCode;
            $pdo->beginTransaction();
            $pdo->exec("DELETE FROM tmp_transactions WHERE entry_by = " . $user->id);

            $reader = ReaderEntityFactory::createReaderFromFile($filePath);
            $reader->open($filePath);

            $rows = [];
            $rownum = 0;

            $sheetCount = 0;
            $selectedSheet = null;
            foreach ($reader->getSheetIterator() as $sheet) {
                $sheetCount++;
                $selectedSheet = $sheet;

                if ($sheetCount > 1) {
                    $reader->close();
                    return response()->json(
                        responseCustom(false, "❌ Excel File has more than one sheet. Please upload a file with a single sheet."),
                        422
                    );
                }
            }

            if ($sheetCount === 0) {
                $reader->close();
                return response()->json(
                    responseCustom(false, "❌ Excel File has no sheets."),
                    422
                );
            }

            foreach ($selectedSheet->getRowIterator() as $i => $row) {
                if ($i === 1) continue;

                $cells = $row->toArray();
                // $nama_rekening = $cells[2] ?? null; // C
                // $usernameCsv   = $cells[3] ?? null; // D
                // $nominal       = $cells[7] ?? null; // H

                $nama_rekening = $cells[0] ?? null; // A
                $usernameCsv   = $cells[1] ?? null; // B
                $nominal       = $cells[2] ?? null; // C

                if (!$usernameCsv) continue;

                $nominalClean = str_replace(',', '', $nominal);
                if (!is_numeric($nominalClean) || $nominalClean <= 0) {
                    return response()->json(
                        responseCustom(false, "❌ Invalid nominal value '{$nominal}' for username '{$usernameCsv}' on row {$i}. It must be a valid Amount."),
                        422
                    );
                }

                $rownum++;
                if ($rownum > 5000) break;

                $rows[] = [
                    strtolower(preg_replace('/\s+/', '', $usernameCsv)),
                    $nama_rekening,
                    str_replace(',', '', $nominal),
                    $user->id,
                    $transactionDate,
                    $batchCode
                ];

                // if (count($rows) >= 1000) {
                //     $this->bulkInsertTmp($pdo, $rows);
                //     $rows = [];
                // }
            }


            $reader->close();

            if (!empty($rows)) {
                $this->bulkInsertTmp($pdo, $rows);
            }

            $pdo->exec("
                INSERT INTO members (username, phone, name, nama_rekening, marketing_id, team_id, created_at, updated_at)
                SELECT
                    t.username,
                    t.phone,
                    COALESCE(t.nama_rekening, t.username) AS name,
                    t.nama_rekening,
                    NULL, NULL, NOW(), NOW()
                FROM tmp_transactions t
                LEFT JOIN members m ON LOWER(m.username) = LOWER(t.username)
                WHERE m.id IS NULL
                GROUP BY t.username
            ");

            $pdo->exec("
                INSERT INTO transactions (id, member_id, user_id, amount, transaction_date, type, username, phone, nama_rekening, batch_code ,created_at, updated_at, import_at)
                SELECT
                    UUID(),
                    m.id,
                    t.entry_by,
                    t.amount,
                    COALESCE(t.transaction_date, NOW()),
                    CASE
                        WHEN EXISTS (
                            SELECT 1 FROM transactions trx WHERE trx.member_id = m.id
                        ) THEN 'REDEPOSIT'
                        WHEN (
                            SELECT COUNT(*)
                            FROM tmp_transactions t2
                            WHERE t2.username = t.username
                            AND (t2.id < t.id)
                        ) = 0 THEN 'DEPOSIT'
                        ELSE 'REDEPOSIT'
                    END,
                    t.username,
                    t.phone,
                    t.nama_rekening,
                    t.batch_code,
                    NOW(),
                    NOW(),
                    NOW()
                FROM tmp_transactions t
                JOIN members m ON m.username = t.username
            ");

            $pdo->exec("DELETE FROM tmp_transactions WHERE entry_by = " . $user->id);
            $pdo->commit();

            if (file_exists($filePath)) unlink($filePath);

            ActivityLogger::log("Replaced transactions {$oldBatchCode} with {$batchCode}. By User : " . $user->name);

            return response()->json(responseCustom(true, "✅ success import transactions {$rownum} records."));

        } catch (\Throwable $th) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return response()->json(responseCustom(false, "❌ Import failed: " . $th->getMessage()), 500);
        }
    }

    private function bulkInsertTmp($pdo, array $rows)
    {
        $placeholders = rtrim(str_repeat("(?, ?, ?, ?, ?, ?),", count($rows)), ",");
        $stmt = $pdo->prepare("
            INSERT INTO tmp_transactions
                (username, nama_rekening, amount, entry_by, transaction_date, batch_code)
            VALUES $placeholders
        ");
        $flatValues = [];
        foreach ($rows as $r) {
            $flatValues = array_merge($flatValues, $r);
        }
        $stmt->execute($flatValues);
    }

    private function importMember($filePath, $transactionDate, $newBatchCode , $oldBatchCode = null)
    {
        try {

            // load existing usernames
            $existingUsernames = \App\Models\Members::withTrashed()
                ->pluck('username')->map(fn($u) => strtolower($u))->toArray();
            $existingUsernames = array_flip($existingUsernames);

            DB::beginTransaction();

            $batchCode = $newBatchCode;
            $countImport = 0;
            $countNewMembers = 0;
            $newMembers = [];
            $newTransactions = [];

            // === Detect extension and open with Spout ===
            // $extension = strtolower($file->getClientOriginalExtension());
            // switch ($extension) {
            //     case 'xlsx':
                    $reader = \Box\Spout\Reader\Common\Creator\ReaderEntityFactory::createXLSXReader();
                    // break;
                // case 'csv':
                    // $reader = \Box\Spout\Reader\Common\Creator\ReaderEntityFactory::createCSVReader();
                    // break;
                // default:
                //     throw new \Exception("Unsupported file type: {$extension}");
            // }

            $reader->open($filePath);

            foreach ($reader->getSheetIterator() as $sheetIndex => $sheet) {
                foreach ($sheet->getRowIterator() as $rowIndex => $row) {
                    $cells = $row->toArray();

                    if ($rowIndex === 1) {
                        continue; // ✅ skip header row
                    }

                    $countImport++;
                    if ($countImport > 5000) break 2;

                    if (isset($cells[0]) && $cells[0] instanceof \DateTimeInterface) {
                        return response()->json(responseCustom(false, "❌ ERROR: Row {$rowIndex}, Col 0 (username) not valid", errors: $cells), 422);
                    }

                    // $tgl        = $cells[1] ?? null;
                    // $marketing  = $cells[2] ?? null;
                    // $namaPlayer = $cells[3] ?? null;
                    // $username   = strtolower(preg_replace('/\s+/', '', trim($cells[4] ?? '')));
                    // $nominal    = isset($cells[6]) ? formatRupiah($cells[6]) : 0;
                    // $phone      = !empty($cells[5]) ? ltrim($cells[5], '+') : null;

                    // NEW
                    $tgl        = now();
                    $marketing  = $cells[3] ?? null;
                    $namaPlayer = $cells[1] ?? null;
                    $username   = strtolower(preg_replace('/\s+/', '', trim($cells[0] ?? '')));
                    $nominal    = isset($cells[4]) ? formatRupiah($cells[4]) : 0;
                    $phone      = !empty($cells[2]) ? ltrim($cells[2], '+') : null;

                    if (isset($existingUsernames[$username])) {
                        continue; // skip existing
                    }

                    $marketingUser = $marketing ? \App\Models\User::where('name', $marketing)->first() : null;
                    $marketingId   = $marketingUser?->id;
                    $teamId        = $marketingUser?->team_id;

                    $newMembers[] = [
                        'name'          => ucwords(strtolower($namaPlayer)),
                        'username'      => $username,
                        'phone'         => $phone,
                        'nama_rekening' => null,
                        'marketing_id'  => $teamId && $marketingId ? $marketingId : null,
                        'team_id'       => $teamId && $marketingId ? $teamId : null,
                        'created_at'    => \Carbon\Carbon::parse($tgl)->format('Y-m-d H:i:s'),
                        'updated_at'    => now(),
                        'batch_code'    => $batchCode,
                        'import_at'     => now(),
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
                            'username'         => $username,
                            'phone'            => $phone,
                            'nama_rekening'    => null,
                            'batch_code'       => $batchCode,
                            'created_at'       => now(),
                            'updated_at'       => now(),
                        ];
                    }
                }
            }

            $reader->close();

            // ✅ Bulk insert
            if (!empty($newMembers)) {
                \App\Models\Members::insert($newMembers);

                // mapping username → id
                $inserted = \App\Models\Members::whereIn('username', array_column($newMembers, 'username'))
                            ->pluck('id', 'username')
                            ->toArray();

                foreach ($newTransactions as &$trx) {
                    $trx['member_id'] = $inserted[$trx['username']] ?? null;
                }

                if (!empty($newTransactions)) {
                    \App\Models\Transaction::insert($newTransactions);
                }
            }

            DB::commit();

            // ✅ Delete file after import
            if (file_exists($filePath)) {
                @unlink($filePath);
            }

            ActivityLogger::log("Replaced members {$oldBatchCode} with {$batchCode}. By User : " . auth()->user()->name);

            return response()->json(responseCustom(true, "✅ Import success, total processed: {$countImport}, new members added: {$countNewMembers}"));

        } catch (\Throwable $th) {
            DB::rollBack();
            // delete file if error
            if (isset($filePath) && file_exists($filePath)) {
                @unlink($filePath);
            }
            return response()->json(responseCustom(false, "❌ Import failed: " . $th->getMessage()), 500);
        }
    }       

}
