@extends('mail.layout')

@section('title', 'Relato recebido')

@section('body')
    Seu relato chegou à torre de controle e está em análise, {{ $nickname }}.
    Você recebe um e-mail quando ele for aprovado ou se a torre pedir algum ajuste.
@endsection
