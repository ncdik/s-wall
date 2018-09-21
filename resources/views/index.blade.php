<!DOCTYPE html>
<html>
<head>
    <title>Сайтсофт</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet" media="screen">
    <script src="{{ asset('js/jquery-min.js') }}"></script>
    <script src="{{ asset('js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('js/md5.js') }}"></script>
    <script src="{{ asset('js/crypt.js') }}"></script>
</head>
<body>

@csrf
<input type="hidden" name="_xsrf" value="<?= $_SERVER['HTTP_COOKIE'] ?>" />
<form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
    @csrf
</form>

<div class="navbar">
    <div class="navbar-inner">
        <a class="brand" href="#">Сайтсофт</a>
        <ul class="nav">
            <li class="active"><a href="#">Главная</a></li>
            @guest
                <li><a href="{{ route('login') }}">Авторизация</a></li>
                <li><a href="{{ route('register') }}">Регистрация</a></li>
            @endguest
        </ul>

        @auth
            <ul class="nav pull-right">
                <li><a>{{ Auth::user()->name }}</a></li>
                <li><a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Выход</a></li>
            </ul>
        @endauth
    </div>
</div>

<div class="row-fluid">
    <div class="span2"></div>
    <div class="span8">
        @auth
            <form action="" method="post" class="form-horizontal" style="margin-bottom: 50px;">
                <div id="error_message" class="alert alert-error">
                    Сообщение не может быть пустым, а также состоять только из пробелов
                </div>
                <div id="error_key" class="alert alert-error">
                    Ключ не может быть пустым
                </div>

                <div class="control-group">
                    <textarea style="width: 100%; height: 50px;" type="password" id="inputText" placeholder="Ваше сообщение..."
                           data-cip-id="inputText"></textarea>
                </div>
                <div class="control-group">
                    <button type="button" onclick="tsend();" class="btn btn-primary">Отправить сообщение</button>
                    <button type="button" onclick="crypt();" class="btn btn-primary">Отправить в зашифрованном виде</button>
                    <div class="pull-right">
                        <label class="label" for="textkey">Ключ</label>
                        <input type="text" id="textkey" class="input input-large">
                    </div>
                </div>
            </form>
        @endauth
        
        <div id='messagewell'>
            @foreach ($messages as $message)
            <div class="well">
                <h5>{{ @($message->user)->name }} <div class="pull-right" style="font-weight: normal; color:silver;">{{ $message->created_at }}</div></h5>

                    @if($message->crypted)
                        <label id="text_{{ $message->id }}">
                            ###
                        </label>
                        <div class="form-horizontal">
                            <label class="label" for="key_{{ $message->id }}">Ключ</label>&nbsp;
                            <input type="password" id="key_{{ $message->id }}" class="input input-large">&nbsp;
                            <button class="btn btn-primary" onclick="filldiv('{{ $message->id }}','{{ $message->text }}');">Расшифровать</button>
                        </div>
                    @else
                        <label id="text_{{ $message->id }}">
                            {{ $message->text }}
                        </label>
                    @endif
            </div>
            @endforeach
        </div>
    </div>
</div>


</body>
</html>