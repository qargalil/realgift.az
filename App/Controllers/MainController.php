<?php

namespace App\Controllers;

use App\Models;
use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Exception\PayPalConnectionException;
use PDO;

class MainController
{
    public function index(){
        $model = new Models\Product();
        $products = $model->setSelectables(array('name_'.lang.' as name','id','price','image'))->where(array('ok'=>1,'amount!'=>0))->orderBy('rand')->getAll(12);

        echo $GLOBALS['twig']->render('start.html',array('products'=>$products));
    }
    
    public function panel(){

        echo $GLOBALS['twig']->render('panel/start.html');
    }

    public function dil(){
        global $url,$Route;
        $id = explode('/',$url);
        $lang = $id[count($id)-1];
        if($lang=='ru') $_SESSION['lang'] = 'ru';
        if($lang=='az') $_SESSION['lang'] = 'az';

        $Route->route($_SERVER['HTTP_REFERER']);
    }
    
    public function mehsul(){
        global $url;
        $id = explode('/',$url);
        $id = $id[count($id)-1];
        $model = new Models\Comment();
        if(strlen(trim($_POST['comment']))>=3 && $_SESSION['user']){
            $data = array(
                'comment'=>trim($_POST['comment']),
                'user_name'=>$_SESSION['user']['username'],
                'product_id'=>$id,
                'time'=>time()
            );
            $model->post($data);
            $model = new Models\User();
            $users = $model->setSelectables(array('email'))->where(array('privilege'=>3))->getAll();
            $emails = array();
            foreach ($users as $user){
                $emails[]=$user['email'];
            }
            $emails = implode(',',$emails);

            mail($emails,'Yeni Komment',"Sayta baxmaqiniz yaxshi olardi. Saytda {$_SESSION['user']['username']} adli  istifadeci  komment yazib.",'from: updateproduct@realgift.az');
        }
        global $db;
        //$comments = $model->where(array('product_id'=>$id,'approved'=>'1'))->getAll();
        $comments = $db->query("SELECT * FROM comments WHERE product_id='$id' AND (approved='1' OR user_name='{$_SESSION['user']['username']}')")->fetchAll(PDO::FETCH_ASSOC);
        $model = new Models\Product();

        $product = $model->setSelectables(array('name_'.lang.' as name','text_'.lang.' as text','material_'.lang.' as material','color_'.lang.' as color',id,image,price,amount))->getOne($id);
        //echo '<pre>';
        //print_r($product);
        //echo '</pre>';

        echo $GLOBALS['twig']->render('mehsul/mehsul.html',array('product'=>$product,'comments'=>$comments));
    }

    public function getPBC(){
        global $url;
        $id = explode('/',$url);
        $id = $id[count($id)-1];
        global $db;
        $model = new Models\Category();
        $category = $model->getOne($id);

        if($category['category_id']==0){
            $sql = "SELECT DISTINCT(id),name_".lang." as name,price,image FROM products join procats on products.id = procats.product_id WHERE category_id in (SELECT id FROM categories WHERE category_id=$id) AND amount!=0 AND ok=1";

        }
        else{
            $sql = "SELECT id,name_".lang." as name,price,image FROM products join procats on products.id = procats.product_id WHERE category_id = '$id' AND amount!=0 AND ok=1";
        }
        $say = ceil($db->query($sql)->rowCount()/18);
        if($seh = (int)$_GET['seh'])
        $seh =18*abs((int)($seh-1));
        $sql.=" LIMIT $seh,18";
        $data = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        echo $GLOBALS['twig']->render('items/index.html',array('products'=>$data,'say'=>$say));

    }

    

    public function order($data){

        $model = new Models\Product();

        $product = $model->getOne((int)$data['product_id']);

        if($act = $_GET['act']=='upd'){
            $model = new Models\Order();
            $order = $model->where(array('user_id'=>$_SESSION['user']['id'],'id'=>(int)$data['id'],'product_id'=>(int)$data['product_id']))->getAll();
            if($order && $data['amount']>0){
                if($data['amount']>$product['amount']){
                    $data['amount'] = $product['amount'];
                }
                $model = new Models\Order();
                $model->where(array('id'=>(int)$data['id']))->update(array('amount'=>$data['amount'],'payment_id'=>''));
            }

        }
        else{
            $data['user_id']= $_SESSION['user']['id'];
            if($data['amount']>0){


                if($product){
                    $model = new Models\Order();
                    $seA =array('product_id'=>$data['product_id'],'user_id'=>$_SESSION['user']['id'],'ordered'=>0);
                    $order = $model->where($seA)->getAll();
                    if($order){
                        $data['amount']+=$order[0]['amount'];
                        $model->delete($order[0]['id']);
                    }
                    if($product['amount']<$data['amount']){

                        $data['amount']=$product['amount'];
                    }
                    $model= new Models\Order();
                    $model->post($data);
                    //print_r($order);
                }
                else{
                    $problem=$GLOBALS['notOrdered'];
                }
            }
        }

        //$orders = $model->where(array('user_id'=>$_SESSION['user']['id']))->getAll();

        //print_r($orders);
        global $Route;
        $Route->route('/order');


        //$model->post($data);


    }

