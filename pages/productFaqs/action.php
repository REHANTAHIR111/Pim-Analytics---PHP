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

$tiR = $qeR = $qaR = $aeR = $aaR = '';
$success = false;

$showModal = false;
$editModal = false;

$old = $_POST;
$errors = [];

if (!$old['title']) $errors['title'] = 'Enter Faq Title';
if (!$old['question_english']) $errors['question_english'] = 'Enter Question (EN)';
if (!$old['question_arabic']) $errors['question_arabic'] = 'Enter Question (AR)';
if (!$old['answer_english']) $errors['answer_english'] = 'Enter Answer (EN)';
if (!$old['answer_arabic']) $errors['answer_arabic'] = 'Enter Answer (AR)';

$editData = $_SESSION['old'] ?? []; 
$editDataDb = []; 

//edit entry in database//
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = $_POST['id'];
    $title = trim($_POST['title']);
    $question_english = trim($_POST['question_english']);
    $question_arabic = trim($_POST['question_arabic']);
    $answer_english = trim($_POST['answer_english']);
    $answer_arabic = trim($_POST['answer_arabic']);
    $status = isset($_POST['status']) ? 1 : 0;
    
    if (!$title) $tiR = 'Please enter Title, ';
    if (!$question_english) $qeR = 'Please enter Please enter Question English, ';
    if (!$question_arabic) $qaR = 'Please enter Please enter Question Arabic, ';
    if (!$answer_english) $aeR = 'Please enter Please enter Answer English, ';
    if (!$answer_arabic) $aaR = 'Please enter Please enter Answer Arabic';
    
    if ($tiR === '' && $qeR === '' && $qaR === '' && $aeR === '' && $aaR === '') {
        
        $sql = "UPDATE `product_faqs` SET `title` = '$title', `question_english` = '$question_english', `question_arabic` = '$question_arabic', `answer_english` = '$answer_english', `answer_arabic` = '$answer_arabic', `creator_id` = '$user_id', `status` = '$status' WHERE `id` = '$id'";
        $result = mysqli_query($conn, $sql);
        if ($result) {
            unset($_SESSION['old']);
            unset($_SESSION['errors']);
            header('Location: ./');
            exit;
        } else {
            $_SESSION['old'] = $_POST;
            $_SESSION['errors'] = [
                'title' => $tiR,
                'question_english' => $qeR,
                'question_arabic' => $qaR,
                'answer_english' => $aeR,
                'answer_arabic' => $aaR,
            ];
            $_SESSION['edit_mode'] = true;
            $_SESSION['edit_id'] = $id;
            header('Location: ./');
            exit;
        }
    } else {
        $_SESSION['old'] = $_POST;
        $_SESSION['errors'] = [
            'title' => $tiR,
            'question_english' => $qeR,
            'question_arabic' => $qaR,
            'answer_english' => $aeR,
            'answer_arabic' => $aaR,
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
        $title = trim($_POST['title']);
        $question_english = trim($_POST['question_english']);
        $question_arabic = trim($_POST['question_arabic']);
        $answer_english = trim($_POST['answer_english']);
        $answer_arabic = trim($_POST['answer_arabic']);

        if (!$title) $tiR = 'Please enter Title, ';
        if (!$question_english) $qeR = 'Please enter Question English, ';
        if (!$question_arabic) $qaR = 'Please enter Question Arabic, ';
        if (!$answer_english) $aeR = 'Please enter Answer English, ';
        if (!$answer_arabic) $aaR = 'Please enter Answer Arabic';

        if ($tiR === '' && $qeR === '' && $qaR === '' && $aeR === '' && $aaR === '') {
            $status = isset($_POST['status']) ? 1 : 0;

            $sql = "INSERT INTO `product_faqs` (`title`, `question_english`, `question_arabic`, `answer_english`, `answer_arabic`, `creator_id`, `status`) VALUES ('$title', '$question_english', '$question_arabic', '$answer_english', '$answer_arabic', '$user_id', '$status')";
            $result = mysqli_query($conn, $sql);

            if ($result) {
                unset($_SESSION['old']);
                unset($_SESSION['errors']);
                header('Location: ./');
                exit;
            } else {
                $_SESSION['old'] = $_POST;
                $_SESSION['errors'] = [
                    'title' => $tiR,
                    'question_english' => $qeR,
                    'question_arabic' => $qaR,
                    'answer_english' => $aeR,
                    'answer_arabic' => $aaR,
                ];
                $_SESSION['add_mode'] = true;
                header('Location: ./');
                exit;
            }
        } else {
            $_SESSION['old'] = $_POST;
            $_SESSION['errors'] = [
                'title' => $tiR,
                'question_english' => $qeR,
                'question_arabic' => $qaR,
                'answer_english' => $aeR,
                'answer_arabic' => $aaR,
            ];
            $_SESSION['add_mode'] = true;
            header('Location: ./');
            exit;
        }
    }
    $editData = [];
    $tiR = $qeR = $qaR = $aeR = $aaR = '';

    if (isset($_SESSION['edit_mode']) && $_SESSION['edit_mode'] === true) {
        $editModal = true;
        $editData = $_SESSION['old'] ?? [];
        $tiR = $_SESSION['errors']['title'] ?? '';
        $qeR = $_SESSION['errors']['question_english'] ?? '';
        $qaR = $_SESSION['errors']['question_arabic'] ?? '';
        $aeR = $_SESSION['errors']['answer_english'] ?? '';
        $aaR = $_SESSION['errors']['answer_arabic'] ?? '';
        unset($_SESSION['edit_mode']);
    }

    if (isset($_SESSION['add_mode']) && $_SESSION['add_mode'] === true) {
        $addModal = true;
        $addData = $_SESSION['old'] ?? [];
        $tiR = $_SESSION['errors']['title'] ?? '';
        $qeR = $_SESSION['errors']['question_english'] ?? '';
        $qaR = $_SESSION['errors']['question_arabic'] ?? '';
        $aeR = $_SESSION['errors']['answer_english'] ?? '';
        $aaR = $_SESSION['errors']['answer_arabic'] ?? '';
        unset($_SESSION['add_mode']);
    }

    unset($_SESSION['old'], $_SESSION['errors']);
//entry added in database
ob_end_flush();
?>