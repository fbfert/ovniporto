@extends('mail.layout')

@section('eyebrow', 'Pesquisa da origem')
@section('title', 'Nova colaboração')

@section('body')
    A oferta #{{ $collaboratorId }} chegou pelo Atlas.
    Os detalhes ficam no painel, atrás do login.
@endsection

@section('button')
    <a href="{{ $panelUrl }}" style="display:inline-block;background:#54C933;color:#061121;font-weight:600;text-decoration:none;padding:14px 28px;border-radius:999px;">Abrir no painel</a>
@endsection