    public function axtar(){
        $axtar = trim(addslashes($_GET['search']));
        global $db;
        if (lang=='az'){
            $pattern = array('/sh/', '/ch/');
            $replace = array('ş', 'ç');
            $axtar = preg_replace($pattern,$replace,$axtar);
        }
        $sql = "SELECT id,name_".lang." as name,image,price FROM products WHERE amount!=0 AND ok=1 AND(name_".lang." LIKE '%$axtar%' OR text_".lang." LIKE '%$axtar%' OR color_".lang." LIKE '%$axtar%' OR tags_".lang." LIKE '%$axtar%')";
        //echo "<pre>";
        //print_r($query);
        //echo "</pre>";
        $say = ceil($db->query($sql)->rowCount()/18);
        if($seh = (int)$_GET['seh'])
            $seh =18*abs((int)($seh-1));
        $sql.=" LIMIT $seh,18";
        $products = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        echo $GLOBALS['twig']->render('items/index.html',array('products'=>$products,'axtar'=>$axtar,'say'=>$say));
    }

    public function seeOrders(){
        if($act = $_GET['act']){
            if($act=='del' && $id = (int) $_GET['id']){
                $model = new Models\Order();
                $order = $model->getOne($id);
                if($order['user_id']==$_SESSION['user']['id']){
                    $model = new Models\Order();
                    $model->delete($id);

                }
            }
            global $Route;
            $Route->route('/order');
        }


        global $db;
        $user_id = $_SESSION['user']['id'];
        //echo "<pre>";
        $orders = $db->query("SELECT orders.id,product_id,name_".lang." as name,image,orders.amount,price,products.amount as max FROM products JOIN orders ON products.id=orders.product_id WHERE orders.user_id = $user_id and ordered='0'")->fetchAll(PDO::FETCH_ASSOC);
        $say = count($orders);
        $total = 0;
        foreach ($orders as $order){
            $total+=$order['amount']*$order['price']+4;
        }
        $problems = $_SESSION['problems'];
        unset($_SESSION['problems']);
        //print_r($problems);
        echo $GLOBALS['twig']->render('order/orders.html',array('orders'=>$orders,'say'=>$say,'total'=>$total,'problems'=>$problems));
    }

    private function finishPayment($paypal){
        global $db;

        $problems = array();

        $paymentId = addslashes($_GET['paymentId']);
        $payerId = addslashes($_GET['PayerID']);

        if($paymentId&&$payerId){
            $model = new Models\Order();
            $totalOrder = count($model->where(array('user_id'=>$_SESSION['user']['id'],'ordered'=>0))->getAll());
            $must = count($model->where(array('user_id'=>$_SESSION['user']['id'],'ordered'=>0,'payment_id'=>trim(addslashes($paymentId))))->getAll());
            if($totalOrder==$must){
                $payment = Payment::get($paymentId,$paypal);
                $execute = new PaymentExecution();
                $execute->setPayerId($payerId);

                $user_id=$_SESSION['user']['id'];
                $orders = $db->query("SELECT orders.id,products.amount as pamount,orders.amount,name_".lang." as name,product_id,price FROM orders JOIN products ON orders.product_id=products.id WHERE orders.user_id='$user_id' and ordered='0'")->fetchAll(PDO::FETCH_ASSOC);

                for($i=0;$i<count($orders);$i++){
                    if($orders[$i]['pamount']<$orders[$i]['amount']){
                        $problems[] = $orders[$i]['name'].' istenilen qeder yoxdur';
                    }
                    else{
                        $orders[$i]['final']=$orders[$i]['pamount']-$orders[$i]['amount'];
                    }
                }
                if(!$problems){
                    $olar = true;
                    try{
                        $payment->execute($execute,$paypal);
                    }
                    catch (PayPalConnectionException $e){
                        $problems[]=$GLOBALS['lang']['cantOrder'];
                        $olar = false;
                    }
                    if($olar){
                        $model = new Models\Order();
                        $model->where(array('ordered'=>0,'user_id'=>$_SESSION['user']['id']))->update(array('ordered'=>1,'time'=>time(),'paid'=>'1'));
                        $model =new Models\Transaction();
                        $model->where(array('payment_id'=>$paymentId))->update(array('ok'=>1));
                        foreach ($orders as $order){
                            $model = new Models\Product();
                            $model->where(array('id'=>$order['product_id']))->update(array('amount'=>$order['final']));
                        }
                        $problems['success']=$GLOBALS['lang']['ordered'];
                        //$GLOBALS['twig']->addGlobal('sifSay',0);
                        $_SESSION['problems'] = $problems;
                    }
                }
            }
            else{
                $problems[]=$GLOBALS['lang']['cartChangedError'];
                $_SESSION['problems'] = $problems;
                //echo $GLOBALS['lang']['cartChangedError'];
            }

        }
        global $Route;
        $Route->route('/order');
    }

