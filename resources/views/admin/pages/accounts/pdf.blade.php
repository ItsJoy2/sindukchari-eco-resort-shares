<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">

<style>
body {
    font-family: sans-serif;
    font-size: 12px;
}

.header {
    text-align: center;
    margin-bottom: 10px;
}

.company-name {
    font-size: 18px;
    font-weight: bold;
}

.report-title {
    font-size: 14px;
    margin-top: 3px;
}

.filter-box {
    margin-top: 10px;
    margin-bottom: 10px;
}

.filter-box p {
    margin: 2px 0;
}

.report-box {
    /* border: 1px solid #000; */
    padding: 8px;
}

table {
    width:100%;
    border-collapse: collapse;
}

td, th {
    border:1px solid #000;
    padding:6px;
    font-size:12px;
}

th {
    background: #f2f2f2;
}
</style>
</head>

<body>

<div class="report-box">

    {{-- HEADER --}}
    <div class="header">
        <div class="company-name">
            {{ $app_name }}
        </div>

        <div class="report-title">
            Accounts Report
        </div>
    </div>

    {{-- FILTER INFO --}}
    <div class="filter-box">
        <p><strong>Date Range:</strong> {{ $date_range ?? 'All' }}</p>
        <p><strong>Filter:</strong> {{ $filter ?? 'All' }}</p>
        <p><strong>Search:</strong> {{ $search ?? '--' }}</p>
    </div>

    {{-- TABLE --}}
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Type</th>
                <th>Category</th>
                <th>Amount</th>
                <th>Note</th>
            </tr>
        </thead>

        <tbody>
            @foreach($accounts as $a)
            <tr>
                <td>{{ $a['date'] }}</td>
                <td>{{ ucfirst($a['type']) }}</td>
                <td>{{ $a['category'] }}</td>
                <td>{{ number_format($a['amount'], 2) }}</td>
                <td>{{ $a['note'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

</div>

</body>
</html>
