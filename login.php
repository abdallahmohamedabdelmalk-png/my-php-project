<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = $_POST["email"];
    $password = $_POST["password"];

    $correctEmail = "abdallah@gmail.com";
    $correctPassword = "123456";

    if ($email == $correctEmail && $password == $correctPassword) {

        header("Location:dashbord.php");
        exit;
    } else {

        echo "Invalid Email or Password";

    }
}

?>