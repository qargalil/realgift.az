<?php

$host = 'localhost';

$dbname = 'realgift';

$user = 'root';

$pass = '';

//Connect to server
try{
    $db = new PDO("mysql:host=$host;dbname=$dbname",$user,$pass);
}
catch(PDOException $e){
    echo $e->getMessage();
    exit();
}
