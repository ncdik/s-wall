@extends('layouts.sitesoft')

@section('class_home')
class="active"
@endsection

@section('content')
<div class="row-fluid">
    <div class="span2"></div>
    <div class="span8">
        @auth
            <form action="" method="post" class="form-horizontal" style="margin-bottom: 50px;">
                <div id="error_message" class="alert alert-error" hidden>
                    Сообщение не может быть пустым, а также состоять только из пробелов
                </div>
                <div id="error_key" class="alert alert-error" hidden>
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
                    <div class="pull-right" style="font-weight: normal; color:silver; font-size:13px">
                        Создано - {{ $message->created_at }}
                        @if ($message->created_at != $message->updated_at)
                            <br>Обновлено - {{ $message->updated_at }}
                        @endif
                    </div>

                    <h5>
                        {{ @($message->user)->name }}
                    </h5>

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
@endsection