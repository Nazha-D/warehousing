<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Quotation #{{ $quotation->quotation_number }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            margin: 0;
            padding: 20px;
            box-sizing: border-box;
        }

        .header {
            display: table;
            width: 100%;
            margin-bottom: 30px;
            font-size: 12px
        }

        .header-cell {
            display: table-cell;
            vertical-align: top;
            padding: 0 10px;
            font-size: 12px
        }

        .company-info {
            width: 33%;
            font-size: 12px;
            text-align: left;
        }

        .company-info2 {
            width: 33%;
            text-align: center;
            font-size: 12px
        }

        .logo-container {
            width: 33%;
            text-align: right;
            font-size: 12px
        }

        .logo-container img {
            max-width: 140px;
            height: auto;
            font-size: 12px
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 12px
        }

        th, td {
            padding: 8px;
            border: 1px solid #ccc;
            text-align: left;
            font-size: 12px
        }

        .total {
            margin-top: 20px;
            /*font-size: 16px;*/
            font-weight: bold;
            font-size: 12px
        }
    </style>
</head>
<body>

<div class="header">
    <div class="header-cell company-info">
        <h2>{{ $companySetting->full_company_name ?? 'Company Name' }}</h2>
        <p>Telephone: {{ $companySetting->phone_code . $companySetting->phone_number }}</p>
        <p>Address: {{ $companySetting->address }}</p>

    </div>

    <div class="header-cell company-info2">

        <p>Sales Person: {{ $quotation->salesPerson->name }}</p>
        <p>Currency: {{ $quotation->currency->name ?? 'N/A' }}</p>
        <p>Date: {{ $quotation->created_at->format('Y-m-d') }}</p>
        <p>TO: {{ $quotation->client->name ?? 'N/A' }}</p>
    </div>

    @if($companySetting->logo)
        <div class="header-cell logo-container">
            <img src="{{ 'https://theravenstyle.com/rooster-backend/public/' . $companySetting->logo }}" alt="Company Logo">
        </div>
    @endif
</div>
<h4>Offer #{{ $quotation->quotation_number }}</h2>
    <h3>Items:</h3>
    <table>
        <thead>
        <tr>
            <th>Description</th>
            <th>Quantity</th>
            <th>Unit Price</th>
            <th>Total</th>
        </tr>
        </thead>
        <tbody>
        @foreach($quotation->quotationLines as $line)
            @if ($line->description)
                <tr>
                    <td>{{ $line->description }}</td>
                    <td>{{ $line->quantity }}</td>
                    <td>{{ number_format($line->unit_price, 2) }}</td>
                    <td>{{ number_format($line->quantity * $line->unit_price, 2) }}</td>
                </tr>
            @elseif ($line->combo_description)
                <tr>
                    <td>{{ $line->combo_description }}</td>
                    <td>{{ $line->combo_quantity }}</td>
                    <td>{{ number_format($line->combo_price, 2) }}</td>
                    <td>{{ number_format($line->combo_quantity * $line->combo_price, 2) }}</td>
                </tr>
            @elseif ($line->image)
                <tr>
                    <td colspan="4" style="text-align: center;">
                        <img src="{{ 'https://theravenstyle.com/rooster-backend/public/' . $line->image }}" style="width: 150px;" alt="Image">
                    </td>
                </tr>
            @elseif ($line->note)
                <tr>
                    <td colspan="4"><strong>Note:</strong> {{ $line->note }}</td>
                </tr>
            @endif
        @endforeach
        </tbody>
    </table>



    <p class="total">Discount: {{ number_format($quotation->global_discount_amount, 2) }}</p>
    <p class="total">Special Discount: {{ number_format($quotation->special_discount_amount, 2) }}</p>
    <p class="total">VAT: {{ number_format($quotation->vat, 2)  }}</p>
    <p class="total">Total: {{ number_format($quotation->total, 2)  }}</p>
    <p class="total">Total Before VAT: {{ number_format($quotation->total_before_vat, 2) . ' ' . $quotation->currency->name }}</p>
</body>
</html>
