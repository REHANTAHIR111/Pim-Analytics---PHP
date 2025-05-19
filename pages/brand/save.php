<?php
ob_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

include '../../dbcon.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: /php/pim/auth/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

$old = $_POST;
$errors = [];

$rne = $rna = $est = '';

if (!$old['name']) $errors['name'] = 'Enter Brand name (EN)';
if (!$old['nameAr']) $errors['nameAr'] = 'Enter Brand name (AR)';
if (!$old['sorting']) $errors['sorting'] = 'Enter Sorting';

if (!empty($errors)) {
    $_SESSION['old'] = $old;
    $_SESSION['errors'] = $errors;
    header('Location: ./add.php');
    exit;
}

if (isset($_POST['submit']) || isset($_POST['save'])) {

    $image_mobile = mysqli_real_escape_string($conn, $_POST['image_mobile'] ?? '');
    $image_website = mysqli_real_escape_string($conn, $_POST['image_website'] ?? '');
    $slug = mysqli_real_escape_string($conn, basename($_POST['slug'] ?? ''));
    $sorting = mysqli_real_escape_string($conn, $_POST['sorting'] ?? '');
    $name = mysqli_real_escape_string($conn, $_POST['name'] ?? '');
    $h1 = mysqli_real_escape_string($conn, $_POST['h1'] ?? '');
    $brand_description = mysqli_real_escape_string($conn, $_POST['brand_description'] ?? '');
    $brand_description_arabic = mysqli_real_escape_string($conn, $_POST['brand_descriptionAr'] ?? '');
    $nameAr = mysqli_real_escape_string($conn, $_POST['nameAr'] ?? '');
    $h1Ar = mysqli_real_escape_string($conn, $_POST['h1Ar'] ?? '');
    $metatitle = mysqli_real_escape_string($conn, $_POST['metatitle'] ?? '');
    $metatags = mysqli_real_escape_string($conn, $_POST['metatags'] ?? '');
    $metadescription = mysqli_real_escape_string($conn, $_POST['metadescription'] ?? '');
    $metatitleAr = mysqli_real_escape_string($conn, $_POST['metatitleAr'] ?? '');
    $metatagsAr = mysqli_real_escape_string($conn, $_POST['metatagsAr'] ?? '');
    $metadescriptionAr = mysqli_real_escape_string($conn, $_POST['metadescriptionAr'] ?? '');
    $contenttitle = mysqli_real_escape_string($conn, $_POST['contenttitle'] ?? '');
    $contentdescription = mysqli_real_escape_string($conn, $_POST['contentdescription'] ?? '');
    $contenttitleAr = mysqli_real_escape_string($conn, $_POST['contenttitleAr'] ?? '');
    $contentdescriptionAr = mysqli_real_escape_string($conn, $_POST['contentdescriptionAr'] ?? '');
    $status = isset($_POST['status']) ? 1 : 0;
    $popular_brand = isset($_POST['this_is_popular_brand']) ? 1 : 0;
    $show_in_front = isset($_POST['show_in_front']) ? 1 : 0;
    $selectSubtag = isset($_POST['selectCat']) ?? [];
    $brand_id = mysqli_insert_id($conn);    

    $errors = [];

    if (!$name) $errors[] = 'Brand Name, ';
    if (!$nameAr) $errors[] = 'Brand Name - Ar, ';
    if (!$sorting) $errors[] = 'Sorting.';

    if (!$name || !$nameAr || !$sorting) {
        $rne = !$name ? 'Please fill Name.' : '';
        $rna = !$nameAr ? 'Please fill Name Arabic.' : '';
        $est = !$sorting ? 'Please fill Sorting.' : '';
    } else {
        $sql = "INSERT INTO `brand` (
            slug, sorting, name, h1_en, description, description_arabic, name_arabic, h1_arabic, 
            meta_title, meta_tags, meta_description, meta_title_arabic, meta_tags_arabic, 
            meta_description_arabic, content_title, content_description, 
            content_title_arabic, content_description_arabic, status, popular_brand, show_in_front,
            mobile_image, website_image ,creator_id
        ) VALUES (
            '$slug', '$sorting', '$name', '$h1', '$brand_description', '$brand_description_arabic', 
            '$nameAr', '$h1Ar', '$metatitle', '$metatags', '$metadescription', 
            '$metatitleAr', '$metatagsAr', '$metadescriptionAr', '$contenttitle', 
            '$contentdescription', '$contenttitleAr', '$contentdescriptionAr', $status, $popular_brand,
            $show_in_front, '$image_mobile', '$image_website', '$user_id'
        )";
    
        if (mysqli_query($conn, $sql)) {
            $brand_id = mysqli_insert_id($conn);
            if (isset($_POST['selectCat']) && !empty($_POST['selectCat'])) {
                foreach ($_POST['selectCat'] as $categoryId) {
                    $filter_id = mysqli_real_escape_string($conn, $categoryId);
                    $brandCat_id = mysqli_real_escape_string($conn, $brand_id);
                    $insertBrCatSql = "INSERT INTO brand_categories (category_id, brand_id) VALUES ('$filter_id', '$brandCat_id')";
                    mysqli_query($conn, $insertBrCatSql);
                }
            }   
            unset($_SESSION['old'], $_SESSION['errors']);
    
            if (isset($_POST['save'])) {
                header('Location: /php/pim/pages/brand/edit.php?id=' . $brand_id);
                exit;
            } else {
                header('Location: /php/pim/pages/brand/');
                exit;
            }
        }
    }    
}
ob_end_flush();
?>