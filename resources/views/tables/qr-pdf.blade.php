<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>QR Code - {{ $table->table_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            padding: 40px;
        }
        .qr-container {
            border: 3px solid #000;
            padding: 30px;
            display: inline-block;
            margin: 20px auto;
        }
        .qr-code {
            margin: 20px 0;
        }
        h1 {
            font-size: 48px;
            margin: 20px 0;
            color: #333;
        }
        .table-info {
            font-size: 24px;
            margin: 10px 0;
            color: #666;
        }
        .instructions {
            margin-top: 30px;
            font-size: 14px;
            color: #999;
            border-top: 2px solid #eee;
            padding-top: 20px;
        }
    </style>
</head>
<body>
<div class="qr-container">
    <h1>{{ $table->table_number }}</h1>
    <div class="table-info">{{ $table->table_name }}</div>

    <div class="qr-code">
        <img src="data:image/png;base64,{{ $qrCode }}" alt="QR Code" style="width: 300px; height: 300px;">
    </div>

    <div class="table-info">
        <strong>Scan to view table details</strong>
    </div>
</div>

<div class="instructions">
    <p>Instructions:</p>
    <p>1. Print this QR code</p>
    <p>2. Laminate for durability</p>
    <p>3. Mount on table {{ $table->table_number }}</p>
    <p>4. Scan with supervisor app to log production</p>
</div>
</body>
</html>
