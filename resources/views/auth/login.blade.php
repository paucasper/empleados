@extends('layouts.app')

@section('body')
<div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="w-full max-w-md rounded-2xl bg-white p-8 shadow">
        <h2 class="mb-6 text-center text-2xl font-bold">
            Iniciar sesión
        </h2>

        @if (session('status'))
            <div class="mb-4 text-sm font-medium text-green-600">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 text-sm text-red-600">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>- {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Email</label>
                <input
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                >
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Contraseña</label>
                <input
                    type="password"
                    name="password"
                    required
                    class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                >
            </div>

            <div class="mb-4 flex items-center">
                <input type="checkbox" name="remember" class="mr-2">
                <span class="text-sm text-gray-600">Recordarme</span>
            </div>

            <button
                type="submit"
                class="w-full rounded-lg bg-green-700 py-2 font-semibold text-white transition hover:bg-green-800"
            >
                Entrar
            </button>
        </form>
    </div>
</div>
@endsection