<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Ordem de produção {{ $order['number'] }}</title>
    <style>
        @page { margin: 28mm 20mm; }
        body { font-family: DejaVu Sans, sans-serif; color: #061121; font-size: 11pt; }
        .head { display: table; width: 100%; }
        .head > div { display: table-cell; vertical-align: middle; }
        h1 { font-size: 20pt; letter-spacing: 1px; margin: 0; text-transform: uppercase; }
        .muted { color: #494383; }
        table { width: 100%; border-collapse: collapse; margin-top: 18pt; }
        th { text-align: left; font-size: 9pt; text-transform: uppercase; letter-spacing: 1px; border-bottom: 2px solid #061121; padding: 6pt 4pt; }
        td { border-bottom: 1px dashed #b9b9c9; padding: 8pt 4pt; vertical-align: top; }
        .qty { font-size: 16pt; font-weight: bold; width: 40pt; }
        .box { margin-top: 18pt; padding: 10pt; border: 2px dashed #494383; border-radius: 8pt; }
        .foot { margin-top: 28pt; font-size: 9pt; color: #494383; }
    </style>
</head>
<body>
    <div class="head">
        <div style="width: 80pt;"><img src="{{ $seal }}" width="70" alt="Selo OVNIPORTO"></div>
        <div>
            <p class="muted" style="margin:0;">Ordem de produção</p>
            <h1>{{ $order['number'] }}</h1>
            <p class="muted" style="margin:4pt 0 0;">Pago em {{ $order['paidAt'] ? \Illuminate\Support\Carbon::parse($order['paidAt'])->timezone('America/Sao_Paulo')->format('d/m/Y') : '—' }}
                · {{ $order['pickup'] ? 'Retirada em Lages' : 'Envio' }}</p>
        </div>
    </div>

    <table>
        <thead><tr><th>Qtd.</th><th>Item</th><th>Opção</th><th>SKU</th><th>Prazo</th></tr></thead>
        <tbody>
        @foreach ($order['items'] as $item)
            <tr>
                <td class="qty">{{ $item['quantity'] }}</td>
                <td>{{ $item['name'] }}</td>
                <td>{{ $item['variant'] }}</td>
                <td>{{ $item['sku'] }}</td>
                <td>{{ $item['madeToOrder'] ? $item['productionDays'].' dias' : 'pronta entrega' }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <div class="box">Conferir quantidade e opção de cada item antes de embalar. Etiqueta e rastreio saem pelo painel.</div>
    <p class="foot">OVNIPORTO · Lages, SC · Guardei um lugar pra você.</p>
</body>
</html>
