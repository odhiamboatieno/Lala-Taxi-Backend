<p>Your trip receipt for booking <strong>#{{ $trip->id }}</strong> is ready.</p>

<p>
    <strong>Pickup:</strong> {{ $trip->pickup_location ?: 'N/A' }}<br>
    <strong>Drop-off:</strong> {{ $trip->drop_location ?: 'N/A' }}<br>
    <strong>Total Fare:</strong> {{ optional($trip->currency)->symbol ?: optional($trip->currency)->original_symbol ?: ($trip->currency_code ?? '$') }}{{ number_format((float) ($trip->total_fare ?? 0), 2) }}
</p>

<p>You can view or download your receipt using the links below:</p>

<p>
    <a href="{{ $viewUrl }}">View Receipt</a><br>
    <a href="{{ $downloadUrl }}">Download PDF Receipt</a>
</p>
