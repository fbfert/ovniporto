@extends('mail.layout')

@section('eyebrow', 'Saudade da torre')
@section('title', 'Sua conta vai ser anonimizada')

@section('body')
    {{ $nickname }}, faz tempo que você não entra no OVNIPORTO. Para não guardar dados de quem não usa
    mais o site, no dia <strong>{{ $anonymizeOn }}</strong> sua conta vai ser anonimizada: nome, e-mail,
    foto e o vínculo com o Google são apagados. Os relatos publicados continuam no Livro com o seu apelido.<br><br>
    Quer manter a conta? É só entrar com o Google antes dessa data.
@endsection

@section('button')
    <a href="{{ $url }}" style="display:inline-block;background:#54C933;color:#061121;font-weight:600;text-decoration:none;padding:14px 28px;border-radius:999px;">Entrar e manter a conta</a>
@endsection
