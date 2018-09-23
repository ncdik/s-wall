//функция шифрования
function crypt_main(text, textkey) {
    var atext = text.split('');
    var akey = md5(textkey).split('');
    var atmp = [];
    var i = 0;

    atext.forEach(function (item, num, atext) {
        atmp[num] = item.charCodeAt(0) + akey[i % 32].charCodeAt(0);
        i++;
    });
    
    return JSON.stringify(atmp);
}

//функция расшифровки
function decrypt_main(text, textkey) {
    var arr = JSON.parse(text);
    var akey = md5(textkey).split('');
    var atmp = [];
    var i = 0;

    arr.forEach(function (item, num, atext) {
        atmp[num] = String.fromCharCode(item - akey[i % 32].charCodeAt(0));
        i++;
    });

    var len = atmp.length;
    if (atmp[0] == 'b' && atmp[1] == 'e' && atmp[2] == 'g' && atmp[3] == ':' && atmp[len - 4] == ':' && atmp[len - 3] == 'e' && atmp[len - 2] == 'n' && atmp[len - 1] == 'd') {
        
        return atmp.slice(4, len - 4).join('');
    }
    return '###';
}

//обрабатывает сообщение от вебсокет сервера
function onMessage(data){
    var cmd = JSON.parse(data.data);
    if(cmd && cmd.type && cmd.cmd){
        if(cmd.type === 'op_msg'){
            if(cmd.cmd === 'create'){
                var msg = JSON.parse(cmd.text);
                addOpenMsg(cmd.user_name, cmd.message_id, msg, cmd.data.date.split('.')[0]);
            }
            else if(cmd.cmd === 'edit'){
                var msg = JSON.parse(cmd.text);
                editOpenMsg(cmd.message_id, msg);
            }
        }
        else if(cmd.type === 'cr_msg'){
            var msg = JSON.parse(cmd.text);
            addCryptedMsg(cmd.user_name, cmd.message_id, msg, cmd.data.date.split('.')[0]);
        }
        else if(cmd.type === 'msg'){
            if(cmd.cmd === 'delete'){
                delMessage(cmd.message_id);
            }
        }
    }
}

//используется для вывода расшифрованного сообщения
function filldiv(id, text){
    var ikey = $('#key_'+id);
    $('#text_'+id).text(decrypt_main(text, ikey.val()));
    ikey.val('');
}

//возвращает зашифрованное сообщение
function crypt(msg, key){
    return crypt_main('beg:' + msg + ':end', key);
}

//отправляет сформированное сообщение на вебсокет сервер
function sendCommand(cmd){ WS.send(cmd); }

//возвращает элемент стены сообщений
function getWellDiv(){ return $('#messagewell'); }
//возвращает csrf токен текущего пользователя
function getTokenValue(){ return $('input[name="_token"]').val(); }
//возвращает xsrf токен текущего пользователя
function getXSRFValue(){ return $('input[name="_xsrf"]').val(); }
//возвращает id текущего пользователя
function getUserId(){ return $('input[name="user_id"]').val(); }
//возвращает токен usertok текущего пользователя
function getUsertok(){ return $('input[name="usertok"]').val(); }
//возвращает имя текущего пользователя
function getUserName(){ return $('input[name="user_name"]').val(); }
//возвращает введенное сообщение
function getMessageValue() { return $('#inputText').val(); }
//возвращает отредактированное сообщение
function getEditMessageValue(id) { return $('input[id="e_text_'+id+'"]').val(); }
//возвращает значение введенного ключа
function getKeyValue(){ return $('input[id="textkey"]').val(); }

//очищает поле ввода сообщения
function clearMessage(){ $('#inputText').val(''); }
//очищает поле воода ключа
function clearKey(){ $('input[id="textkey"]').val(''); }
//очищает поля ввода сообщения и ключа
function clearFields(){ clearMessage(); clearKey(); }

//проверка измененного сообщения
function checkEditMessage(id){
    hideErrorMessage();

    var itext = $('input[id="e_text_'+id+'"]');
    var text = itext.val();
    text = text.replace(/^\s+|\s+$/g, '');
    
    if(text){
        return true;
    }
    else{
        errorMessage();
        return false;
    }
}
//проверка сообщения
function checkMessage(){
    hideErrorMessage();

    var itext = $("#inputText");
    var text = itext.val();
    text = text.replace(/^\s+|\s+$/g, '');
    
    if(text){
        return true;
    }
    else{
        errorMessage();
        return false;
    }
}
//проверка ключа
function checkKey(){
    hideErrorKey();

    var ikey = $('input[id="textkey"]');
    if(ikey.val().length){
        return true;
    }
    else{
        errorKey();
        return false;
    }
}

//общая ф-я проверки сообщения и ключа
function checks(){
    var c_message = checkMessage();
    var c_key = checkKey();

    if(c_message && c_key) return true;
    else return false;
}

//отправляет необходимые данные при подключении к вебсокет серверу
function sendOnOpen(){
    var user_id = getUserId();
    var user_name = getUserName();
    var usertok = getUsertok();
    var token = getTokenValue();
    var xsrf = getXSRFValue();

    var cmd = {
        'type': 'connectinfo',
        'usertok': usertok,
        '_token': token,
        '_xsrf': xsrf,
        'user_id': user_id,
        'user_name': user_name,
    };

    sendCommand(JSON.stringify(cmd));
}


