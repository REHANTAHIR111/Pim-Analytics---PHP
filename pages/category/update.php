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
    include '../../header-main.php';
    $id = null;
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        if (isset($_GET['id']) && is_numeric($_GET['id'])) {
            $id = (int)$_GET['id'];
        }
    } else {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : null;
    }
    $old = $_POST;
    $old['status'] = isset($_POST['status']) ? '1' : '0';
    $errors = [];

    if (!$old['name']) $errors['name'] = 'Enter Category name (EN)';
    if (!$old['nameAr']) $errors['nameAr'] = 'Enter Category name (AR)';
    if (!$old['redirection_link']) $errors['redirection_link'] = 'Enter Redirection Link';

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
        $upload_icon = mysqli_real_escape_string($conn, $_POST['upload_icon'] ?? '');
        $brand_link = mysqli_real_escape_string($conn, $_POST['brand_link'] ?? '');
        $image_link = mysqli_real_escape_string($conn, $_POST['image_link'] ?? '');
        $parent_category_id = $_POST['selectCat'] ?? '';
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
            $sql = "UPDATE `productcategories` SET slug = '$slug', sorting = '$sorting', name = '$name', h1_en = '$h1', description = '$category_description', description_arabic = '$category_description_arabic', name_arabic = '$nameAr', h1_arabic = '$h1Ar', meta_title = '$metatitle', meta_tags = '$metatags', meta_canonical = '$meta_canonical', meta_description = '$metadescription', meta_title_arabic = '$metatitleAr', meta_tags_arabic = '$metatagsAr', meta_canonical_arabic = '$meta_canonicalAr', meta_description_arabic = '$metadescriptionAr', content_title = '$contenttitle', content_description = '$contentdescriptipon', content_title_arabic = '$contenttitleAr', content_description_arabic = '$contentdescriptiponAr', status = $status, not_for_export = $not_for_export, show_in_menu = $show_in_menu, show_on_arabyads = $show_on_arabyads, nofollow_analytics = $no_follow, noindex_analytics = $no_index, redirection_link = '$redirection_link', sell_type = '$selltype', value = '$value', parent_category = '$parent_category', icon = '$upload_icon', brand_link = '$brand_link', image_link = '$image_link', mobile_image = '$image_mobile', website_image = '$image_website' WHERE id = $id";
            if (mysqli_query($conn, $sql)) {
                $filterCat = $_POST['selectSubtag'] ?? null;
                mysqli_query($conn, "DELETE FROM category_filter WHERE category_id = '$id'");
                if (!empty($filterCat)) {
                    foreach ($filterCat as $items_id) {
                        $items_id = mysqli_real_escape_string($conn, $items_id);
                        $id_safe = mysqli_real_escape_string($conn, $id);
                        $insertFilterSql = "INSERT INTO category_filter (filter_id, category_id) VALUES ('$items_id', '$id_safe')";
                        mysqli_query($conn, $insertFilterSql);
                    }
                }
                if(isset($_POST['save'])){
                    header("Location: ".$_SERVER['HTTP_REFERER']."");
                }else{
                    header("Location: /php/pim/pages/category/");
                    exit;
                }
            }
        }
    }
    ob_end_flush()
?>