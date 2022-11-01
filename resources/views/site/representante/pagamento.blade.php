@extends('site.representante.app')

@section('content-representante')

@include('site.inc.form_pagamento', ['user' => auth()->guard('representante')->user()])

@endsection