<?php 
$data = $_POST['data'];
$namedata = $_POST['namedata'];
include ('../Core/database.php');
try{
    $db = new PDO("mysql:host=$host;dbname=$dbname",$user,$pass);
}
catch(PDOException $e){
    echo $e->getMessage();
    exit();
}

function checkReg($checkData, $db, $what){
$sth = $db->prepare("SELECT * FROM users");
$sth->execute();
$results = $sth->fetchAll();
foreach ($results as $result) {

	if($result[$what] == $checkData){
		return 1;
	}else{
		return 2;
	}
}

}
if(isset($data)){
$bb = checkReg($data, $db, "email");
}
if(isset($namedata)){
$bb = checkReg($namedata, $db, "username");
}
echo $bb;
?>