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

$rne = $rna = $erp = $esk = $est = '';

// post Data
$status = '';
$enable_pre_product = '';
$check_in_sku_qty = '';
$not_fetch_order = '';
$vat_on_us = '';

$old = [];

$old = $_POST;

$old['type'] = $_POST['type'] ?? '1';
$old['featured_image'] = $_POST['featured_image'] ?? '';
$old['image_mobile'] = $_POST['image_mobile'] ?? '';
$old['image_website'] = $_POST['image_website'] ?? '';
foreach ([
    'status','enable_pre_product','not_fetch_order','vat_on_us',
    'best_selling_product','free_gift_product','google_merchant',
    'installation','is_bundle','low_in_stock','product_installation',
    'top_selling', 'check_in_sku_qty'
    ] as $cb) {
    $old[$cb] = isset($_POST[$cb]) ? 1 : 0;
}
foreach (['upsale_items','brand','category','product_faqs','product_tags','product_categories', 'product_brands'] as $m) {
    $old[$m] = $_POST[$m] ?? [];
}
$_SESSION['old_features'] = $_POST['features_json'];
$_SESSION['old_specGroups'] = $_POST['specifications_json'];

$errors = [];
if (!$old['productNameEn']) $errors['productNameEn'] = 'Enter product name (EN)';
if (!$old['productNameAr']) $errors['productNameAr'] = 'Enter product name (AR)';
if (!$old['regular_price']) $errors['regular_price'] = 'Enter Price';
if (!$old['sku']) $errors['sku'] = 'Enter SKU';
if (!$old['sorting']) $errors['sorting'] = 'Enter Sorting';

if (!empty($errors)) {
    $_SESSION['old_input'] = $old;
    $_SESSION['errors']    = $errors;
    header('Location: ./add.php');
    exit;
}

