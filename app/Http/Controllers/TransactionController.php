<?php

namespace App\Http\Controllers;

use App\DataTables\TransactionDataTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        //
    }

    public function import(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:csv,txt',
            ]);

            $user   = auth()->user();
            $teamId = $user->team_id;


            $path = $request->file('file')->storeAs(
                'imports',
                'transactions_' . time() . '.csv'
            );

            $csvfile = storage_path('app/' . $path);
            $host = trim(config('app.connect_info.host'), " ");
            $db   = config('database.connections.mysql.database');
            $username = config('database.connections.mysql.username');
            $pass = config('database.connections.mysql.password');

            $pdo = new PDO("mysql:host=$host;dbname=$db", $username, $pass, [
                PDO::MYSQL_ATTR_LOCAL_INFILE => true,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);

            $pdo->exec("DELETE FROM tmp_transactions WHERE entry_by = " . $user->id);

            $pdo->exec("
                LOAD DATA LOCAL INFILE " . $pdo->quote($csvfile) . "
                INTO TABLE tmp_transactions
                FIELDS TERMINATED BY ',' ENCLOSED BY '\"'
                LINES TERMINATED BY '\n'
                IGNORE 1 LINES
                (@username, @phone, amount, transaction_date)
                SET
                    username = LOWER(@username),
                    phone = CASE
                        WHEN @phone IS NULL OR @phone = '' THEN NULL
                        WHEN @phone REGEXP 'E\\+' THEN NULL   -- scientific notation → NULL
                        WHEN LEFT(REGEXP_REPLACE(@phone, '[^0-9]', ''),1)='0'
                            THEN CONCAT('62', SUBSTRING(REGEXP_REPLACE(@phone, '[^0-9]', ''),2))
                        WHEN LEFT(REGEXP_REPLACE(@phone, '[^0-9]', ''), 1) = '8' THEN
                            CONCAT('62', REGEXP_REPLACE(@phone, '[^0-9]', ''))
                        ELSE REGEXP_REPLACE(@phone, '[^0-9]', '')
                    END,
                    entry_by = " . $user->id. ",
                    created_at = NOW(),
                    updated_at = NOW()
            ");

            $invalidCount = DB::table('tmp_transactions')
                ->whereNull('phone')
                ->count();

            if ($invalidCount > 0) {
                $pdo->exec("DELETE FROM tmp_transactions WHERE entry_by = " . $user->id);
                throw new \Exception("❌ Import failed: {$invalidCount} invalid phone numbers found in CSV.");
            }

            $pdo->exec("
                INSERT INTO members (username, phone, name, marketing_id, team_id, created_at, updated_at)
                SELECT t.username, t.phone, t.username, t.entry_by, {$teamId}, NOW(), NOW()
                FROM tmp_transactions t
                LEFT JOIN members m ON m.username = t.username
                WHERE m.id IS NULL
            ");

            $pdo->exec("
                INSERT INTO transactions (id, member_id, user_id, amount, transaction_date, type, username, phone, created_at, updated_at)
                SELECT
                    UUID(),
                    m.id,
                    t.entry_by,
                    t.amount,
                    t.transaction_date,
                    CASE
                        WHEN EXISTS (
                            SELECT 1 FROM transactions trx WHERE trx.member_id = m.id
                        ) THEN 'REDEPOSIT'  -- member already has previous transactions
                        WHEN (
                            SELECT COUNT(*)
                            FROM tmp_transactions t2
                            WHERE t2.username = t.username
                            AND (t2.transaction_date < t.transaction_date OR
                                (t2.transaction_date = t.transaction_date AND t2.id < t.id))
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

            $pdo->exec("DELETE FROM tmp_transactions WHERE entry_by = " . $user->id);


            return response()->json(responseCustom(true, "✅ Import successful"));
        } catch (\Throwable $th) {
            return response()->json(responseCustom(false, "❌ Import failed: " . $th->getMessage()));
        }
    }

    public function followUp(string $id)
    {
        $transaction = \App\Models\Transaction::findOrFail($id);
        return view('pages.apps.transactions.follow-up', compact('transaction'));
    }
}
