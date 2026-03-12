<?php

$password = "";

// Hash the password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Display the hashed password
echo "Original Password: " . $password . "<br>";
echo "Hashed Password: " . $hashed_password;

?>