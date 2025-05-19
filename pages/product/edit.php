<?php
    include '../../dbcon.php';
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['user_id'])) {
        header('Location: /php/pim/auth/login.php');
        exit;
    }
    include '../../header-main.php';

    $old = $_SESSION['old_input'] ?? [];
    $errors = $_SESSION['errors']    ?? [];
    unset($_SESSION['old_input'], $_SESSION['errors']);
    
    $roleid = $_SESSION['role_id'];
    $user_id = $_SESSION['user_id'];

    $perm = "SELECT * FROM permission JOIN modules ON modules.id = permission.module_id WHERE role_id = $roleid AND module_id = 3";
    $result = mysqli_query($conn, $perm);
    $row = mysqli_fetch_assoc($result);
    $can_view_all = $row['view_all'];
    if ($row['create'] != 1) {
        header('Location: /php/pim/');
        exit;
    }

    $id = null;
    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
        $id = (int)$_GET['id'];
    } else {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : null;
    }

    $brquery = "SELECT * FROM permission JOIN modules ON modules.id = permission.module_id WHERE role_id = $roleid AND module_id = 5";
    $brquery_result = mysqli_query($conn, $brquery);
    $row = mysqli_fetch_assoc($brquery_result);
    $br_view_all = $row['view_all'];
    $catquery = "SELECT * FROM permission JOIN modules ON modules.id = permission.module_id WHERE role_id = $roleid AND module_id = 4";
    $catquery_result = mysqli_query($conn, $catquery);
    $row = mysqli_fetch_assoc($catquery_result);
    $cat_view_all = $row['view_all'];
    $faqquery = "SELECT * FROM permission JOIN modules ON modules.id = permission.module_id WHERE role_id = $roleid AND module_id = 7";
    $faqquery_result = mysqli_query($conn, $faqquery);
    $row = mysqli_fetch_assoc($faqquery_result);
    $faq_view_all = $row['view_all'];
    $stquery = "SELECT * FROM permission JOIN modules ON modules.id = permission.module_id WHERE role_id = $roleid AND module_id = 6";
    $stquery_result = mysqli_query($conn, $stquery);
    $row = mysqli_fetch_assoc($stquery_result);
    $st_view_all = $row['view_all'];

    if (!$id) {
        echo "
            <div class='relative top-20 bg-danger p-4 rounded shadow-md shadow-danger text-white text-lg' style='width: 436px; left:32%;'>
                <span class='font-bold'>Warning:</span> <br>
                Please provide a Valid ID!
            </div>
        ";
        exit;
    }

    $rne = '';
    $rna = '';
    $erp = '';
    $esk = '';
    $est = '';

    // Get Product Data //
    $condition = ($can_view_all == 1) ? "" : "AND product.creator_id = $user_id";
    $upsaleOptions = mysqli_query($conn, "SELECT sku, id FROM product WHERE status = 1 AND id != $id $condition");
    // Get Brand Data //
    $condition2 = ($br_view_all == 1) ? "" : "AND brand.creator_id = $user_id";
    $brands_query = mysqli_query($conn, "SELECT id, name FROM brand WHERE status = 1 $condition2");
    // Get Category Data //
    $condition3 = ($cat_view_all == 1) ? "" : "AND productcategories.creator_id = $user_id";
    $categories_query = mysqli_query($conn, "SELECT id, name FROM productcategories WHERE status = 1 $condition3");
    // Get Faq Data //
    $condition4 = ($faq_view_all == 1) ? "" : "AND product_faqs.creator_id = $user_id";
    $faqs_query = mysqli_query($conn, "SELECT id, title FROM product_faqs WHERE status = 1 $condition4");
    // Get Subtag Data //
    $condition5 = ($st_view_all == 1) ? "" : "AND sub_tags.creator_id = $user_id";
    $subTagsOptions = mysqli_query($conn, "SELECT id, name FROM sub_tags where status = 1 $condition5");

    // post Data
    $status = '';
    $enable_pre_product = '';
    $not_fetch_order = '';
    $vat_on_us = '';
    $check_in_sku_qty = '';

    $query = mysqli_query($conn, "SELECT * FROM product WHERE id = $id");
    $product = mysqli_fetch_assoc($query);

    $selectedUpsaleIds = [];
    $upsaleLinked = mysqli_query($conn, "SELECT items_id FROM upsale_items WHERE product_id = $id");
    while ($row = mysqli_fetch_assoc($upsaleLinked)) {
        $selectedUpsaleIds[] = $row['items_id'];
    }

    // Selected Product Brands
    $selectedBrandIds = [];
    $brandResult = mysqli_query($conn, "SELECT brand_id FROM product_brands WHERE product_id = $id");
    while ($row = mysqli_fetch_assoc($brandResult)) {
        $selectedBrandIds[] = $row['brand_id'];
    }

    // Selected Product Related Categories
    $selectedCategoryIds = [];
    $categoryResult = mysqli_query($conn, "SELECT category_id FROM product_related_categories WHERE product_id = $id");
    while ($row = mysqli_fetch_assoc($categoryResult)) {
        $selectedCategoryIds[] = $row['category_id'];
    }

    // Selected Product FAQs
    $selectedFaqIds = [];
    $faqResult = mysqli_query($conn, "SELECT faq_id FROM productfaqs WHERE product_id = $id");
    while ($row = mysqli_fetch_assoc($faqResult)) {
        $selectedFaqIds[] = $row['faq_id'];
    }

    // Selected Product Tags
    $selectedTagIds = [];
    $tagResult = mysqli_query($conn, "SELECT tag_id FROM product_tags WHERE product_id = $id");
    while ($row = mysqli_fetch_assoc($tagResult)) {
        $selectedTagIds[] = $row['tag_id'];
    }

    // Selected Product Categories
    $productCategoryIds = [];
    $productCategoryResult = mysqli_query($conn, "SELECT category_id FROM product_categories WHERE product_id = $id");
    while ($row = mysqli_fetch_assoc($productCategoryResult)) {
        $productCategoryIds[] = $row['category_id'];
    }
    $selectedCategories = $old['product_categories'] ?? $productCategoryIds ?? [];

    //get key features
    $featureQuery = mysqli_query($conn, "SELECT feature, feature_arabic, feature_image FROM key_feature WHERE product_id = $id");
    $getFeature = [];
    while ($row = mysqli_fetch_assoc($featureQuery)) {
        $getFeature[] = [
            'en' => $row['feature'],
            'ar' => $row['feature_arabic'],
            'image' => $row['feature_image'],
        ];
    }

    // get specifications
    $sql = "SELECT 
                specification_heading.id AS group_id,
                specification_heading.specs_heading,
                specification_heading.specs_heading_arabic,
                specification_value.specs,
                specification_value.specs_arabic,
                specification_value.value,
                specification_value.value_arabic
            FROM specification_heading
            LEFT JOIN specification_value ON specification_value.specs_heading_id = specification_heading.id
            WHERE specification_heading.product_id = $id
            ORDER BY specification_heading.id ASC
        ";

    $specifications = [];
    $result = mysqli_query($conn, $sql);

    while ($row = mysqli_fetch_assoc($result)) {
        $group_id = $row['group_id'];

        if (!isset($specifications[$group_id])) {
            $specifications[$group_id] = [
                'specs_heading' => $row['specs_heading'],
                'specs_headingAr' => $row['specs_heading_arabic'],
                'specsList' => []
            ];
        }

        if ($row['specs'] || $row['specs_arabic'] || $row['value'] || $row['value_arabic']) {
            $specifications[$group_id]['specsList'][] = [
                'specs' => $row['specs'],
                'specsAr' => $row['specs_arabic'],
                'value' => $row['value'],
                'valueAr' => $row['value_arabic'],
            ];
        }
    }

    $specifications = array_values($specifications);
    if (empty($specifications)) {
        $specifications[] = [
            'specs_heading' => '',
            'specs_headingAr' => '',
            'specsList' => [
                ['specs' => '', 'specsAr' => '', 'value' => '', 'valueAr' => '']
            ]
        ];
    }

    $oldFeatures = json_decode($_SESSION['old_features'] ?? '[]', true);
    $oldSpecs = json_decode($_SESSION['old_specGroups'] ?? '[]', true);
    $featuresToUse = !empty($oldFeatures) ? $oldFeatures : (!empty($getFeature) ? $getFeature : [['en' => '', 'ar' => '', 'image' => '']]);

    $specsToUse = !empty($oldSpecs) ? $oldSpecs : (!empty($specifications) ? $specifications : [[
        'specs_heading' => '',
        'specs_headingAr' => '',
        'specsList' => [
            ['specs' => '', 'specsAr' => '', 'value' => '', 'valueAr' => '']
        ]
    ]]);
    unset($_SESSION['old_features'], $_SESSION['old_specGroups']);

