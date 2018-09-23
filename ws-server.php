#!/usr/bin/env php
<?php

class WebsocketServer
{
    public function __construct($config) {
        $this->config = $config;
    }

    public function start() {
        //открываем серверный сокет
        $server = stream_socket_server("tcp://{$this->config['host']}:{$this->config['port']}", $errorNumber, $errorString);

        if (!$server) {
            die("error: stream_socket_server: $errorString ($errorNumber)\r\n");
        }

        list($pid, $master, $workers) = $this->spawnWorkers();//создаём дочерние процессы

        if ($pid) {//мастер
            fclose($server);//мастер не будет обрабатывать входящие соединения на основном сокете
            $WebsocketMaster = new WebsocketMaster($workers);//мастер будет пересылать сообщения между воркерами
            $WebsocketMaster->start();
        } else {//воркер
            $WebsocketHandler = new WebsocketHandler($server, $master);
            $WebsocketHandler->start();
        }
    }

    protected function spawnWorkers() {
        $master = null;
        $workers = array();
        $i = 0;
        while ($i < $this->config['workers']) {
            $i++;
            //создаём парные сокеты, через них будут связываться мастер и воркер
            $pair = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);

            $pid = pcntl_fork();//создаём форк
            if ($pid == -1) {
                die("error: pcntl_fork\r\n");
            } elseif ($pid) { //мастер
                fclose($pair[0]);
                $workers[$pid] = $pair[1];//один из пары будет в мастере
            } else { //воркер
                fclose($pair[1]);
                $master = $pair[0];//второй в воркере
                break;
            }
        }

        return array($pid, $master, $workers);
    }
}

class WebsocketMaster
{
    protected $workers = array();
    protected $clients = array();

    public function __construct($workers) {
        $this->clients = $this->workers = $workers;
    }

    public function start() {
        while (true) {
            //подготавливаем массив всех сокетов, которые нужно обработать
            $read = $this->clients;

            stream_select($read, $write, $except, null);//обновляем массив сокетов, которые можно обработать

            if ($read) {//пришли данные от подключенных клиентов
                foreach ($read as $client) {
                    $data = fread($client, 1000);

                    if (!$data) { //соединение было закрыто
                        unset($this->clients[intval($client)]);
                        @fclose($client);
                        continue;
                    }

                    foreach ($this->workers as $worker) {//пересылаем данные во все воркеры
                        if ($worker !== $client) {
                            fwrite($worker, $data);
                        }
                    }
                }
            }
        }
    }
}

abstract class WebsocketWorker
{
    protected $clients = array();
    protected $server;
    protected $master;
    protected $pid;
    protected $handshakes = array();
    protected $ips = array();

    protected $_tokens = array();
    protected $_xsrfs = array();
    protected $user_ids = array();
    protected $usertoks = array();
    protected $user_names = array();

    public function __construct($server, $master) {
        $this->server = $server;
        $this->master = $master;
        $this->pid = posix_getpid();
    }

