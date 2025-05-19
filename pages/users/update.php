<?php
ob_start();
include '../../dbcon.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: /php/pim/auth/login.php');
    exit;
}

$roleid = $_SESSION['role_id'];
$user_id = $_SESSION['user_id'];

$perm = "SELECT * FROM permission JOIN modules ON modules.id = permission.module_id WHERE role_id = $roleid AND module_id = 2";
$result = mysqli_query($conn, $perm);
$row = mysqli_fetch_assoc($result);

if($row['edit'] == 1) {
}else{
    header('Location: /php/pim/');
}

include '../../header-main.php';

if (headers_sent()) {
    error_log("Headers already sent. Cannot redirect.");
    exit;
}

$successCheck = '';
$firstName = '';
$lastName = '';
$phone = null;
$email = '';
$dob = '';
$gender = '';
$password = '';
$confirm_password = '';
$status = 0;
$role_id = null;
$fnR = '';
$lnR = '';
$pnR = '';
$emR = '';
$dbR = '';
$gdR = '';
$rlR = '';

$vfn = '';
$vln = '';
$vpn = '';
$vem = '';
$vdb = '';
$vgd = '';
$vpw = '';
$vcp = '';
$vrl = '';
$ex1 = '';
$ex2 = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $successCheck = false; 
    $userId = isset($_POST['id']) ? (int)$_POST['id'] : (isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : null);

    $firstName = $_POST['fname'];
    $lastName = $_POST['lname'];
    $phone = $_POST['phn'];
    $email = $_POST['email'];
    $dob = $_POST['dob'];
    $gender = isset($_POST['gender']) ? (int)$_POST['gender'] : null;

    $password = $_POST['password'];
    $pass = md5($password);
    $confirm_password = $_POST['cpassword'];
    $status = isset($_POST['status']) ? 1 : 0;
    $role_id = isset($_POST['selectRole']) ? (int)$_POST['selectRole'] : null;

    if (!$firstName) $errors[] = 'Firstname, ';
    if (!$lastName) $errors[] = 'Lastname ';
    if (!$phone) $errors[] = 'Phone, ';
    if (!$email) $errors[] = 'Email, ';

    if (!$firstName || !$lastName || !$phone || !$email) {
        $fnR = !$firstName ? 'Please enter First name.' : '';
        $lnR = !$lastName ? 'Please enter Last name.' : '';
        $pnR = !$phone ? 'Please enter Phone.' : '';
        $emR = !$email ? 'Please enter an Email' : '';
    }
    if ($firstName || $lastName || $phone || $email || $password || $confirm_password) {
        $successCheck1 = true;
        $successCheck2 = true;
        $successCheck3 = true;
        $successCheck4 = true;
        $successCheck5 = true;
        $successCheck6 = true;
        $successCheck7 = true;

        if ($firstName && !filter_var($firstName, FILTER_VALIDATE_REGEXP, array("options" => array("regexp" => "/^[a-zA-Z0-9\s'-]{3,}$/")))) {
            $vfn = 'Please enter valid First name';
            $successCheck1 = false;
        } elseif (!$firstName) {
            $successCheck1 = false;
        }
        if ($lastName && !filter_var($lastName, FILTER_VALIDATE_REGEXP, array("options" => array("regexp" => "/^[a-zA-Z0-9\s'-]{3,}$/")))) {
            $vln = 'Please enter valid Last name';
            $successCheck2 = false;
        } elseif (!$lastName) {
            $successCheck1 = false;
        }
        if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $vem = 'Please enter valid Email';
            $successCheck3 = false;
        } elseif (!$email) {
            $successCheck1 = false;
        }
        if ($phone && !preg_match("/^[0-9]{11,13}$/", $phone)) {
            $vpn = 'Please enter valid Phone Number.';
            $successCheck4 = false;
        } elseif (!$phone) {
            $successCheck1 = false;
        }
        if ($password && !preg_match('/^[a-zA-Z0-9._%+-@]{8,16}+$/', $password)) {
            $vpw = 'Please enter valid password';
            $successCheck5 = false;
        }
        if ($confirm_password && $confirm_password != $password) {
            $vcp = 'Password does not Matched!';
            $successCheck6 = false;
        }

        if ($firstName && $lastName && $phone && $email && $password && $confirm_password) {
            $checkUser = 'SELECT email FROM users WHERE email="' . $email . '" AND id != ' . $userId . ' ';
            $result = mysqli_query($conn, $checkUser);
            $row = mysqli_fetch_assoc($result);

            $subcheck1 = true;
            if (isset($row['email'])) {
                $ex1 = $row['email'] == $email ? "That email already exists." : '';
                $subcheck1 = false;
            }

            if ($subcheck1 == false) {
                $successCheck7 = false;
            }
        }

        if ($firstName && $lastName && $phone && $email) {
            $checkUser = 'SELECT phone_number FROM users WHERE phone_number = "' . $phone . '" AND id != ' . $userId . ' ';
            $result = mysqli_query($conn, $checkUser);
            $row = mysqli_fetch_assoc($result);

            $subcheck2 = true;
            if (isset($row['phone_number'])) {
                $ex2 = $row['phone_number'] == $phone ? "This number already exists." : '';
                $subcheck2 = false;
            }

            if ($subcheck2 == false) {
                $successCheck7 = false;
            }
        }

        if ($successCheck1 && $successCheck2 && $successCheck3 && $successCheck4 && $successCheck5 && $successCheck6 && $successCheck7) {
            $successCheck = true;
        }
    }


    if ($successCheck == true) {
        $sql = "UPDATE `users` 
                    SET `first_name` = '$firstName', 
                        `last_name` = '$lastName', 
                        `phone_number` = '$phone', 
                        `email` = '$email', 
                        `date_of_birth` = '$dob', 
                        `gender` = $gender,";
                        if (!empty($password)) {
                            $sql .= " `password` = '$pass',";
                        }
                        $sql .= " `role` = $role_id,
                        `status` = $status 
                    WHERE `id` = $userId";
        $result = mysqli_query($conn, $sql);
        if ($result) {
            header('Location: /php/pim/pages/users/');
            exit;
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    } else {
        $_SESSION['errors'] = [
            'fnR' => $fnR ?? '',
            'lnR' => $lnR ?? '',
            'pnR' => $pnR ?? '',
            'emR' => $emR ?? '',
            'vfn' => $vfn ?? '',
            'vln' => $vln ?? '',
            'vpn' => $vpn ?? '',
            'vem' => $vem ?? '',
            'vpw' => $vpw ?? '',
            'vcp' => $vcp ?? '',
            'ex1' => $ex1 ?? '',
            'ex2' => $ex2 ?? '',
        ];
    
        $_SESSION['old'] = [
            'fname' => $firstName,
            'lname' => $lastName,
            'phn' => $phone,
            'email' => $email,
            'dob' => $dob,
            'gender' => $gender,
            'status' => $status,
            'selectRole' => $role_id,
        ];
    
        header("Location: ./edit.php?id=$userId");
        exit;
    }
}

ob_end_flush();
?>