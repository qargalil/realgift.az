<?php

    $Route = new Core\Router();

if(empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == "off"){
    $redirect = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header('HTTP/1.1 301 Moved Permanently');
    header('Location: ' . $redirect);
    exit();
}

    if($_SESSION['user']){

        //Satici ve Admin
        if($_SESSION['user']['privilege']>1){
            //Admin
            if($_SESSION['user']['privilege']>2){
                $Route->get('panel/users','MainController@users');

                $Route->getjson('panel/category','MainController@addCats');

                $Route->get('panel/category/\d+','MainController@editCat');

                $Route->get('panel/category','MainController@category');

                $Route->get('panel/comment','MainController@comment');
                
                $Route->get("panel/approve",'MainController@approve');

                $Route->get("panel/seeorders",'MainController@orders');

                $Route->post('category/update',"MainController@catUpdate");

                $Route->get('user/\d+',"MainController@user");

                $Route->get("panel/pages",'MainController@pages');

                $Route->get("panel/settings",'MainController@settings');

            }

            $Route->getjson('panel/yeni-mehsul','ProductController@addProduct');

            $Route->get('panel/yeni-mehsul','MainController@yeni');

            $Route->getjson('panel/edit',"ProductController@updateProduct");

            $Route->get('panel/edit/\d+','ProductController@edit');

            $Route->get('fileupload','ProductController@upload');

            $Route->get('myProducts','ProductController@getMyProducts');

            $Route->get('image_act',"ProductController@imageAct");
        }


        //Alicilar
        $Route->get('panel','MainController@panel');

        $Route->get('sil',"ProductController@deleteProduct");

        $Route->get('cix',"RegSignController@cix");


        $Route->post('order',"MainController@order");

        $Route->get('order',"MainController@seeOrders");

        $Route->get('panel/order/finish','MainController@finish');

        $Route->get("panel/profil",'RegSignController@profil');


    }
    else{

        //Qeydiyyatsiz

        $Route->getjson('qeydiyyat','RegSignController@store');

        $Route->get('qeydiyyat','RegSignController@register');

        $Route->post('daxilol',"RegSignController@daxilOl");

        $Route->get('daxilol','RegSignController@signIn');

        $Route->get('order',"RegSignController@signIn");

    }

    $Route->get('email',"MainController@email");

    $Route->get('selectedCats','ProductController@getSelectedCats');

    $Route->get("images",'ProductController@images');

    $Route->get("category/get",'MainController@getCats');

    $Route->get("category/\d+","MainController@getPBC");

    $Route->get('axtar',"MainController@axtar");

    $Route->get('sehife/\d+','OtherController@pages');

    $Route->get('mehsul/\d+','MainController@mehsul');


$Route->get("wishlist","MainController@wishlist");

    $Route->get('dil/\w+','MainController@dil');

    $Route->defAction('MainController@index');
