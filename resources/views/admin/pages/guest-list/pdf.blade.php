<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Guest List</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .logo {
            width: 80px;
            height: auto;
            margin-bottom: 10px;
        }

        h2 {
            margin: 0;
            padding: 0;
        }

        .subtitle {
            font-size: 12px;
            color: #666;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th {
            background: #f4f4f4;
            padding: 8px;
            text-align: left;
        }

        td {
            padding: 6px;
        }

        .status-interested {
            color: #0d6efd;
            font-weight: bold;
        }

        .status-motivated {
            color: #198754;
            font-weight: bold;
        }

        .status-not {
            color: #dc3545;
            font-weight: bold;
        }

    </style>
</head>

<body>

@php
    $settings = \App\Models\GeneralSetting::first();
@endphp

<div class="header">

    @if($settings && $settings->logo)
        <img class="logo" src="{{ public_path('storage/' . $settings->logo) }}">
    @endif

    <h2>{{ $settings->app_name ?? 'Guest List Report' }}</h2>

    <div class="subtitle">
        Generated on: {{ date('d M Y') }}
    </div>
</div>

<table>

    <thead>
        <tr>
            <th>Date</th>
            <th>Name</th>
            <th>Mobile</th>
            <th>Address</th>
            <th>Profession</th>
            <th>Status</th>
            <th>Reference</th>
            <th>Note</th>
        </tr>
    </thead>

    <tbody>

    @foreach($guestLists as $g)
        <tr>
            <td>{{ \Carbon\Carbon::parse($g->date)->format('d M Y') }}</td>
            <td>{{ $g->name }}</td>
            <td>{{ $g->mobile ?? 'N/A' }}</td>
            <td>{{ $g->address ?? 'N/A' }}</td>
            <td>{{ $g->profession ?? 'N/A' }}</td>

            <td>
                @if($g->status == 'Interested')
                    <span class="status-interested">Interested</span>
                @elseif($g->status == 'Highly Motivated')
                    <span class="status-motivated">Highly Motivated</span>
                @else
                    <span class="status-not">Not Interested</span>
                @endif
            </td>

            <td>{{ $g->reference ?? 'N/A' }}</td>
            <td>{{ $g->note ?? 'N/A' }}</td>
        </tr>
    @endforeach

    </tbody>

</table>

</body>
</html>
