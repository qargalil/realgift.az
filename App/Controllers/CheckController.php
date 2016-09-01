<?php 
namespace App\Controllers;
use App\Models;
use PDO;

class CheckController
{
	public function checkReg($checkData){
        $sql = "SELECT * FROM users WHERE email='$checkData'";
        $result = $db->query($sql);
        return $result;
    }
}
?>
