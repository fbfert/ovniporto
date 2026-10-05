@extends('mail.layout')

@section('eyebrow', 'Torre de controle agradece')
@section('title', 'Oferta recebida')

@section('body')
    Oi, {{ $name }}. Recebemos sua oferta para ajudar na pesquisa da origem: o Atlas dos Ovnipuertos e o dossiê de Cachi.
    A equipe lê cada mensagem e responde neste e-mail quando houver uma tarefa que combine com o que você ofereceu.
    Se mudar de ideia, responda pedindo a exclusão dos seus dados.
@endsection
