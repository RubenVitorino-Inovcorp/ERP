<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SupplierPaymentProofMail extends Mailable
{
    use Queueable, SerializesModels;

    public $invoiceNumber;

    public $proofPath;

    public $documentPath;

    /**
     * Create a new message instance.
     */
    public function __construct($invoiceNumber, $proofPath = null, $documentPath = null)
    {
        $this->invoiceNumber = $invoiceNumber;
        $this->proofPath = $proofPath;
        $this->documentPath = $documentPath;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Comprovativo de Pagamento - Fatura {$this->invoiceNumber}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.supplier-payment',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        $attachments = [];
        $safeNumber = str_replace(['/', '\\'], '-', $this->invoiceNumber);

        if ($this->proofPath) {
            $extension = pathinfo($this->proofPath, PATHINFO_EXTENSION);
            $filename = 'Comprovativo-' . $safeNumber . '.' . $extension;

            $attachments[] = Attachment::fromStorageDisk('local', $this->proofPath)
                ->as($filename);
        }

        if ($this->documentPath) {
            $extension = pathinfo($this->documentPath, PATHINFO_EXTENSION);
            $filename = 'Fatura-' . $safeNumber . '.' . $extension;

            $attachments[] = Attachment::fromStorageDisk('local', $this->documentPath)
                ->as($filename);
        }

        return $attachments;
    }
}
