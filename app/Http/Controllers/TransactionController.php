<?php

namespace App\Http\Controllers;

use App\DataTables\TransactionDataTable;
use App\Exports\TransactionsExport;
use App\Helpers\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use PDO;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Carbon\Carbon;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(TransactionDataTable $dataTable)
    {
        $teams = \App\Models\Team::orderBy('id', 'asc')->get();
        $marketings = \App\Models\User::whereHas('roles', function ($query) {
            $query->where('name', 'marketing');
        })->orderBy('id', 'asc')->get();
        return $dataTable->render('pages.apps.transactions.index', compact('teams', 'marketings'));
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
        try {
            $validator = Validator::make($request->all(), [
                'transaction_id' => 'required|exists:transactions,id',
                'amount' => 'required',
            ]);

            if ($validator->fails()) {
                $errorMsg = collect($validator->errors())->flatten()->implode(' ');
                return response()->json(responseCustom(false, "Validation Failed : " . $errorMsg, errors: $validator->errors()), 422);
            }

            $transaction = \App\Models\Transaction::find($id);
            if (!$transaction) {
                return response()->json(responseCustom(false, "Transaction not found."));
            }

            $oldAmount = $transaction->amount;
            $transaction->amount = $request->amount;
            $transaction->save();

            ActivityLogger::log("Updated transaction ID: {$transaction->id}, Member: {$transaction->member?->name}, Amount: from {$oldAmount} to {$transaction->amount}");
            return response()->json(responseCustom(true, "Transaction updated successfully."));
        } catch (\Throwable $th) {
            return response()->json(responseCustom(false, "Failed to update transaction: " . $th->getMessage()));
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $transaction = \App\Models\Transaction::find($id);
            if (!$transaction) {
                return response()->json(responseCustom(false, "Transaction not found."));
            }

            $transaction->delete();
            ActivityLogger::log("Deleted transaction ID: {$transaction->id}, Member: {$transaction->member?->name}, Amount: {$transaction->amount}");
            return response()->json(responseCustom(true, "Transaction deleted successfully."));
        } catch (\Throwable $th) {
            return response()->json(responseCustom(false, "Failed to delete transaction: " . $th->getMessage()));
        }
    }

    /**
     * Restore the specified resource from storage.
     */
    public function restore(string $id)
    {
        try {
            $transaction = \App\Models\Transaction::withTrashed()->find($id);
            if (!$transaction) {
                return response()->json(responseCustom(false, "Transaction not found."));
            }
            if (!$transaction->trashed()) {
                return response()->json(responseCustom(false, "Transaction is not deleted."));
            }

            $transaction->restore();
            ActivityLogger::log("Restored transaction ID: {$transaction->id}, Member: {$transaction->member?->name}, Amount: {$transaction->amount}");
            return response()->json(responseCustom(true, "Transaction restored successfully."));
        } catch (\Throwable $th) {
            return response()->json(responseCustom(false, "Failed to restore transaction: " . $th->getMessage()));
        }
    }



// public function import(Request $request)
// {
//     try {
//         $validator = Validator::make($request->all(), [
//             'file' => 'required|file|mimes:xlsx,xls,csv',
//             'transaction_date' => 'required|date_format:d-m-Y'
//         ]);

//         if ($validator->fails()) {
//             $errorMsg = collect($validator->errors())->flatten()->implode(' ');
//             return response()->json(responseCustom(false, "Validation Failed : " . $errorMsg, errors: $validator->errors()), 422);
//         }

//         $user = auth()->user();

//         // store uploaded file
//         $path = $request->file('file')->storeAs(
//             'imports',
//             'transactions_' . time() . '.' . $request->file('file')->getClientOriginalExtension()
//         );
//         $filePath = storage_path('app/' . $path);

//         // DB connection
//         $host = trim(config('app.connect_info.host'));
//         $db   = config('database.connections.mysql.database');
//         $username = config('database.connections.mysql.username');
//         $pass = config('database.connections.mysql.password');

//         $pdo = new \PDO("mysql:host=$host;dbname=$db", $username, $pass, [
//             \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
//         ]);