    private function setPayment($paypal){

        global $db;

        $problems = array();

        $user_id=$_SESSION['user']['id'];
        $orders = $db->query("SELECT orders.id,products.amount as pamount,orders.amount,name_".lang." as name,product_id,price FROM orders JOIN products ON orders.product_id=products.id WHERE orders.user_id='$user_id' and ordered='0'")->fetchAll(PDO::FETCH_ASSOC);
        //$model = new Models\Order();
        //$orders = $model->where(array('user_id'=>$_SESSION['user']['id']))->getAll();
        if($orders){

            for($i=0;$i<count($orders);$i++){
                if($orders[$i]['pamount']<$orders[$i]['amount']){
                    $problems[] = $orders[$i]['name'].' istenilen qeder yoxdur';
                }
            }
            if(!$problems){


                $payer = new Payer();

                $payer->setPaymentMethod('paypal');

                $items = array();
                $subTotal = 0;
                $total = 0;
                foreach ($orders as $order){
                    $item = new Item();
                    $item->setName($order['name'])
                        ->setCurrency('USD')
                        ->setQuantity($order['amount'])
                        ->setPrice($order['price']);
                    $items[]=$item;
                    $subTotal += $order['price']*$order['amount'];
                    $total+=$order['price']*$order['amount'] + 4;
                }

                $itemList = new ItemList();
                $itemList->setItems($items);

                $details = new Details();
                $details->setShipping(count($items)*4)
                    ->setSubtotal($subTotal);

                $amount = new Amount();
                $amount->setCurrency('USD')
                    ->setTotal($total)
                    ->setDetails($details);

                $transaction = new Transaction();
                $transaction->setAmount($amount)
                    ->setItemList($itemList)
                    ->setDescription("Payment for RealGift")
                    ->setInvoiceNumber(uniqid());

                $url = 'http://'.$_SERVER['HTTP_HOST'].'/panel/order/finish';
                $redirectUrl = new RedirectUrls();
                $redirectUrl->setReturnUrl("$url?success=ok")
                    ->setCancelUrl("$url?success=cancel");

                $payment = new Payment();
                $payment->setIntent('sale')
                    ->setPayer($payer)
                    ->setRedirectUrls($redirectUrl)
                    ->setTransactions(array($transaction));

                try{
                    $payment->create($paypal);
                }catch (PayPalConnectionException $e){
                    die($e->getMessage());
                }

                //var_dump($payment->getApprovalLink());

                $paymentId=$payment->getId();
                $model = new Models\Transaction();
                $model->delete(array('user_id'=>$_SESSION['user']['id'],'ok'=>0));
                $model = new Models\Transaction();
                $model->post(array('user_id'=>$_SESSION['user']['id'],'payment_id'=>$paymentId,'amount'=>count($items),'money'=>$total));
                $model = new Models\Order();
                $model->where(array('user_id'=>$_SESSION['user']['id'],'ordered'=>0))->update(array('payment_id'=>$paymentId,'time'=>time()));
                header("location: {$payment->getApprovalLink()}");
            }
            else{
                $_SESSION['problems'] = $problems;
                global $Route;
                $Route->route('/order');
            }

        }
        else{
            $problems[]=$GLOBALS['lang']['noOrderError'];
            //echo $GLOBALS['lang']['noOrderError'];
            $_SESSION['problems'] = $problems;
            global $Route;
            $Route->route('/order');
        }

    }

    public function finish(){
        global $paypal;
        if($_POST['method']=='paypal'){
            $this->setPayment($paypal);
        }
        if($_POST['method']=='diger') {
            $this->h2h();
        }

        if($_GET['success']){
            if($_GET['success']=='ok'){
                $this->finishPayment($paypal);
            }
            else{
                echo $_GET['success'];
            }
        }


       // echo $GLOBALS['twig']->render("order/orders.html",array('problems'=>$problems));
    }

