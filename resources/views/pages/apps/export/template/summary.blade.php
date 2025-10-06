<table>
    <thead>
        <tr>
            <th>Marketing</th>
            <th>Team</th>
            <th>Start Kerja</th>
            <th>Member Daftar</th>
            <th>Total Deposit Amount</th>
            <th>Total Deposit Transactions</th>
            <th>Total Redeposit Amount</th>
            <th>Total Redeposit Transactions</th>
            <th>Total Followup</th>
        </tr>
    </thead>
    <tbody>
        @foreach($report as $row)
            <tr>
                <td>{{ $row->marketing }}</td>
                <td>{{ $row->team_name }}</td>
                <td>{{ $row->start_kerja ? \Carbon\Carbon::parse($row->start_kerja)->format('d-F-Y') : '-' }}</td>
                <td>{{ (int) $row->member_daftar ?? 0 }}</td>
                <td>{{ $row->total_deposit_amount }}</td>
                <td>{{ (int) $row->total_deposit_transactions }}</td>
                <td>{{ $row->total_redeposit_amount }}</td>
                <td>{{ (int) $row->total_redeposit_transactions }}</td>
                <td>{{ (int) $row->total_followups ?? 0 }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
