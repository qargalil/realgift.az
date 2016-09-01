<?php

namespace App\Controllers;

use App\Models;

class RegSignController{

    public function register(){
        global $url;
        $url = "https://{$_SERVER[HTTP_HOST]}";
        if (!($_SERVER[HTTP_REFERER] ==$url.'/daxilol'  || $url==$_SERVER[HTTP_REFERER]))
        $_SESSION['before'] = $_SERVER['HTTP_REFERER'];

        echo $GLOBALS['twig']->render('regsign/register.html');
    }

    public function signIn(){
        $url = "https://{$_SERVER[HTTP_HOST]}";
        if (!($_SERVER[HTTP_REFERER] ==$url.'/qeydiyyat'  || $url==$_SERVER[HTTP_REFERER]))
        $_SESSION['before'] = $_SERVER['HTTP_REFERER'];

        echo $GLOBALS['twig']->render('regsign/signIn.html');
    }

    public function cix(){
        unset($_SESSION['user']);
        global $Route;
        $Route->route('/');
    }

    public function profil(){
        $model= new Models\User();
        $user = $model->getOne($_SESSION['user']['id']);
        $user = $model->getOne($_SESSION['user']['id']);
        if($upd=$_GET['upd']){
            switch ($upd){
                case 'name':
                    $pattern = '/^[a-zа-яЁёəüıöğşçİi\s]{3,50}$/i';
                    if(preg_match($pattern,trim($_POST['name']))){
                        $model->where(array('id'=>$_SESSION['user']['id']))->update(array('name'=>$_POST['name']));
                        $message['text'] = $GLOBALS['lang']['nameChanged'];
                        $message['class'] = 'success';
                    }
                    else{
                        $message['text'] = $GLOBALS['lang']['nameDChanged'];
                        $message['class'] = 'warning';
                    }
                    break;
                case 'surname':
                    $pattern = '/^[a-zа-яЁёəüıöğşçİi\s]{3,50}$/i';
                    if(preg_match($pattern,trim($_POST['surname']))){
                        $model->where(array('id'=>$_SESSION['user']['id']))->update(array('surname'=>$_POST['surname']));
                        $message['text'] = $GLOBALS['lang']['surnameChanged'];
                        $message['class'] = 'success';
                    }
                    else{
                        $message['text'] = $GLOBALS['lang']['surnameDChanged'];
                        $message['class'] = 'warning';
                    }
                    break;
                case 'email':
                    $pattern = '/^[a-z0-9\._-]{3,20}@[a-z]{3,15}\.[a-z\.]{2,6}$/i';
                    if(preg_match($pattern,trim($_POST['email']))){
                        $email = trim($_POST['email']);
                        $mm = new Models\User();
                        $user = $mm->where(array('email'=>$email))->getAll();
                        if (!$user){
                            $model->where(array('id'=>$_SESSION['user']['id']))->update(array('email'=>$_POST['email']));
                            $message['text'] = $GLOBALS['lang']['emailChanged'];
                            $message['class'] = 'success';
                        }
                        else{
                            $message['text'] = $GLOBALS['lang']['emailExist'];
                            $message['class'] = 'warning';
                        }

                    }
                    else{
                        $message['text'] = $GLOBALS['lang']['emailDChanged'];
                        $message['class'] = 'warning';
                    }
                    break;
                case 'mobile':
                    $pattern = '/^0[0-9]{2}\s*-{0,1}\s*[0-9]{3}\s*-{0,1}\s*[0-9]{2}\s*-{0,1}\s*[0-9]{2}$/';
                    if(preg_match($pattern,trim($_POST['mobile']))){
                        $model->where(array('id'=>$_SESSION['user']['id']))->update(array('mobile'=>$_POST['mobile']));
                        $message['text'] = $GLOBALS['lang']['mobileChanged'];
                        $message['class'] = 'success';
                    }
                    else{
                        $message['text'] = $GLOBALS['lang']['mobileDChanged'];
                        $message['class'] = 'warning';
                    }
                    break;
                case 'address':
                    $pattern = '/^[a-zа-яЁё-əüıöğşçiİ,\s\/\\\]{5,200}$/i';
                    if(preg_match($pattern,trim($_POST['address']))){
                        $model->where(array('id'=>$_SESSION['user']['id']))->update(array('address'=>$_POST['address']));
                        $message['text'] = $GLOBALS['lang']['addressChanged'];
                        $message['class'] = 'success';
                    }
                    else{
                        $message['text'] = $GLOBALS['lang']['addressDChanged'];
                        $message['class'] = 'warning';
                    }
                    break;
                case 'password':
                    if(md5($_POST['passwordCurrent'])==$user['password']){
                        $pattern = '/^[a-zа-яЁё1-9@&!]{8,}$/i';
                        if(preg_match($pattern,trim($_POST['password']))){
                            if($_POST['password']==$_POST['password1']){
                                $model->where(array('id'=>$_SESSION['user']['id']))->update(array('password'=>md5($_POST['password'])));
                                $message['text'] = $GLOBALS['lang']['passwordChanged'];
                                $message['class'] = 'success';
                            }
                            else{
                                $message['text'] = $GLOBALS['lang']['passwordMatch'];
                                $message['class'] = 'warning';
                            }
                        }
                        else{
                            $message['text'] = $GLOBALS['lang']['passwordDChanged'];
                            $message['class'] = 'warning';
                        }
                    }
                    else{
                        $message['text'] = $GLOBALS['lang']['currentPasswordWrong'];
                        $message['class'] = 'warning';
                    }

                    break;
            }
        }
        $model = new Models\User();
        $user = $model->getOne($_SESSION['user']['id']);
        echo $GLOBALS['twig']->render('regsign/profil.html',array('user'=>$user,'message'=>$message));
    }

