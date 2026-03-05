<?php
session_start();
include "../config/database.php";

// Get form data
$university_id = $_POST["university_id"] ?? '';
$password = $_POST["password"] ?? '';
$remember_me = $_POST["remember_me"] ?? '';

// Convert university_id to uppercase to match database        
$university_id = strtoupper(trim($university_id));

if(empty($university_id)){
    echo ("Please Enter Your University ID.");
}else if(empty($password)){
    echo ("Please Enter Your Password.");
}else{

    // Search for user with the provided university_id (case sensitive now since we converted to uppercase)
    $result = Database::search("SELECT * FROM `lab_user` WHERE `university_id`='" . $university_id . "'");
    $count = $result->num_rows;

    if($count == 1){
        
        $user = $result->fetch_assoc();
        
        // Verify password
        if(password_verify($password, $user['password_user'])){
            
            echo ("success");
            $_SESSION["user"] = $user;

            // Handle Remember Me
            if($remember_me == "true" || $remember_me == "1"){
                
                // Set cookies for 1 year
                setcookie("university_id", $university_id, time()+(60*60*24*365), "/");
                setcookie("password", $password, time()+(60*60*24*365), "/");
                
            }else{
                // Clear cookies if they exist
                setcookie("university_id", "", time()-3600, "/");
                setcookie("password", "", time()-3600, "/");
            }

        }else{
            echo ("Invalid University ID or Password.");
        }

    }else{
        echo ("Invalid University ID or Password.");
    }

}
?>