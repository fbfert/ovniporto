@extends('mail.layout')

@section('eyebrow', 'Pista de pouso avisando')
@section('title', 'Devolução recebida')

@section('body')
    <p style="margin:0 0 12px;">Recebemos seu pedido de devolução do pedido {{ $orderNumber }}. O protocolo é <strong>{{ $protocol }}</strong>: guarde para falar com a gente.</p>
    <p style="margin:0 0 12px;">Motivo: {{ $reason }}.@if ($withinWithdrawal) Está dentro dos 7 dias do direito de arrependimento.@endif</p>
    <p style="margin:0 0 12px;">A equipe responde {{ $deadline }}, neste e-mail, com o jeito de mandar o produto de volta. O frete de volta é por conta do OVNIPORTO.</p>
@endsection
