@extends('layouts.app-shell')

@section('title', 'Nueva ausencia')

@section('content')
    <div
        id="vacation-request-root"
        data-pernr="{{ auth()->user()->sap_employee_id }}"
    ></div>
@endsection