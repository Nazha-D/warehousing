@component('mail::message')
    # Quotation #{{ $quotation->quotation_number }}

    Dear {{ $quotation->customer->name ?? 'Customer' }},

    Please find your quotation attached as a PDF.

    Thank you for doing business with us.

    Thanks,<br>
    {{ config('app.name') }}
@endcomponent
