@extends('mail.layout')

@section('eyebrow', 'Fila da torre')
@section('title', 'Novo relato')

@section('body')
    O relato #{{ $sightingId }} entrou na fila de moderação.
    Os detalhes ficam no painel, atrás do login.
@endsection
