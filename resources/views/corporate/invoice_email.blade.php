<h1>{{ __('corporate.invoice_subject', ['number' => $invoice->invoice_number]) }}</h1>
<p>{{ data_get($invoice->bill_to_snapshot, 'legal_name') }}</p>
<p>{{ __('corporate.invoice_email_body', ['amount' => $invoice->currency_code.' '.$invoice->total_amount, 'date' => optional($invoice->due_date)->format('Y-m-d')]) }}</p>
<p>{{ __('corporate.no_personal_collection') }}</p>
