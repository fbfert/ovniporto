@extends('mail.layout')

@section('eyebrow', 'Alerta da torre')
@section('title', 'Uma tarefa falhou de vez')

@section('body')
    A tarefa <strong>{{ $job }}</strong> falhou {{ $attempts }} vezes e saiu da fila.
    O erro foi: <em>{{ $error }}</em><br><br>
    Veja os detalhes em Painel → Filas e, depois de corrigir a causa, reenvie a tarefa por lá.
@endsection