    public function start() {
        while (true) {
            //подготавливаем массив всех сокетов, которые нужно обработать
            $read = $this->clients;
            $read[] = $this->server;
            $read[] = $this->master;

            $write = array();
            if ($this->handshakes) {
                foreach ($this->handshakes as $clientId => $clientInfo) {
                    if ($clientInfo) {
                        $write[] = $this->clients[$clientId];
                    }
                }
            }

            stream_select($read, $write, $except, null);//обновляем массив сокетов, которые можно обработать

            if (in_array($this->server, $read)) { //на серверный сокет пришёл запрос от нового клиента
                //подключаемся к нему и делаем рукопожатие, согласно протоколу вебсокета
                if ($client = stream_socket_accept($this->server, -1)) {
                    $address = explode(':', stream_socket_get_name($client, true));
                    if (isset($this->ips[$address[0]]) && $this->ips[$address[0]] > 5) {//блокируем более пяти соединий с одного ip
                        @fclose($client);
                    } else {
                        @$this->ips[$address[0]]++;

                        $this->clients[intval($client)] = $client;
                        $this->handshakes[intval($client)] = array();//отмечаем, что нужно сделать рукопожатие
                    }
                }

                //удаляем сервеный сокет из массива, чтобы не обработать его в этом цикле ещё раз
                unset($read[array_search($this->server, $read)]);
            }

            if (in_array($this->master, $read)) { //пришли данные от мастера
                $data = fread($this->master, 1000);

                $this->onSend($data);//вызываем пользовательский сценарий

                //удаляем мастера из массива, чтобы не обработать его в этом цикле ещё раз
                unset($read[array_search($this->master, $read)]);
            }

            if ($read) {//пришли данные от подключенных клиентов
                foreach ($read as $client) {
                    if (isset($this->handshakes[intval($client)])) {
                        if ($this->handshakes[intval($client)]) {//если уже было получено рукопожатие от клиента
                            continue;//то до отправки ответа от сервера читать здесь пока ничего не надо
                        }

                        if (!$this->handshake($client)) {
                            unset($this->clients[intval($client)]);
                            unset($this->handshakes[intval($client)]);
                            $address = explode(':', stream_socket_get_name($client, true));
                            if (isset($this->ips[$address[0]]) && $this->ips[$address[0]] > 0) {
                                @$this->ips[$address[0]]--;
                            }
                            @fclose($client);
                        }
                    } else {
                        $data = fread($client, 1000);

                        if (!$data) { //соединение было закрыто
                            unset($this->clients[intval($client)]);
                            unset($this->handshakes[intval($client)]);
                            $address = explode(':', stream_socket_get_name($client, true));
                            if (isset($this->ips[$address[0]]) && $this->ips[$address[0]] > 0) {
                                @$this->ips[$address[0]]--;
                            }
                            @fclose($client);
                            $this->onClose($client);//вызываем пользовательский сценарий
                            continue;
                        }

                        $this->onMessage($client, $data);//вызываем пользовательский сценарий
                    }
                }
            }

            if ($write) {
                foreach ($write as $client) {
                    if (!$this->handshakes[intval($client)]) {//если ещё не было получено рукопожатие от клиента
                        continue;//то отвечать ему рукопожатием ещё рано
                    }
                    $info = $this->handshake($client);
                    $this->onOpen($client, $info);//вызываем пользовательский сценарий
                }
            }
        }
    }

