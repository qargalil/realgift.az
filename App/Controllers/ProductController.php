<?php
namespace App\Controllers;

use App\Models;

class ProductController{

    public function yeni(){
        echo $GLOBALS['twig']->render('panel/newitem.html');
    }
    
    public function updateProduct($data){
        $error=array();

        $pattern = "/^[a-zа-яЁё\d\s\.\-əüıöğşçİ::i&]{3,}$/iu";

        if(!(preg_match($pattern,$data['name_az']) || preg_match($pattern,$data['name_ru']) ||preg_match($pattern,$data['name_en']))){
            $error[]=$GLOBALS['lang']['atLeastOneName'];
        }

        $pattern = "/^[a-zа-яЁё\d\s\.\-əüıöğşçiİ::i,&]{3,}$/iu";
        if(!(preg_match($pattern,$data['text_az']) || preg_match($pattern,$data['text_ru']) ||preg_match($pattern,$data['text_en']))){
            $error[]=$GLOBALS['lang']['atLeastOneText'];
        }
        if(!(preg_match($pattern,$data['tags_az']) || preg_match($pattern,$data['tags_ru']) ||preg_match($pattern,$data['tags_en']))){
            $error[]=$GLOBALS['lang']['atLeastOneTag'];
        }
        if(!(preg_match($pattern,$data['color_az']) || preg_match($pattern,$data['color_ru']) ||preg_match($pattern,$data['color_en']))){
            $error[]=$GLOBALS['lang']['atLeastOneColor'];
        }
        if(!(preg_match($pattern,$data['material_az']) || preg_match($pattern,$data['material_ru']) ||preg_match($pattern,$data['tags_en']))){
            $error[]=$GLOBALS['lang']['atLeastOneMaterial'];
        }
        if(!is_numeric($data['price'])){
            $error[]=$GLOBALS['lang']['wrongPrice'];
        }
        if(!is_numeric($data['amount'])){
            $error[]=$GLOBALS['lang']['wrongAmount'];
        }

        if(!count($error)){
            $model = new \App\Models\Product();
            unset($data['user_id']);
            $data['ok']=0;
            $model->where(array('id'=>$_SESSION['last_id']))->update($data);
            echo $_SESSION['last_id'];
            $model = new Models\User();
            $users = $model->setSelectables(array('email'))->where(array('privilege'=>3))->getAll();
            $emails = array();
            foreach ($users as $user){
                $emails[]=$user['email'];
            }
            $emails = implode(',',$emails);

            mail($emails,'Mehsul Yenilendi',"Sayta baxmaqiniz yaxshi olardi. Saytda {$data['name_az']} adli  mehsul yenilenib.",'from: updateproduct@realgift.az');
        }else{
            echo json_encode(array("error"=>$error));
        }



    }
    public function addProduct($data){
        $error=array();
        if($get=(int)$_GET['id']){
            $model = new Models\Product();
            $product = $model->getOne($get);
            if($product['user_id']==$_SESSION['user']['id'] || $_SESSION['user']['privilege']>2){
                $model = new Models\Procat();
                $model->delete(array('product_id'=>$get));
                $model = new Models\Procat();
                foreach ($data as $item){

                    $model->post(array('category_id'=>$item["id"],'product_id'=>$get));
                }
                echo "ok";
            }
        }
        else
        {
            $pattern = "/^[a-zа-яЁёəüıöğşçİ:;\d\s\.\-&]{3,}$/iu";
            if(!(preg_match($pattern,$data['name_az']) || preg_match($pattern,$data['name_ru']) ||preg_match($pattern,$data['name_en']))){
                $error[]=$GLOBALS['lang']['atLeastOneName'];
            }

            $pattern = "/^[a-zа-яЁёəüıöğşçiİ:;\d\s\.\-,&]{3,}$/iu";
            if(!(preg_match($pattern,$data['text_az']) || preg_match($pattern,$data['text_ru']) ||preg_match($pattern,$data['text_en']))){
                $error[]=$GLOBALS['lang']['atLeastOneText'];
            }
            if(!(preg_match($pattern,$data['tags_az']) || preg_match($pattern,$data['tags_ru']) ||preg_match($pattern,$data['tags_en']))){
                $error[]=$GLOBALS['lang']['atLeastOneTag'];
            }
            if(!(preg_match($pattern,$data['color_az']) || preg_match($pattern,$data['color_ru']) ||preg_match($pattern,$data['color_en']))){
                $error[]=$GLOBALS['lang']['atLeastOneColor'];
            }
            if(!(preg_match($pattern,$data['material_az']) || preg_match($pattern,$data['material_ru']) ||preg_match($pattern,$data['tags_en']))){
                $error[]=$GLOBALS['lang']['atLeastOneMaterial'];
            }
            if(!is_numeric($data['price'])){
                $error[]=$GLOBALS['lang']['wrongPrice'];
            }
            if(!is_numeric($data['amount'])){
                $error[]=$GLOBALS['lang']['wrongAmount'];
            }

            if(!count($error)){
                $model = new \App\Models\Product();
                $data['user_id']=$_SESSION['user']['id'];
                $data['ok']=0;
                $model->post($data);
                $da = $model->lastid;
                $_SESSION['last_id'] = $da;
                $model = new Models\User();
                $users = $model->setSelectables(array('email'))->where(array('privilege'=>3))->getAll();
                $emails = array();
                foreach ($users as $user){
                    $emails[]=$user['email'];
                }
                $emails = implode(',',$emails);

                mail($emails,'Yeni Mehsul',"Sayta baxmaqiniz yaxshi olardi. Sayta {$data['name_az']} adli yeni mehsul elave edilib.",'from: newproduct@realgift.az');

                echo $da;
            }else{
                echo json_encode(array("error"=>$error));
            }
        }



    }
    public function edit(){
        global $url;
        $id = explode('/',$url);
        $id = $id[count($id)-1];
        $model = new Models\Product();
        $product = $model->getOne($id);
        if($product['user_id']==$_SESSION['user']['id'] || $_SESSION['user']['privilege']==3){
            $_SESSION['last_id'] = $id;
            $array = array(
                'json'=>json_encode($product),
                'product'=>$product
            );
            echo $GLOBALS['twig']->render("panel/edit.html",array('product'=>$array));
        }
        else echo "Olmaz";

    }
    public function getSelectedCats(){
        $id=$_GET['id'];
        $model = new Models\Procat();
        echo json_encode($model->where(array('product_id'=>$id))->getAll());
    }
    public function deleteProduct(){
        if($id=(int)$_GET['id']){
            $model = new Models\Product();
            $product=$model->getOne($id);
            if($product['user_id']==$_SESSION['user']['id']||$_SESSION['user']['privilege']==3){
                $tema = new Models\Order();
                $tema = $tema->where(array('product_id'=>$id,'paid'=>1))->getAll();
                if($tema){
                    $model->where(array('id'=>$id))->update(array('user_id'=>'0','ok'=>0));
                    $model= new Models\Comment();
                    $model->delete(array('product_id'=>$id));
                }
                else{
                    $model->delete($id);
                    $model = new Models\Image();
                    $array = array('product_id'=>$id);

                    $images = $model->where($array)->getAll();
                    foreach ($images as $image){
                        unlink("img/".$image['name']);
                    }
                    $model->delete($array);
                    $model = new Models\Procat();
                    $model->delete($array);
                    $model= new Models\Comment();
                    $model->delete(array('product_id'=>$id));
                }
            }
        }
        else{
            global $Router;
            $Router->route('/');
        }
    }
    public function imageAct(){
        if(($image = $_GET['image']) && ($act = $_GET['act']) && ($_SESSION['last_id'])){

            if($act=='del'){
                $model = new Models\Product();
                if($model->getOne(array('image'=>$image))) {
                    echo "olmaz";
                }
                else{
                    $model = new Models\Image();
                    $simage=$model->getOne(array('name'=>$image));
                    if ($simage['product_id']==$_SESSION['last_id']){
                        $model = new Models\Image();

                        $model->delete(array('name'=>$image));

                        unlink("img/".$image);
                    }
                }
            }else
            if($act=='upd'){
                $model = new Models\Product();
                $model->where(array('id'=>$_SESSION['last_id']))->update(array('image'=>$image));
            }
        }
    }
    public function upload(){
        if($_FILES && is_numeric($_GET['id']) && $_SESSION['last_id']){
            $id = $_SESSION['last_id'];
            $model = new Models\Product();
            $product = $model->getOne($id);
            if($product['user_id']==$_SESSION['user']['id']){
                $image = new \Core\ImageUploader('img',750,750);
                $image = $image->upload();

                $images = new Models\Image();

                $data = array();
                $data['name'] = $image;
                $data['product_id'] = $id;
                $images->post($data);
                if(!$product['image']){
                    $model->where(array('id'=>$id))->update(array('image'=>$image));
                }
            }
            else echo "olmaz";
        }

        //else echo $GLOBALS['twig']->render('upload.html');
    }
    public function images(){
        if($id = (int)$_GET['id']){
            $image = new Models\Image();
            echo json_encode($image->where(array('product_id'=>$id))->getAll());
        }
    }
    public function getMyProducts(){
        $model = new Models\Product();
        echo json_encode($model->setSelectables(array('name_'.lang.' as name','text_'.lang.' as text',id,image))->where(array('user_id'=>$_SESSION['user']['id']))->getAll());
    }
}

