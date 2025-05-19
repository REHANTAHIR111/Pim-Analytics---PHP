<?php
    ob_start();
    ini_set('display_errors', 1);
    error_reporting(E_ALL);

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $user_id = $_SESSION['user_id'];

    include '../../dbcon.php';

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $role = $_POST['role_name'];

        if (!$role) {
            $fnR = 'Please enter Role.';
            $_SESSION['fnR'] = $fnR;
            $_SESSION['old_input'] = $_POST;
            $_SESSION['errors'] = $errors;
            header("Location: ./add.php");
            exit;
        } else {
            // Check if the role already exists in the database
            $checkRoleQuery = "SELECT * FROM `role` WHERE `role` = '$role'";
            $checkRoleResult = mysqli_query($conn, $checkRoleQuery);

            if (mysqli_num_rows($checkRoleResult) > 0) {
                $fnR = 'This Role Already Exists.';
                $_SESSION['fnR'] = $fnR;
                $_SESSION['old_input'] = $_POST;
                $_SESSION['errors'] = ['Role Name!'];
                header("Location: ./add.php");
                exit;
            } else {
                $sql = "INSERT INTO `role` (`role`, `creator_id`) VALUES ('$role', '$user_id')";
                $result = mysqli_query($conn, $sql);
                $role_id = mysqli_insert_id($conn);

                $modules = [];
                $moduleQuery = "SELECT * FROM `modules`";
                $moduleResult = mysqli_query($conn, $moduleQuery);
                while ($moduleRow = mysqli_fetch_assoc($moduleResult)) {
                    $modules[] = $moduleRow;
                }

                foreach ($modules as $index => $module) {
                    $view_all = isset($_POST["view_all_$index"]) ? 1 : 0;
                    $view = isset($_POST["view_$index"]) ? 1 : 0;
                    $edit = isset($_POST["edit_$index"]) ? 1 : 0;
                    $create = isset($_POST["create_$index"]) ? 1 : 0;
                    $delete = isset($_POST["delete_$index"]) ? 1 : 0;

                    $module_id = $module['id'];
                    $permission_sql = "INSERT INTO `permission` (`role_id`, `module_id`, `view_all`, `view`, `edit`, `create`, `delete`) VALUES ('$role_id', '$module_id', '$view_all', '$view', '$edit', '$create', '$delete')";
                    mysqli_query($conn, $permission_sql);
                }

                header('Location: /php/pim/pages/role/');
                exit();
            }
        }
    }

    ob_end_flush();
?>