if (isset($_POST['submit']) || isset($_POST['save'])) {

    $name = $_POST['productNameEn'] ?? '';
    $name_arabic = $_POST['productNameAr'] ?? '';
    $short_description = $_POST['shortDescriptionEn'] ?? '';
    $short_description_arabic = $_POST['shortDescriptionAr'] ?? '';
    $product_description = $_POST['descriptionEn'] ?? '';
    $product_description_arabic = $_POST['descriptionAr'] ?? '';
    $promotion = $_POST['promotion_en'] ?? '';
    $promotion_arabic = $_POST['promotion_ar'] ?? '';
    $promotion_color = $_POST['promotion_color'] ?? '';
    $badge_left = $_POST['badge_left_en'] ?? '';
    $badge_left_arabic = $_POST['badge_left_ar'] ?? '';
    $badge_left_color = $_POST['badge_left_color'] ?? '';
    $badge_right = $_POST['badge_right_en'] ?? '';
    $badge_right_arabic = $_POST['badge_right_ar'] ?? '';
    $badge_right_color = $_POST['badge_right_color'] ?? '';
    $slug = basename($_POST['slug'] ?? '');
    $regular_price = $_POST['regular_price'] ?? '';
    $sale_price = $_POST['sale_price'] ?? '';
    $sorting = $_POST['sorting'] ?? '';
    $bundle_price = $_POST['bundle_price'] ?? '';
    $promo_price = $_POST['promo_price'] ?? '';
    $promo_title = $_POST['promo_title'] ?? '';
    $promo_title_arabic = $_POST['promo_title_ar'] ?? '';
    $notes = $_POST['notes'] ?? '';
    $sku = $_POST['sku'] ?? '';
    $mpn_flix_media = $_POST['mpn_flix_media'] ?? '';
    $mpn_flix_media_en = $_POST['mpn_flix_media_en'] ?? '';
    $mpn_flix_media_ar = $_POST['mpn_flix_media_ar'] ?? '';
    $ln_sku = $_POST['ln_sku'] ?? '';
    $quantity = $_POST['quantity'] ?? '';
    $amazon_stock = $_POST['amazon_stock'] ?? '';
    $check_in_sku_qty = $_POST['check_in_sku_qty'] ?? 0;
    $type = $_POST['type'] ?? '';
    $brand = $_POST['product_brands'] ?? '';
    $custom_badge_en = $_POST['custom_badge_en'] ?? '';
    $custom_badge_ar = $_POST['custom_badge_ar'] ?? '';
    $meta_canonical = $_POST['meta_canonical'] ?? '';
    $meta_canonicalAr = $_POST['meta_canonicalAr'] ?? '';
    $metadescription = $_POST['metadescription'] ?? '';
    $metadescriptionAr = $_POST['metadescriptionAr'] ?? '';
    $metatags = $_POST['metatags'] ?? '';
    $metatagsAr = $_POST['metatagsAr'] ?? '';
    $metatitle = $_POST['metatitle'] ?? '';
    $metatitleAr = $_POST['metatitleAr'] ?? '';
    $status = $_POST['status'] ?? 0;
    $enable_pre_product = $_POST['enable_pre_product'] ?? 0;
    $not_fetch_order = $_POST['not_fetch_order'] ?? 0;
    $vat_on_us = $_POST['vat_on_us'] ?? 0;
    $best_selling_product = $_POST['best_selling_product'] ?? 0;
    $free_gift_product = $_POST['free_gift_product'] ?? 0;
    $google_merchant = $_POST['google_merchant'] ?? 0;
    $installation = $_POST['installation'] ?? 0;
    $is_bundle = $_POST['is_bundle'] ?? 0;
    $low_in_stock = $_POST['low_in_stock'] ?? 0;
    $product_installation = $_POST['product_installation'] ?? 0;
    $top_selling = $_POST['top_selling'] ?? 0;
    $warranty = $_POST['warranty'] ?? '';
    $featured_image = $_POST['featured_image'] ?? '';
    $image_mobile = $_POST['image_mobile'] ?? '';

    $id = mysqli_insert_id($conn);

    if (!$name || !$name_arabic || !$regular_price || !$sku || !$sorting) {
        $rne = !$name ? 'Please fill Product Name.' : '';
        $rna = !$name_arabic ? 'Please fill Product Name - Ar.' : '';
        $erp = !$regular_price ? 'Please fill Price.' : '';
        $esk = !$sku ? 'Please fill SKU.' : '';
        $est = !$sorting ? 'Please fill Sorting.' : '';
    } else {
        // Perform INSERT query
        $sql = "INSERT INTO `product` (
            `name`, `name_arabic`, `short_description`, `short_description_arabic`, `product_description`, 
            `product_description_arabic`, `pormotion`, `pormotion_arabic`, `pormotion_color`, `badge_left`, 
            `badge_left_arabic`, `badge_left_color`, `badge_right`, `badge_right_arabic`, `badge_right_color`, 
            `slug`, `regular_price`, `sale_price`, `sorting`, `bundle_price`, `promo_price`, `promo_title`, 
            `promo_title_arabic`, `notes`, `sku`, `mpn_flix_media`, `mpn_flix_media_english`, `mpn_flix_media_arabic`, 
            `ln_sku`, `quantity`, `amazon_stock`, `ln_check_quantity`, `type`, `brand`, `custom_badge`, 
            `custom_badge_arabic`, `meta_title`, `meta_title_arabic`, `meta_tags`, `meta_tags_arabic`, `meta_canonical`, 
            `meta_canonical_arabic`, `meta_description`, `meta_description_arabic`, `status`, `enable_pre_order`, 
            `not_fetch_order`, `vat_on_us`, `best_selling_product`, `free_gift_product`, `low_in_stock`, `top_selling`, 
            `installation`, `is_bundle`, `product_installation`, `allow_goole_merchant`, `product_featured_image`, 
            `upload_featured_image`, `creator_id`
        ) VALUES (
            '$name', '$name_arabic', '$short_description', '$short_description_arabic', '$product_description', 
            '$product_description_arabic', '$promotion', '$promotion_arabic', '$promotion_color', '$badge_left', 
            '$badge_left_arabic', '$badge_left_color', '$badge_right', '$badge_right_arabic', '$badge_right_color', 
            '$slug', '$regular_price', '$sale_price', '$sorting', '$bundle_price', '$promo_price', '$promo_title', 
            '$promo_title_arabic', '$notes', '$sku', '$mpn_flix_media', '$mpn_flix_media_en', '$mpn_flix_media_ar', 
            '$ln_sku', '$quantity', '$amazon_stock', '$check_in_sku_qty', '$type', '$brand', '$custom_badge_en', 
            '$custom_badge_ar', '$meta_canonical', '$meta_canonicalAr', '$metadescription', '$metadescriptionAr', 
            '$metatags', '$metatagsAr', '$metatitle', '$metatitleAr', '$status', '$enable_pre_product', 
            '$not_fetch_order', '$vat_on_us', '$best_selling_product', '$free_gift_product', '$low_in_stock', 
            '$top_selling', '$installation', '$is_bundle', '$product_installation', '$google_merchant', 
            '$featured_image', '$image_mobile', '$user_id'
        )";

        if (mysqli_query($conn, $sql)) {
            $product_id = mysqli_insert_id($conn);
            // key features
            $features = json_decode($_POST['features_json'], true);

            foreach ($features as $feature) {
                $feature_en = mysqli_real_escape_string($conn, $feature['en'] ?? '');
                $feature_ar = mysqli_real_escape_string($conn, $feature['ar'] ?? '');
                $feature_img = mysqli_real_escape_string($conn, $feature['image'] ?? '');

                if($feature_en || $feature_ar || $feature_img){
                    $insert_feature = "INSERT INTO key_feature (feature, feature_arabic, feature_image, product_id) VALUES ('$feature_en', '$feature_ar', '$feature_img', '$product_id')";
                    mysqli_query($conn, $insert_feature);
                }
            }

            $specGroups = [];
            if (!empty($_POST['specifications_json'])) {
                $specGroups = json_decode($_POST['specifications_json'], true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $specGroups = [];
                }
            }

            foreach ($specGroups as $group) {
                $heading_en = mysqli_real_escape_string($conn, $group['specs_heading'] ?? '');
                $heading_ar = mysqli_real_escape_string($conn, $group['specs_headingAr'] ?? '');

                if($heading_en || $heading_ar){
                    $insert_group = "INSERT INTO specification_heading (specs_heading, specs_heading_arabic, product_id) VALUES ('$heading_en', '$heading_ar', '$product_id')";
                    mysqli_query($conn, $insert_group);
                }
                $group_id = mysqli_insert_id($conn);

                foreach ($group['specsList'] as $spec) {
                    $spec_en = mysqli_real_escape_string($conn, $spec['specs'] ?? '');
                    $spec_ar = mysqli_real_escape_string($conn, $spec['specsAr'] ?? '');
                    $value_en = mysqli_real_escape_string($conn, $spec['value'] ?? '');
                    $value_ar = mysqli_real_escape_string($conn, $spec['valueAr'] ?? '');

                    if($spec_en || $spec_ar || $value_en || $value_ar){
                        $insert_spec = "INSERT INTO specification_value (specs, specs_arabic, value, value_arabic, specs_heading_id, product_id) VALUES ('$spec_en', '$spec_ar', '$value_en', '$value_ar', '$group_id', '$product_id')";
                        mysqli_query($conn, $insert_spec);                
                    }
                }
            }

            // upsale_items
            if (!empty($_POST['upsale_items']) && isset($product_id)) {
                foreach ($_POST['upsale_items'] as $items_id) {
                    $items_id = mysqli_real_escape_string($conn, $items_id);
                    $product_id_safe = mysqli_real_escape_string($conn, $product_id);
                    $insertUpsale = "INSERT INTO upsale_items (items_id, product_id) VALUES ('$items_id', '$product_id_safe')";
                    mysqli_query($conn, $insertUpsale);
                }
            }

            //multi_brands
            if (!empty($_POST['brand']) && isset($product_id)) {
                foreach ($_POST['brand'] as $brand_id) {
                    $brand_id = mysqli_real_escape_string($conn, $brand_id);
                    $product_id_safe = mysqli_real_escape_string($conn, $product_id);
                    $insertBrand = "INSERT INTO product_brands (brand_id, product_id) VALUES ('$brand_id', '$product_id_safe')";
                    mysqli_query($conn, $insertBrand);
                }
            }
            
            //multicategries
            if (!empty($_POST['category']) && isset($product_id)) {
                foreach ($_POST['category'] as $cat_id) {
                    $cat_id_safe = mysqli_real_escape_string($conn, $cat_id);
                    $product_id_safe = mysqli_real_escape_string($conn, $product_id);
                    $insertCat = "INSERT INTO product_related_categories (category_id, product_id) VALUES ('$cat_id_safe', '$product_id_safe')";
                    mysqli_query($conn, $insertCat);
                }
            }

            //mutli_faqs
            if (!empty($_POST['product_faqs']) && isset($product_id)) {
                foreach ($_POST['product_faqs'] as $faq_id) {
                    $faq_id_safe = mysqli_real_escape_string($conn, $faq_id);
                    $product_id_safe = mysqli_real_escape_string($conn, $product_id);            
                    $insertFaq = "INSERT INTO productfaqs (faq_id, product_id) VALUES ('$faq_id_safe', '$product_id_safe')";
                    mysqli_query($conn, $insertFaq);
                }
            }

            //multi_tags
            if (!empty($_POST['product_tags']) && isset($product_id)) {
                foreach ($_POST['product_tags'] as $tag_id) {
                    $tag_id_safe = mysqli_real_escape_string($conn, $tag_id);
                    $product_id_safe = mysqli_real_escape_string($conn, $product_id);
                    $insertTag = "INSERT INTO product_tags (tag_id, product_id) VALUES ('$tag_id_safe', '$product_id_safe')";
                    mysqli_query($conn, $insertTag);
                }
            }

            //multi_categories
            if (!empty($_POST['product_categories']) && isset($product_id)) {
                foreach ($_POST['product_categories'] as $category_id) {
                    $category_id_safe = mysqli_real_escape_string($conn, $category_id);
                    $product_id_safe = mysqli_real_escape_string($conn, $product_id);
                    $insertCategory = "INSERT INTO product_categories (category_id, product_id) VALUES ('$category_id_safe', '$product_id_safe')";
                    mysqli_query($conn, $insertCategory);
                }
            }

            //image_gallery
            if (!empty($_POST['image_website']) && isset($product_id)) {
                $imageUrls = explode(',', $_POST['image_website']);
                $product_id_safe = mysqli_real_escape_string($conn, $product_id);
            
                foreach ($imageUrls as $url) {
                    $url_safe = mysqli_real_escape_string($conn, trim($url));
                    mysqli_query($conn, "INSERT INTO product_image_gallery (product_id, image_url) VALUES ('$product_id_safe', '$url_safe')");
                }
            }
            unset($_SESSION['old_input'], $_SESSION['errors'], $_SESSION['old_features'], $_SESSION['old_specGroups']);
            if (isset($_POST['save'])) {
                header('Location: /php/pim/pages/product/edit.php?id=' . $product_id);
                exit;
            } else {
                header('Location: /php/pim/pages/product/');
                exit;
            }
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    }
}
?>