//         // === Generate batch code ===
//         $today = now()->format('Y-m-d');
//         $stmt = $pdo->prepare("
//             SELECT COUNT(DISTINCT batch_code)
//             FROM transactions
//             WHERE DATE(created_at) = :today
//         ");
//         $stmt->execute([':today' => $today]);
//         $countToday = (int) $stmt->fetchColumn();
//         $nextNumber = $countToday + 1;
//         $batchCode = "BATCH_TRANSACTIONS_{$today}_{$nextNumber}";

//         $pdo->beginTransaction();
//         $pdo->exec("DELETE FROM tmp_transactions WHERE entry_by = " . $user->id);

//         // === Read with Spout ===
//         $reader = ReaderEntityFactory::createReaderFromFile($filePath);
//         $reader->open($filePath);

//         $rows = [];
//         $rownum = 0;

//         $sheetDate = Carbon::createFromFormat('d-m-Y', $request->transaction_date);
//         $sheetDay = (string) $sheetDate->day;
//         $sheetFound = false;
//         foreach ($reader->getSheetIterator() as $sheet) {
//             if ($sheet->getName() === $sheetDay) {
//                 $sheetFound = true;
//                 foreach ($sheet->getRowIterator() as $i => $row) {
//                     if ($i === 1) continue;

//                     $cells = $row->toArray();
//                     $nama_rekening = $cells[2] ?? null; // C
//                     $usernameCsv   = $cells[3] ?? null; // D
//                     $nominal       = $cells[7] ?? null; // H

//                     if (!$usernameCsv) continue;

//                     $rownum++;
//                     if ($rownum > 10000) break;

//                     $rows[] = [
//                         strtolower(preg_replace('/\s+/', '', $usernameCsv)),
//                         $nama_rekening,
//                         str_replace(',', '', $nominal),
//                         $user->id,
//                         \Carbon\Carbon::createFromFormat('d-m-Y', $request->transaction_date)->format('Y-m-d'),
//                         $batchCode
//                     ];

//                     // Insert in chunks (to avoid memory blowup)
//                     if (count($rows) >= 1000) {
//                         $this->bulkInsertTmp($pdo, $rows);
//                         $rows = [];
//                     }
//                 }
//             }
//         }

//         if (!$sheetFound) {
//             return response()->json(
//                 responseCustom(false, "❌ Sheet With The Day '{$sheetDay}' Not Found in the Excel File."),
//                 422
//             );
//         }

//         $reader->close();

//         if (!empty($rows)) {
//             $this->bulkInsertTmp($pdo, $rows);
//         }

//         // same as your existing logic: insert into members & transactions
//         $pdo->exec("
//             INSERT INTO members (username, phone, name, nama_rekening, marketing_id, team_id, created_at, updated_at)
//             SELECT
//                 t.username,
//                 t.phone,
//                 COALESCE(t.nama_rekening, t.username) AS name,
//                 t.nama_rekening,
//                 NULL, NULL, NOW(), NOW()
//             FROM tmp_transactions t
//             LEFT JOIN members m ON LOWER(m.username) = LOWER(t.username)
//             WHERE m.id IS NULL
//             GROUP BY t.username
//         ");

//         $pdo->exec("
//             INSERT INTO transactions (id, member_id, user_id, amount, transaction_date, type, username, phone, nama_rekening, batch_code ,created_at, updated_at)
//             SELECT
//                 UUID(),
//                 m.id,
//                 t.entry_by,
//                 t.amount,
//                 COALESCE(t.transaction_date, NOW()),
//                 CASE
//                     WHEN EXISTS (
//                         SELECT 1 FROM transactions trx WHERE trx.member_id = m.id
//                     ) THEN 'REDEPOSIT'
//                     WHEN (
//                         SELECT COUNT(*)
//                         FROM tmp_transactions t2
//                         WHERE t2.username = t.username
//                         AND (t2.id < t.id)
//                     ) = 0 THEN 'DEPOSIT'
//                     ELSE 'REDEPOSIT'
//                 END,
//                 t.username,
//                 t.phone,
//                 t.nama_rekening,
//                 t.batch_code,
//                 NOW(),
//                 NOW()
//             FROM tmp_transactions t
//             JOIN members m ON m.username = t.username
//         ");

