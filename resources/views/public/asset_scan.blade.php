<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asset Info — {{ $asset->name }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f3f4f6; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 16px; }
        .card { background: #fff; border-radius: 12px; box-shadow: 0 4px 24px rgba(0,0,0,0.08); max-width: 480px; width: 100%; overflow: hidden; }
        .header { background: linear-gradient(135deg, #1e40af, #3b82f6); padding: 24px; color: #fff; display: flex; align-items: center; gap: 16px; }
        .header img.logo { width: 56px; height: 56px; border-radius: 8px; object-fit: cover; background: rgba(255,255,255,0.2); }
        .header .inst-name { font-size: 0.875rem; opacity: 0.85; }
        .header h1 { font-size: 1.25rem; font-weight: 700; margin-top: 4px; }
        .body { padding: 24px; }
        .qr-wrap { text-align: center; margin-bottom: 20px; }
        .qr-wrap img { width: 120px; height: 120px; }
        .badge { display: inline-block; padding: 3px 10px; border-radius: 99px; font-size: 0.75rem; font-weight: 600; }
        table { width: 100%; border-collapse: collapse; }
        tr { border-bottom: 1px solid #f3f4f6; }
        tr:last-child { border-bottom: none; }
        td { padding: 10px 4px; font-size: 0.875rem; }
        td:first-child { color: #6b7280; width: 40%; font-weight: 500; }
        td:last-child { color: #111827; font-weight: 500; }
        .footer { background: #f9fafb; border-top: 1px solid #e5e7eb; padding: 12px 24px; text-align: center; font-size: 0.75rem; color: #9ca3af; }
    </style>
</head>
<body>
<div class="card">
    <div class="header">
        @if($asset->room->branch?->institution->logo)
            <img class="logo" src="{{ asset('storage/' . $asset->room->branch->institution->logo) }}" alt="Logo">
        @endif
        <div>
            <div class="inst-name">{{ $asset->room->branch?->institution->name ?? $asset->room->institution->name }}</div>
            <h1>{{ $asset->name }}</h1>
        </div>
    </div>

    <div class="body">
        <div class="qr-wrap">
            <img src="{{ $asset->qr_image_url }}" alt="QR Code">
        </div>

        <table>
            <tr>
                <td>Status</td>
                <td>
                    <span class="badge" style="background: {{ $asset->status?->color ?? '#6b7280' }}20; color: {{ $asset->status?->color ?? '#6b7280' }};">
                        {{ $asset->status?->name ?? '—' }}
                    </span>
                </td>
            </tr>
            <tr>
                <td>Category</td>
                <td>{{ $asset->category?->name ?? '—' }}</td>
            </tr>
            <tr>
                <td>Serial Number</td>
                <td>{{ $asset->serial_number ?? '—' }}</td>
            </tr>
            @if($asset->room->branch)
            <tr>
                <td>Branch</td>
                <td>{{ $asset->room->branch->name }}</td>
            </tr>
            @endif
            <tr>
                <td>Room</td>
                <td>
                    {{ $asset->room->name }}
                    @if($asset->room->room_number)
                        ({{ $asset->room->room_number }})
                    @endif
                </td>
            </tr>
            <tr>
                <td>Responsible</td>
                <td>
                    @if($asset->room->responsiblePerson)
                        {{ $asset->room->responsiblePerson->name }}
                        @if($asset->room->responsiblePerson->contact)
                            <br><small style="color:#6b7280">{{ $asset->room->responsiblePerson->contact }}</small>
                        @endif
                    @else
                        —
                    @endif
                </td>
            </tr>
            @if($asset->purchase_date)
            <tr>
                <td>Purchased</td>
                <td>{{ $asset->purchase_date->format('d M Y') }}</td>
            </tr>
            @endif
        </table>
    </div>

    <div class="footer">EduInventory &mdash; Scan to view asset details</div>
</div>
</body>
</html>
