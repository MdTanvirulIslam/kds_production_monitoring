<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>All QR Codes</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .page-break {
            page-break-after: always;
        }
        .qr-item {
            width: 48%;
            display: inline-block;
            text-align: center;
            border: 2px solid #000;
            padding: 15px;
            margin: 5px;
            vertical-align: top;
            box-sizing: border-box;
        }
        .qr-code {
            margin: 10px 0;
        }
        h2 {
            font-size: 24px;
            margin: 10px 0;
        }
        .table-info {
            font-size: 14px;
            color: #666;
        }
        @media print {
            .qr-item {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
<h1 style="text-align: center; padding: 20px;">Factory QR Codes - All Tables</h1>

@foreach($qrCodes as $index => $item)
    <div class="qr-item">
        <h2>{{ $item['table']->table_number }}</h2>
        <div class="table-info">{{ $item['table']->table_name }}</div>
        <div class="qr-code">
            <img src="data:image/png;base64,{{ $item['qrCode'] }}" style="width: 150px; height: 150px;">
        </div>
    </div>

    @if(($index + 1) % 6 == 0)
        <div class="page-break"></div>
    @endif
@endforeach
</body>
</html>