//         $pdo->exec("DELETE FROM tmp_transactions WHERE entry_by = " . $user->id);
//         $pdo->commit();

//         if (file_exists($filePath)) unlink($filePath);

//         ActivityLogger::log("Imported transactions via Excel/CSV File. By User : " . $user->name);

//         return response()->json(responseCustom(true, "✅ Import successful"));

//     } catch (\Throwable $th) {
//         if (isset($pdo) && $pdo->inTransaction()) {
//             $pdo->rollBack();
//         }
//         return response()->json(responseCustom(false, "❌ Import failed: " . $th->getMessage()), 500);
//     }
// }

// private function bulkInsertTmp($pdo, array $rows)
// {
//     $placeholders = rtrim(str_repeat("(?, ?, ?, ?, ?, ?),", count($rows)), ",");
//     $stmt = $pdo->prepare("
//         INSERT INTO tmp_transactions
//             (username, nama_rekening, amount, entry_by, transaction_date, batch_code)
//         VALUES $placeholders
//     ");
//     $flatValues = [];
//     foreach ($rows as $r) {
//         $flatValues = array_merge($flatValues, $r);
//     }
//     $stmt->execute($flatValues);
// }




// public function import(Request $request)
// {
//     try {
//         $validator = Validator::make($request->all(), [
//             'file' => 'required|file|mimes:xlsx,xls,csv',
//             'transaction_date' => 'required|date_format:d-m-Y'
//         ]);

//         if ($validator->fails()) {
//             $errorMsg = collect($validator->errors())->flatten()->implode(' ');
//             return response()->json(responseCustom(false, "Validation Failed : " . $errorMsg, errors: $validator->errors()), 422);
//         }

//         $user = auth()->user();

//         // store uploaded file
//         $path = $request->file('file')->storeAs(
//             'imports',
//             'transactions_' . time() . '.' . $request->file('file')->getClientOriginalExtension()
//         );
//         $filePath = storage_path('app/' . $path);

//         // DB connection
//         $host = trim(config('app.connect_info.host'));
//         $db   = config('database.connections.mysql.database');
//         $username = config('database.connections.mysql.username');
//         $pass = config('database.connections.mysql.password');

//         $pdo = new \PDO("mysql:host=$host;dbname=$db", $username, $pass, [
//             \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
//             \PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => false
//         ]);


//         $today = now()->format('Y-m-d');
//         $stmt = $pdo->prepare("
//             SELECT COUNT(DISTINCT batch_code)
//             FROM transactions
//             WHERE DATE(created_at) = :today
//         ");
//         $stmt->execute([':today' => $today]);
//         $countToday = (int) $stmt->fetchColumn();
//         $nextNumber = $countToday + 1;
//         $batchCode = "BATCH_TRANSACTIONS_{$today}_{$nextNumber}";

//         $pdo->beginTransaction();
//         $pdo->exec("DELETE FROM tmp_transactions WHERE entry_by = " . $user->id);

//         $reader = ReaderEntityFactory::createReaderFromFile($filePath);
//         $reader->open($filePath);

//         $rows = [];
//         $rownum = 0;

//         $sheetCount = 0;
//         $selectedSheet = null;
//         foreach ($reader->getSheetIterator() as $sheet) {
//             $sheetCount++;
//             $selectedSheet = $sheet;

//             if ($sheetCount > 1) {
//                 $reader->close();
//                 return response()->json(
//                     responseCustom(false, "❌ Excel File has more than one sheet. Please upload a file with a single sheet."),
//                     422
//                 );
//             }
//         }

//         if ($sheetCount === 0) {
//             $reader->close();
//             return response()->json(
//                 responseCustom(false, "❌ Excel File has no sheets."),
//                 422
//             );
//         }

//         foreach ($selectedSheet->getRowIterator() as $i => $row) {
//             if ($i === 1) continue;

