<?php
/**
 * Created by PhpStorm.
 * User: Murad
 * Date: 6/16/2016
 * Time: 1:00 PM
 */

namespace App\Controllers;


use App\Models\Page;

class OtherController
{
    public function pages(){
        global $url;
        $id = explode('/',$url);
        $id = $id[count($id)-1];
        $model = new Page();
        $page = $model->setSelectables(array('id','name_'.lang.' as name','text_'.lang.' as text'))->getOne($id);
        foreach ($page as $key=>$value){
            $page[$key]=trim($value);
        }
        //echo "<pre>";
        //print_r($page);
        //echo "</pre>";
        echo $GLOBALS['twig']->render('pages/page.html',array('page'=>$page));
    }
}