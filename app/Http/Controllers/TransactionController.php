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

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(TransactionDataTable $dataTable)
    {
        return $dataTable->render('pages.apps.transactions.index');
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
        //
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
    //            'file' => 'required|file|mimes:csv,txt'
    //         ]);

    //         if ($validator->fails()) {
    //             $errorMsg = collect($validator->errors())->flatten()->implode(' ');
    //             return response()->json(responseCustom(false, "Validation Failed : " . $errorMsg, errors: $validator->errors()), 422);
    //         }

    //         $user   = auth()->user();
    //         $teamId = $user->team_id;
    //         // if (!$teamId) {
    //         //     return response()->json(responseCustom(false, "❌ Import failed: You are not assigned to any team. Please contact the administrator."), 422);
    //         // }

    //         $path = $request->file('file')->storeAs(
    //             'imports',
    //             'transactions_' . time() . '.csv'
    //         );

    //         $csvfile = storage_path('app/' . $path);
    //         $host = trim(config('app.connect_info.host'), " ");
    //         $db   = config('database.connections.mysql.database');
    //         $username = config('database.connections.mysql.username');
    //         $pass = config('database.connections.mysql.password');

    //         $pdo = new PDO("mysql:host=$host;dbname=$db", $username, $pass, [
    //             PDO::MYSQL_ATTR_LOCAL_INFILE => true,
    //             PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    //         ]);

    //         // $pdo->exec("DELETE FROM tmp_transactions WHERE entry_by = " . $user->id);

    //          try {
    //             // 4. Delete previous tmp_transactions for this user
    //             $pdo->exec("DELETE FROM tmp_transactions WHERE entry_by = " . $user->id);

    //             // 5. Open CSV and read line by line
    //             $handle = fopen($csvfile, 'r');
    //             if (!$handle) {
    //                 return response()->json(responseCustom(false, "Cannot open CSV file"), 422);
    //             }

    //             // Skip header row
    //             fgetcsv($handle, 0, ",");

    //             $rownum = 0;
    //             $pdo->beginTransaction();

    //             while (($data = fgetcsv($handle, 0, ",")) !== false) {
    //                 // Adjust columns based on your CSV layout
    //                 $nama_rekening = $data[2] ?? null; // Column C
    //                 $username     = $data[3] ?? null; // Column D
    //                 $nominal      = $data[7] ?? null; // Column H

    //                 if (!$username) continue; // skip empty rows

    //                 $rownum++;
    //                 if ($rownum > 2000) break; // limit to 2000 rows

    //                 $stmt = $pdo->prepare("
    //                     INSERT INTO tmp_transactions
    //                         (nama_rekening, username, amount, entry_by, created_at, updated_at)
    //                     VALUES
    //                         (:nama_rekening, :username, :amount, :entry_by, NOW(), NOW())
    //                 ");

    //                 $stmt->execute([
    //                     ':nama_rekening' => $nama_rekening,
    //                     ':username'      => strtolower($username),
    //                     ':amount'        => str_replace(',', '', $nominal),
    //                     ':entry_by'      => $user->id
    //                 ]);
    //             }

    //             $pdo->commit();
    //             fclose($handle);

    //             return response()->json(responseCustom(true, "Import successful"));

    //         } catch (\Exception $e) {
    //             $pdo->rollBack();
    //             return response()->json(responseCustom(false, "Import failed: " . $e->getMessage()), 500);
    //         }

    //         // $pdo->exec("
    //         //     LOAD DATA LOCAL INFILE " . $pdo->quote($csvfile) . "
    //         //     INTO TABLE tmp_transactions
    //         //     FIELDS TERMINATED BY ',' ENCLOSED BY '\"'
    //         //     LINES TERMINATED BY '\n'
    //         //     IGNORE 1 LINES
    //         //     (@username, @phone, amount, transaction_date)
    //         //     SET
    //         //         username = LOWER(@username),
    //         //         phone = CASE
    //         //             WHEN @phone IS NULL OR @phone = '' THEN NULL
    //         //             WHEN @phone REGEXP 'E\\+' THEN NULL   -- scientific notation → NULL
    //         //             WHEN LEFT(REGEXP_REPLACE(@phone, '[^0-9]', ''),1)='0'
    //         //                 THEN CONCAT('62', SUBSTRING(REGEXP_REPLACE(@phone, '[^0-9]', ''),2))
    //         //             WHEN LEFT(REGEXP_REPLACE(@phone, '[^0-9]', ''), 1) = '8' THEN
    //         //                 CONCAT('62', REGEXP_REPLACE(@phone, '[^0-9]', ''))
    //         //             ELSE REGEXP_REPLACE(@phone, '[^0-9]', '')
    //         //         END,
    //         //         entry_by = " . $user->id. ",
    //         //         created_at = NOW(),
    //         //         updated_at = NOW()
    //         // ");

    //         $pdo->exec("SET @rownum := 0;");
    //         $pdo->exec("
    //             LOAD DATA LOCAL INFILE " . $pdo->quote($csvfile) . "
    //             INTO TABLE tmp_transactions
    //             FIELDS TERMINATED BY ',' ENCLOSED BY '\"'
    //             LINES TERMINATED BY '\\n'
    //             IGNORE 1 LINES
    //             (@shift, @bank, @nama_rekening, @username, @nominal, @potongan, @bonus, @total_dp)
    //             SET
    //                 @rownum := @rownum + 1,
    //                 username = LOWER(@username),
    //                 nama_rekening = @nama_rekening,
    //                 amount = REPLACE(@nominal, ',', ''),  -- remove commas from number
    //                 entry_by = " . $user->id . ",
    //                 created_at = NOW(),
    //                 updated_at = NOW()
    //             WHERE @rownum <= 2000
    //             AND @username IS NOT NULL
    //             AND @username != ''
    //         ");

    //         die;

    //         // $invalidCount = DB::table('tmp_transactions')
    //         //     ->whereNull('phone')
    //         //     ->count();

    //         // if ($invalidCount > 0) {
    //         //     $pdo->exec("DELETE FROM tmp_transactions WHERE entry_by = " . $user->id);
    //         //     throw new \Exception("❌ Import failed: {$invalidCount} invalid phone numbers found in CSV.");
    //         // }

    //         // $pdo->exec("
    //         //     INSERT INTO members (username, phone, name, marketing_id, team_id, created_at, updated_at)
    //         //     SELECT t.username, t.phone, t.username, t.entry_by, {$teamId}, NOW(), NOW()
    //         //     FROM tmp_transactions t
    //         //     LEFT JOIN members m ON m.username = t.username
    //         //     WHERE m.id IS NULL
    //         // ");
    //         $teamIdValue = $teamId !== null ? (int) $teamId : 'NULL';
    //         $pdo->exec("INSERT INTO members (username, phone, name, marketing_id, team_id, created_at, updated_at)
    //             SELECT t.username,
    //                 MAX(t.phone)       AS phone,
    //                 MAX(t.username)    AS name,
    //                 MAX(t.entry_by)    AS marketing_id,
    //                 {$teamIdValue}     AS team_id,
    //                 NOW(),
    //                 NOW()
    //             FROM tmp_transactions t
    //             LEFT JOIN members m ON m.username = t.username
    //             WHERE m.id IS NULL
    //             GROUP BY t.username
    //         ");
    //         // $pdo->exec("
    //         //     INSERT INTO members (username, phone, name, marketing_id, team_id, created_at, updated_at)
    //         //     SELECT t.username, t.phone, t.username, t.entry_by, {$teamIdValue}, NOW(), NOW()
    //         //     FROM tmp_transactions t
    //         //     LEFT JOIN members m ON m.username = t.username
    //         //     WHERE m.id IS NULL
    //         // ");
    //         // die("DEBUG: STOPPED BEFORE INSERT TRANSACTIONS");

    //         $pdo->exec("
    //             INSERT INTO transactions (id, member_id, user_id, amount, transaction_date, type, username, phone, created_at, updated_at)
    //             SELECT
    //                 UUID(),
    //                 m.id,
    //                 t.entry_by,
    //                 t.amount,
    //                 t.transaction_date,
    //                 CASE
    //                     WHEN EXISTS (
    //                         SELECT 1 FROM transactions trx WHERE trx.member_id = m.id
    //                     ) THEN 'REDEPOSIT'  -- member already has previous transactions
    //                     WHEN (
    //                         SELECT COUNT(*)
    //                         FROM tmp_transactions t2
    //                         WHERE t2.username = t.username
    //                         AND (t2.transaction_date < t.transaction_date OR
    //                             (t2.transaction_date = t.transaction_date AND t2.id < t.id))
    //                     ) = 0 THEN 'DEPOSIT'  -- first transaction in this batch
    //                     ELSE 'REDEPOSIT'       -- subsequent transactions in batch
    //                 END AS type,
    //                 t.username,
    //                 t.phone,
    //                 NOW(),
    //                 NOW()
    //             FROM tmp_transactions t
    //             JOIN members m ON m.username = t.username
    //         ");

    //         $pdo->exec("DELETE FROM tmp_transactions WHERE entry_by = " . $user->id);

    //         if (file_exists($csvfile)) {
    //             unlink($csvfile);
    //         }

    //         ActivityLogger::log("Imported transactions via CSV File. By User : " . $user->name);
    //         return response()->json(responseCustom(true, "✅ Import successful"));
    //     } catch (\Throwable $th) {
    //         return response()->json(responseCustom(false, "❌ Import failed Exception: " . $th->getMessage()));
    //     }
    // }


    public function import(Request $request)
{
    try {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:csv,txt'
        ]);

        if ($validator->fails()) {
            $errorMsg = collect($validator->errors())->flatten()->implode(' ');
            return response()->json(responseCustom(false, "Validation Failed : " . $errorMsg, errors: $validator->errors()), 422);
        }

        $user = auth()->user();
        $path = $request->file('file')->storeAs(
            'imports',
            'transactions_' . time() . '.csv'
        );

        $csvfile = storage_path('app/' . $path);
        $host = trim(config('app.connect_info.host'));
        $db   = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $pass = config('database.connections.mysql.password');

        $pdo = new PDO("mysql:host=$host;dbname=$db", $username, $pass, [
            PDO::MYSQL_ATTR_LOCAL_INFILE => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);

        $pdo->beginTransaction();

        // Delete previous tmp_transactions for this user
        $pdo->exec("DELETE FROM tmp_transactions WHERE entry_by = " . $user->id);

        // Open CSV and read line by line
        $handle = fopen($csvfile, 'r');
        if (!$handle) {
            return response()->json(responseCustom(false, "Cannot open CSV file"), 422);
        }

        fgetcsv($handle, 0, ","); // skip header
        $rownum = 0;

        while (($data = fgetcsv($handle, 0, ",")) !== false) {
            $nama_rekening = $data[2] ?? null; // C
            $username     = $data[3] ?? null; // D
            $nominal      = $data[7] ?? null; // H

            if (!$username) continue;

            $rownum++;
            if ($rownum > 2000) break;

            $stmt = $pdo->prepare("
                INSERT INTO tmp_transactions
                    (nama_rekening, username, amount, entry_by, created_at, updated_at)
                VALUES
                    (:nama_rekening, :username, :amount, :entry_by, NOW(), NOW())
            ");

            $stmt->execute([
                ':nama_rekening' => $nama_rekening,
                // ':username'      => strtolower($username),
                ':username'     => strtolower(preg_replace('/\s+/', '', $username)),
                ':amount'        => str_replace(',', '', $nominal),
                ':entry_by'      => $user->id
            ]);
        }

        fclose($handle);

        //  $pdo->exec("
        //     INSERT INTO members (username, phone, name, nama_rekening, marketing_id, team_id, created_at, updated_at)
        //     SELECT DISTINCT t.username, t.phone, COALESCE(t.nama_rekening, t.username), t.nama_rekening, NULL, NULL, NOW(), NOW()
        //     FROM tmp_transactions t
        //     LEFT JOIN members m ON m.username = t.username
        //     WHERE m.id IS NULL
        // ");
        $pdo->exec("
            INSERT INTO members (username, phone, name, nama_rekening, marketing_id, team_id, created_at, updated_at)
            SELECT
                t.username,
                t.phone,
                COALESCE(t.nama_rekening, t.username) AS name,
                t.nama_rekening,
                NULL AS marketing_id,
                NULL AS team_id,
                NOW(),
                NOW()
            FROM tmp_transactions t
            LEFT JOIN members m
                ON LOWER(m.username) = LOWER(t.username)
            WHERE m.id IS NULL
            GROUP BY t.username
        ");


        // // Bulk insert into transactions from tmp_transactions
        $pdo->exec("
            INSERT INTO transactions (id, member_id, user_id, amount, transaction_date, type, username, phone, created_at, updated_at)
            SELECT
                UUID(),
                m.id,
                t.entry_by,
                t.amount,
                COALESCE(t.transaction_date, NOW()) AS transaction_date,  -- default to NOW() if null
                CASE
                    WHEN EXISTS (
                        SELECT 1 FROM transactions trx WHERE trx.member_id = m.id
                    ) THEN 'REDEPOSIT'  -- member already has previous transactions
                    WHEN (
                        SELECT COUNT(*)
                        FROM tmp_transactions t2
                        WHERE t2.username = t.username
                        AND (t2.id < t.id)
                    ) = 0 THEN 'DEPOSIT'  -- first transaction in this batch
                    ELSE 'REDEPOSIT'       -- subsequent transactions in batch
                END AS type,
                t.username,
                t.phone,
                NOW(),
                NOW()
            FROM tmp_transactions t
            JOIN members m ON m.username = t.username
        ");

        // Clean up
        $pdo->exec("DELETE FROM tmp_transactions WHERE entry_by = " . $user->id);
        $pdo->commit();

        if (file_exists($csvfile)) {
            unlink($csvfile);
        }

        ActivityLogger::log("Imported transactions via CSV File. By User : " . $user->name);

        return response()->json(responseCustom(true, "✅ Import successful"));

    } catch (\Throwable $th) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        return response()->json(responseCustom(false, "❌ Import failed: " . $th->getMessage()), 500);
    }
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
