<?php

    require "./vendor/autoload.php";

    use Firebase\JWT\JWT;

    $POST = file_get_contents('php://input');
    $post = json_decode($POST,true);

    $pdo = new \PDO("mysql:host=127.0.0.1;dbname=chatroom","root",'123456');
    $pdo->exec("set names utf8");
    // var_dump($post);
    $stmt = $pdo->prepare("SELECT * FROM chat_user WHERE name = ? AND pwd = ?");
    $stmt->execute([
        $post['username'],
        $post['password']
    ]);

    $user = $stmt->fetch(\PDO::FETCH_ASSOC);
    // var_dump($user);
    if($user){
        $key = 'abcd123';
        // $now = time();
        $data = [
            'id'=>$user['id'],
            'name'=>$user['name']
        ];
        $jwt = JWT::encode($data,$key);
        echo json_encode([
            'code'=>'200',
            'jwt'=>$jwt,
            'id'=>$user['id'],
            'name'=>$user['name']
        ]);
    }else{
        echo json_encode([
            'code'=>'403',
            'error'=>'用户名或密码错误'
        ]);
    }