    public function daxilOl($data){
        $model =new \App\Models\User();
        $data['password'] = md5($data['password']);
        global $db;
        $data['email']=addslashes($data['email']);
        $user = $db->query("SELECT * FROM users WHERE (email = '{$data['email']}' OR username = '{$data['email']}') AND password = '{$data['password']}'")->fetchAll(\PDO::FETCH_ASSOC);
        //if $user=$model->where($data)->getAll()
        if($user){
            $_SESSION['user']=$user[0];
            global $Route;
            $go = '/panel';
            if($_SESSION['before']){
                $go = $_SESSION['before'];
                unset($_SESSION['before']);
            }
            $Route->route($go);
        }
        else
        {
            $message=$GLOBALS['lang']['emailOrPassword'];
            echo $GLOBALS['twig']->render("regsign/signIn.html",array("error"=>$message));
        }
    }

    public function sendmail($to,$subject,$message){
        $file = fopen('log.txt','w');
        fwrite($file,"to:$to; subject:$subject; Text: $message");
        fclose($file);
        //mail($to,$subject,$message,'From: noreply@realgift.az');
    }

    public function store($data){
        $model = new \App\Models\User();

        $response = array();
        if($model->where(array('email'=>trim(strtolower($data['email']))))->getAll()){
            $response['error'][]=$GLOBALS['lang']['emailExist'];
        }
        if($model->where(array('username'=>trim(strtolower($data['username']))))->getAll()){
            $response['error'][]=$GLOBALS['lang']['userExists'];
        }
        if($data['password']!=$data['password2']){
            $response['error'][]=$GLOBALS['lang']['passwordMatch'];
        }
        if(!preg_match('/^[a-z0-9а-яЁё@&!]{8,}$/iu',$data['password'])){
            $response['error'][]=$GLOBALS['lang']['passwordDChanged'];
        }
        if(!preg_match('/^[a-zа-яЁёəüıöğşçİ]{3,40}$/iu',$data['name'])){
            $response['error'][]=$GLOBALS['lang']['wrongName'];
        }
        if(!preg_match('/^0[0-9]{2}\s*-{0,1}\s*[0-9]{3}\s*-{0,1}\s*[0-9]{2}\s*-{0,1}\s*[0-9]{2}$/',$data['mobile'])){
            $response['error'][]=$GLOBALS['lang']['wrongMobile'];
        }
        if(!preg_match('/^[a-z0-9\._-]{3,20}@[a-z]{3,15}\.[a-z\.]{2,6}$/i',$data['email'])){
            $response['error'][]=$GLOBALS['lang']['emailDChanged'];
        }
        if(!preg_match('/^[a-z0-9\._-]{5,}$/i',$data['username'])){
            $response['error'][]=$GLOBALS['lang']['wrongUserName'];
        }
        if(!preg_match('/^[a-zа-яЁёəüıöğşçİ]{4,}$/iu',$data['surname'])){
            $response['error'][]=$GLOBALS['lang']['wrongSurName'];
        }
        if(!$data['gender']!=''){
            $response['error'][]=$GLOBALS['lang']['selectGender'];
        }
        if (!isset($response['error'])){
            $data['password'] = md5($data['password']);
            $data['is_real']=0;
            $data['email']=trim(strtolower($data['email']));
            $data['username']=trim(strtolower($data['username']));
            $server = $_SERVER['HTTP_HOST'];
            $app = md5("approve".$data['email']."approve");
            $message = "{$GLOBALS['lang']['approveEmail']}\nhttp://realgift.az/app?link=$app";

            if($model->post($data)){
                $go = '/panel';
                if($_SESSION['before']){
                    $go = $_SESSION['before'];
                }
                $response['success'] = $go;
                global $db;
                $db->query("INSERT into email_app(link) VALUES('$app')");
                $_SESSION['user'] = $model->getLast();
                //$this->sendmail($data['email'],$GLOBALS['lang']['approve'],$message);
            }
            else{
                echo  $response['error'][]="asdasd";
            }

        }
        echo json_encode($response);
    }
}
