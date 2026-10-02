@extends('mail.layout')

@section('eyebrow', 'Pista de pouso avisando')
@section('title', $headline)

@section('body')
    @foreach ($lines as $line)
        <p style="margin:0 0 12px;">{{ $line }}</p>
    @endforeach
@endsection

@section('button')
    <a href="{{ $url }}" style="display:inline-block;background:#FCB802;color:#061121;font-weight:600;text-decoration:none;padding:14px 28px;border-radius:999px;">{{ $buttonLabel }}</a>
@endsection
