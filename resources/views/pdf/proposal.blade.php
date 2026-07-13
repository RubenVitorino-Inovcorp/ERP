<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Proposta #{{ $proposal->number }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1e293b;
            font-size: 13px;
            line-height: 1.5;
            margin: 0;
            padding: 20px;
        }
        .header {
            width: 100%;
            margin-bottom: 30px;
            border-bottom: 2px solid #1D454C;
            padding-bottom: 15px;
        }
        .header table {
            width: 100%;
        }
        .company-name {
            font-size: 22px;
            font-weight: bold;
            color: #1D454C;
        }
        .doc-title {
            text-align: right;
            font-size: 20px;
            font-weight: bold;
            color: #1D454C;
            text-transform: uppercase;
        }
        .doc-details {
            text-align: right;
            font-size: 12px;
            color: #64748b;
        }
        .details-section {
            width: 100%;
            margin-bottom: 30px;
        }
        .details-section table {
            width: 100%;
        }
        .box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 12px;
        }
        .box-title {
            font-weight: bold;
            color: #1D454C;
            font-size: 11px;
            text-transform: uppercase;
            margin-bottom: 6px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .items-table th {
            background-color: #1D454C;
            color: #ffffff;
            text-align: left;
            padding: 8px 10px;
            font-size: 11px;
            text-transform: uppercase;
        }
        .items-table td {
            padding: 10px;
            border-bottom: 1px solid #e2e8f0;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .totals-section {
            width: 100%;
            margin-top: 20px;
        }
        .totals-table {
            width: 40%;
            margin-left: auto;
            border-collapse: collapse;
        }
        .totals-table td {
            padding: 6px 10px;
        }
        .totals-table .grand-total {
            font-size: 15px;
            font-weight: bold;
            color: #1D454C;
            border-top: 2px solid #1D454C;
            border-bottom: 2px solid #1D454C;
        }
        .footer {
            margin-top: 50px;
            padding-top: 15px;
            border-top: 1px solid #e2e8f0;
            font-size: 11px;
            color: #94a3b8;
            text-align: center;
        }
    </style>
</head>
<body>

    <!-- Header -->
    <div class="header">
        <table>
            <tr>
                <td style="width: 50%;">
                    <div class="company-name">{{ $company->name ?? 'InovCorp ERP' }}</div>
                    <div>{{ $company->address ?? 'Morada da Empresa' }}</div>
                    <div>{{ $company->zip_code ?? '' }} {{ $company->city ?? '' }}</div>
                    @if(!empty($company->nif))
                        <div>NIF: {{ $company->nif }}</div>
                    @endif
                </td>
                <td style="width: 50%;" class="text-right">
                    <div class="doc-title">Proposta</div>
                    <div class="doc-details">
                        <strong>N.º:</strong> {{ $proposal->number }}<br>
                        <strong>Data:</strong> {{ \Carbon\Carbon::parse($proposal->proposal_date)->format('d/m/Y') }}<br>
                        <strong>Validade:</strong> {{ \Carbon\Carbon::parse($proposal->validity_date)->format('d/m/Y') }}
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Details -->
    <div class="details-section">
        <table>
            <tr>
                <td style="width: 48%; vertical-align: top;">
                    <div class="box">
                        <div class="box-title">Cliente</div>
                        <strong>{{ $proposal->entity->name ?? '-' }}</strong><br>
                        @if(!empty($proposal->entity->nif))
                            NIF: {{ $proposal->entity->nif }}<br>
                        @endif
                        @if(!empty($proposal->entity->address))
                            {{ $proposal->entity->address }}<br>
                            {{ $proposal->entity->zip_code }} {{ $proposal->entity->city }}
                        @endif
                    </div>
                </td>
                <td style="width: 4%;"></td>
                <td style="width: 48%; vertical-align: top;">
                    <div class="box">
                        <div class="box-title">Condições Gerais</div>
                        <div><strong>Estado:</strong> {{ ucfirst($proposal->status) }}</div>
                        <div><strong>Validade da Proposta:</strong> Válida até {{ \Carbon\Carbon::parse($proposal->validity_date)->format('d/m/Y') }}</div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Tabela de Linhas -->
    <table class="items-table">
        <thead>
            <tr>
                <th>Ref.</th>
                <th>Descrição</th>
                <th class="text-center">Qtd</th>
                <th class="text-right">Preço Unit.</th>
                <th class="text-right">IVA</th>
                <th class="text-right">Total</th>
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
                    <td>{{ $line->article->reference ?? '-' }}</td>
                    <td>{{ $line->article->name ?? 'Artigo' }}</td>
                    <td class="text-center">{{ $line->quantity }}</td>
                    <td class="text-right">{{ number_format($line->unit_price, 2, ',', ' ') }} €</td>
                    <td class="text-right">{{ $vatRate }}%</td>
                    <td class="text-right">{{ number_format($lineSubtotal + $lineVat, 2, ',', ' ') }} €</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Totais -->
    <div class="totals-section">
        <table class="totals-table">
            <tr>
                <td>Subtotal:</td>
                <td class="text-right">{{ number_format($subtotal, 2, ',', ' ') }} €</td>
            </tr>
            <tr>
                <td>Total IVA:</td>
                <td class="text-right">{{ number_format($vatTotal, 2, ',', ' ') }} €</td>
            </tr>
            <tr class="grand-total">
                <td><strong>Total:</strong></td>
                <td class="text-right"><strong>{{ number_format($subtotal + $vatTotal, 2, ',', ' ') }} €</strong></td>
            </tr>
        </table>
    </div>

    <!-- Footer -->
    <div class="footer">
        Esta proposta foi processada por computador e é válida por 30 dias.
    </div>

</body>
</html>
