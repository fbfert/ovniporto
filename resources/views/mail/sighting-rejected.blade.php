@extends('mail.layout')

@section('title', 'Relato não publicado')

@section('body')
    {{ $nickname }}, a torre leu seu relato e decidiu não publicá-lo no Livro. O motivo:
    <p style="margin:16px 0;padding:14px 18px;border-radius:14px;background:#061121;color:#F4F5E8;">{{ $reason }}</p>
    Se viu outra coisa no céu, o caminho para relatar continua aberto.
@endsection
