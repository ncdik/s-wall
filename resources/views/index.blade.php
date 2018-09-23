@extends('layouts.sitesoft')

@section('class_home')
class="active"
@endsection

@section('content')
<div class="row-fluid">
    <div class="span2"></div>
    <div class="span8">
        <div id="error_ws" class="alert alert-error" hidden>
            Отсутствует подключение к websocket-серверу.<br>
            Обновите страницу для повторной попытки подключения.
        </div>
        @auth
            <form action="" method="post" class="form-horizontal" style="margin-bottom: 50px;">
                <div id="error_message" class="alert alert-error" hidden>
                    Сообщение не может быть пустым, а также состоять только из пробелов
                </div>
                <div id="error_key" class="alert alert-error" hidden>
                    Ключ не может быть пустым
                </div>


                <div class="control-group">
                    <textarea style="width: 100%; height: 50px;" maxlength="500" type="password" id="inputText" placeholder="Ваше сообщение..."
                           data-cip-id="inputText"></textarea>
                </div>
                <div class="control-group">
                    <button type="button" onclick="sendOpenMsg();" class="btn btn-primary">Отправить сообщение</button>
                    <button type="button" onclick="sendCryptedMsg();" class="btn btn-primary">Отправить в зашифрованном виде</button>
                    <div class="pull-right">
                        <label class="label" for="textkey">Ключ</label>
                        <input type="password" id="textkey" class="input input-large">
                    </div>
                </div>
            </form>
        @endauth
        
        <div id='messagewell'>
            @foreach ($messages as $message)
                <div class="well" id="div_msg_{{ $message->id }}">
                    <div class="pull-right" style="font-weight: normal; color:silver; font-size:13px">
                        Создано - {{ $message->created_at }}
                        @if ($message->created_at != $message->updated_at)
                            <br>Обновлено - {{ $message->updated_at }}
                        @endif
                    </div>

                    <h5>
                        {{ $message->user->name }}
                    </h5>

                    @if($message->crypted)
                        <span id="text_{{ $message->id }}">
                            ###
                        </span>
                        @auth
                            @if($message->user->id == Auth::User()->id)
                                <a style="cursor: pointer;" onclick="sendDeleteMsg({{ $message->id }});"><i class="fa fa-trash"></i></a>
                            @endif
                        @endauth

                        <div class="form-horizontal">
                            <label class="label" for="key_{{ $message->id }}">Ключ</label>&nbsp;
                            <input type="password" id="key_{{ $message->id }}" class="input input-large">&nbsp;
                            <button class="btn btn-primary" onclick="filldiv('{{ $message->id }}','{{ $message->text }}');">Расшифровать</button>
                        </div>
                    @else
                        <div id="text_view_{{ $message->id }}">
                            <span id="text_{{ $message->id }}">
                                {{ $message->text }}
                            </span>
                            @auth
                                @if($message->user->id == Auth::User()->id)
                                    <a style="cursor: pointer;" onclick="editMsg({{ $message->id }})"><i class="fa fa-pencil"></i></a>
                                    <a style="cursor: pointer;" onclick="sendDeleteMsg({{ $message->id }});"><i class="fa fa-trash"></i></a>
                                @endif
                            @endauth
                        </div>
                        <div id="text_edit_{{ $message->id }}" hidden>
                            <input id="e_text_{{ $message->id }}" value="{{ $message->text }}" />
                            <button onClick="sendEditOpenMsg({{ $message->id }})" class="btn btn-primary">Применить</button>
                            <button onClick="cancelEditOpenMsg({{ $message->id }})" class="btn btn-danger">Отмена</button>
                        </div>

                        

                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection