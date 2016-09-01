<?php
require 'database.php';
require 'paypal.php';
//Start the session
session_start();

//check the language
if(!isset($_SESSION['lang']))
    $_SESSION['lang']='az';

define('lang',$_SESSION['lang']);
$lang= lang;

//Ondemand Class Loader

spl_autoload_register(function($c){
    require '../'.preg_replace("/\\\/","/",$c).'.php';
});

//include language
require_once('../lang/lang_'.lang.'.php');

//Install Twig
$loader = new Twig_Loader_Filesystem("../view");
$GLOBALS['twig'] = new Twig_Environment($loader);


$model=new \App\Models\Category();
$categories = $model->setSelectables(array('name_'.lang.' as name','id'))->where(array('category_id'=>'0'))->getAll();
//echo"<pre>";
for ($i = 0;$i<count($categories);$i++){
    $category = $categories[$i];
    $model = new App\Models\Category();
    $subs = $model->setSelectables(array('name_'.lang.' as name','id'))->where(array('category_id'=>$category['id']))->getAll();
    $categories[$i]['subs'] = $subs;
}
$images = array(
    '/img/3.jpg',
    '/img/5.jpg',
    '/img/acc.jpg',
    '/img/dec.jpg',
    '/img/ann.jpg',
    '/img/wed.jpg'
);
$mainCats = $db->query("SELECT name_$lang as name,id FROM categories WHERE main>0 ORDER BY main")->fetchAll(PDO::FETCH_ASSOC);

for ($i = 0; $i<count($mainCats); $i++){
    $mainCats[$i]['image'] = "/img/category/".$mainCats[$i]['id'];
}

//print_r($mainCats);

$model = new App\Models\Page();
$pagesFirst = $model->setSelectables(array('id','name_'.lang.' as name'))->where(array('purpose'=>2))->getAll();
$pagesSecond = $model->setSelectables(array('id','name_'.lang.' as name'))->where(array('purpose'=>3))->getAll();
$pagesThird = $model->setSelectables(array('id','name_'.lang.' as name'))->where(array('purpose'=>4))->getAll();

//echo "<pre>";

//print_r($pagesFirst);
//print_r($pagesSecond);

//echo "</pre>";
//print_r($mainCats);
//print_r($categories);
$settings = json_decode(file_get_contents('data.json'),true);
//print_r($settings);
$GLOBALS['twig']->addGlobal('settings',$settings);
$GLOBALS['twig']->addGlobal('pages',$pagesFirst);
$GLOBALS['twig']->addGlobal('pages2',$pagesSecond);
$GLOBALS['twig']->addGlobal('pages3',$pagesThird);
$GLOBALS['twig']->addGlobal('mainCats',$mainCats);
$GLOBALS['twig']->addGlobal('categories',$categories);
$GLOBALS['twig']->addGlobal('lang',$language);
$GLOBALS['twig']->addGlobal('user',$_SESSION['user']);

//End Twig
$GLOBALS['lang'] = $language;
if($_SESSION['user']){
    $model = new App\Models\Order();
    $sifSay = $model->where(array('user_id'=>$_SESSION['user']['id'],'ordered'=>0))->getAll();
    $sifSay = count($sifSay);
}
else{
    $sifSay =0;
}
$GLOBALS['twig']->addGlobal('sifSay',$sifSay);
$url=$_GET['url'];
unset($_GET['url']);

