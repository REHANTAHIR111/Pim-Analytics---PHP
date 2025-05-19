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

if($row['create'] == 1) {
}else{
    header('Location: /php/pim/');
}

$successCheck = false;
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
$pwR = '';
$cpR = '';
$rlR = '';

// Validation variables
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
    // Database connection

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

    $errors = [];

    if (!$firstName) $errors[] = 'Firstname';
    if (!$lastName) $errors[] = 'Lastname';
    if (!$phone) $errors[] = 'Phone';
    if (!$email) $errors[] = 'Email';
    if (!$password) $errors[] = 'Password';
    if (!$confirm_password) $errors[] = 'verify Password';

    if (!$firstName || !$lastName || !$phone || !$email || !$password || !$confirm_password) {
        $fnR = !$firstName ? 'Please enter First name.' : '';
        $lnR = !$lastName ? 'Please enter Last name.' : '';
        $pnR = !$phone ? 'Please enter Phone.' : '';
        $emR = !$email ? 'Please enter an Email' : '';
        $pwR = !$password ? 'Please enter Password!' : '';
        $cpR = !$confirm_password ? 'Please verify Password!' : '';
    }

    if ($firstName || $lastName || $phone || $email || $password || $confirm_password) {
        $successCheck1 = true;
        $successCheck2 = true;
        $successCheck3 = true;
        $successCheck4 = true;
        $successCheck5 = true;
        $successCheck6 = true;
        $successCheck7 = true;

        // Validation for each field
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
        } elseif (!$password) {
            $successCheck1 = false;
        }
        if ($confirm_password && $confirm_password != $password) {
            $vcp = 'Password does not Matched!';
            $successCheck6 = false;
        } elseif (!$confirm_password) {
            $successCheck1 = false;
        }

        // Check if email or phone already exists
        if ($firstName && $lastName && $phone && $email && $password && $confirm_password) {
            $checkUser = 'SELECT email FROM users WHERE email="' . $email . '"';
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

        // Check if phone number exists
        if ($firstName && $lastName && $phone && $email && $password && $confirm_password) {
            $checkUser = 'SELECT phone_number FROM users WHERE phone_number = "' . $phone . '"';
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

    // ...existing code...

    if ($successCheck === true) {
        $sql = 'INSERT INTO `users` (`first_name`, `last_name`, `phone_number`, `email`, `date_of_birth`, `gender`, `password`, `role`, `creator_id`, `status`) 
                VALUES ("' . $firstName . '", "' . $lastName . '", "' . $phone . '", "' . $email . '", "' . $dob . '", "' . $gender . '", "' . $pass . '", "' . $role_id . '", "' . $user_id . '", "' . $status . '")';

        $result = mysqli_query($conn, $sql);
        if ($result) {
            // Redirect to the users list page on success
            header('Location: /php/pim/pages/users/');
            exit;
        } else {
            // Handle database error
            $_SESSION['errors'] = ['Failed to save user. Please try again.'];
            $_SESSION['old'] = [
                'fname' => $firstName,
                'lname' => $lastName,
                'phn' => $phone,
                'email' => $email,
                'dob' => $dob,
                'gender' => $gender,
                'status' => $status,
                'selectRole' => $role_id
            ];
            header('Location: ./add.php');
            exit;
        }
    } else {
        $_SESSION['errors'] = $errors;
        $_SESSION['old'] = [
            'fname' => $firstName,
            'lname' => $lastName,
            'phn' => $phone,
            'email' => $email,
            'dob' => $dob,
            'gender' => $gender,
            'status' => $status,
            'selectRole' => $role_id
        ];
        $_SESSION['validation'] = [
            'fnR' => $fnR,
            'lnR' => $lnR,
            'pnR' => $pnR,
            'emR' => $emR,
            'pwR' => $pwR,
            'cpR' => $cpR,
            'vem' => $vem,
            'vpn' => $vpn,
            'vfn' => $vfn,
            'vln' => $vln,
            'vpw' => $vpw,
            'vcp' => $vcp,
            'ex1' => $ex1,
            'ex2' => $ex2
        ];
        header('Location: ./add.php');
        exit;
    }
}

// End output buffering and send output
ob_end_flush();
?>