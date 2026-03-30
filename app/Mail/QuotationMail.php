<?php
namespace App\Mail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\CompanyHeader;

class QuotationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $quotation;
    public $companySetting;

    public function __construct($quotation)
    {
        $this->quotation = $quotation;
        $this->companySetting =  $quotation->companyHeader()->first();
    }



// public function build()
// {
//$pdf = Pdf::loadView('pdf.quotation', ['quotation' => $this->quotation]);

// return $this->subject('Your Quotation #' . $this->quotation->quotation_number)
// ->view('emails.quotation-email');
// // ->attachData($pdf->output(), 'quotation.pdf', [
// // 'mime' => 'application/pdf',
// // ]);
// // }
// }
    public function build()
    {
        $pdf = Pdf::loadView('pdf.quotation', [
            'quotation' => $this->quotation,
            'companySetting' => $this->companySetting
        ]);

        $message = "Dear Customer,\n\n";
        $message .= "Your quotation number is #" . $this->quotation->quotation_number . ".\n";
        $message .= "Please see the attached PDF for full details.\n\n";
        $message .= "VITA Team";

        return $this->subject('Your Quotation #' . $this->quotation->quotation_number)
            ->text('emails.raw-quotation') // هنا نرسل نص عادي فقط
            ->with(['textBody' => $message])
            ->attachData($pdf->output(), 'Quotation_'.$this->quotation->quotation_number.'.pdf', [
                'mime' => 'application/pdf',
            ]);;
    }
}
