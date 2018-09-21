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
        
        console.log(atmp.slice(4, len - 4).join(''));
        return atmp.slice(4, len - 4).join('');
    }
    return '###';
}

function addmsg(name, text){
    $('#messagewell').prepend('<div class="well"><h5>'+name+'</h5>'+text+'</div>');
}

function tstart(){
    var ws = new WebSocket('ws://192.168.10.205:8000');
    ws.onmessage = function(data) { addmsg('SERVER', data.data); };
    return ws;
}

var WS = tstart();

function filldiv(id, text){
    var ikey = $('#key_'+id);
    $('#text_'+id).text(decrypt_main(text, ikey.val()));
    ikey.val('');
    //alert(decrypt_main(text, textkey));
}

function tsend(){
    WS.send($("#inputText").val());
}

function checkMessage(){
    var itext = $("#inputText");
    var text = itext.val();
    text = text.replace(/^\s+|\s+$/g, '');
    
    return text?true:false;
}
function checkKey(){
    var ikey = $('input[id="textkey"]');
    return ikey.val().length?true:false;
}

function crypt() {
    var itext = $("#inputText");
    var ikey = $('input[id="textkey"]');

    WS.send(crypt_main('beg:' + itext.val() + ':end', ikey.val()));
    //console.log(crypt_main('beg:' + itext.val() + ':end', ikey.val()));
    
//var ires = $('input[name="result"]');

    //ires.val(crypt_main('beg:' + itext.val() + ':end', ikey.val()));
}

function decrypt() {
    var itext = $('input[name="crypttext"]');
    var ikey = $('input[name="dtextkey"]');

    var ires = $('input[name="dectext"]');

    ires.val(decrypt_main(itext.val(), ikey.val()));
}

function sendOpenMsg(){
    
}
function sendCryptedMsg(){
    
}