    protected function handshake($client) {
        $key = $this->handshakes[intval($client)];

        if (!$key) {
            //считываем загаловки из соединения
            $headers = fread($client, 10000);
            preg_match("/Sec-WebSocket-Key: (.*)\r\n/", $headers, $match);

            if (empty($match[1])) {
                return false;
            }

            $key = $match[1];

            $this->handshakes[intval($client)] = $key;
        } else {
            //отправляем заголовок согласно протоколу вебсокета
            $SecWebSocketAccept = base64_encode(pack('H*', sha1($key . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
            $upgrade = "HTTP/1.1 101 Web Socket Protocol Handshake\r\n" .
                "Upgrade: websocket\r\n" .
                "Connection: Upgrade\r\n" .
                "Sec-WebSocket-Accept:$SecWebSocketAccept\r\n\r\n";
            fwrite($client, $upgrade);
            unset($this->handshakes[intval($client)]);
        }

        return $key;
    }

    protected function encode($payload, $type = 'text', $masked = false)
    {
        $frameHead = array();
        $payloadLength = strlen($payload);

        switch ($type) {
            case 'text':
                // first byte indicates FIN, Text-Frame (10000001):
                $frameHead[0] = 129;
                break;

            case 'close':
                // first byte indicates FIN, Close Frame(10001000):
                $frameHead[0] = 136;
                break;

            case 'ping':
                // first byte indicates FIN, Ping frame (10001001):
                $frameHead[0] = 137;
                break;

            case 'pong':
                // first byte indicates FIN, Pong frame (10001010):
                $frameHead[0] = 138;
                break;
        }

        // set mask and payload length (using 1, 3 or 9 bytes)
        if ($payloadLength > 65535) {
            $payloadLengthBin = str_split(sprintf('%064b', $payloadLength), 8);
            $frameHead[1] = ($masked === true) ? 255 : 127;
            for ($i = 0; $i < 8; $i++) {
                $frameHead[$i + 2] = bindec($payloadLengthBin[$i]);
            }
            // most significant bit MUST be 0
            if ($frameHead[2] > 127) {
                return array('type' => '', 'payload' => '', 'error' => 'frame too large (1004)');
            }
        } elseif ($payloadLength > 125) {
            $payloadLengthBin = str_split(sprintf('%016b', $payloadLength), 8);
            $frameHead[1] = ($masked === true) ? 254 : 126;
            $frameHead[2] = bindec($payloadLengthBin[0]);
            $frameHead[3] = bindec($payloadLengthBin[1]);
        } else {
            $frameHead[1] = ($masked === true) ? $payloadLength + 128 : $payloadLength;
        }

        // convert frame-head to string:
        foreach (array_keys($frameHead) as $i) {
            $frameHead[$i] = chr($frameHead[$i]);
        }
        if ($masked === true) {
            // generate a random mask:
            $mask = array();
            for ($i = 0; $i < 4; $i++) {
                $mask[$i] = chr(rand(0, 255));
            }

            $frameHead = array_merge($frameHead, $mask);
        }
        $frame = implode('', $frameHead);

        // append payload to frame:
        for ($i = 0; $i < $payloadLength; $i++) {
            $frame .= ($masked === true) ? $payload[$i] ^ $mask[$i % 4] : $payload[$i];
        }

        return $frame;
    }

    protected function decode($data)
    {
        $unmaskedPayload = '';
        $decodedData = array();

        // estimate frame type:
        $firstByteBinary = sprintf('%08b', ord($data[0]));
        $secondByteBinary = sprintf('%08b', ord($data[1]));
        $opcode = bindec(substr($firstByteBinary, 4, 4));
        $isMasked = ($secondByteBinary[0] == '1') ? true : false;
        $payloadLength = ord($data[1]) & 127;

        // unmasked frame is received:
        if (!$isMasked) {
            return array('type' => '', 'payload' => '', 'error' => 'protocol error (1002)');
        }

        switch ($opcode) {
            // text frame:
            case 1:
                $decodedData['type'] = 'text';
                break;

            case 2:
                $decodedData['type'] = 'binary';
                break;

            // connection close frame:
            case 8:
                $decodedData['type'] = 'close';
                break;

            // ping frame:
            case 9:
                $decodedData['type'] = 'ping';
                break;

            // pong frame:
            case 10:
                $decodedData['type'] = 'pong';
                break;

            default:
                return array('type' => '', 'payload' => '', 'error' => 'unknown opcode (1003)');
        }

        if ($payloadLength === 126) {
            $mask = substr($data, 4, 4);
            $payloadOffset = 8;
            $dataLength = bindec(sprintf('%08b', ord($data[2])) . sprintf('%08b', ord($data[3]))) + $payloadOffset;
        } elseif ($payloadLength === 127) {
            $mask = substr($data, 10, 4);
            $payloadOffset = 14;
            $tmp = '';
            for ($i = 0; $i < 8; $i++) {
                $tmp .= sprintf('%08b', ord($data[$i + 2]));
            }
            $dataLength = bindec($tmp) + $payloadOffset;
            unset($tmp);
        } else {
            $mask = substr($data, 2, 4);
            $payloadOffset = 6;
            $dataLength = $payloadLength + $payloadOffset;
        }

        /**
         * We have to check for large frames here. socket_recv cuts at 1024 bytes
         * so if websocket-frame is > 1024 bytes we have to wait until whole
         * data is transferd.
         */
        if (strlen($data) < $dataLength) {
            return false;
        }

        if ($isMasked) {
            for ($i = $payloadOffset; $i < $dataLength; $i++) {
                $j = $i - $payloadOffset;
                if (isset($data[$i])) {
                    $unmaskedPayload .= $data[$i] ^ $mask[$j % 4];
                }
            }
            $decodedData['payload'] = $unmaskedPayload;
        } else {
            $payloadOffset = $payloadOffset - 4;
            $decodedData['payload'] = substr($data, $payloadOffset);
        }

        return $decodedData;
    }

    abstract protected function onOpen($client, $info);

    abstract protected function onClose($client);

    abstract protected function onMessage($client, $data);

    abstract protected function onSend($data);

    abstract protected function send($data);
}

//пример реализации чата
class WebsocketHandler extends WebsocketWorker
{
    private $site_addr = '127.0.5.1';
    private $site_protocol = 'http'; //http | https

    protected function onOpen($client, $info) {//вызывается при соединении с новым клиентом
        //$this->sendHelper('Присоединился ' . intval($client));
    }

    protected function onClose($client) {//вызывается при закрытии соединения клиентом
        //$this->sendHelper('Отключился ' . intval($client));
    }

    protected function onMessage($client, $data) {//вызывается при получении сообщения от клиента
        $data = $this->decode($data);

        if (!$data['payload']) {
            return;
        }

        if (!mb_check_encoding($data['payload'], 'utf-8')) {
            return;
        }
        //вызываем ф-ю обработки входящей комманды
        $this->parseCommand($client, $data);
    }

    protected function parseCommand($client, $data){
        $tmp = json_decode($data['payload']);
        //return;

        //если JSON_decode прошел успешно
        if($tmp && isset($tmp->type)){
            error_log('name: '.var_export($tmp, true).chr(10).chr(10), 3, 'tst-error-tmp.log');
            //если пришла информация о новом подключении
            if($tmp->type === 'connectinfo'){
                //если заполнены все необходимые поля
                if(isset($tmp->_token) && isset($tmp->_xsrf) && isset($tmp->user_id) && isset($tmp->usertok) && isset($tmp->user_name)){
                    $this->_tokens[intval($client)] = $tmp->_token;
                    $this->_xsrfs[intval($client)] = $tmp->_xsrf;
                    $this->user_ids[intval($client)] = $tmp->user_id;
                    $this->usertoks[intval($client)] = $tmp->usertok;
                    $this->user_names[intval($client)] = $tmp->user_name;
                }
            }
            //если пришла команда создать откртое сообщение
            elseif($tmp->type === 'op_msg' && $tmp->cmd === 'create'){
                
                if(isset($tmp->message)){
                    $result = json_decode($this->curlSendCreate($client, ['crypted'=>false, 'text'=>$tmp->message]));
                    error_log('name: '.var_export($result, true).chr(10).chr(10), 3, 'tst-error-res-464.log');

                    if($result && isset($result->status) && $result->status == 'ok' && isset($result->date) && isset($result->message_id)){
                        $this->sendCommand($tmp->type, $tmp->cmd, $client, $result->date, $result->message_id, $tmp->message);
                    }
                }
            }
            //если пришла команда создать зашифрованное сообщение
            elseif($tmp->type === 'cr_msg' && $tmp->cmd === 'create'){
                if(isset($tmp->message)){
                    $result = json_decode($this->curlSendCreate($client, ['crypted'=>true, 'text'=>$tmp->message]));

                    if($result && isset($result->status) && $result->status == 'ok' && isset($result->date) && isset($result->message_id)){
                        $this->sendCommand($tmp->type, $tmp->cmd, $client, $result->date, $result->message_id, $tmp->message);
                    }
                }
            }
            //если пришла команда редактирование открытого сообщения
            elseif($tmp->type === 'op_msg' && $tmp->cmd === 'edit'){
                if(isset($tmp->message) && isset($tmp->message_id)){
                    $result = json_decode($this->curlSendEdit($client, ['crypted'=>false, 'message_id'=>$tmp->message_id, 'text'=>$tmp->message]));

                    if($result && isset($result->status) && $result->status == 'ok'){
                        $this->sendCommand($tmp->type, $tmp->cmd, $client, null, $result->message_id, $tmp->message);
                    }
                }
            }
            //если пришла команда удаления сообщения
            elseif($tmp->type === 'msg' && $tmp->cmd === 'delete'){
                if(isset($tmp->message_id)){
                    $result = json_decode($this->curlSendDelete($client, ['message_id'=>$tmp->message_id]));

                    if($result && isset($result->status) && $result->status == 'ok'){
                        $this->sendCommand($tmp->type, $tmp->cmd, $client, null, $result->message_id, null);
                    }
                }
            }
        }
    }

    //формирует и отправляет клиентам команду в зависимости от входящей команды
    protected function sendCommand($type, $typecmd, $client, $date=null, $message_id=0, $json_message=''){
        switch($type){
            case 'op_msg':{
                if($typecmd == 'create'){
                    $msg = [
                        'type' => $type,
                        'cmd' => $typecmd,
                        'user_name' => $this->user_names[intval($client)],
                        'message_id' => $message_id,
                        'text' => $json_message,
                        'data' => $date,
                    ];
                    $this->send(json_encode($msg));
                    $this->sendHelper(json_encode($msg));
                    return;
                }
                elseif($typecmd == 'edit'){
                    $msg = [
                        'type' => $type,
                        'cmd' => $typecmd,
                        'message_id' => $message_id,
                        'text' => $json_message,
                    ];

                    $this->send(json_encode($msg));
                    $this->sendHelper(json_encode($msg));
                    return;
                }
                return;
            }
            case 'cr_msg':{
                if($typecmd == 'create'){
                    $msg = [
                        'type' => $type,
                        'cmd' => $typecmd,
                        'user_name' => $this->user_names[intval($client)],
                        'message_id' => $message_id,
                        'text' => $json_message,
                        'data' => $date,
                    ];

                    $this->send(json_encode($msg));
                    $this->sendHelper(json_encode($msg));
                    return;
                }
                return;
            }
            case 'msg':{
                if($typecmd == 'delete'){
                    $msg = [
                        'type' => $type,
                        'cmd' => $typecmd,
                        'message_id' => $message_id,
                    ];

                    $this->send(json_encode($msg));
                    $this->sendHelper(json_encode($msg));
                    return;
                }
                return;
            }
        }
    }

    //отправляет сформированную команду post-запросом через curl в laravel для создания сообщения
    protected function curlSendCreate($client, $msg){
        if($ch = curl_init()){
            curl_setopt($ch, CURLOPT_URL, $this->site_protocol.'://'.$this->site_addr.'/msg/create');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
            curl_setopt($ch, CURLOPT_COOKIE, $this->_xsrfs[intval($client)]);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, 
                '_token='.$this->_tokens[intval($client)] .
                '&user_id='.$this->user_ids[intval($client)] .
                '&usertok='.$this->usertoks[intval($client)] .
                '&crypted='.$msg['crypted'] . 
                '&text='.$msg['text']
            );

            $result = curl_exec($ch);
            //echo $result;
            curl_close($ch);
            return $result;
        }
        return;
    }
    //отправляет сформированную команду post-запросом через curl в laravel для редактирования сообщения
    protected function curlSendEdit($client, $msg){
        if($ch = curl_init()){
            curl_setopt($ch, CURLOPT_URL, $this->site_protocol.'://'.$this->site_addr.'/msg/edit');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
            curl_setopt($ch, CURLOPT_COOKIE, $this->_xsrfs[intval($client)]);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, 
                '_token='.$this->_tokens[intval($client)] .
                '&user_id='.$this->user_ids[intval($client)] .
                '&usertok='.$this->usertoks[intval($client)] .
                '&message_id='.$msg['message_id'] .
                '&text='.$msg['text']
            );

            $result = curl_exec($ch);
            //echo $result;
            curl_close($ch);
            return $result;
        }
        return;
    }
    //отправляет сформированную команду post-запросом через curl в laravel для удаления сообщения
    protected function curlSendDelete($client, $msg){
        if($ch = curl_init()){
            curl_setopt($ch, CURLOPT_URL, $this->site_protocol.'://'.$this->site_addr.'/msg/delete');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
            curl_setopt($ch, CURLOPT_COOKIE, $this->_xsrfs[intval($client)]);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, 
                '_token='.$this->_tokens[intval($client)] .
                '&user_id='.$this->user_ids[intval($client)] .
                '&usertok='.$this->usertoks[intval($client)] .
                '&message_id='.$msg['message_id']
            );

            $result = curl_exec($ch);
            //echo $result;
            curl_close($ch);
            return $result;
        }
        return;
    }

    protected function onSend($data) {//вызывается при получении сообщения от мастера
        $this->sendHelper($data);
    }

    protected function send($message) {//отправляем сообщение на мастер, чтобы он разослал его на все воркеры
        @fwrite($this->master, $message);
    }

    private function sendHelper($data) {
        $data = $this->encode($data);

        $write = $this->clients;
        if (stream_select($read, $write, $except, 0)) {
            foreach ($write as $client) {
                @fwrite($client, $data);
            }
        }
    }
}

$config = array(
    'host' => '0.0.0.0',
    'port' => 8000,
    'workers' => 1,
);

$WebsocketServer = new WebsocketServer($config);
$WebsocketServer->start(); 
