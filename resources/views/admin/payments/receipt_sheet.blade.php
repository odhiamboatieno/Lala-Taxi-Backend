@php
    $rider = $result->users;
    $driver = $result->driver;
    $currencySymbol = optional($result->currency)->symbol ?: optional($result->currency)->original_symbol ?: ($result->currency_code ?? '$');
    $printMode = $printMode ?? false;
@endphp
<div style="max-width:860px;margin:24px auto;background:#fff;border:1px solid #e5e7eb;border-radius:24px;padding:28px;box-shadow:{{ $printMode ? 'none' : '0 20px 40px rgba(0,0,0,.08)' }};">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:20px;border-bottom:1px solid #edf2f7;padding-bottom:18px;">
        <div>
            <div style="font-size:12px;letter-spacing:.12em;text-transform:uppercase;color:#6366f1;font-weight:700;">Trip Receipt</div>
            <h1 style="margin:8px 0 4px;font-size:28px;color:#0f172a;">#{{ $result->id }}</h1>
            <div style="color:#64748b;">{{ optional($rider)->first_name }} {{ optional($rider)->last_name }} · {{ optional($driver)->first_name }} {{ optional($driver)->last_name }}</div>
        </div>
        <div style="text-align:right;">
            <div style="font-size:13px;color:#64748b;">Total Paid</div>
            <div style="font-size:28px;font-weight:800;color:#4f46e5;">{{ $currencySymbol }}{{ number_format((float) ($result->total_fare ?? 0), 2) }}</div>
        </div>
    </div>

    <div style="display:flex;gap:24px;margin-top:24px;">
        <div style="flex:1;">
            <div style="margin-bottom:16px;">
                <div style="font-size:12px;color:#94a3b8;text-transform:uppercase;">Pickup</div>
                <div style="font-size:16px;font-weight:700;color:#0f172a;">{{ $result->pickup_location ?: 'N/A' }}</div>
            </div>
            <div style="margin-bottom:16px;">
                <div style="font-size:12px;color:#94a3b8;text-transform:uppercase;">Drop-off</div>
                <div style="font-size:16px;font-weight:700;color:#0f172a;">{{ $result->drop_location ?: 'N/A' }}</div>
            </div>
            <div style="display:grid;grid-template-columns:repeat(2, minmax(0, 1fr));gap:14px;">
                <div><div style="font-size:12px;color:#94a3b8;">Trip Started</div><div style="font-weight:700;color:#0f172a;">{{ ($result->begin_trip && $result->begin_trip !== '0000-00-00 00:00:00') ? date('d M Y, H:i', strtotime($result->begin_trip)) : 'N/A' }}</div></div>
                <div><div style="font-size:12px;color:#94a3b8;">Trip Ended</div><div style="font-weight:700;color:#0f172a;">{{ ($result->end_trip && $result->end_trip !== '0000-00-00 00:00:00') ? date('d M Y, H:i', strtotime($result->end_trip)) : 'N/A' }}</div></div>
                <div><div style="font-size:12px;color:#94a3b8;">Payment Mode</div><div style="font-weight:700;color:#0f172a;">{{ $result->payment_mode ?: 'N/A' }}</div></div>
                <div><div style="font-size:12px;color:#94a3b8;">Transaction ID</div><div style="font-weight:700;color:#0f172a;">{{ $result->transaction_id ?: ($result->paykey ?: 'N/A') }}</div></div>
            </div>
        </div>

        <div style="width:280px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:20px;padding:18px;">
            <div style="font-weight:800;color:#0f172a;margin-bottom:12px;">Fare Breakdown</div>
            @foreach($invoice_data as $invoice)
                <div style="display:flex;justify-content:space-between;gap:12px;padding:8px 0;border-bottom:1px solid #e5e7eb;">
                    <span style="font-size:13px;color:#64748b;">{{ $invoice['key'] }}</span>
                    <strong style="font-size:13px;color:#0f172a;">{{ $invoice['value'] }}</strong>
                </div>
            @endforeach
        </div>
    </div>
</div>
