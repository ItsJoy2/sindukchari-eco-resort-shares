<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice</title>

    <style>
        body {
            font-family: solaimanlipi, DejaVu Sans, sans-serif;
            font-size: 13px;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .sub-title {
            font-size: 16px;
            font-weight: bold;
        }
                .title {
            font-size: 22px;
            font-weight: bold;
        }

        .row {
            width: 100%;
            margin-bottom: 10px;
        }

        .col-6 {
            width: 50%;
            float: left;
        }

        .text-right {
            text-align: right;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table th, table td {
            border: 1px solid #ddd;
            padding: 8px;
        }

        table th {
            background: #f2f2f2;
        }

        .total-box {
            margin-top: 20px;
            width: 40%;
            float: right;
        }

        .clear {
            clear: both;
        }
    </style>
</head>

<body>

<div class="header">
    <div class="app-name title">
        {{ $settings->app_name ?? 'AJ Application' }}
    </div>
    <div class="Sub-title">INVOICE</div>
    <div>#{{ $invoice->invoice_no }}</div>
</div>

<div class="row">
    <div class="col-6">
        <strong>Customer:</strong><br>
        {{ $invoice->user->name }}<br>
        {{ $invoice->user->email }} <br>
        {{ $invoice->user->phone }}
    </div>

    <div class="col-6 text-right">
        <strong>Date:</strong><br>
        {{ $invoice->created_at->format('Y-m-d') }}
    </div>
</div>

<div class="clear"></div>

<table>
    <thead>
        <tr>
            <th>Share</th>
            <th class="text-right">Amount</th>
            <th class="text-right">Discount</th>
        </tr>
    </thead>

    <tbody>
        <tr>
            <td>{{ $invoice->investor->package->share_name ?? 'N/A' }}</td>
            <td class="text-right">৳ {{ number_format($invoice->amount,2) }}</td>
            <td class="text-right">৳ {{ number_format($invoice->discount_amount,2) }}</td>
        </tr>
    </tbody>
</table>

<div class="total-box">
    <table>
        <tr>
            <th>Total</th>
            <td class="text-right">
                ৳ {{ number_format($invoice->amount - $invoice->discount_amount,2) }}
            </td>
        </tr>
    </table>
</div>

<div class="clear"></div>

<br><br>

<p><strong>Status:</strong> {{ ucfirst($invoice->status) }}</p>

</body>
</html>
