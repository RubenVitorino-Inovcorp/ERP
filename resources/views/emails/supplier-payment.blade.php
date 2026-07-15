<!DOCTYPE html>
<html>
<head>
    <title>Comprovativo de Pagamento</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <p>Estimado(a) Fornecedor,</p>
    
    <p>Informamos que o pagamento referente à fatura <strong>{{ $invoiceNumber }}</strong> foi processado.</p>
    
    @if($proofPath && $documentPath)
        <p>Enviamos em anexo a fatura original e o respetivo comprovativo de pagamento.</p>
    @elseif($proofPath)
        <p>Enviamos em anexo o comprovativo de pagamento.</p>
    @elseif($documentPath)
        <p>Enviamos em anexo o documento da fatura.</p>
    @endif
    
    <p>Qualquer questão, entre em contacto connosco.</p>
    
    @php
        $company = \App\Models\Company::first();
    @endphp
    <p>Cumprimentos,<br>
    @if($company && $company->logo_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($company->logo_path))
        <img src="{{ $message->embed(\Illuminate\Support\Facades\Storage::disk('public')->path($company->logo_path)) }}" alt="{{ $company->name }}" style="max-height: 60px; display: block; margin-top: 10px;" />
    @elseif($company)
        <strong>{{ $company->name }}</strong>
    @else
        <strong>ERP InovCorp</strong>
    @endif
    </p>
</body>
</html>
