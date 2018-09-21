<!DOCTYPE html>
<html>
<head>
    <title>Сайтсофт</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet" media="screen">
    <script src="http://code.jquery.com/jquery.js"></script>
    <script src="{{ asset('js/bootstrap.min.js') }}"></script>
</head>
<body>

<div class="navbar">
    <div class="navbar-inner">
        <a class="brand" href="#">Сайтсофт</a>
        <ul class="nav">
            <li><a href="#">Главная</a></li>
            <li><a href="#">Авторизация</a></li>
            <li class="active"><a href="#">Регистрация</a></li>
        </ul>

        <ul class="nav pull-right">
            <li><a>Username</a></li>
            <li><a href="#">Выход</a></li>
        </ul>
    </div>
</div>

<div class="row-fluid">
    <div class="span2"></div>
    <div class="span8">

        <h3>Ура!</h3>

        <p>
            Поздравляем! Вы успешно зарегистрировались.
        </p>

        <p>
            Воспользуйтесь <a href="#">формой авторизации</a> чтобы войти на сайт под своей учетной записью
        </p>
    </div>
</div>


</body>
</html>