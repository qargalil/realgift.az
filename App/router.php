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
                $Route->get('panel/users','AdminController@users');

                $Route->getjson('panel/category','AdminController@addCats');

                $Route->get('panel/category/\d+','AdminController@editCat');

                $Route->get('panel/category','AdminController@category');

                $Route->get('panel/comment','AdminController@comment');
                
                $Route->get("panel/approve",'AdminController@approve');

                $Route->get("panel/seeorders",'AdminController@orders');

                $Route->post('category/update',"AdminController@catUpdate");

                $Route->get('user/\d+',"AdminController@user");

                $Route->get("panel/pages",'AdminController@pages');

                $Route->get("panel/settings",'AdminController@settings');

            }

            $Route->getjson('panel/yeni-mehsul','ProductController@addProduct');

            $Route->get('panel/yeni-mehsul','ProductController@yeni');

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

        $Route->get('panel/arzu',"MainController@arzu");

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
