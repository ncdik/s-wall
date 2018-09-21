<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Message;

class MessageController extends Controller
{
    
    public function create(){
        if(isset($_POST['user_id']) && isset($_POST['text'])){            
            Message::create([
                'user_id' => $_POST['user_id'],
                'text' => $_POST['text'],
                'crypted' => isset($_POST['crypted']) ? ($_POST['crypted']=='on'?true:false) : false
            ]);
        }
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
