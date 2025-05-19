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

$user_id = $_SESSION['user_id'];

include '../../header-main.php';

$rne = '';
$rna = '';
$erl = '';

$old = $_POST;
$errors = [];

if (!$old['name']) $errors['name'] = 'Enter Category name (EN)';
if (!$old['nameAr']) $errors['nameAr'] = 'Enter Category name (AR)';
if (!$old['redirection_link']) $errors['redirection_link'] = 'Enter Redirection Link';

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
    $category_description = mysqli_real_escape_string($conn, $_POST['category_description'] ?? '');
    $category_description_arabic = mysqli_real_escape_string($conn, $_POST['category_descriptionAr'] ?? '');
    $nameAr = mysqli_real_escape_string($conn, $_POST['nameAr'] ?? '');
    $h1Ar = mysqli_real_escape_string($conn, $_POST['h1Ar'] ?? '');
    $metatitle = mysqli_real_escape_string($conn, $_POST['metatitle'] ?? '');
    $metatags = mysqli_real_escape_string($conn, $_POST['metatags'] ?? '');
    $meta_canonical = mysqli_real_escape_string($conn, $_POST['meta_canonical'] ?? '');
    $metadescription = mysqli_real_escape_string($conn, $_POST['metadescription'] ?? '');
    $metatitleAr = mysqli_real_escape_string($conn, $_POST['metatitleAr'] ?? '');
    $metatagsAr = mysqli_real_escape_string($conn, $_POST['metatagsAr'] ?? '');
    $meta_canonicalAr = mysqli_real_escape_string($conn, $_POST['meta_canonicalAr'] ?? '');
    $metadescriptionAr = mysqli_real_escape_string($conn, $_POST['metadescriptionAr'] ?? '');
    $contenttitle = mysqli_real_escape_string($conn, $_POST['contenttitle'] ?? '');
    $contentdescriptipon = mysqli_real_escape_string($conn, $_POST['contentdescription'] ?? '');
    $contenttitleAr = mysqli_real_escape_string($conn, $_POST['contenttitleAr'] ?? '');
    $contentdescriptiponAr = mysqli_real_escape_string($conn, $_POST['contentdescriptionAr'] ?? '');
    $status = isset($_POST['status']) ? 1 : 0;
    $not_for_export = isset($_POST['not_for_export']) ? 1 : 0;
    $show_in_menu = isset($_POST['show_in_menu']) ? 1 : 0;
    $show_on_arabyads = isset($_POST['show_on_arabyads']) ? 1 : 0;
    $no_follow = isset($_POST['no_follow']) ? 1 : 0;
    $no_index = isset($_POST['no_index']) ? 1 : 0;
    $redirection_link = mysqli_real_escape_string($conn, $_POST['redirection_link'] ?? '');
    $selltype = mysqli_real_escape_string($conn, $_POST['selltype'] ?? '');
    $value = mysqli_real_escape_string($conn, $_POST['value'] ?? '');
    $selectSubtag = isset($_POST['selectSubtag']) ?? [];
    $upload_icon = mysqli_real_escape_string($conn, $_POST['upload_icon'] ?? '');
    $brand_link = mysqli_real_escape_string($conn, $_POST['brand_link'] ?? '');
    $image_link = mysqli_real_escape_string($conn, $_POST['image_link'] ?? '');
    $category_id = mysqli_insert_id($conn);
    $parent_category_id = $_POST['selectCat'] ?? '';
    $parent_category = '';

    if (!empty($parent_category_id)) {
        $result = mysqli_query($conn, "SELECT name FROM productcategories WHERE id = '" . mysqli_real_escape_string($conn, $parent_category_id) . "'");
        if ($row = mysqli_fetch_assoc($result)) {
            $parent_category = mysqli_real_escape_string($conn, $row['name']);
        }
    }

    if (!$name) $errors[] = 'Category Name, ';
    if (!$nameAr) $errors[] = 'Category Name - Ar, ';
    if (!$redirection_link) $errors[] = 'Link.';

    if (!$name || !$nameAr || !$redirection_link) {
        $rne = !$name ? 'Please fill Name.' : '';
        $rna = !$nameAr ? 'Please fill Name Arabic.' : '';
        $erl = !$redirection_link ? 'Please Enter Link.' : '';
    } else {
        $sql = "INSERT INTO `productcategories` (
            slug, sorting, name, h1_en, description, description_arabic, name_arabic, h1_arabic, 
            meta_title, meta_tags, meta_canonical, meta_description, meta_title_arabic, meta_tags_arabic, 
            meta_canonical_arabic, meta_description_arabic, content_title, content_description, 
            content_title_arabic, content_description_arabic, status, not_for_export, show_in_menu, 
            show_on_arabyads, nofollow_analytics, noindex_analytics, redirection_link, sell_type, 
            value, parent_category, icon, brand_link, image_link, mobile_image, website_image ,creator_id
        ) VALUES (
            '$slug', '$sorting', '$name', '$h1', '$category_description', '$category_description_arabic', 
            '$nameAr', '$h1Ar', '$metatitle', '$metatags', '$meta_canonical', '$metadescription', 
            '$metatitleAr', '$metatagsAr', '$meta_canonicalAr', '$metadescriptionAr', '$contenttitle', 
            '$contentdescriptipon', '$contenttitleAr', '$contentdescriptiponAr', $status, $not_for_export, 
            $show_in_menu, $show_on_arabyads, $no_follow, $no_index, '$redirection_link', '$selltype', 
            '$value', '$parent_category', '$upload_icon', '$brand_link', '$image_link', '$image_mobile',
            '$image_website', '$user_id'
        )";

        if (mysqli_query($conn, $sql)) {
            $category_id = mysqli_insert_id($conn);

            if (isset($_POST['selectSubtag']) && !empty($_POST['selectSubtag'])) {
                foreach ($_POST['selectSubtag'] as $subCategoryId) {
                    $filter_id = mysqli_real_escape_string($conn, $subCategoryId);
                    $category_id = mysqli_real_escape_string($conn, $category_id);
                    $insertFilterSql = "INSERT INTO category_filter (filter_id, category_id) VALUES ('$filter_id', '$category_id')";
                    mysqli_query($conn, $insertFilterSql);
                }
            }   
            unset($_SESSION['old'], $_SESSION['errors']);
            if (isset($_POST['save'])) {
                header('Location: /php/pim/pages/category/edit.php?id=' . $category_id);
                exit;
            } else {
                header('Location: /php/pim/pages/category/');
                exit;
            }
        }

    }
}
ob_end_flush();
?>