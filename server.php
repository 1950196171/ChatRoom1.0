<?php
use Workerman\Worker;
use Firebase\JWT\JWT;
require_once "./Workerman-master/Autoloader.php";
require("./vendor/autoload.php");
// Worker用来绑定端口、启动服务器。
$worker = new Worker('websocket://0.0.0.0:9999');

// 设置线程
$worker->count = 1;
// 用户
$users = [];
// 用户和客户端的关系
$userConn = [];
// 绑定回调 每当有一个客户端连接时 会调用
$worker->onConnect = function($connection){
    // 想获取客户端数据 就要加这行
        $connection->onWebSocketConnect = function ($connection, $http_header) {
            
            global $users,$userConn,$worker;
            
            $key = 'abcd123';
            
            $data = JWT::decode($_GET['jwt_token'],$key,array('HS256'));
            
            $connection->uid = $data->id;
            $connection->name = $data->name;
            $users[$data->id] = $data->name;
            $userConn[$data->id] = $connection;
            foreach($worker->connections as $v){
                $v->send(json_encode([
                    'type'=>'users',
                    'user'=>$users
                ]));
            }
            // var_dump($data);
        };
};

$worker->onMessage = function($connection,$data){
     global $userConn,$users,$worker;

    $res = explode(':',$data);
    $user_id = $res[0];
    unset($res[0]);
    $resData = implode(':',$res);
    // var_dump($user_id);
    if($user_id=='all'){
        foreach($worker->connections as $v){
            $v->send(json_encode([
                'type'=>'message',
                'user_id'=>$connection->uid,
                'shou_id'=>'all',
                'u_name'=>$users[$connection->uid],
                'message'=>$resData
            ]));
        }
    }else{
        // 发消息的人
        // var_dump($connection);
        //user_id收消息的
        $userConn[$user_id]->send(json_encode([
            'type'=>'message',
            'user_id'=>$connection->uid,
            'u_name'=>$users[$connection->uid],
            'shou_id'=>$user_id,
            's_name'=>$users[$user_id],
            'message'=>$resData
        ]));
        // 发消息
        $userConn[$connection->uid]->send(json_encode([
            'type'=>'message',
            'user_id'=>$connection->uid,
            'u_name'=>$connection->name,
            'shou_id'=>$user_id,
            's_name'=>$users[$user_id],
            'message'=>$resData
        ]));


    }
};


Worker::runAll();










?>