<?php

$email = $_POST['email'];
$password = $_POST['password'];

if($email =="david@david.com" && $password=="123"){
    header("Location: /views/profile.html");
}else{
    header("Location: /views/login.html");
}
?>