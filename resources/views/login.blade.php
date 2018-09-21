@extends('layouts.sitesoft')

@section('class_auth')
class="active"
@endsection

@section('content')
<div class="row-fluid">
    <div class="span4"></div>
    <div class="span3">

        @if ($errors->has('name') || $errors->has('password'))
            <div class="alert alert-error">
                Вход в систему с указанными данными невозможен
            </div>
        @endif

        <form action="{{ route('login') }}" method="post" class="form-horizontal">
            @csrf

            <div class="control-group">
                <b>Авторизация</b>
            </div>
            <div class="control-group{{ $errors->has('name') ? ' error' : '' }}">
                <input type="text" id="inputLogin" name="name" placeholder="Логин" data-cip-id="inputLogin"
                       autocomplete="off" value="{{ old('name') }}">
            </div>
            <div class="control-group{{ $errors->has('password') ? ' error' : '' }}">
                <input type="password" id="inputPassword" name="password" placeholder="Пароль"
                       data-cip-id="inputPassword">
            </div>
            <div class="control-group">
                <label class="checkbox">
                    <input type="checkbox" name="remember" value="1"> Запомнить меня
                </label>
                <button type="submit" class="btn btn-primary">Вход</button>
            </div>
        </form>
    </div>
</div>
@endsection