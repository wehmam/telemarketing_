<table>
    <thead>
        <tr>
            <th>Team Name</th>
            <th>Amount Redeposit</th>
            <th>Total Redeposit</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($report as $row)
            <tr>
                <td>{{ $row->team_name ?? "-" }}</td>
                <td>{{ $row->total_redeposit_amount ?? 0 }}</td>
                <td>{{ (int) $row->total_redeposit_count ?? 0 }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
