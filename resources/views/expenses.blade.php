@extends('layouts.app-shell')

@section('title', 'Gestión de gastos')

@section('content')
    <div
        id="expense-request-root"
        data-pernr="{{ auth()->user()->sap_employee_id }}"
    ></div>
@endsection