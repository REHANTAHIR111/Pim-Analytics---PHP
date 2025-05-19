<?php
    ob_start();
    include '../../dbcon.php';
    
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['user_id'])) {
        header("Location: /php/pim/auth/login.php");
        exit;
    }
    
    $user_id = $_SESSION['user_id'];
    $role_id = $_SESSION['role_id'];
    $edit = false;
    $delete = false;
    
    include '../../header-main.php';
    
    $tnR = $etnR = $eanR = $anR = '';
    $success = false;

    $showModal = false;
    $editModal = false;

    $old = $_POST;
    $errors = [];
    
    if (!$old['tagNameEn']) $errors['tagNameEn'] = 'Enter Tag name (EN)';
    if (!$old['tagNameAr']) $errors['tagNameAr'] = 'Enter Tag name (AR)';

    $editData = $_SESSION['old'] ?? []; 
    $editDataDb = []; 

    //edit entry in database//
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
        $id = $_POST['id'];
        $tagNameEn = $_POST['tagNameEn'];
        $tagNameAr = $_POST['tagNameAr'];

         // Validate
        if (!$tagNameEn) $etnR = 'Please enter tag name in English';
        if (!$tagNameAr) $eanR = 'Please enter tag name in Arabic';

        if ($etnR === '' && $eanR === '') {
            $sorting = $_POST['sorting'];
            $status = isset($_POST['status']) ? 1 : 0;
            $imageLink = $_POST['imageLink'];
            $icon = $_POST['uploadIcon'];
            
            $sql = "UPDATE `tags` SET `name` = '$tagNameEn',`name_ar` = '$tagNameAr',`sorting` = '$sorting',`status` = '$status',`creator_id` = '$user_id',`image_link_app` = '$imageLink', `icon` = '$icon' WHERE id = $id";
            $result = mysqli_query($conn, $sql);
            if ($result) {
                // Reset session flags on successful edit
                unset($_SESSION['old']);
                unset($_SESSION['errors']);
                header('Location: ./');
                exit;
            } else {
                // If there's an error during edit, set flags for edit mode
                $_SESSION['old'] = $_POST;
                $_SESSION['errors'] = [
                    'tagNameEn' => $etnR,
                    'tagNameAr' => $eanR
                ];
                $_SESSION['edit_mode'] = true;  // Set flag for edit mode
                $_SESSION['edit_id'] = $id;
                header('Location: ./');
                exit;
            }
        } else {
            $_SESSION['old'] = $_POST;
            $_SESSION['errors'] = [
                'tagNameEn' => $etnR,
                'tagNameAr' => $eanR
            ];
            $_SESSION['edit_mode'] = true;
            $_SESSION['edit_id'] = $id;
            header('Location: ./');
            exit;
        }
    
    }
    //entry updated in database//
    
    
    //add entry in database//
        if (isset($_POST['save'])) {

            $tagNameEn = trim($_POST['tagNameEn']);
            $tagNameAr = trim($_POST['tagNameAr']);

            if (!$tagNameEn) $tnR = 'Please enter tag name in English';
            if (!$tagNameAr) $anR = 'Please enter tag name in Arabic';
    
            if ($tnR === '' && $anR === '') {
                $sorting = $_POST['sorting'] ?? 0;
                $uploadIcon = $_POST['uploadIcon'] ?? '';
                $imageLink = $_POST['imageLink'] ?? '';
                $status = isset($_POST['status']) ? 1 : 0;
    
                $sql = "INSERT INTO `tags` (name, name_ar, sorting, icon, image_link_app, status, creator_id) VALUES ('$tagNameEn', '$tagNameAr', '$sorting', '$uploadIcon', '$imageLink', '$status', '$user_id')";
                $result = mysqli_query($conn, $sql);
    
                if ($result) {
                    unset($_SESSION['old']);
                    unset($_SESSION['errors']);
                    header('Location: ./');
                    exit;
                } else {
                    $_SESSION['old'] = $_POST;
                    $_SESSION['errors'] = [
                        'tagNameEn' => $tnR,
                        'tagNameAr' => $anR
                    ];
                    $_SESSION['add_mode'] = true;
                    header('Location: ./');
                    exit;
                }
            } else {
                $_SESSION['old'] = $_POST;
                $_SESSION['errors'] = [
                    'tagNameEn' => $tnR,
                    'tagNameAr' => $anR
                ];
                $_SESSION['add_mode'] = true;
                header('Location: ./');
                exit;
            }
        }
        $editData = [];
        $etnR = $eanR = '';

        if (isset($_SESSION['edit_mode']) && $_SESSION['edit_mode'] === true) {
            $editModal = true;
            $editData = $_SESSION['old'] ?? [];
            $etnR = $_SESSION['errors']['tagNameEn'] ?? '';
            $eanR = $_SESSION['errors']['tagNameAr'] ?? '';
            unset($_SESSION['edit_mode']);
        }

        if (isset($_SESSION['add_mode']) && $_SESSION['add_mode'] === true) {
            $addModal = true;
            $addData = $_SESSION['old'] ?? [];
            $tnR = $_SESSION['errors']['tagNameEn'] ?? '';
            $anR = $_SESSION['errors']['tagNameAr'] ?? '';
            unset($_SESSION['add_mode']);
        }

        unset($_SESSION['old'], $_SESSION['errors']);
    //entry added in database
    ob_end_flush();
?>