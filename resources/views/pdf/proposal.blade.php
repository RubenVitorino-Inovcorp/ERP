<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Proposta #{{ $proposal->number }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #000;
            font-size: 10px;
            line-height: 1.3;
            margin: 0;
            padding: 20px 30px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }

        /* Header */
        .header-table td {
            vertical-align: top;
        }
        .logo-container {
            width: 50%;
        }
        .logo-container img {
            max-width: 200px;
            max-height: 80px;
        }
        .company-name-fallback {
            font-size: 24px;
            font-weight: bold;
            color: #d4a373; /* Example color, should be dynamic or generic */
            letter-spacing: 2px;
        }
        
        .doc-title-container {
            width: 50%;
            text-align: right;
        }
        .doc-title {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .doc-number {
            font-size: 14px;
            margin-top: 5px;
        }

        /* Client Section */
        .client-section {
            margin-top: 40px;
            margin-left: 50%;
            font-size: 11px;
            line-height: 1.4;
        }
        .client-name {
            font-weight: bold;
            text-transform: uppercase;
        }

        /* Meta Table (Cliente Nº, Contribuinte, etc) */
        .meta-container {
            margin-top: 40px;
            width: 100%;
        }
        .meta-container table {
            width: 100%;
        }
        .meta-container td {
            vertical-align: top;
        }
        .meta-box {
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
            padding: 2px 0;
            text-align: center;
        }
        .meta-box th {
            font-size: 9px;
            font-weight: bold;
            padding-bottom: 3px;
        }
        .meta-box td {
            font-size: 10px;
        }

        /* Items Section */
        .items-section {
            margin-top: 20px;
        }
        .items-header {
            background-color: #e5e7eb;
            text-align: center;
            font-weight: bold;
            padding: 5px;
            border: 1px solid #d1d5db;
        }
        .items-table {
            width: 100%;
            margin-top: 5px;
        }
        .items-table th {
            text-align: left;
            font-size: 9px;
            border-bottom: 1px solid #000;
            padding: 4px 2px;
        }
        .items-table td {
            padding: 4px 2px;
            vertical-align: top;
        }
        .items-table .text-right {
            text-align: right;
        }
        .items-table .text-center {
            text-align: center;
        }
        .item-description {
            padding-left: 20px;
            color: #374151;
            font-size: 9px;
            margin-top: 4px;
        }

        /* Totals & Conditions */
        .bottom-section {
            margin-top: 30px;
        }
        .bottom-section td {
            vertical-align: top;
        }
        .conditions-box {
            width: 50%;
            padding-right: 20px;
        }
        .conditions-title {
            font-weight: bold;
            font-size: 11px;
            border-bottom: 1px solid #000;
            padding-bottom: 2px;
            margin-bottom: 5px;
        }
        .conditions-text {
            font-size: 9px;
            line-height: 1.5;
        }
        
        .totals-box {
            width: 50%;
        }
        .totals-table {
            border-collapse: collapse;
            width: 100%;
        }
        .totals-table td {
            padding: 4px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .totals-table .amount {
            text-align: right;
            font-weight: bold;
        }
        .grand-total td {
            background-color: #d1d5db;
            font-weight: bold;
            font-size: 12px;
            padding: 4px;
            border-bottom: none;
        }
        .not-invoice {
            text-align: center;
            margin-top: 10px;
            font-size: 10px;
        }

        /* Footer Contacts */
        .footer {
            position: fixed;
            bottom: -10px;
            width: 100%;
            font-size: 9px;
            line-height: 1.4;
        }
        .footer-title {
            font-weight: bold;
            font-size: 11px;
            margin-bottom: 3px;
        }
        .footer-page {
            text-align: right;
            position: absolute;
            bottom: 0;
            right: 0;
        }
    </style>
</head>
<body>

    @php
        $logoPath = null;
        if (!empty($company->logo_path)) {
            $logoPath = storage_path('app/public/' . $company->logo_path);
        }
        $logoBase64 = null;
        if ($logoPath && file_exists($logoPath)) {
            $mime = mime_content_type($logoPath);
            $logoBase64 = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($logoPath));
        }
        
        \Carbon\Carbon::setLocale('pt');
    @endphp

    <!-- Header -->
    <table class="header-table">
        <tr>
            <td class="logo-container">
                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" alt="Logo">
                @else
                    <div class="company-name-fallback">{{ $company->name ?? 'EMPRESA' }}</div>
                @endif
            </td>
            <td class="doc-title-container">
                <div class="doc-title">PROPOSTA</div>
                <div class="doc-number">{{ $proposal->number }}/{{ \Carbon\Carbon::parse($proposal->proposal_date)->format('Y') }}</div>
            </td>
        </tr>
    </table>

    <!-- Client Section -->
    <div class="client-section">
        <div class="client-name">{{ $proposal->entity->name ?? 'Nome do Cliente' }}</div>
        <div>{{ $proposal->entity->address ?? '' }}</div>
        <div>{{ $proposal->entity->zip_code ?? '' }} {{ $proposal->entity->city ?? '' }}</div>
    </div>

    <!-- Meta Information -->
    <div class="meta-container">
        <table>
            <tr>
                <td style="width: 45%;">
                    <table class="meta-box">
                        <tr>
                            <th>Cliente Nº</th>
                            <th>Contribuinte</th>
                        </tr>
                        <tr>
                            <td>{{ $proposal->entity->number ?? '-' }}</td>
                            <td>{{ $proposal->entity->nif ?? '-' }}</td>
                        </tr>
                    </table>
                </td>
                <td style="width: 10%;"></td>
                <td style="width: 45%;">
                    <table class="meta-box">
                        <tr>
                            <th>Edição</th>
                            <th>de</th>
                        </tr>
                        <tr>
                            <td>1</td>
                            <td>{{ \Carbon\Carbon::parse($proposal->proposal_date)->translatedFormat('d M Y') }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    <!-- Items -->
    <div class="items-section">
        <div class="items-header">ARTIGOS / SERVIÇOS</div>
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 50%;">Ref | Descrição</th>
                    <th class="text-center" style="width: 10%;">Qtd</th>
                    <th class="text-right" style="width: 15%;">Preço Unit.</th>
                    <th class="text-right" style="width: 10%;">IVA</th>
                    <th class="text-right" style="width: 15%;">Total</th>
                </tr>
            </thead>
            <tbody>
                @php $subtotal = 0; $vatTotal = 0; @endphp
                @foreach($proposal->lines as $line)
                    @php
                        $lineSubtotal = $line->quantity * $line->unit_price;
                        $vatRate = $line->vatRate ? (float)$line->vatRate->value : 0;
                        $lineVat = $lineSubtotal * ($vatRate / 100);
                        $subtotal += $lineSubtotal;
                        $vatTotal += $lineVat;
                    @endphp
                    <tr>
                        <td>
                            <strong>{{ $line->article->reference ?? '-' }} | {{ $line->article->name ?? 'Artigo' }}</strong>
                        </td>
                        <td class="text-center">{{ $line->quantity }} Un</td>
                        <td class="text-right">{{ number_format($line->unit_price, 2, ',', ' ') }} €</td>
                        <td class="text-right">{{ $vatRate }}%</td>
                        <td class="text-right">{{ number_format($lineSubtotal, 2, ',', ' ') }} €</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div style="border-top: 1px solid #000; margin-top: 5px;"></div>
    </div>

    <!-- Bottom Section -->
    <table class="bottom-section">
        <tr>
            <td class="conditions-box">
                <div class="conditions-title">Termos e Condições</div>
                <div class="conditions-text">
                    <strong>Válido até:</strong> {{ \Carbon\Carbon::parse($proposal->validity_date)->format('d/m/Y') }}<br>
                    <strong>Estado:</strong> {{ ucfirst($proposal->status) }}<br>
                    <br>
                    *Valor sem IVA incluído<br>
                </div>
            </td>
            <td class="totals-box">
                <table class="totals-table">
                    <tr>
                        <td>Subtotal</td>
                        <td class="amount">{{ number_format($subtotal, 2, ',', ' ') }} €</td>
                    </tr>
                    <tr>
                        <td>Desconto Geral</td>
                        <td class="amount">0,00 €</td>
                    </tr>
                    <tr>
                        <td>Total sem IVA</td>
                        <td class="amount">{{ number_format($subtotal, 2, ',', ' ') }} €</td>
                    </tr>
                    <tr>
                        <td>IVA</td>
                        <td class="amount">{{ number_format($vatTotal, 2, ',', ' ') }} €</td>
                    </tr>
                    <tr class="grand-total">
                        <td>Total com IVA</td>
                        <td class="amount">{{ number_format($subtotal + $vatTotal, 2, ',', ' ') }} €</td>
                    </tr>
                </table>
                <div class="not-invoice">Este documento não serve de fatura</div>
            </td>
        </tr>
    </table>

    <!-- Footer -->
    <div class="footer">
        <div class="footer-title">Contactos</div>
        <div>{{ $company->name ?? 'InovCorp ERP' }}</div>
        @if(!empty($company->address))
            <div>{{ $company->address }}</div>
        @endif
        @if(!empty($company->zip_code) || !empty($company->city))
            <div>{{ $company->zip_code ?? '' }} {{ $company->city ?? '' }}</div>
        @endif
        @if(!empty($company->nif))
            <div>NIF: {{ $company->nif }}</div>
        @endif
        
        <div class="footer-page">
            <script type="text/php">
                if (isset($pdf)) {
                    $text = "Pág {PAGE_NUM} de {PAGE_COUNT}";
                    $size = 8;
                    $font = $fontMetrics->getFont("Helvetica");
                    $width = $fontMetrics->get_text_width($text, $font, $size) / 2;
                    $x = ($pdf->get_width() - $width) - 30;
                    $y = $pdf->get_height() - 30;
                    $pdf->page_text($x, $y, $text, $font, $size);
                }
            </script>
        </div>
    </div>

</body>
</html>