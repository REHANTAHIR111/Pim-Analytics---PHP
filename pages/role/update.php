<?php
ob_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

include '../../dbcon.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$dbModules = [];
$modRes = mysqli_query($conn, "SELECT * FROM `modules`");
if (!$modRes) {
    die('Module fetch failed: ' . mysqli_error($conn));
}
while ($m = mysqli_fetch_assoc($modRes)) {
    $dbModules[] = $m;
}

if (!isset($_SESSION['user_id'])) {
    header('Location: /php/pim/auth/login.php');
    exit;
}
$id = $_GET['id'] ?? $_POST['id'] ?? null;
$roleid = $_SESSION['role_id'];
$user_id = $_SESSION['user_id'];

$perm = "SELECT * FROM permission JOIN modules ON modules.id = permission.module_id WHERE role_id = $roleid AND module_id = 1";
$result = mysqli_query($conn, $perm);
$row = mysqli_fetch_assoc($result);

if($row['edit'] == 1) {
}else{
    header('Location: /php/pim/');
}

include '../../header-main.php';

$fnR = '';
$role_name = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = $_POST['role_name'];
    if (!$role) {
        $old = $_POST;
        foreach ($dbModules as $idx => $module) {
            foreach (['view_all','view','edit','create','delete'] as $perm) {
                $key = "{$perm}_{$idx}";
                $old[$key] = isset($_POST[$key]) ? 1 : 0;
            }
        }
        $_SESSION['errors']    = ['role_name' => 'Please enter Role.'];
        $_SESSION['old_input'] = $old;
        header("Location: ./edit.php?id=$id");
        exit;
    } else {
        $roleCheckQuery = "SELECT * FROM `role` WHERE `role` = '$role' AND id != $id";
        $roleCheckResult = mysqli_query($conn, $roleCheckQuery);

        if (mysqli_num_rows($roleCheckResult) > 0) {
            $_SESSION['errors'] = ['role_name' => 'This Role already exists.'];
            $_SESSION['old_input'] = $_POST;
            header("Location: ./edit.php?id=$id");
            exit;
        } else {
            mysqli_query($conn, "UPDATE `role` SET `role` = '$role'  WHERE id = $id");

            mysqli_query($conn, "DELETE FROM `permission` WHERE role_id = $id");

            foreach ($dbModules as $index => $module) {
                $view_all = isset($_POST["view_all_$index"]) ? 1 : 0;
                $view = isset($_POST["view_$index"]) ? 1 : 0;
                $edit = isset($_POST["edit_$index"]) ? 1 : 0;
                $create = isset($_POST["create_$index"]) ? 1 : 0;
                $delete = isset($_POST["delete_$index"]) ? 1 : 0;
                $module_id = $module['id'];

                $sql = "INSERT INTO `permission` (`role_id`, `module_id`, `view_all`, `view`, `edit`, `create`, `delete`) 
                        VALUES ('$id', '$module_id', '$view_all', '$view', '$edit', '$create', '$delete')";
                mysqli_query($conn, $sql);
            }
            unset($_SESSION['old_input']);
            unset($_SESSION['errors']);
        
            header('Location: /php/pim/pages/role/');
            exit();
        }
    }
}

ob_end_flush();
?>