//добавляет открытое сообщение на страницу
function addOpenMsg(name, message_id, text, data=''){
    var code = 
        '<div class="well" id="div_msg_'+message_id+'">'+
            '<div class="pull-right" style="font-weight: normal; color:silver; font-size:13px">'+
                'Создано - '+data+
            '</div>'+
            '<h5>'+name+'</h5>'+
            '<div id="text_view_'+message_id+'">'+
                '<span id="text_'+message_id+'">'+
                    text+
                '</span>\n';

    if(getUserName() === name){
        code += '<a style="cursor: pointer;" onclick="editMsg('+message_id+')"><i class="fa fa-pencil"></i></a>\n'+
                '<a style="cursor: pointer;" onclick="sendDeleteMsg('+message_id+')"><i class="fa fa-trash"></i></a>';
    }

    code +=     '</div>'+
            '<div id="text_edit_'+message_id+'" hidden>'+
                '<input id="e_text_'+message_id+'" value="'+text+'" />'+
                '<button onclick="sendEditOpenMsg('+message_id+')" class="btn btn-primary">Применить</button>'+
                '<button onclick="cancelEditOpenMsg('+message_id+')" class="btn btn-danger">Отмена</button>'+
            '</div>'+
        '</div>';

    getWellDiv().prepend(code);
}
//добавляет шифрованное сообщение на страницу
function addCryptedMsg(name, message_id, text, data=''){
    var code = 
        '<div class="well" id="div_msg_'+message_id+'">'+
            '<div class="pull-right" style="font-weight: normal; color:silver; font-size:13px">'+
                'Создано - '+data+
            '</div>'+
            '<h5>'+name+'</h5>'+
            '<span id="text_'+message_id+'">'+
                '###'+
            '</span>\n';

    if(getUserName() === name){
        code += '<a style="cursor: pointer;" onclick="sendDeleteMsg('+message_id+');"><i class="fa fa-trash"></i></a>';
    }

    code += '<div class="form-horizontal">'+
                '<label class="label" for="key_'+message_id+'">Ключ</label>&nbsp;'+
                '<input type="password" id="key_'+message_id+'" class="input input-large">&nbsp;'+
                '<button class="btn btn-primary" onclick="filldiv(\''+message_id+'\',\''+text+'\');">Расшифровать</button>'+
            '</div>'+
        '</div>';

    getWellDiv().prepend(code);
}
//изменяет содержимое отрытого сообщения
function editOpenMsg(id, text){ $('#text_'+id).text(text); }
//удаляет сообщение со страницы
function delMessage(id){ $('#div_msg_'+id).remove(); }


//отправляет команду удаления сообщения на вебсокет сервер
function sendDeleteMsg(id){
    if(confirm('Удалить сообщение?')){
        var user_id = getUserId();

        var cmd = {
            'type': 'msg',
            'cmd': 'delete',
            'message_id': id
        };

        sendCommand(JSON.stringify(cmd));
    }
}
//отправляет команду создания открытого сообщения на вебсокет сервер
function sendOpenMsg(){
    if(checkMessage()){
        var user_id = getUserId();
        var message = getMessageValue();

        var cmd = {
            'type': 'op_msg',
            'cmd': 'create',
            'message': JSON.stringify(message)
        };

        clearMessage();

        sendCommand(JSON.stringify(cmd));
    }
}
//отправляет команду создания зашифрованного сообщения на вебсокет сервер
function sendCryptedMsg(){
    if(checks()){
        var user_id = getUserId();
        var message = getMessageValue();
        var key = getKeyValue();
        var crypted = crypt(message, key);

        var cmd = {
            'type': 'cr_msg',
            'cmd': 'create',
            'message': JSON.stringify(crypted)
        };

        clearFields();

        sendCommand(JSON.stringify(cmd));
    }
}
//отправляет команду редактирования открытого сообщения на вебсокет сервер
function sendEditOpenMsg(id){
    endEditMsg(id);

    if(checkEditMessage(id)){
        var user_id = getUserId();
        var message = getEditMessageValue(id);

        var cmd = {
            'type': 'op_msg',
            'cmd': 'edit',
            'message_id': id,
            'message': JSON.stringify(message)
        };

        clearMessage();

        sendCommand(JSON.stringify(cmd));
    }
}
//отменяет редактирование сообщения
function cancelEditOpenMsg(id){
    endEditMsg(id);
    $('input[id="e_text_'+id+'"]').val('');
}

//переключает отображение элементов при начале редактирования сообщения
function editMsg(id){
    $('#text_view_'+id).hide();
    $('#text_edit_'+id).show();
}
//переключает отображение элементов при завершении редактирования сообщения
function endEditMsg(id){
    $('#text_edit_'+id).hide();
    $('#text_view_'+id).show();
}

//обрабатывает ошибку ввода сообщения
function errorMessage(){ showErrorMessage(); }
//обрабатывает ошибку ввода ключа
function errorKey(){ showErrorKey(); }
//отображает ошибку ввода сообщения
function showErrorMessage(){ $('#error_message').show(); }
//скрывает ошибку ввода сообщения
function hideErrorMessage(){ $('#error_message').hide(); }
//отображает ошибку ввода ключа
function showErrorKey(){ $('#error_key').show(); }
//скрывает ошибку ввода ключа
function hideErrorKey(){ $('#error_key').hide(); }

//инициализирует WebSocket
function tstart(address, port){
    var ws = new WebSocket('ws://'+address+':'+port);
    ws.onmessage = function(data) { onMessage(data); };
    ws.onopen = function(data) { sendOnOpen(); };
    return ws;
}