?>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <script src="/php/pim/assets/js/nice-select2.js"></script>
        <script src="/php/pim/assets/js/simple-datatables.js"></script>
        <link rel="stylesheet" href="/php/pim/assets/js/nice-select2.css">
        <script>
            window.initialFeatures = <?= json_encode($featuresToUse, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
            window.initialSpecs = <?= json_encode($specsToUse, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
            console.log(window.initialSpecs)
        </script>
        <title>Edit Product</title>
    </head>
    <body>
        <form action="./update.php?id='.$id.'" method="POST" enctype="multipart/form-data">
            <?php if (!empty($errors)): ?>
                <div id="error-message" class="flex items-start justify-center p-6 rounded text-danger bg-danger dark:bg-danger-dark-light fixed z-10 w-80 shadow-md shadow-danger" style='right:12px; min-height: 70px; top:70px;'>
                    <div class="flex text-white gap-2">
                        <strong class="block mb-1 text-white">Error!</strong>
                        <div>
                        Please Fill <?php foreach ($errors as $error): ?>
                            <?= htmlspecialchars($error) ?>
                        <?php endforeach; ?>
                        </div>
                    </div>
                    <button type="button" class="ltr:ml-auto rtl:mr-auto hover:opacity-80" onclick="this.parentElement.remove()">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24px" height="24px" viewBox="0 0 24 24"
                        fill="none" stroke="white" stroke-width="1.5" stroke-linecap="round"
                        stroke-linejoin="round" class="w-5 h-5">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                </div>
            <?php endif; ?>
            <input type="hidden" name="id" value="<?php echo $id; ?>">
            <div class="flex xl:flex-row flex-col gap-3">
                <div class="px-0 flex-1 py-0 ltr:xl:mr-1 rtl:xl:ml-1">
                    <div class="mb-5" x-data="{
                        tab: 'English',
                        productNameEn: '<?= htmlspecialchars($old['productNameEn'] ?? $product['name'] ?? '') ?>',
                        shortDescriptionEn: '<?= htmlspecialchars($old['shortDescriptionEn'] ?? $product['short_description'] ?? '') ?>',
                        descriptionEn: '<?= htmlspecialchars($old['descripionEn'] ?? $product['product_description'] ?? '') ?>',
                        productNameAr: '<?= htmlspecialchars($old['productNameAr'] ?? $product['name_arabic'] ?? '') ?>',
                        shortDescriptionAr: '<?= htmlspecialchars($old['shortDescriptionAr'] ?? $product['short_description_arabic'] ?? '') ?>',
                        descriptionAr: '<?= htmlspecialchars($old['descriptionAr'] ?? $product['product_description_arabic'] ?? '') ?>'
                    }">
                        <div>
                            <ul class="flex flex-wrap mt-3">
                                <li>
                                    <a href="javascript:;" class="p-7 py-3 flex items-center bg-[#f6f7f8] dark:bg-transparent border-transparent border-t-2 dark:hover:bg-[#191e3a] hover:border-secondary hover:text-secondary" :class="{'!border-secondary text-secondary dark:bg-[#191e3a]' : tab !== 'Arabic'}" @click="tab = 'English'">
                                        English
                                    </a>
                                </li>
                                <li>
                                    <a href="javascript:;" class="p-7 py-3 flex items-center bg-[#f6f7f8] dark:bg-transparent dark:hover:bg-[#191e3a] border-transparent border-t-2  hover:border-secondary hover:text-secondary" :class="{'!border-secondary text-secondary dark:bg-[#191e3a]' : tab === 'Arabic'}" @click="tab = 'Arabic'">
                                        Arabic
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <div class="flex-1 text-sm">
                            <!-- English Tab -->
                            <div x-show="tab === 'English'" class="panel space-y-4" >
                                <div>
                                    <label>Product Name</label>
                                    <input
                                        id="productNameEn"
                                        type="text"
                                        name="productNameEn"
                                        class="form-input"
                                        placeholder="Enter Product Name"
                                        x-model="productNameEn"
                                        @input="updateSlug(productNameEn)"
                                    />
                                    <?php if (isset($errors['productNameEn'])): ?>
                                        <small style="color: #b91c1c;" class="mt-1"><?= htmlspecialchars($errors['productNameEn']) ?></small>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <label>Short Description</label>
                                    <textarea
                                        id="shortDescriptionEn"
                                        placeholder="Enter Content Here"
                                        name="shortDescriptionEn"
                                        class="form-input h-32"
                                        x-model="shortDescriptionEn"
                                    ></textarea>
                                </div>
                                <div>
                                    <label>Product Description</label>
                                    <textarea
                                        id="descriptionEn"
                                        placeholder="Enter Product Description"
                                        name="descriptionEn"
                                        class="form-input h-32"
                                        x-model="descriptionEn"
                                    ></textarea>
                                </div>
                            </div>

                            <!-- Arabic Tab -->
                            <div x-show="tab === 'Arabic'" class="panel space-y-2" >
                                <div>
                                    <label>Product Name - Ar</label>
                                    <input
                                        id="productNameAr"
                                        type="text"
                                        name="productNameAr"
                                        class="form-input"
                                        placeholder="Enter Product Name - Ar"
                                        x-model="productNameAr"
                                    />
                                    <?php if (isset($errors['productNameAr'])): ?>
                                        <small style="color: #b91c1c;" class="mt-1"><?= htmlspecialchars($errors['productNameAr']) ?></small>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <label>Short Description - Ar</label>
                                    <textarea
                                        id="shortDescriptionAr"
                                        placeholder="Enter Content Here - Ar"
                                        name="shortDescriptionAr"
                                        class="form-input h-32"
                                        x-model="shortDescriptionAr"
                                    ></textarea>
                                </div>
                                <div>
                                    <label>Product Description - Ar</label>
                                    <textarea
                                        id="descriptionAr"
                                        placeholder="Enter Product Description - Ar"
                                        name="descriptionAr"
                                        class="form-input h-32"
                                        x-model="descriptionAr"
                                    ></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-5" x-data="featureRepeater()" x-init="initFeatures(window.initialFeatures)">
                        <div>
                            <ul class="flex flex-wrap mt-3">
                                <li>
                                    <a href="javascript:;" class="p-7 py-3 flex items-center bg-[#f6f7f8] dark:bg-transparent border-transparent border-t-2 dark:hover:bg-[#191e3a] hover:border-secondary hover:text-secondary"
                                    :class="{'!border-secondary text-secondary dark:bg-[#191e3a]' : tab === 'Key Features'}"
                                    @click="tab = 'Key Features'">
                                        Key Features
                                    </a>
                                </li>
                                <li>
                                    <a href="javascript:;" class="p-7 py-3 flex items-center bg-[#f6f7f8] dark:bg-transparent dark:hover:bg-[#191e3a] border-transparent border-t-2 hover:border-secondary hover:text-secondary"
                                    :class="{'!border-secondary text-secondary dark:bg-[#191e3a]' : tab === 'Product Specifications'}"
                                    @click="tab = 'Product Specifications'">
                                        Product Specifications
                                    </a>
                                </li>
                                <li>
                                    <a href="javascript:;" class="p-7 py-3 flex items-center bg-[#f6f7f8] dark:bg-transparent border-transparent border-t-2 dark:hover:bg-[#191e3a] hover:border-secondary hover:text-secondary"
                                    :class="{'!border-secondary text-secondary dark:bg-[#191e3a]' : tab === 'Promotion'}"
                                    @click="tab = 'Promotion'">
                                        Promotion
                                    </a>
                                </li>
                                <li>
                                    <a href="javascript:;" class="p-7 py-3 flex items-center bg-[#f6f7f8] dark:bg-transparent dark:hover:bg-[#191e3a] border-transparent border-t-2 hover:border-secondary hover:text-secondary"
                                    :class="{'!border-secondary text-secondary dark:bg-[#191e3a]' : tab === 'Badge Left'}"
                                    @click="tab = 'Badge Left'">
                                        Badge Left
                                    </a>
                                </li>
                                <li>
                                    <a href="javascript:;" class="p-7 py-3 flex items-center bg-[#f6f7f8] dark:bg-transparent dark:hover:bg-[#191e3a] border-transparent border-t-2 hover:border-secondary hover:text-secondary"
                                    :class="{'!border-secondary text-secondary dark:bg-[#191e3a]' : tab === 'Badge Right'}"
                                    @click="tab = 'Badge Right'">
                                        Badge Right
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <div class="flex-1 text-sm">
                            <!-- Key Features Tab -->
                            <div x-show="tab === 'Key Features'" class="panel space-y-2" >
                                <input type="hidden" name="features_json" :value="JSON.stringify(features)">
                                <div class="flex flex-col gap-4">
                                    <template x-for="(feature, index) in features" :key="index">
                                        <div class="flex gap-3 items-end">
                                            <div class="w-full">
                                                <label>Feature - EN</label>
                                                <input type="text" class="form-input" placeholder="Enter Feature - English" x-model="feature.en" />
                                            </div>

                                            <div class="w-full">
                                                <label>Feature - AR</label>
                                                <input type="text" class="form-input" placeholder="أدخل الميزة (الاسم عربي)" x-model="feature.ar" />
                                            </div>

                                            <div class="w-full">
                                                <label>Feature - Image</label>
                                                <input type="text" class="form-input" placeholder="Image Link (i.e. washing-machine/)" x-model="feature.image" />
                                            </div>

                                            <button type="button" class="btn btn-danger rounded" style='width: 62px;' x-show="index !== 0" @click="removeFeature(index)">
                                                <svg width="20" height="20" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                                </svg>
                                            </button>

                                            <button type="button" class="btn btn-dark rounded" style='width: 62px;' x-show="index === 0" @click="addFeature">
                                                <svg width="20" height="20" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                    <line x1="12" y1="5" x2="12" y2="19"></line>
                                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                                </svg>
                                            </button>
                                        </div>
                                    </template>
                                </div>
                            </div>
                            <!-- Product Specifications Tabs -->
                            <div x-show="tab === 'Product Specifications'" class="panel space-y-2" >
                                <div class="flex flex-col gap-4">
                                    <div x-data="nestedSpecRepeater()" x-init="initSpecGroups(window.initialSpecs)">
                                        <input type="hidden" name="specifications_json" :value="JSON.stringify(specGroups)">
                                        <template x-for="(group, groupIndex) in specGroups" :key="groupIndex">
                                            <div class="space-y-4 mb-6">

                                                <!-- Heading -->
                                                <div class="flex gap-3 items-end">
                                                    <div class="w-full">
                                                        <label>Specs Heading - EN</label>
                                                        <input type="text" class="form-input" x-model="group.specs_heading" placeholder="Enter Specs Heading" />
                                                    </div>
                                                    <div class="w-full">
                                                        <label>Specs Heading - AR</label>
                                                        <input type="text" class="form-input" x-model="group.specs_headingAr" placeholder="أدخل العنوان" />
                                                    </div>
                                                    <button type="button" class="btn btn-danger rounded" style='width: 62px;' x-show="groupIndex !== 0" @click="removeSpecGroup(groupIndex)">
                                                        <svg width="20" height="20" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                            <line x1="5" y1="12" x2="19" y2="12"></line>
                                                        </svg>
                                                    </button>
                                                    <button type="button" class="btn btn-dark rounded" style='width: 62px;' x-show="groupIndex === 0" @click="addSpecGroup">
                                                        <svg width="20" height="20" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                            <line x1="12" y1="5" x2="12" y2="19"></line>
                                                            <line x1="5" y1="12" x2="19" y2="12"></line>
                                                        </svg>
                                                    </button>
                                                </div>

                                                <!-- Specs List -->
                                                <template x-for="(item, itemIndex) in group.specsList" :key="itemIndex">
                                                    <div class="flex gap-3 items-end">
                                                        <div class="w-full">
                                                            <label>Specs - EN</label>
                                                            <input type="text" class="form-input" x-model="item.specs" placeholder="Enter Specs" />
                                                        </div>
                                                        <div class="w-full">
                                                            <label>Specs - AR</label>
                                                            <input type="text" class="form-input" x-model="item.specsAr" placeholder="أدخل الميزة" />
                                                        </div>
                                                        <div class="w-full">
                                                            <label>Value - EN</label>
                                                            <input type="text" class="form-input" x-model="item.value" placeholder="Enter Value" />
                                                        </div>
                                                        <div class="w-full">
                                                            <label>Value - AR</label>
                                                            <input type="text" class="form-input" x-model="item.valueAr" placeholder="أدخل القيمة" />
                                                        </div>
                                                        <button type="button" class="btn btn-danger rounded" style='width: 62px;' x-show="itemIndex !== 0" @click="removeSpecItem(groupIndex, itemIndex)">
                                                            <svg width="20" height="20" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                                <line x1="5" y1="12" x2="19" y2="12"></line>
                                                            </svg>
                                                        </button>
                                                        <button type="button" class="btn btn-dark rounded" style='width: 62px;' x-show="itemIndex === 0" @click="addSpecItem(groupIndex)">
                                                            <svg width="20" height="20" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                                <line x1="12" y1="5" x2="12" y2="19"></line>
                                                                <line x1="5" y1="12" x2="19" y2="12"></line>
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </template>
                                                <hr style='border-top-width: 2px; margin-top:22px' />
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                            <!-- Promotion -->
                            <div x-show="tab === 'Promotion'" class="panel space-y-2" >
                                <div class="flex gap-3 items-end">
                                    <div class="w-full">
                                        <label>Promotion - En</label>
                                        <input type="text" name="promotion_en" class="form-input" placeholder="Enter Promotion - En" value='<?= htmlspecialchars($old['promotion_en'] ?? $product['pormotion'] ?? '') ?>'/>
                                    </div>

                                    <div class="w-full">
                                        <label>Promotion - Ar</label>
                                        <input type="text" name="promotion_ar" class="form-input" placeholder="Enter Promotion - Ar" value='<?= htmlspecialchars($old['promotion_ar'] ?? $product['pormotion_arabic'] ?? '') ?>'/>
                                    </div>

                                    <div class="w-full">
                                        <label>Promotion Color <small style='color:gray; font-size: 10px;'>Hexa Code</small></label>
                                        <input type="text" name="promotion_color" class="form-input" placeholder="Enter Value..." value='<?= htmlspecialchars($old['promotion_color'] ?? $product['pormotion_color'] ?? '') ?>'/>
                                    </div>
                                </div>
                            </div>

                            <!-- Badge Left -->
                            <div x-show="tab === 'Badge Left'" class="panel space-y-2" >
                                <div class="flex gap-3 items-end">
                                    <div class="w-full">
                                        <label>Badge Left - En</label>
                                        <input type="text" name="badge_left_en" class="form-input" placeholder="Enter Badge Left - En" value='<?php echo $old['badge_left_en'] ?? $product['badge_left'];?>'/>
                                    </div>

                                    <div class="w-full">
                                        <label>Badge Left - Ar</label>
                                        <input type="text" name="badge_left_ar" class="form-input" placeholder="Enter Badge Left - Ar" value='<?php echo $old['badge_left_ar'] ?? $product['badge_left_arabic'];?>'/>
                                    </div>

                                    <div class="w-full">
                                        <label>Badge Left Color <small style='color:gray; font-size: 10px;'>Hexa Code</small></label>
                                        <input type="text" name="badge_left_color" class="form-input" placeholder="Enter Value..." value='<?php echo $old['badge_left_color'] ?? $product['badge_left_color'];?>'/>
                                    </div>
                                </div>
                            </div>

                            <!-- Badge Right -->
                            <div x-show="tab === 'Badge Right'" class="panel space-y-2" >
                                <div class="flex gap-3 items-end">
                                    <div class="w-full">
                                        <label>Badge Right - En</label>
                                        <input type="text" name="badge_right_en" class="form-input" placeholder="Enter Badge Right - En" value='<?php echo $old['badge_right_en'] ?? $product['badge_right'];?>'/>
                                    </div>

                                    <div class="w-full">
                                        <label>Badge Right - Ar</label>
                                        <input type="text" name="badge_right_ar" class="form-input" placeholder="Enter Badge Right - Ar" value='<?php echo $old['badge_right_en'] ?? $product['badge_right_arabic'];?>'/>
                                    </div>

                                    <div class="w-full">
                                        <label>Badge Right Color <small style='color:gray; font-size: 10px;'>Hexa Code</small></label>
                                        <input type="text" name="badge_right_color" class="form-input" placeholder="Enter Value..." value='<?php echo $old['badge_right_en'] ?? $product['badge_right_color'];?>'/>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-5" x-data="Object.assign({
                        tab: 'General',
                        en: '',
                        ar: '',
                        image: '',
                        specs_heading: '',
                        specs_headingAr: '',
                        specs: '',
                        specsAr: '',
                        value: '',
                        valueAr: '',
                    }, featureRepeater())">
                        <div>
                            <ul class="flex flex-wrap mt-3">
                                <li>
                                    <a href="javascript:;" class="p-7 py-3 flex items-center bg-[#f6f7f8] dark:bg-transparent border-transparent border-t-2 dark:hover:bg-[#191e3a] hover:border-secondary hover:text-secondary"
                                    :class="{'!border-secondary text-secondary dark:bg-[#191e3a]' : tab !== 'Inventory' && tab !== 'Linked Products' && tab !== 'Setting'}"
                                    @click="tab = 'General'">
                                        General
                                    </a>
                                </li>
                                <li>
                                    <a href="javascript:;" class="p-7 py-3 flex items-center bg-[#f6f7f8] dark:bg-transparent dark:hover:bg-[#191e3a] border-transparent border-t-2 hover:border-secondary hover:text-secondary"
                                    :class="{'!border-secondary text-secondary dark:bg-[#191e3a]' : tab === 'Inventory'}"
                                    @click="tab = 'Inventory'">
                                        Inventory
                                    </a>
                                </li>
                                <li>
                                    <a href="javascript:;" class="p-7 py-3 flex items-center bg-[#f6f7f8] dark:bg-transparent border-transparent border-t-2 dark:hover:bg-[#191e3a] hover:border-secondary hover:text-secondary"
                                    :class="{'!border-secondary text-secondary dark:bg-[#191e3a]' : tab === 'Linked Products'}"
                                    @click="tab = 'Linked Products'">
                                        Linked Products
                                    </a>
                                </li>
                                <li>
                                    <a href="javascript:;" class="p-7 py-3 flex items-center bg-[#f6f7f8] dark:bg-transparent dark:hover:bg-[#191e3a] border-transparent border-t-2 hover:border-secondary hover:text-secondary"
                                    :class="{'!border-secondary text-secondary dark:bg-[#191e3a]' : tab === 'Setting'}"
                                    @click="tab = 'Setting'">
                                        Setting
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <div class="flex-1 text-sm">
                            <!-- General Tab -->
                            <div x-show="tab !== 'Inventory' && tab !== 'Linked Products' && tab !== 'Setting'" class="panel space-y-4" >
                                <div>
                                    <label for="slug" class='w-72'>Product Slug</label>
                                    <input
                                        id="slug"
                                        type="text"
                                        name="slug"
                                        class="form-input disabled:bg-gray-100"
                                        value="<?php echo $old['slug'] ?? 'http://localhost/php/pim/pages/product/' . $product['slug'] ?>"
                                        readonly
                                    />
                                </div>
                                <div class='my-3 grid grid-cols-4 gap-3'>
                                    <div>
                                        <label for="">Regular Price</label>
                                        <input type="number" name="regular_price" id="regular_price" placeholder='0.00' class="form-input" value='<?php echo $old['regular_price'] ?? $product['regular_price'];?>'>
                                        <?php if (isset($errors['regular_price'])): ?>
                                            <small style="color: #b91c1c;" class="mt-1"><?= htmlspecialchars($errors['regular_price']) ?></small>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <label for="">Sale Price</label>
                                        <input type="number" name="sale_price" id="sale_price" placeholder='0.00' class="form-input" value='<?php echo $old['sale_price'] ?? $product['sale_price'];?>'>
                                    </div>
                                    <div>
                                        <label for="">Sorting</label>
                                        <input type="number" name="sorting" id="sorting" placeholder='0' class="form-input" value='<?php echo $old['sorting'] ?? $product['sorting'];?>'>
                                        <?php if (isset($errors['sorting'])): ?>
                                            <small style="color: #b91c1c;" class="mt-1"><?= htmlspecialchars($errors['sorting']) ?></small>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <label for="">Bundle Price Without Vat</label>
                                        <input type="number" name="bundle_price" id="bundle_price" placeholder='0.00' class="form-input" value='<?php echo $old['bundle_price'] ?? $product['bundle_price'];?>'>
                                    </div>
                                    <div>
                                        <label for="">Promo Price</label>
                                        <input type="number" name="promo_price" id="promo_price" placeholder='0.00' class="form-input" value='<?php echo $old['promo_price'] ?? $product['promo_price'];?>'>
                                    </div>
                                    <div>
                                        <label for="">Promo Title - En</label>
                                        <input type="text" name="promo_title" id="promo_title" placeholder='Enter Promo Title' class="form-input" value='<?php echo $old['promo_title'] ?? $product['promo_title'];?>'>
                                    </div>
                                    <div>
                                        <label for="">Promo Title - Ar</label>
                                        <input type="text" name="promo_title_ar" id="promo_title_ar" placeholder='Enter Promo Title - Ar' class="form-input" value='<?php echo $old['promo_title_ar'] ?? $product['promo_title_arabic'];?>'>
                                    </div>
                                </div>
                                <div>
                                    <label>Notes</label>
                                    <textarea
                                        id="notes"
                                        placeholder="Write Notes Here..."
                                        name="notes"
                                        class="form-input"
                                        style='height: 96px;'
                                    ><?php echo $old['notes'] ?? $product['notes'] ?></textarea>
                                </div>
                            </div>

                            <!-- Inventory Tabs -->
                            <div x-show="tab === 'Inventory'" class="panel space-y-2" >
                                <div class="grid grid-cols-4 gap-3 mb-3 items-end">
                                    <div>
                                        <label for="">SKU</label>
                                        <input type="text" name="sku" id="sku" class="form-input" value='<?php echo $old['sku'] ?? $product['sku'];?>'>
                                        <?php if (isset($errors['sku'])): ?>
                                            <small style="color: #b91c1c;" class="mt-1"><?= htmlspecialchars($errors['sku']) ?></small>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <label for="">MPN Flix Media</label>
                                        <input type="text" name="mpn_flix_media" id="mpn_flix_media" class="form-input" value='<?php echo $old['mpn_flix_media'] ?? $product['mpn_flix_media'];?>'>
                                    </div>
                                    <div>
                                        <label for="">MPN Flix En</label>
                                        <input type="text" name="mpn_flix_media_en" id="mpn_flix_media_en" class="form-input" value='<?php echo $old['mpn_flix_media_en'] ?? $product['mpn_flix_media_english'];?>'>
                                    </div>
                                    <div>
                                        <label for="">MPN Flix Ar</label>
                                        <input type="text" name="mpn_flix_media_ar" id="mpn_flix_media_ar" class="form-input" value='<?php echo $old['mpn_flix_media_ar'] ?? $product['mpn_flix_media_arabic'];?>'>
                                    </div>
                                    <div>
                                        <label for="">ln-SKU</label>
                                        <input type="text" name="ln_sku" id="ln_sku" class="form-input" value='<?php echo $old['ln_sku'] ?? $product['ln_sku'];?>'>
                                    </div>
                                    <div>
                                        <label for="">Quantity</label>
                                        <input type="number" name="quantity" id="quantity" placeholder='0' class="form-input" value='<?php echo $old['quantity'] ?? $product['quantity'];?>'>
                                    </div>
                                    <div>
                                        <label for="">Amazon Stock</label>
                                        <input type="number" name="amazon_stock" id="amazon_stock" placeholder='0' class="form-input" value='<?php echo $old['amazon_stock'] ?? $product['amazon_stock'];?>'>
                                    </div>
                                    <label>
                                        <input
                                            type="checkbox"
                                            name="check_in_sku_qty"
                                            class="form-checkbox"
                                            value="1"
                                            <?= (isset($old['check_in_sku_qty']) && $old['check_in_sku_qty'] == '1') || (!isset($old['check_in_sku_qty']) && isset($product['ln_check_quantity']) && $product['ln_check_quantity'] == 1) ? 'checked' : '' ?>
                                        />
                                        <span class="mb-0">In Stock Check Qty</span>
                                    </label>
                                </div>
                            </div>
                            <!-- Linked Products  -->
                            <div x-show="tab === 'Linked Products'" class="panel space-y-2" >
                                <div>
                                    <label for="upsale_items[]">Up-Sale Items</label>
                                    <select name="upsale_items[]" id="upsale_items" class="w-full form-multiselect" multiple>
                                        <option value="" disabled>Select Upsale Items</option>
                                        <?php
                                            $selectedUpsale = $old['upsale_items'] ?? $selectedUpsaleIds ?? [];
                                            while ($upsale = mysqli_fetch_assoc($upsaleOptions)):
                                                $selected = in_array($upsale['id'], $selectedUpsale) ? 'selected' : '';
                                        ?>
                                            <option value="<?= $upsale['id'] ?>" <?= $selected ?>>
                                                <?= htmlspecialchars($upsale['sku']) ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                    <hr style='border-top-width: 2px; margin:22px 0' />
                                </div>
                                <h4 class='font-bold font-size:12px;'>Related Product Marketing</h4>
                                <?php $selectedType = $_POST['type'] ?? $product['type'] ?? '';?>
                                <div class="grid grid-cols-2 gap-3" style="margin-top:20px" x-data="{ selectedType: '<?= $old['type'] ?? $selectedType ?>' }">
                                    <div>
                                        <label for="">Select Type</label>
                                        <select name="type" id="shipping_class" class="w-full form-select" x-model="selectedType">
                                            <option value="" disabled <?= ($old['type'] ?? $selectedType) === '' ? 'selected' : '' ?>>
                                                Select
                                            </option>
                                            <option value="1" <?= ($old['type'] ?? $selectedType) === '1' ? 'selected' : '' ?>>
                                                Brands
                                            </option>
                                            <option value="2" <?= ($old['type'] ?? $selectedType) === '2' ? 'selected' : '' ?>>
                                                Product Categories
                                            </option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="" x-text="selectedType === '1' ? 'Brands' : selectedType === '2' ? 'Product Categories' : 'Select Type'"></label>
                                        <div x-show="selectedType === '1'" x-cloak>
                                            <select name="brand[]" id="brand" class="w-full form-multiselect" multiple>
                                                <option value="" disabled>Select Brands</option>
                                                <?php
                                                    $selectedBrands = $old['brand'] ?? $selectedBrandIds ?? [];
                                                    while ($brand = mysqli_fetch_assoc($brands_query)):
                                                        $selected = in_array($brand['id'], $selectedBrands) ? 'selected' : '';
                                                ?>
                                                    <option value="<?= $brand['id'] ?>" <?= $selected ?>>
                                                        <?= htmlspecialchars($brand['name']) ?>
                                                    </option>
                                                <?php endwhile; ?>
                                            </select>
                                        </div>
                                        <div x-show="selectedType === '2'" x-cloak>
                                            <select name="category[]" id="category" class="w-full form-multiselect" multiple>
                                                <option value="" disabled>Select Categories</option>
                                                <?php
                                                    $selectedCats = $old['category'] ?? $selectedCategoryIds ?? [];
                                                    while ($category = mysqli_fetch_assoc($categories_query)):
                                                        $selected = in_array($category['id'], $selectedCats) ? 'selected' : '';
                                                ?>
                                                    <option value="<?= $category['id'] ?>" <?= $selected ?>>
                                                        <?= htmlspecialchars($category['name']) ?>
                                                    </option>
                                                <?php endwhile; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Setting -->
                            <div x-show="tab === 'Setting'" class="panel space-y-2" >
                                <div class="grid grid-cols-2 gap-3">
                                    <div class="w-full">
                                        <label>Custom Badge - En</label>
                                        <input type="text" name="custom_badge_en" class="form-input" placeholder="Text Goes Here" value='<?php echo $old['custom_badge_en'] ?? $product['custom_badge'];?>'/>
                                    </div>
                                    <div class="w-full">
                                        <label>Custom Badge - Ar</label>
                                        <input type="text" name="custom_badge_ar" class="form-input" placeholder="Text Goes Here" value='<?php echo $old['custom_badge_ar'] ?? $product['custom_badge_arabic'];?>'/>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-5" x-data='<?= json_encode([
                        "tab" => "MetaData - En",
                        "metatitle" => $old['metatitle'] ?? $product['meta_title'],
                        "metatags" => $old['metatags'] ?? $product['meta_tags'],
                        "meta_canonical" => $old['meta_canonical'] ?? $product['meta_canonical'],
                        "metadescription" => $old['metadescription'] ?? $product['meta_description'],
                        "metatitleAr" => $old['metatitleAr'] ?? $product['meta_title_arabic'],
                        "metatagsAr" => $old['metatagsAr'] ?? $product['meta_tags_arabic'],
                        "meta_canonicalAr" => $old['meta_canonicalAr'] ?? $product['meta_canonical_arabic'],
                        "metadescriptionAr" => $old['metadescriptionAr'] ?? $product['meta_description_arabic'],
                    ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>'>
                        <div>
                            <ul class="flex flex-wrap mt-3">
                                <li>
                                    <a href="javascript:;" class="p-7 py-3 flex items-center bg-[#f6f7f8] dark:bg-transparent border-transparent border-t-2 dark:hover:bg-[#191e3a] hover:border-secondary hover:text-secondary" :class="{'!border-secondary text-secondary dark:bg-[#191e3a]' : tab !== 'MetaData - Ar'}" @click="tab = 'MetaData - En'">
                                        MetaData - En
                                    </a>
                                </li>
                                <li>
                                    <a href="javascript:;" class="p-7 py-3 flex items-center bg-[#f6f7f8] dark:bg-transparent dark:hover:bg-[#191e3a] border-transparent border-t-2 hover:border-secondary hover:text-secondary" :class="{'!border-secondary text-secondary dark:bg-[#191e3a]' : tab === 'MetaData - Ar'}" @click="tab = 'MetaData - Ar'">
                                        MetaData - Ar
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <div class="flex-1 text-sm">
                            <!-- MetaData - En Tab -->
                            <div x-show="tab === 'MetaData - En'" class="panel space-y-4" >
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label>Meta Title</label>
                                        <input
                                            id="metatitle"
                                            type="text"
                                            name="metatitle"
                                            class="form-input"
                                            placeholder="Enter Meta Title"
                                            x-model="metatitle"
                                        />
                                    </div>
                                    <div>
                                        <label>Meta Tags</label>
                                        <input
                                            id="metatags"
                                            type="text"
                                            name="metatags"
                                            class="form-input"
                                            placeholder="Enter Meta Tags"
                                            x-model="metatags"
                                        />
                                    </div>
                                </div>
                                <div>
                                    <label>Meta Canonical</label>
                                    <input
                                        id="meta_canonical"
                                        type="text"
                                        name="meta_canonical"
                                        class="form-input"
                                        placeholder="Enter Meta Canonical"
                                        x-model="meta_canonical"
                                    />
                                </div>
                                <div>
                                    <label>Meta Description</label>
                                    <textarea
                                        id="metadescription"
                                        placeholder="Enter Meta Description ..."
                                        name="metadescription"
                                        class="form-input h-20"
                                        x-model="metadescription"
                                    ></textarea>
                                </div>
                            </div>

                            <!-- MetaData - Ar Tab -->
                            <div x-show="tab === 'MetaData - Ar'" class="panel space-y-2" >
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label>Meta Title - Ar</label>
                                        <input
                                            id="metatitleAr"
                                            type="text"
                                            name="metatitleAr"
                                            class="form-input"
                                            placeholder="Enter Meta Title - Ar"
                                            x-model="metatitleAr"
                                        />
                                    </div>
                                    <div>
                                        <label>Meta Tags - Ar</label>
                                        <input
                                            id="metatagsAr"
                                            type="text"
                                            name="metatagsAr"
                                            class="form-input"
                                            placeholder="Enter Meta Tags - Ar"
                                            x-model="metatagsAr"
                                        />
                                    </div>
                                </div>
                                <div>
                                    <label>Meta Canonical - Ar</label>
                                    <input
                                        id="meta_canonicalAr"
                                        type="text"
                                        name="meta_canonicalAr"
                                        class="form-input"
                                        placeholder="Enter Meta Canonical - Ar"
                                        x-model="meta_canonicalAr"
                                    />
                                </div>
                                <div>
                                    <label>Meta Description - Ar</label>
                                    <textarea
                                        id="metadescriptionAr"
                                        placeholder="Enter Meta Description - Ar"
                                        name="metadescriptionAr"
                                        class="form-input h-20"
                                        x-model="metadescriptionAr"
                                    ></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="xl:w-96 w-full xl:mt-0 mt-6">
                    <div class="panel mb-3">
                        <div class="flex xl:grid-cols-1 lg:grid-cols-4 sm:grid-cols-2 grid-cols-1 gap-2">
                            <button class="btn btn-success w-full gap-2 flex items-center" type="submit" name="submit">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M3.46447 20.5355C4.92893 22 7.28595 22 12 22C16.714 22 19.0711 22 20.5355 20.5355C22 19.0711 22 16.714 22 12C22 11.6585 22 11.4878 21.9848 11.3142C21.9142 10.5049 21.586 9.71257 21.0637 9.09034C20.9516 8.95687 20.828 8.83317 20.5806 8.58578L15.4142 3.41944C15.1668 3.17206 15.0431 3.04835 14.9097 2.93631C14.2874 2.414 13.4951 2.08581 12.6858 2.01515C12.5122 2 12.3415 2 12 2C7.28595 2 4.92893 2 3.46447 3.46447C2 4.92893 2 7.28595 2 12C2 16.714 2 19.0711 3.46447 20.5355Z"
                                        stroke="currentColor"
                                        strokeWidth="1.5"
                                    />
                                    <path
                                        d="M17 22V21C17 19.1144 17 18.1716 16.4142 17.5858C15.8284 17 14.8856 17 13 17H11C9.11438 17 8.17157 17 7.58579 17.5858C7 18.1716 7 19.1144 7 21V22"
                                        stroke="currentColor"
                                        strokeWidth="1.5"
                                    />
                                    <path d="M7 8H13" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" />
                                </svg>
                                Update & Close
                            </button>
                            <button type="submit" class="btn btn-dark w-auto" name="save">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M3.46447 20.5355C4.92893 22 7.28595 22 12 22C16.714 22 19.0711 22 20.5355 20.5355C22 19.0711 22 16.714 22 12C22 11.6585 22 11.4878 21.9848 11.3142C21.9142 10.5049 21.586 9.71257 21.0637 9.09034C20.9516 8.95687 20.828 8.83317 20.5806 8.58578L15.4142 3.41944C15.1668 3.17206 15.0431 3.04835 14.9097 2.93631C14.2874 2.414 13.4951 2.08581 12.6858 2.01515C12.5122 2 12.3415 2 12 2C7.28595 2 4.92893 2 3.46447 3.46447C2 4.92893 2 7.28595 2 12C2 16.714 2 19.0711 3.46447 20.5355Z"
                                        stroke="currentColor"
                                        strokeWidth="1.5"
                                    />
                                    <path
                                        d="M17 22V21C17 19.1144 17 18.1716 16.4142 17.5858C15.8284 17 14.8856 17 13 17H11C9.11438 17 8.17157 17 7.58579 17.5858C7 18.1716 7 19.1144 7 21V22"
                                        stroke="currentColor"
                                        strokeWidth="1.5"
                                    />
                                    <path d="M7 8H13" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" />
                                </svg>
                            </button>
                            <a
                                type="button"
                                class="btn btn-danger w-auto"
                                href="/php/pim/pages/product/"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="24"
                                    height="24"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    strokeWidth="1.5"
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    class="shrink-0"
                                >
                                    <line x1="18" y1="6" x2="6" y2="18"></line>
                                    <line x1="6" y1="6" x2="18" y2="18"></line>
                                </svg>
                            </a>
                        </div>
                        <div class="flex flex-wrap gap-3 mt-3">
                            <label class="mt-3 mb-0">
                                <input
                                    type="checkbox"
                                    name="status"
                                    class="form-checkbox"
                                    value="1"
                                    <?= (isset($old['status']) && $old['status'] == '1') || (!isset($old['status']) && isset($product['status']) && $product['status'] == 1) ? 'checked' : '' ?>
                                />
                                <span class="mb-0">Enable</span>
                            </label>
                            <label class="mt-3 mb-0">
                                <input
                                    type="checkbox"
                                    name="enable_pre_product"
                                    class="form-checkbox"
                                    value="1"
                                    <?= (isset($old['enable_pre_product']) && $old['enable_pre_product'] == '1') || (!isset($old['enable_pre_product']) && isset($product['enable_pre_order']) && $product['enable_pre_order'] == 1) ? 'checked' : '' ?>
                                />
                                <span class="mb-0">Enable as Pre-Order Product</span>
                            </label>
                            <label class="mt-1">
                                <input
                                    type="checkbox"
                                    name="not_fetch_order"
                                    class="form-checkbox"
                                    value="1"
                                    <?= (isset($old['not_fetch_order']) && $old['not_fetch_order'] == '1') || (!isset($old['not_fetch_order']) && isset($product['not_fetch_order']) && $product['not_fetch_order'] == 1) ? 'checked' : '' ?>
                                />
                                <span class="mb-0">Not Fetch in Order</span>
                            </label>
                            <label class="mt-1">
                                <input
                                    type="checkbox"
                                    name="vat_on_us"
                                    class="form-checkbox"
                                    value="1"
                                    <?= (isset($old['vat_on_us']) && $old['vat_on_us'] == '1') || (!isset($old['vat_on_us']) && isset($product['vat_on_us']) && $product['vat_on_us'] == 1) ? 'checked' : '' ?>
                                />
                                <span class="mb-0">VAT On Us Promo</span>
                            </label>
                        </div>
                    </div>
                    <div class='mb-3 panel space-y-4'>
                        <div>
                            <label>Product FAQ'S</label>
                            <select name="product_faqs[]" id="product_faqs" class="form-multiselect" multiple>
                                <option value="" disabled>Select Product Faq's</option>
                                <?php
                                    $selectedFaqs = $old['product_faqs'] ?? $selectedFaqIds ?? [];
                                    while ($product_faqs = mysqli_fetch_assoc($faqs_query)):
                                        $selected = in_array($product_faqs['id'], $selectedFaqs) ? 'selected' : '';
                                ?>
                                    <option value="<?= $product_faqs['id'] ?>" <?= $selected ?>>
                                        <?= htmlspecialchars($product_faqs['title']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div>
                            <label>Product Tags</label>
                            <select name="product_tags[]" id="product_tags" class="form-multiselect" multiple>
                            <option value="" disabled>Select Product Tags</option>
                                <?php
                                    $selectedTags = $old['product_tags'] ?? $selectedTagIds ?? [];
                                    while ($product_tags = mysqli_fetch_assoc($subTagsOptions)):
                                        $selected = in_array($product_tags['id'], $selectedTags) ? 'selected' : '';
                                ?>
                                    <option value="<?= $product_tags['id'] ?>" <?= $selected ?>>
                                        <?= htmlspecialchars($product_tags['name']) ?>
                                    </option>
                                <?php endwhile; ?>
                        </select>
                        </div>
                        <div>
                            <label>Product Brands</label>
                            <select name="product_brands" id="product_brands" class="form-select">
                                <option value="" disabled selected>Select</option>
                                <?php
                                    $selectedBrand = $old['product_brands'] ?? $product['brand'] ?? '';
                                    foreach ($brands_query as $brands) {
                                        $selected = ($selectedBrand == $brands['id']) ? 'selected' : '';
                                        echo "<option value='{$brands['id']}' $selected>{$brands['name']}</option>";
                                    }
                                ?>
                            </select>
                        </div>
                        <div>
                            <label>Product Categories</label>
                            <select name="product_categories[]" id="product_categories" class="form-multiselect" multiple>
                                <option value="" disabled>Select</option>
                                <?php foreach ($categories_query as $category): ?>
                                    <?php $isSelected = in_array($category['id'], $selectedCategories); ?>
                                    <option value="<?= $category['id'] ?>" <?= $isSelected ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($category['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label>Warranty</label>
                            <input type="text" name="warranty" id="warranty" placeholder="Text Goes Here" class="form-input" value='<?php echo $old['warranty'] ?? $product['warranty'] ?>'>
                        </div>
                        <div class="flex flex-wrap gap-3" style="margin-top:28px;" x-data='{
                            best_selling: "<?= (isset($old['best_selling_product']) && $old['best_selling_product'] == "1") || (!isset($old['best_selling_product']) && isset($product['best_selling_product']) && $product['best_selling_product'] == 1) ? "1" : "0" ?>",
                            free_gift: "<?= (isset($old['free_gift_product']) && $old['free_gift_product'] == "1") || (!isset($old['free_gift_product']) && isset($product['free_gift_product']) && $product['free_gift_product'] == 1) ? "1" : "0" ?>",
                            low_in_stock: "<?= (isset($old['low_in_stock']) && $old['low_in_stock'] == "1") || (!isset($old['low_in_stock']) && isset($product['low_in_stock']) && $product['low_in_stock'] == 1) ? "1" : "0" ?>",
                            top_selling: "<?= (isset($old['top_selling']) && $old['top_selling'] == "1") || (!isset($old['top_selling']) && isset($product['top_selling']) && $product['top_selling'] == 1) ? "1" : "0" ?>",
                            installation: "<?= (isset($old['installation']) && $old['installation'] == "1") || (!isset($old['installation']) && isset($product['installation']) && $product['installation'] == 1) ? "1" : "0" ?>",
                            is_bundle: "<?= (isset($old['is_bundle']) && $old['is_bundle'] == "1") || (!isset($old['is_bundle']) && isset($product['is_bundle']) && $product['is_bundle'] == 1) ? "1" : "0" ?>",
                            product_installation: "<?= (isset($old['product_installation']) && $old['product_installation'] == "1") || (!isset($old['product_installation']) && isset($product['product_installation']) && $product['product_installation'] == 1) ? "1" : "0" ?>",
                            google_merchant: "<?= (isset($old['google_merchant']) && $old['google_merchant'] == "1") || (!isset($old['google_merchant']) && isset($product['allow_google_merchant']) && $product['allow_google_merchant'] == 1) ? "1" : "0" ?>"
                        }'>
                            <label>
                                <input type="checkbox" name="best_selling_product" class="form-checkbox" value="1"
                                    :checked="best_selling === '1'"
                                    :disabled="free_gift === '1' || low_in_stock === '1' || top_selling === '1'"
                                    @change="best_selling = $event.target.checked ? '1' : ''"
                                />
                                <span class="mb-0">Best Selling Product</span>
                            </label>

                            <label>
                                <input type="checkbox" name="free_gift_product" class="form-checkbox" value="1"
                                    :checked="free_gift === '1'"
                                    :disabled="best_selling === '1' || low_in_stock === '1' || top_selling === '1'"
                                    @change="free_gift = $event.target.checked ? '1' : ''"
                                />
                                <span class="mb-0">Free Gift Product</span>
                            </label>

                            <label>
                                <input type="checkbox" name="low_in_stock" class="form-checkbox" value="1"
                                    :checked="low_in_stock === '1'"
                                    :disabled="best_selling === '1' || free_gift === '1' || top_selling === '1'"
                                    @change="low_in_stock = $event.target.checked ? '1' : ''"
                                />
                                <span class="mb-0">Low In Stock</span>
                            </label>

                            <label>
                                <input type="checkbox" name="top_selling" class="form-checkbox" value="1"
                                    :checked="top_selling === '1'"
                                    :disabled="best_selling === '1' || free_gift === '1' || low_in_stock === '1'"
                                    @change="top_selling = $event.target.checked ? '1' : ''"
                                />
                                <span class="mb-0">Top Selling</span>
                            </label>

                            <label>
                                <input type="checkbox" name="installation" class="form-checkbox" value="1"
                                    :checked="installation === '1'" />
                                <span class="mb-0">Installation</span>
                            </label>

                            <label>
                                <input type="checkbox" name="is_bundle" class="form-checkbox" value="1"
                                    :checked="is_bundle === '1'" />
                                <span class="mb-0">Is Bundle</span>
                            </label>

                            <label>
                                <input type="checkbox" name="product_installation" class="form-checkbox" value="1"
                                    :checked="product_installation === '1'" />
                                <span class="mb-0">Product Installation</span>
                            </label>

                            <label>
                                <input type="checkbox" name="google_merchant" class="form-checkbox" value="1"
                                    :checked="google_merchant === '1'" />
                                <span class="mb-0">Allow Google Merchant</span>
                            </label>
                        </div>
                    </div>
                    <div class="panel mb-3" x-data="{ imageUrl: '<?= htmlspecialchars($old['featured_image'] ?? $product['product_featured_image']) ?>' }">
                        <label>CDN Product Featured Image <small style="color:gray; font-size: 10px;">(https://cdn-image.php.com.pk/)</small></label>
                        <input type="text" name="featured_image" x-model="imageUrl" id="featured_image" placeholder="Enter image URL" class="form-input">
                        <template x-if="imageUrl">
                            <img :src="imageUrl" name="featured_image_preview" id="featured_image_preview" alt="Image Preview" class="mt-2 h-32 w-36 rounded mx-auto">
                        </template>
                    </div>

                    <div class="panel mb-3">
                        <div class='flex justify-between items-baseline'>
                            <label for="mobileImage" class="block font-semibold mb-2">Upload Mobile Image</label>
                            <div class="text-center mt-2">
                                <button type="button" class="btn btn-dark" onclick="document.getElementById('mobileImage').click()">Update Image</button>
                            </div>
                        </div>
                        <div class="relative rounded-md overflow-hidden w-full max-w-sm" id="mobilePreview">
                            <?php $image_mobile = $old['image_mobile'] ?? $product['upload_featured_image'] ?? ''; ?>
                            <?php if (!empty($image_mobile)): ?>
                                <div class="relative">
                                    <button type="button" class="text-danger text-xl font-bold z-10"
                                        onclick="removeImage('mobile')">×</button>
                                    <img src="<?= htmlspecialchars($image_mobile) ?>" class="w-full h-auto object-contain mt-2 rounded-lg">
                                </div>
                            <?php endif; ?>
                        </div>
                        <input type="file" id="mobileImage" name="mobileImage" accept="image/*" class="hidden">
                        <input type="hidden" id="image_mobile_hidden" name="image_mobile" value="<?= htmlspecialchars($image_mobile) ?>">
                    </div>

                    <!-- Website Image Upload -->
                    <div class="panel mb-3">
                        <div class="flex justify-between items-baseline">
                            <label for="galleryImage" class="block font-semibold mb-2">Product Image Gallery</label>
                            <div class="text-center mt-2">
                                <button type="button" class="btn btn-dark" onclick="document.getElementById('galleryImage').click()">Update Images</button>
                            </div>
                        </div>

                        <div class="relative rounded-md mt-3 grid grid-cols-3 gap-3" id="websitePreview">
                            <?php
                                $image_website = $old['image_website'] ?? '';
                                if (!empty($image_website)) {
                                    $allImages = explode(',', $image_website);
                                } else {
                                    $allImages = [];
                                    $galleryQuery = mysqli_query($conn, "SELECT image_url FROM product_image_gallery WHERE product_id = " . $product['id']);
                                    while ($image = mysqli_fetch_assoc($galleryQuery)) {
                                        $allImages[] = $image['image_url'];
                                    }
                                }
                                foreach ($allImages as $url) {
                                    $cleanUrl = htmlspecialchars($url);
                                    echo <<<HTML
                                    <div class="relative inline-block panel">
                                        <button style='left:6px;' type="button" class="text-danger text-xl font-bold z-10 absolute top-0"
                                            onclick="removeImageElement(this, 'image_website_hidden', '{$cleanUrl}')">×</button>
                                        <img src="{$cleanUrl}" class="w-24 h-16 object-contain mt-2 rounded-lg"/>
                                    </div>
                                    HTML;
                                }
                            ?>
                        </div>

                        <input type="file" id="galleryImage" name="galleryImage[]" accept="image/*" class="hidden" multiple>

                        <!-- Set value of all images (either from old or DB) -->
                        <input type="hidden" id="image_website_hidden" name="image_website" value="<?= htmlspecialchars(implode(',', $allImages)) ?>">
                    </div>
                </div>
            </div>
        </form>
    </body>
</html>
<script>
    const baseSlug = "http://localhost/php/pim/pages/product/";

    function slugify(text) {
        return text.toString().toLowerCase()
            .trim()
            .replace(/[^a-z0-9\s-]/g, '') // Remove invalid chars
            .replace(/\s+/g, '-')         // Replace spaces with -
            .replace(/-+/g, '-');         // Collapse multiple -
    }

    function updateSlug(value) {
        const slugInput = document.getElementById("slug");
        const slugPart = slugify(value);
        slugInput.value = baseSlug + slugPart;    }
</script>

<script>
    function uploadImage(fileInputId, hiddenInputId, previewId, allowMultiple = false) {
        const fileInput = document.getElementById(fileInputId);
        const hiddenInput = document.getElementById(hiddenInputId);
        const preview = document.getElementById(previewId);
        const files = fileInput.files;

        if (!files.length) return;

        if (!allowMultiple) {
            preview.innerHTML = ''; // Reset for single upload
            hiddenInput.value = '';
        }

        [...files].forEach(file => {
            const formData = new FormData();
            formData.append('file', file);

            fetch('/php/pim/pages/product/upload.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Add URL to hidden input
                    if (allowMultiple) {
                        const urls = hiddenInput.value ? hiddenInput.value.split(',') : [];
                        urls.push(data.url);
                        hiddenInput.value = urls.join(',');
                    } else {
                        hiddenInput.value = data.url;
                    }

                    // Append image preview
                    const wrapper = document.createElement('div');
                    wrapper.className = `relative inline-block ${allowMultiple ? 'panel' : ''}`;
                    wrapper.innerHTML = `
                        <button style='left:6px;' type="button" class="text-danger text-xl font-bold z-10 absolute top-0"
                            onclick="removeImageElement(this, '${hiddenInputId}', '${data.url}')">×</button>
                        <img src="${data.url}" class="${allowMultiple ? 'w-24 h-16' : 'w-full h-auto'} object-contain mt-2 rounded-lg"/>
                    `;
                    preview.appendChild(wrapper);
                } else {
                    alert("Upload failed: " + data.error);
                }
            })
            .catch(err => alert("Upload error: " + err));
        });
    }

    // For single image (mobile)
    document.getElementById("mobileImage").addEventListener("change", () => {
        uploadImage('mobileImage', 'image_mobile_hidden', 'mobilePreview');
    });

    // For multiple images (website)
    document.getElementById("galleryImage").addEventListener("change", () => {
        uploadImage('galleryImage', 'image_website_hidden', 'websitePreview', true);
    });

    // Clear all for mobile
    function removeImage(type) {
        document.getElementById(`${type}Preview`).innerHTML = '';
        document.getElementById(`image_${type}_hidden`).value = '';
        const fileInput = document.getElementById(`${type}Image`);
        fileInput.value = '';
    }

    // Remove specific image from multiple
    function removeImageElement(button, hiddenInputId, urlToRemove) {
        const container = button.parentElement;
        container.remove();

        const hiddenInput = document.getElementById(hiddenInputId);
        const urls = hiddenInput.value.split(',').filter(url => url !== urlToRemove);
        hiddenInput.value = urls.join(',');
    }
</script>
<script>
    const errorMessage = document.getElementById('error-message');
    setTimeout(() => {
        if (errorMessage) {
        errorMessage.remove();
        }
    }, 2000);
</script>
<script>
    function featureRepeater() {
        return {
            tab: 'Key Features',
            specs_heading: '',
            specs_headingAr: '',
            specs: '',
            specsAr: '',
            value: '',
            valueAr: '',
            features: [],

            initFeatures(initial) {
                this.features = initial;
            },

            addFeature() {
                this.features.push({ en: '', ar: '', image: '' });
            },

            removeFeature(index) {
                this.features.splice(index, 1);
            }
        };
    }
    function nestedSpecRepeater() {
        return {
            specGroups: [
                {
                    specs_heading: '',
                    specs_headingAr: '',
                    specsList: [
                        {
                            specs: '',
                            specsAr: '',
                            value: '',
                            valueAr: ''
                        }
                    ]
                }
            ],

            initSpecGroups(data) {
                this.specGroups = data;
            },

            addSpecGroup() {
                this.specGroups.push({
                    specs_heading: '',
                    specs_headingAr: '',
                    specsList: [
                        {
                            specs: '',
                            specsAr: '',
                            value: '',
                            valueAr: ''
                        }
                    ]
                });
            },

            removeSpecGroup(groupIndex) {
                this.specGroups.splice(groupIndex, 1);
            },

            addSpecItem(groupIndex) {
                this.specGroups[groupIndex].specsList.push({
                    specs: '',
                    specsAr: '',
                    value: '',
                    valueAr: ''
                });
            },

            removeSpecItem(groupIndex, itemIndex) {
                this.specGroups[groupIndex].specsList.splice(itemIndex, 1);
            }
        };
    }
</script>
<?php 
    include '../../footer-main.php'; 
?>