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
$id = '';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
        $id = (int)$_GET['id'];
    } else {
        echo "
            <div class='relative top-20 bg-danger p-4 rounded shadow-md shadow-danger text-white text-lg' style='width: 436px; left:32%;'>
                <span class='font-bold'>Warning:</span> <br>
                Please provide a Valid ID!
            </div>
        ";
        exit;
    }
} else {
    if (isset($_POST['id']) && is_numeric($_POST['id'])) {
        $id = (int)$_POST['id'];
    } else {
        echo "
            <div class='relative top-20 bg-danger p-4 rounded shadow-md shadow-danger text-white text-lg' style='width: 436px; left:32%;'>
                <span class='font-bold'>Warning:</span> <br>
                Please provide a Valid ID!
            </div>
        ";
        exit;
    }
}

include '../../header-main.php';
$rne = '';
$rna = '';
$est = '';

$old = $_POST;
$old['status'] = isset($_POST['status']) ? '1' : '0';
$errors = [];

if (!$old['name']) $errors['name'] = 'Enter Brand name (EN)';
if (!$old['nameAr']) $errors['nameAr'] = 'Enter Brand name (AR)';
if (!isset($old['sorting']) || $old['sorting'] === '') {
    $errors['sorting'] = 'Enter Sorting';
}

if (!empty($errors)) {
    $_SESSION['old'] = $old;
    $_SESSION['errors'] = $errors;
    header('Location: ./edit.php?id='.$id.'');
    exit;
}

if (isset($_POST['submit']) || isset($_POST['save'])) {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : (isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : null);
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
    $selectCat = isset($_POST['selectCat']) ?? [];
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
        $sql = "UPDATE `brand` SET 
            slug = '$slug',
            sorting = '$sorting',
            name = '$name',
            h1_en = '$h1',
            description = '$brand_description',
            description_arabic = '$brand_description_arabic',
            name_arabic = '$nameAr',
            h1_arabic = '$h1Ar',
            meta_title = '$metatitle',
            meta_tags = '$metatags',
            meta_description = '$metadescription',
            meta_title_arabic = '$metatitleAr',
            meta_tags_arabic = '$metatagsAr',
            meta_description_arabic = '$metadescriptionAr',
            content_title = '$contenttitle',
            content_description = '$contentdescription',
            content_title_arabic = '$contenttitleAr',
            content_description_arabic = '$contentdescriptionAr',
            status = $status,
            popular_brand = $popular_brand,
            show_in_front = $show_in_front,
            mobile_image = '$image_mobile',
            website_image = '$image_website'
        WHERE id = $id";
    
        if (mysqli_query($conn, $sql)) {
            $brandCat = $_POST['selectCat'] ?? null;
            mysqli_query($conn, "DELETE FROM brand_categories WHERE brand_id = '$id'");
            if (!empty($brandCat)) {
                foreach ($brandCat as $items_id) {
                    $items_id = mysqli_real_escape_string($conn, $items_id);
                    $id_safe = mysqli_real_escape_string($conn, $id);
                    $insertBrandSql = "INSERT INTO brand_categories (category_id, brand_id) VALUES ('$items_id', '$id_safe')";
                    mysqli_query($conn, $insertBrandSql);
                }
            }
            
            if(isset($_POST['save'])){
                header("Location: ".$_SERVER['HTTP_REFERER']."");
            }else{
                header("Location: /php/pim/pages/brand/");
                exit;
            }
        }
    }    
}
?>