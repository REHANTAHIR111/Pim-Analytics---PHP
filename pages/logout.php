<?php
    session_start();
    session_unset();
    header('Location: /php/pim/auth/login.php');
?>