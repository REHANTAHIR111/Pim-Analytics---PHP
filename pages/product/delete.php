<?php
include "../../dbcon.php";

if ($_SERVER["REQUEST_METHOD"] === "POST" || $_SERVER["REQUEST_METHOD"] === "GET") {
    $ids = [];
    if (isset($_POST["ids"])) {
        $ids = $_POST["ids"];
    } elseif (isset($_GET["id"])) {
        $ids = [$_GET["id"]];
    }

    if (!empty($ids)) {
        $ids = array_map('intval', $ids);
        $idList = implode(',', $ids);

        $relatedTables = [
            'key_feature' => 'product_id',
            'specification_heading' => 'product_id',
            'specification_value' => 'product_id',
            'upsale_items' => 'product_id',
            'product_brands' => 'product_id',
            'product_related_categories' => 'product_id',
            'productfaqs' => 'product_id',
            'product_tags' => 'product_id',
            'product_categories' => 'product_id',
            'product_image_gallery' => 'product_id'
        ];

        foreach ($relatedTables as $table => $column) {
            mysqli_query($conn, "DELETE FROM `$table` WHERE `$column` IN ($idList)");
        }

        mysqli_query($conn, "DELETE FROM product WHERE id IN ($idList)");

        header("Location: /php/pim/pages/product/");
        exit;
    } else {
        echo "No product selected for deletion.";
    }
} else {
    echo "Invalid request.";
}
?>