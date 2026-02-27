<?php
include "../config/database.php";

$university_id = $_POST["e"];
$newpw = $_POST["n"];
$retypepw = $_POST["r"];
$vcode = $_POST["v"];

if($newpw != $retypepw){
    echo ("Password does not match.");
    exit();
}

// Add password strength validation
if(strlen($newpw) < 6){
    echo ("Password must be at least 6 characters long.");
    exit();
}

$rs = Database::search("SELECT * FROM `lab_user` WHERE `university_id`='".$university_id."' AND `verification_code`='".$vcode."'");
$num = $rs->num_rows;

if($num == 1){
    // Hash the password for security
    $hashed_password = password_hash($retypepw, PASSWORD_DEFAULT);
    
    Database::iud("UPDATE `lab_user` SET `password_user`='".$hashed_password."' WHERE `university_id`='".$university_id."'");
    
    // Clear the verification code after successful reset
    Database::iud("UPDATE `lab_user` SET `verification_code`= NULL WHERE `university_id`='".$university_id."'");
    
    echo ("success");

}else{
    echo ("Invalid University ID or Verification Code");
}
?>