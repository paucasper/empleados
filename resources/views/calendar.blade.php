@extends('layouts.app-shell')

@section('title', 'Calendario')

@section('content')
    <div
        id="calendar-root"
        data-pernr="{{ auth()->user()->sap_employee_id }}"
    ></div>
@endsection