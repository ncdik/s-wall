@extends('layouts.sitesoft')

@section('class_reg')
class="active"
@endsection

@section('content')
<div class="row-fluid">
    <div class="span4"></div>
    <div class="span8">

        <form action="{{ route('register') }}" method="post" class="form-horizontal">
            @csrf

            <div class="control-group">
                <b>Регистрация</b>
            </div>
            <div class="control-group{{ $errors->has('name') ? ' error' : '' }}">
                <input type="text" id="name" name="name" placeholder="Логин" data-cip-id="inputLogin"
                       autocomplete="off" value="{{ old('name') }}">
                @if ($errors->has('name'))
                    <span class="help-inline">{{ $errors->first('name') }}</span>
                @endif
            </div>

            <div class="control-group{{ $errors->has('email') ? ' error' : '' }}">
                <input type="email" id="inputEmail" name="email" placeholder="E-mail" data-cip-id="inputEmail"
                       autocomplete="off" value="{{ old('email') }}">
                @if ($errors->has('email'))
                    <span class="help-inline">{{ $errors->first('email') }}</span>
                @endif

            </div>

            <div class="control-group{{ $errors->has('password') ? ' error' : '' }}">
                <input type="password" id="inputPassword" name="password" placeholder="Пароль"
                       data-cip-id="inputPassword">
                @if ($errors->has('password'))
                    <span class="help-inline">{{ $errors->first('password') }}</span>
                @endif
            </div>

            <div class="control-group">
                <input type="password" id="password-confirm" name="password_confirmation" placeholder="Повторите пароль"
                       data-cip-id="inputPassword2">
            </div>

            <div class="control-group">
                <button type="submit" class="btn btn-primary">Отправить</button>
            </div>
        </form>
    </div>
</div>
@endsection