//             $cells = $row->toArray();
//             // $nama_rekening = $cells[2] ?? null; // C
//             // $usernameCsv   = $cells[3] ?? null; // D
//             // $nominal       = $cells[7] ?? null; // H

//             $nama_rekening = $cells[0] ?? null; // A
//             $usernameCsv   = $cells[1] ?? null; // B
//             $nominal       = $cells[2] ?? null; // C

//             if (!$usernameCsv) continue;

//             $nominalClean = str_replace(',', '', $nominal);
//             if (!is_numeric($nominalClean) || $nominalClean <= 0) {
//                 return response()->json(
//                     responseCustom(false, "❌ Invalid nominal value '{$nominal}' for username '{$usernameCsv}' on row {$i}. It must be a valid Amount."),
//                     422
//                 );
//             }

//             $rownum++;
//             if ($rownum > 5000) break;

//             $rows[] = [
//                 strtolower(preg_replace('/\s+/', '', $usernameCsv)),
//                 $nama_rekening,
//                 str_replace(',', '', $nominal),
//                 $user->id,
//                 \Carbon\Carbon::createFromFormat('d-m-Y', $request->transaction_date)->format('Y-m-d'),
//                 $batchCode
//             ];

//             if (count($rows) >= 1000) {
//                 $this->bulkInsertTmp($pdo, $rows);
//                 $rows = [];
//             }
//         }

//         $reader->close();

//         if (!empty($rows)) {
//             $this->bulkInsertTmp($pdo, $rows);
//         } else if ($rownum === 0) {
//             return response()->json(
//                 responseCustom(false, "❌ No valid data found in the Excel/CSV file."),
//                 422
//             );
//         }

//         $pdo->exec("
//             INSERT INTO members (username, phone, name, nama_rekening, marketing_id, team_id, created_at, updated_at)
//             SELECT
//                 t.username,
//                 t.phone,
//                 COALESCE(t.nama_rekening, t.username) AS name,
//                 t.nama_rekening,
//                 NULL, NULL, COALESCE(t.transaction_date, NOW()), NOW()
//             FROM tmp_transactions t
//             LEFT JOIN members m ON LOWER(m.username) = LOWER(t.username)
//             WHERE m.id IS NULL
//             GROUP BY t.username
//         ");

//         $pdo->exec("
//             INSERT INTO transactions (id, member_id, user_id, amount, transaction_date, type, username, phone, nama_rekening, batch_code ,created_at, updated_at, import_at)
//             SELECT
//                 UUID(),
//                 m.id,
//                 t.entry_by,
//                 t.amount,
//                 COALESCE(t.transaction_date, NOW()),
//                 CASE
//                     WHEN EXISTS (
//                         SELECT 1 FROM transactions trx WHERE trx.member_id = m.id
//                     ) THEN 'REDEPOSIT'
//                     WHEN (
//                         SELECT COUNT(*)
//                         FROM tmp_transactions t2
//                         WHERE t2.username = t.username
//                         AND (t2.id < t.id)
//                     ) = 0 THEN 'DEPOSIT'
//                     ELSE 'REDEPOSIT'
//                 END,
//                 t.username,
//                 t.phone,
//                 t.nama_rekening,
//                 t.batch_code,
//                 NOW(),
//                 NOW(),
//                 NOW()
//             FROM tmp_transactions t
//             JOIN members m ON m.username = t.username
//         ");

//         $pdo->exec("DELETE FROM tmp_transactions WHERE entry_by = " . $user->id);
//         $pdo->commit();

//         if (file_exists($filePath)) unlink($filePath);

//         ActivityLogger::log("Imported transactions via Excel/CSV File. By User : " . $user->name);

//         return response()->json(responseCustom(true, "✅ success import transactions {$rownum} records."));

//     } catch (\Throwable $th) {
//         if (isset($pdo) && $pdo->inTransaction()) {
//             $pdo->rollBack();
//         }
//         return response()->json(responseCustom(false, "❌ Import failed: " . $th->getMessage()), 500);
//     }
// }

