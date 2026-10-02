@extends('mail.layout')

@section('eyebrow', 'Até a próxima vigília')
@section('title', 'Conta excluída')

@section('body')
    A conta de <strong>{{ $nickname }}</strong> foi excluída, junto com os relatos e as fotos.
    Se um dia quiser voltar, é só entrar de novo com o Google: começamos do zero.
@endsection