    private function h2h(){
        global $db;

        $problems = array();

        $user_id=$_SESSION['user']['id'];
        $orders = $db->query("SELECT orders.id,products.amount as pamount,orders.amount,name_".lang." as name,product_id,price FROM orders JOIN products ON orders.product_id=products.id WHERE orders.user_id='$user_id' and ordered='0'")->fetchAll(PDO::FETCH_ASSOC);
        //$model = new Models\Order();
        //$orders = $model->where(array('user_id'=>$_SESSION['user']['id']))->getAll();
        if($orders) {

            for ($i = 0; $i < count($orders); $i++) {
                if ($orders[$i]['pamount'] < $orders[$i]['amount']) {
                    $problems[] = $orders[$i]['name'] . ' istenilen qeder yoxdur';
                }
            }
        }
        if(!$problems){
            $total = 0;
            for($i=0;$i<count($orders);$i++){
                $total+=$orders['amount']*$orders['price']+4;
            }
            $paymentId ='H2H-'.rand(111111,999999).rand(111111,999999).rand(111111,999999);
            //echo $paymentId;
            $model = new Models\Order();
            $model->where(array('ordered'=>0,'user_id'=>$_SESSION['user']['id']))->update(array('ordered'=>1,'time'=>time(),'payment_id'=>$paymentId));
            $model =new Models\Transaction();
            $model->post(array('user_id'=>$_SESSION['user']['id'],'payment_id'=>$paymentId,'amount'=>count($orders),'money'=>$total));
            $problems['success']=$GLOBALS['lang']['ordered'];
            //$GLOBALS['twig']->addGlobal('sifSay',0);
            $_SESSION['problems'] = $problems;
        }
        global $Route;
        $Route->route('/order');
    }

    public function getCats(){
        if($get = $_GET['get']){
            $model = new \App\Models\Category();
            if($get=='cat'){
                echo json_encode($model->setSelectables(array("name_".lang." as name",'id'))->where(array('category_id'=>'0'))->getAll());
            }
            else{
                echo json_encode($model->setSelectables(array("name_".lang." as name",'id','category_id'))->where(array('category_id!'=>'0'))->getAll());
            }
        }
    }

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

    public function email(){

        if (isset($_REQUEST['email']))
//if "email" is filled out, send email
        {
//send email
            $email = $_REQUEST['email'] ;
            $subject = "Mail test" ;
            $server = $_SERVER['HTTP_HOST'];
            $message = "If you got this message, mail sending from $server is working fine :)" ;
            mail( "$email", "$subject", $message, "From: mailtest@" . $_SERVER['HTTP_HOST'] );
            echo "Message has been sent to $email";
        }
        else
//if "email" is not filled out, display the form
        {
            $server = $_SERVER[HTTP_HOST];
            echo "<h1>Mail test from $server</h1>
<form method='post' action>
Enter Your Email: <input name='email' type='text' /><br /><br />
<input type='submit' name='Submit' value='Submit' />
</form>";
        }
    }

    public function arzu(){
        if($id = (int)$_GET['delete']){
            $model = new Models\Wishlist();
            $pro = $model->getOne($id);
            if($pro['id']==$id){
                $model->delete($id);
                $message = $GLOBALS['deleted'];
            }
        }
        global $db;
        $products = $db->query("SELECT wishlists.id,product_id,name_".lang." as name,image FROM products JOIN wishlists on products.id = wishlists.product_id WHERE wishlists.user_id=".$_SESSION['user']['id'])->fetchAll(PDO::FETCH_ASSOC);

        echo $GLOBALS['twig']->render('panel/wishlist.html',array('products'=>$products,'message'=>$message));
    }

    public function wishlist(){
        if($_SESSION['user']){

            $model = new Models\Wishlist();
            if($_GET['act']=='get'){

                if ($id=(int)$_GET['id']){
                    if($_GET['say']=='all'){

                    }
                    else{;
                        $item = $model->where(array('product_id'=>$id,'user_id'=>$_SESSION['user']['id']))->getAll();
                        if($item) echo 1;
                    }
                }
            }
            else{
                if ($id = (int)$_GET['id']){

                    if($_GET['say']=='all'){

                    }
                    else{
                        $product = new Models\Product();
                        $product = $product->getOne($id);
                        if($product){
                            $item = $model->where(array('product_id'=>$id,'user_id'=>$_SESSION['user']['id']))->getAll();
                            if($item){

                                $model->delete(array(
                                    'user_id'=>$_SESSION['user']['id'],
                                    'product_id'=>$id
                                ));
                            }
                            else
                                $model->post(array(
                                    'user_id'=>$_SESSION['user']['id'],
                                    'product_id'=>$id
                                ));
                        }
                    }
                }

            }
        }
        else{
            echo 0;
        }
    }
    public function arzuYox(){
        echo  0;
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

    
}