// private function bulkInsertTmp($pdo, array $rows)
// {
//     $placeholders = rtrim(str_repeat("(?, ?, ?, ?, ?, ?),", count($rows)), ",");
//     $stmt = $pdo->prepare("
//         INSERT INTO tmp_transactions
//             (username, nama_rekening, amount, entry_by, transaction_date, batch_code)
//         VALUES $placeholders
//     ");
//     $flatValues = [];
//     foreach ($rows as $r) {
//         $flatValues = array_merge($flatValues, $r);
//     }
//     $stmt->execute($flatValues);
// }


    public function import(Request $request)
    {
        ini_set('max_execution_time', 90); // stay under Cloudflare 100s
        ini_set('memory_limit', '512M');

        try {
            // --- 1️⃣ Validate request ---
            $validator = Validator::make($request->all(), [
                'file' => 'required|file|mimes:xlsx,xls,csv|max:51200', // 50MB max
                'transaction_date' => 'required|date_format:d-m-Y'
            ]);

            if ($validator->fails()) {
                $msg = collect($validator->errors())->flatten()->implode(' ');
                return response()->json(responseCustom(false, "Validation Failed: " . $msg, errors: $validator->errors()), 422);
            }

            $user = auth()->user();
            $file = $request->file('file');
            $filename = 'transactions_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('imports', $filename);
            $filePath = storage_path('app/' . $path);

            // --- 2️⃣ DB connection ---
            $dbConf = config('database.connections.mysql');
            $pdo = new \PDO("mysql:host={$dbConf['host']};dbname={$dbConf['database']}", $dbConf['username'], $dbConf['password'], [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            ]);

            // --- 3️⃣ Generate batch code ---
            $today = now()->format('Y-m-d');
            $countToday = (int)$pdo->query("SELECT COUNT(DISTINCT batch_code) FROM transactions WHERE DATE(created_at) = '$today'")->fetchColumn();
            $batchCode = "BATCH_TRANSACTIONS_{$today}_" . ($countToday + 1);

            // --- 4️⃣ Cleanup old tmp ---
            $pdo->exec("DELETE FROM tmp_transactions WHERE entry_by = {$user->id}");

            // --- 5️⃣ Read Excel ---
            $reader = \Box\Spout\Reader\Common\Creator\ReaderEntityFactory::createReaderFromFile($filePath);
            $reader->open($filePath);

            $sheet = null;
            foreach ($reader->getSheetIterator() as $s) {
                $sheet = $s;
                break; // only first sheet
            }

            if (!$sheet) {
                $reader->close();
                return response()->json(responseCustom(false, "❌ Excel file has no sheets."), 422);
            }

            $pdo->beginTransaction();

            $rows = [];
            $rownum = 0;
            $transactionDate = \Carbon\Carbon::createFromFormat('d-m-Y', $request->transaction_date)->format('Y-m-d');

            foreach ($sheet->getRowIterator() as $i => $row) {
                if ($i === 1) continue; // skip header

                $cells = $row->toArray();
                $nama_rekening = trim($cells[0] ?? '');
                $usernameCsv   = strtolower(trim(preg_replace('/\s+/', '', $cells[1] ?? '')));
                $nominal       = str_replace(',', '', $cells[2] ?? '');

                if (!$usernameCsv || !is_numeric($nominal) || $nominal <= 0) continue;

                $rows[] = [$usernameCsv, $nama_rekening, $nominal, $user->id, $transactionDate, $batchCode];
                $rownum++;

                if ($rownum >= 5000) break; // limit to avoid timeout
            }

            $reader->close();

            if ($rownum === 0) {
                $pdo->rollBack();
                return response()->json(responseCustom(false, "❌ No valid data found in file."), 422);
            }

            // --- 6️⃣ Single bulk insert for all rows ---
            $this->bulkInsertTmp($pdo, $rows);

            // --- 7️⃣ Fast insert for new members ---
            $pdo->exec("
                INSERT IGNORE INTO members (username, name, nama_rekening, created_at, updated_at)
                SELECT username, COALESCE(nama_rekening, username), nama_rekening, NOW(), NOW()
                FROM tmp_transactions WHERE entry_by = {$user->id}
            ");

            $pdo->exec("
                -- Precompute existing members who already have transactions
                CREATE TEMPORARY TABLE existing_members AS
                SELECT DISTINCT member_id
                FROM transactions;

                -- Precompute first occurrence per username in tmp_transactions
                CREATE TEMPORARY TABLE first_occurrence AS
                SELECT
                    username,
                    MIN(id) AS first_id
                FROM tmp_transactions
                WHERE entry_by = {$user->id}
                GROUP BY username;

                -- Insert final transactions
                INSERT INTO transactions (
                    id, member_id, user_id, amount, transaction_date,
                    type, username, phone, nama_rekening, batch_code,
                    created_at, updated_at, import_at
                )
                SELECT
                    UUID(),
                    m.id,
                    t.entry_by,
                    t.amount,
                    COALESCE(t.transaction_date, NOW()),
                    CASE
                        WHEN em.member_id IS NOT NULL THEN 'REDEPOSIT'
                        WHEN fo.first_id = t.id THEN 'DEPOSIT'
                        ELSE 'REDEPOSIT'
                    END AS type,
                    t.username,
                    t.phone,
                    t.nama_rekening,
                    t.batch_code,
                    NOW(), NOW(), NOW()
                FROM tmp_transactions t
                JOIN members m ON m.username = t.username
                LEFT JOIN existing_members em ON em.member_id = m.id
                LEFT JOIN first_occurrence fo ON fo.username = t.username
                WHERE t.entry_by = {$user->id};
            ");


            // --- 9️⃣ Cleanup + commit ---
            $pdo->exec("DELETE FROM tmp_transactions WHERE entry_by = {$user->id}");
            $pdo->commit();
            unlink($filePath);

            ActivityLogger::log("Imported {$rownum} transactions by {$user->name}");

            return response()->json(responseCustom(true, "✅ Successfully imported {$rownum} records."));
        } catch (\Throwable $th) {
            if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
            return response()->json(responseCustom(false, "❌ Import failed: " . $th->getMessage()), 500);
        }
    }

    private function bulkInsertTmp($pdo, array $rows)
    {
        $placeholders = rtrim(str_repeat('(?, ?, ?, ?, ?, ?),', count($rows)), ',');
        $stmt = $pdo->prepare("
            INSERT INTO tmp_transactions
            (username, nama_rekening, amount, entry_by, transaction_date, batch_code)
            VALUES $placeholders
        ");
        $flat = array_merge(...$rows);
        $stmt->execute($flat);
    }


    /**
     * Follow up member via WhatsApp link and record the follow-up action.
     */
    public function followUpMember(string $id)
    {
        try {
            $currentUser = auth()->user();
            $transaction = \App\Models\Transaction::find($id);
            if (!$transaction) {
                return response()->json(responseCustom(false, "Transaction not found."));
            }

            if (!$transaction->user && !$currentUser->hasRole(['administrator', 'leader'])) {
                return response()->json(responseCustom(false, "Please assign a user to this transaction before follow-up!"));
            }

            $phone = $transaction->member?->phone ?? null;
            if (!$phone) {
                return response()->json(responseCustom(false, "Member phone number not available, please update member phone data first."));
            }

            $waLink = "https://wa.me/{$phone}";
            $transaction->followups()->create([
                'member_id'      => $transaction->member->id,
                'user_id'        => auth()->id(),
                'note'           => "Followed up via Link WA : {$waLink}!",
                'followed_up_at' => now(),
            ]);

            ActivityLogger::log("Followed up member {$transaction->member?->name} (Transaction ID: {$transaction->id}) via WhatsApp link.");
            return response()->json(responseCustom(true, "Follow-up recorded successfully.", [
                'redirectUrl' => $waLink
            ]));
        } catch (\Throwable $th) {
            return response()->json(responseCustom(false, "Failed to record follow-up: " . $th->getMessage()));
        }
    }

    /**
    * Export to Excel or CSV.
    */
    public function export(Request $request, $type)
    {
        ActivityLogger::log("Exported transactions data file.");
        return Excel::download(
            new \App\Exports\TransactionsExport($request->all()),
            'transactions-' . now()->format('YmdHis') . '.xlsx'
        );
    }
}
