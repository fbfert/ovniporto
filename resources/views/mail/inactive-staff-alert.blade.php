@extends('mail.layout')

@section('eyebrow', 'Alerta da torre')
@section('title', 'Conta do painel sem acesso')

@section('body')
    A conta de <strong>{{ '@'.$nickname }}</strong>, com o papel <strong>{{ $role }}</strong> no painel,
    não entra desde {{ $lastSeenOn }}. Contas com papel nunca são anonimizadas automaticamente.<br><br>
    Se a pessoa não faz mais parte da equipe, tire o papel dela na ficha do membro; a rotina
    diária cuida do resto como faz com qualquer conta.
@endsection

@section('button')
    <a href="{{ $url }}" style="display:inline-block;background:#54C933;color:#061121;font-weight:600;text-decoration:none;padding:14px 28px;border-radius:999px;">Abrir a ficha</a>
@endsection
