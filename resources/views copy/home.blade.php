@extends('layouts.parent')

@section('content')
    @if (Route::has('login'))
    @auth
        <p>ログインおｋ</p>
    @else
        @include('components.login-container')
    @endauth
    @endif
@endsection