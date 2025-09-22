<table>
    <thead>
        <tr>
            <th>Marketing Name</th>
            <th>Start Working</th>
            <th>Total New Members</th>
            <th>Total New Deposit Amount</th>
            <th>Total New Deposit</th>
            <th>Team</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($report as $row)
            <tr>
                <td>{{ $row->marketing }}</td>
                <td>{{ $row->start_kerja ? \Carbon\Carbon::parse($row->start_kerja)->format('d-F-Y') : '-' }}</td>
                <td>{{ (int) $row->member_daftar ?? 0 }}</td>
                <td>{{ $row->total_deposit_amount }}</td>
                <td>{{ $row->total_deposit_transactions }}</td>
                <td>{{ $row->team_name ?? "-" }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
