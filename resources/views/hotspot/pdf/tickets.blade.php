<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        @page {
            margin: 12mm;
        }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 12px;
            color: #222;
        }

        .grid {
            display: flex;
            flex-wrap: wrap;
        }

        .ticket {
            width: 48%;
            border: 1px solid #444;
            border-radius: 6px;
            padding: 8px 10px 10px;
            margin: 1%;
            box-sizing: border-box;
            position: relative;
            min-height: 140px;
        }

        .header {
            font-weight: bold;
            font-size: 13px;
            margin-bottom: 4px;
        }

        .meta {
            font-size: 10px;
            color: #555;
            margin-bottom: 6px;
        }

        .cred {
            font-size: 14px;
            letter-spacing: .5px;
        }

        .label {
            font-weight: 600;
            display: inline-block;
            width: 70px;
        }

        .footer {
            position: absolute;
            bottom: 6px;
            left: 10px;
            right: 10px;
            font-size: 9px;
            color: #555;
        }

        .qr {
            position: absolute;
            top: 8px;
            right: 8px;
            width: 54px;
            height: 54px;
            border: 1px dashed #999;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 9px;
            color: #888;
        }

        .badge {
            display: inline-block;
            background: #0d6efd;
            color: #fff;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 10px;
        }

        .expired {
            background: #6c757d;
        }

        .suspended {
            background: #ffc107;
            color: #222;
        }

        .row-line {
            margin-bottom: 3px;
        }

        .title-top {
            margin-bottom: 8px;
        }

        hr {
            border: none;
            border-top: 1px solid #ccc;
            margin: 6px 0;
        }
    </style>
</head>

<body>
    <h2 class="title-top">{{ $title }}</h2>
    @if ($batch_ref)
        <p style="margin-top:-10px;font-size:11px;">Batch Ref: {{ $batch_ref }} | Généré:
            {{ $generated_at->format('Y-m-d H:i') }}</p>
    @endif>

    <div class="grid">
        @foreach ($users as $u)
            @php
                $status = $u->status;
                $badgeClass = $status === 'active' ? '' : ($status === 'expired' ? 'expired' : 'suspended');
            @endphp
            <div class="ticket">
                <div class="qr">
                    {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(54)->generate($u->username . '|' . $u->password) !!}
                </div>
                <div class="header">
                    HOTSPOT ACCESS
                    <span class="badge {{ $badgeClass }}">{{ strtoupper($status) }}</span>
                </div>
                <div class="row-line cred">
                    <span class="label">User</span> {{ $u->username }}
                </div>
                <div class="row-line cred">
                    <span class="label">Pass</span> {{ $u->password }}
                </div>
                <hr>
                <div class="row-line">
                    <span class="label">Profil</span> {{ $u->userProfile?->name }}
                </div>
                <div class="row-line">
                    <span class="label">Validité</span>
                    {{ $u->validity_minutes }} min (≈ {{ round($u->validity_minutes / 60, 1) }} h)
                </div>
                <div class="row-line">
                    <span class="label">Quota</span>
                    {{ $u->data_limit_mb ? $u->data_limit_mb . ' MB' : 'Illimité' }}
                </div>
                <div class="footer">
                    Support: {{ config('app.name') }} - Généré le {{ $generated_at->format('d/m/Y H:i') }}
                </div>
            </div>
        @endforeach
    </div>
</body>

</html>
