<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Message;
use App\User;

class MessageController extends Controller
{
    //error_log('name: '.var_export(name, true).chr(10).chr(10), 3, 'tst-error.log');
    private function checkPost(){
        if(isset($_POST['user_id']) && isset($_POST['text']) && isset($_POST['usertok']) && isset($_POST['_token'])){
            return true;
        } else {
            return false;
        }
    }

    private function checkDelPost(){
        if(isset($_POST['user_id']) && isset($_POST['usertok']) && isset($_POST['_token'])){
            return true;
        } else {
            return false;
        }
    }

    private function createUsertokPost(){
        return md5($_POST['_token'].$_POST['user_id'].User::find($_POST['user_id'])->name);
    }

    private function error($code=null){
        echo json_encode([
                'status' => 'error'.$code?$code:'0',
            ]);
    }
    private function success(){
        echo json_encode([
                'status' => 'ok',
            ]);
    }

    public function create(){
        if($this->checkPost()){
            $tmp = $this->createUsertokPost();

            if($_POST['usertok'] == $tmp){
                $msg = Message::create([
                    'user_id' => $_POST['user_id'],
                    'text' => json_decode($_POST['text']),
                    'crypted' => isset($_POST['crypted']) ? ($_POST['crypted']==='1'?true:false) : false
                ]);
                echo json_encode([
                    'status' => 'ok',
                    'message_id' => $msg->id,
                    'date' => $msg->created_at,
                ]);
            } else {
                $this->error();
            }
        }
    }

    public function edit(){
        if($this->checkPost()){
            $tmp = $this->createUsertokPost();
            if($_POST['usertok'] == $tmp){
                $msg = Message::find($_POST['message_id']);
                if($msg && $msg->user->id == $_POST['user_id']){
                    $msg->text = json_decode($_POST['text']);
                    $msg->save();
                    echo json_encode([
                        'status' => 'ok',
                        'message_id' => $msg->id,
                    ]);
                    return;
                }
            }
        }
        $this->error();
    }

    public function delete(){
        if($this->checkDelPost()){
            $tmp = $this->createUsertokPost();
            if($_POST['usertok'] == $tmp){
                $msg = Message::find($_POST['message_id']);
                if($msg && $msg->user->id == $_POST['user_id']){
                    $msg->delete();
                    echo json_encode([
                        'status' => 'ok',
                        'message_id' => $_POST['message_id'],
                    ]);
                    return;
                }
            }
        }
        $this->error();
    }
    
    public function showall(){
        $messages = Message::all();
        foreach ($messages as $message){
            echo '<p style="margin: 5px;"><b>Пользователь:</b> ' . $message->user_id . '</p>';
            echo '<p style="margin: 5px;"><b>Сообщение:</b> ' . (($message->crypted==1)?'###':$message->text) . '</p>';
            echo '<p style="margin: 5px;"><b>msg: </b> ' . $message->text . '</p>';
            echo '<hr>';
        }
    }
}
