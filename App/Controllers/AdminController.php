<?php

namespace App\Controllers;

use App\Models;
use PDO;

class AdminController
{
    public function users(){
        if($act=$_GET['act']){
            $id = (int)$_GET['id'];
            $data = (int)$_GET['type'];
            $model = new Models\User();
            if($act=='get'){
                $data = $model->where(array('privilege!'=>3))->getAll();
                echo json_encode($data);
            }
            elseif ($act=='del' && $id){
                $model->delete($id);
            }
            elseif($act=='upd' && $id && $data){
                if($model->custom("UPDATE users SET privilege='$data' WHERE id='$id'"));
            }
            else print_r($_GET);


        }
        else
            echo $GLOBALS['twig']->render('panel/users.html');
    }

    public function user(){
        global $url;
        $id = explode('/',$url);
        $id = $id[count($id)-1];
        $model = new Models\User();
        $user = $model->getOne($id);
        echo $GLOBALS['twig']->render('panel/user.html',array('user_'=>$user));
    }

    public function orders(){
        if($_GET['tema']=='ok'){
            $id = addslashes(trim($_GET['id']));
            $model = new Models\Transaction();
            $say = $model->where(array('payment_id'=>$id))->update(array('ok'=>1));
            if($say){
                global $db;

                $orders = $db->query("SELECT products.id as product_id, orders.id,products.amount as pamount, orders.id,orders.amount FROM products JOIN orders on products.id = orders.product_id WHERE payment_id = '$id'")->fetchAll(PDO::FETCH_ASSOC);
                foreach ($orders as $order){
                    $sum = $order['pamount']-$order['amount'];
                    $model= new Models\Product();
                    $model->where(array('id'=>$order['product_id']))->update(array('amount'=>$sum));
                }
                $model = new Models\Order();
                $model->where(array('payment_id'=>$id))->update(array('paid'=>1));

            }

        }
        else if($_GET['tema']=='delete'){
            $id = $_GET['id'];
            $model = new Models\Transaction();
            $model->delete(array('payment_id'=>$id));
            $model = new Models\Order();
            $model->delete(array('payment_id'=>$id));
        }
        //echo "<pre>";
        global $db;
        $orderers = $db->query("SELECT  DISTINCT(payment_id) as pay_id,users.name,mobile,users.id as user_id from orders join users on orders.user_id = users.id WHERE ordered='1' AND paid='0' ORDER BY orders.time")->fetchAll(PDO::FETCH_ASSOC);
        for($i = 0; $i<count($orderers);$i++){
            $orderers[$i]['orders'] = $db->query("SELECT products.user_id as owner,products.id,products.name_az as name,orders.amount FROM products join orders on products.id = orders.product_id WHERE orders.payment_id = '{$orderers[$i]['pay_id']}'")->fetchAll(PDO::FETCH_ASSOC);
        }

        //print_r($orderers);
        //echo"</pre>";

        echo $GLOBALS['twig']->render('panel/orders.html',array('orderers'=>$orderers));

    }
    public function category(){
        if($get = $_GET['delete']){

            $model = new Models\Procat();
            $model->delete(array('category_id'=>$get));
            if (file_exists("img/category/$get.jpg")){
                unlink("img/category/$get.jpg");
            };

            $model = new Models\Category();
            echo $model->delete($get);
        }
        else{
            if ($_SESSION['message']){
                $message = $_SESSION['message'];
                unset($_SESSION['message']);
            }
            echo $GLOBALS['twig']->render('panel/category.html',array('message'=>$message));
        }

    }

    public function catUpdate($data){
        $model = new Models\Category();
        $model->where(array(id=>$data['id']))->update($data);
        if(!$_FILES[image][error]){
            $ext = $data['id'];
            move_uploaded_file($_FILES[image][tmp_name],'img/category/'.$ext);
        }
        global $Route;

        $_SESSION['message'] = 'Kateqoriya Yenilendi';
        $Route->route("/panel/category");

    }

    public function comment(){
        if($_GET['tema']==123){
            $model = new Models\Comment();
            $comments = $model->where(array('approved'=>0))->orderBy('time','DESC')->getAll();
            echo(json_encode($comments));
        }else if($_GET['tema']=='del' && $_GET['id']){
            $model = new Models\Comment();
            $model->delete($_GET['id']);
        }
        else if($_GET['tema']=='tes' && $_GET['id']){
            $model = new Models\Comment();
            $model->where(array('id'=>$_GET['id']))->update(array('approved'=>1));
        }
        else{
            echo $GLOBALS['twig']->render('panel/comment.html');
        }

    }

