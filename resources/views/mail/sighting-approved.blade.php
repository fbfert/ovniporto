@extends('mail.layout')

@section('eyebrow', 'Aprovado pela torre')
@section('title', 'Seu relato está no Livro')

@section('body')
    {{ $nickname }}, a torre conferiu seu relato e ele já está no Livro de avistamentos,
    com o ponto no mapa e só o seu apelido. Mande o postal pra quem também olha o céu de Lages.
@endsection

@section('button')
    <a href="{{ $url }}" style="display:inline-block;background:#54C933;color:#061121;font-weight:600;text-decoration:none;padding:14px 28px;border-radius:999px;">Ver no Livro</a>
@endsection
