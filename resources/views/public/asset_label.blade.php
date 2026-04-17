<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 10px; background: #fff; }
        .label { width: 200px; padding: 12px; border: 1px solid #ccc; border-radius: 6px; }
        .inst { font-size: 8px; color: #666; margin-bottom: 4px; }
        .asset-name { font-size: 12px; font-weight: bold; margin-bottom: 2px; }
        .qr { text-align: center; margin: 8px 0; }
        .qr img { width: 100px; height: 100px; }
        .info { font-size: 8px; color: #444; }
        .info div { margin-bottom: 2px; }
        .info span { font-weight: bold; }
    </style>
</head>
<body>
<div class="label">
    <div class="inst">
        {{ $asset->room->branch?->institution->name ?? $asset->room->institution->name }}
        @if($asset->room->branch) / {{ $asset->room->branch->name }} @endif
    </div>
    <div class="asset-name">{{ $asset->name }}</div>

    <div class="qr">
        <img src="{{ storage_path('app/public/qrcodes/' . $asset->qr_code . '.svg') }}" alt="QR">
    </div>

    <div class="info">
        <div><span>Room:</span> {{ $asset->room->name }}{{ $asset->room->room_number ? ' (' . $asset->room->room_number . ')' : '' }}</div>
        @if($asset->serial_number)
        <div><span>S/N:</span> {{ $asset->serial_number }}</div>
        @endif
        @if($asset->room->responsiblePerson)
        <div><span>Contact:</span> {{ $asset->room->responsiblePerson->name }}</div>
        @endif
        <div style="margin-top:4px; font-size:7px; color:#999;">{{ $asset->qr_code }}</div>
    </div>
</div>
</body>
</html>
