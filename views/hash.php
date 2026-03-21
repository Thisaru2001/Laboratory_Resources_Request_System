<?php
// hash.php
$password = "TO/2022/100Lab@123";
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

echo "output: " . $hashed_password . "\n";

?>