    public function addCats($data){
        $response = array();
        $model = new Models\Category();
        if($_GET['add']=='cat'){
            if(trim($data['name_az']) && trim($data['name_en']) && trim($data['name_ru'])){
                if($model->post($data))
                    $response["success"]='Category is added';
                else $response["error"]='Something is wrong';
            }
            else $response['error']="$data";
        }
        else{
            if(trim($data['name_az']) && trim($data['name_en']) && trim($data['name_ru'])&& (int)$data['category_id']){
                if($model->post($data))
                    $response["success"]='Subcategory is added';
                else $response["error"]='Something is wrong';
            }
            else $response['error']="Saheleri doldur";
        }
        echo json_encode($response);
    }

    public function editCat(){
        if($_POST){

        }
        else{
            global $url;
            $url = explode('/',$url);
            $url = $url[count($url)-1];
            $model = new Models\Category();
            $data = $model->getOne($url);
            if(file_exists("img/category/$url")){
                $data['img'] = $url;
            }
            echo $GLOBALS['twig']->render('panel/catedit.html',array('data'=>$data));
        }
    }

    public function approve(){
        $model = new Models\Product();
        if($_GET['tema']=='all'){
            global $db;
            $products = $db->query("SELECT products.id,products.name_".lang." as name,image,users.username,users.id as user_id FROM products join users on products.user_id = users.id WHERE ok='0'")->fetchAll(PDO::FETCH_ASSOC);
            //$products = $model->setSelectables(array('id','name_'.lang.' as name','image'))->where(array('ok'=>0))->getAll();
            echo json_encode($products);
        }
        else if($_GET['tema']=='app' && $id = (int)$_GET['id']){
            $model->where(array('id'=>$id))->update(array('ok'=>1));
            echo $id;
        }
        else echo $GLOBALS['twig']->render('panel/approve.html');
    }

    public function pages(){
        global $Route;
        $page = 'new.html';
        if($_GET['page']=='new'){
            $data = $_POST;
            //print_r($data);
            if($data){

                $pattern = "/.{3,}/iu";

                if(!(preg_match($pattern,$data['name_az']) && preg_match($pattern,$data['name_ru']) &&preg_match($pattern,$data['name_en']) && preg_match($pattern,$data['text_az']) && preg_match($pattern,$data['text_ru']) &&preg_match($pattern,$data['text_en']))){
                    $error[]='Sahələrin hamısı doldurulmalıdı';
                }
                else{
                    $model = new Models\Page();
                    $model->post($data);
                    $error['success']='Səhifə Yaradıldı';
                    $_SESSION['errors'] = $error;
                    $page='main.html';
                    $Route->route('/panel/pages');
                }
            }

        }
        else  if($_GET['page']=='update'){

            if ( $_POST){
                $data=$_POST;
                $pattern = "/./iu";
                if(!(preg_match($pattern,$data['name_az']) && preg_match($pattern,$data['name_ru']) &&preg_match($pattern,$data['name_en']) && preg_match($pattern,$data['text_az']) && preg_match($pattern,$data['text_ru']) &&preg_match($pattern,$data['text_en']))){
                    $error[]='Sahələrin hamısı doldurulmalıdı';
                }
                else{
                    $model = new Models\Page();
                    $model->where(array('id'=>$_GET['id']))->update($data);
                    $error['success']='Səhifə Yeniləndi';
                    $page='main.html';
                    $_SESSION['errors'] = $error;
                    $Route->route('/panel/pages');
                }
            }
            else{
                $model = new Models\Page();
                $data = $model->getOne($_GET['id']);
                $page = 'update.html';
            }
        }
        else{
            if($_GET['page']=='delete'){
                $model = new Models\Page();
                $model->delete($_GET['id']);
                $error['success']='Sehifə Silindi';
            }
            if($_SESSION['errors']){
                $error = $_SESSION['errors'];
                unset($_SESSION['errors']);
            }
            $model = new Models\Page();
            $data = $model->getAll();
            $page = 'main.html';
        }
        echo $GLOBALS['twig']->render('panel/pages/'.$page,array('errors'=>$error,'data'=>$data));
    }

    public function settings(){
        if($_POST){
            //print_r($_POST);
            $data = json_encode($_POST);
            $file = fopen("data.json",'w');
            fwrite($file,$data);
            fclose($file);
            $message = "Qeyde alindi";
            if(!$_FILES[image][error]){

                move_uploaded_file($_FILES[image][tmp_name],'img/back.png');
            }
        }
        if(file_exists('data.json'))
            $data = json_decode(file_get_contents("data.json"),true);
        echo $GLOBALS['twig']->render('panel/settings.html',array('data'=>$data,'message'=>$message));
    }
}