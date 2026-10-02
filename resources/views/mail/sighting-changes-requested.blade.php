@extends('mail.layout')

@section('title', 'Um ajuste no relato')

@section('body')
    {{ $nickname }}, a torre leu seu relato e pediu um ajuste antes de publicar:
    <p style="margin:16px 0;padding:14px 18px;border-radius:14px;background:#061121;color:#F4F5E8;">{{ $note }}</p>
    O relato fica fora do Livro até você reenviar. Seus dados já estão carregados no formulário.
@endsection

@section('button')
    <a href="{{ $url }}" style="display:inline-block;background:#54C933;color:#061121;font-weight:600;text-decoration:none;padding:14px 28px;border-radius:999px;">Ajustar e reenviar</a>
@endsection
