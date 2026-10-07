<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Modules\SaaSDispatch\Entities\CompanyCorporateInvoice;

class CorporateInvoiceMail extends Mailable
{
    use Queueable;

    public function __construct(public CompanyCorporateInvoice $invoice, private string $pdf) {}

    public function build()
    {
        return $this->subject(__('corporate.invoice_subject', ['number' => $this->invoice->invoice_number]))
            ->view('corporate.invoice_email')
            ->attachData($this->pdf, $this->invoice->invoice_number.'.pdf', ['mime' => 'application/pdf'])
            ->withSymfonyMessage(function ($message) {
                // Stable identity for transport/client deduplication on ambiguous SMTP retries.
                $message->getHeaders()->addIdHeader('Message-ID', 'corporate-invoice-'.$this->invoice->id.'@'.parse_url(config('app.url'), PHP_URL_HOST));
            });
    }
}
