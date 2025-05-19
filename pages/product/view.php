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
            window.initialFeatures = <?= json_encode($getFeature ?: [['en' => '', 'ar' => '', 'image' => '']]) ?>;
            window.initialSpecs = <?= json_encode($specifications ?: [
                [
                    'specs_heading' => '',
                    'specs_headingAr' => '',
                    'specsList' => [
                        ['specs' => '', 'specsAr' => '', 'value' => '', 'valueAr' => '']
                    ]
                ]
            ]) ?>;
            console.log(window.initialSpecs)
        </script>
        <title>New Product Creation</title>
    </head>
    <body>
        <form action="<?php echo $_SERVER['PHP_SELF'];?>" class='form.ajax-form' method="POST" enctype="multipart/form-data">
            <input disabled type="hidden" name="id" value="<?php echo $id; ?>">
            <div class="flex xl:flex-row flex-col gap-3">
                <div class="px-0 flex-1 py-0 ltr:xl:mr-1 rtl:xl:ml-1">
                    <div class="mb-5" x-data="{
                        tab: 'English',
                        productNameEn: '<?php echo isset($product['name']) ? addslashes($product['name']) : '' ?>',
                        shortDescriptionEn: '<?php echo isset($product['short_description']) ? addslashes($product['short_description']) : '' ?>',
                        descriptionEn: '<?php echo isset($product['product_description']) ? addslashes($product['product_description']) : '' ?>',
                        productNameAr: '<?php echo isset($product['name_arabic']) ? addslashes($product['name_arabic']) : '' ?>',
                        shortDescriptionAr: '<?php echo isset($product['short_description_arabic']) ? addslashes($product['short_description_arabic']) : '' ?>',
                        descriptionAr: '<?php echo isset($product['product_description_arabic']) ? addslashes($product['product_description_arabic']) : '' ?>'
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
                                    <input disabled
                                        id="productNameEn"
                                        type="text"
                                        name="productNameEn"
                                        class="form-input"
                                        placeholder="Enter Product Name"
                                        x-model="productNameEn"
                                        @input="updateSlug(productNameEn)"
                                    />
                                    <small style="color: #b91c1c;" class="mt-1"><?php echo $rne?></small>
                                </div>
                                <div>
                                    <label>Short Description</label>
                                    <textarea disabled
                                        id="shortDescriptionEn"
                                        placeholder="Enter Content Here"
                                        name="shortDescriptionEn"
                                        class="form-input h-32"
                                        x-model="shortDescriptionEn"
                                    ></textarea>
                                </div>
                                <div>
                                    <label>Product Description</label>
                                    <textarea disabled
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
                                    <input disabled
                                        id="productNameAr"
                                        type="text"
                                        name="productNameAr"
                                        class="form-input"
                                        placeholder="Enter Product Name - Ar"
                                        x-model="productNameAr"
                                    />
                                    <small style="color: #b91c1c;" class="mt-1"><?php echo $rna?></small>
                                </div>
                                <div>
                                    <label>Short Description - Ar</label>
                                    <textarea disabled
                                        id="shortDescriptionAr"
                                        placeholder="Enter Content Here - Ar"
                                        name="shortDescriptionAr"
                                        class="form-input h-32"
                                        x-model="shortDescriptionAr"
                                    ></textarea>
                                </div>
                                <div>
                                    <label>Product Description - Ar</label>
                                    <textarea disabled
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
                                <input disabled type="hidden" name="features_json" :value="JSON.stringify(features)">
                                <div class="flex flex-col gap-4">
                                    <template x-for="(feature, index) in features" :key="index">
                                        <div class="flex gap-3 items-end">
                                            <div class="w-full">
                                                <label>Feature - EN</label>
                                                <input disabled type="text" class="form-input" placeholder="Enter Feature - English" x-model="feature.en" />
                                            </div>

                                            <div class="w-full">
                                                <label>Feature - AR</label>
                                                <input disabled type="text" class="form-input" placeholder="أدخل الميزة (الاسم عربي)" x-model="feature.ar" />
                                            </div>

                                            <div class="w-full">
                                                <label>Feature - Image</label>
                                                <input disabled type="text" class="form-input" placeholder="Image Link (i.e. washing-machine/)" x-model="feature.image" />
                                            </div>

                                            <button disabled type="button" class="btn btn-danger rounded" style='width: 62px;' x-show="index !== 0" @click="removeFeature(index)">
                                                <svg width="20" height="20" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                                </svg>
                                            </button>

                                            <button disabled type="button" class="btn btn-dark rounded" style='width: 62px;' x-show="index === 0" @click="addFeature">
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
                                        <input disabled type="hidden" name="specifications_json" :value="JSON.stringify(specGroups)">
                                        <template x-for="(group, groupIndex) in specGroups" :key="groupIndex">
                                            <div class="space-y-4 mb-6">

                                                <!-- Heading -->
                                                <div class="flex gap-3 items-end">
                                                    <div class="w-full">
                                                        <label>Specs Heading - EN</label>
                                                        <input disabled type="text" class="form-input" x-model="group.specs_heading" placeholder="Enter Specs Heading" />
                                                    </div>
                                                    <div class="w-full">
                                                        <label>Specs Heading - AR</label>
                                                        <input disabled type="text" class="form-input" x-model="group.specs_headingAr" placeholder="أدخل العنوان" />
                                                    </div>
                                                    <button disabled type="button" class="btn btn-danger rounded" style='width: 62px;' x-show="groupIndex !== 0" @click="removeSpecGroup(groupIndex)">
                                                        <svg width="20" height="20" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                            <line x1="5" y1="12" x2="19" y2="12"></line>
                                                        </svg>
                                                    </button>
                                                    <button disabled type="button" class="btn btn-dark rounded" style='width: 62px;' x-show="groupIndex === 0" @click="addSpecGroup">
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
                                                            <input disabled type="text" class="form-input" x-model="item.specs" placeholder="Enter Specs" />
                                                        </div>
                                                        <div class="w-full">
                                                            <label>Specs - AR</label>
                                                            <input disabled type="text" class="form-input" x-model="item.specsAr" placeholder="أدخل الميزة" />
                                                        </div>
                                                        <div class="w-full">
                                                            <label>Value - EN</label>
                                                            <input disabled type="text" class="form-input" x-model="item.value" placeholder="Enter Value" />
                                                        </div>
                                                        <div class="w-full">
                                                            <label>Value - AR</label>
                                                            <input disabled type="text" class="form-input" x-model="item.valueAr" placeholder="أدخل القيمة" />
                                                        </div>
                                                        <button disabled type="button" class="btn btn-danger rounded" style='width: 62px;' x-show="itemIndex !== 0" @click="removeSpecItem(groupIndex, itemIndex)">
                                                            <svg width="20" height="20" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                                <line x1="5" y1="12" x2="19" y2="12"></line>
                                                            </svg>
                                                        </button>
                                                        <button disabled type="button" class="btn btn-dark rounded" style='width: 62px;' x-show="itemIndex === 0" @click="addSpecItem(groupIndex)">
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
                                        <input disabled type="text" name="promotion_en" class="form-input" placeholder="Enter Promotion - En" value='<?php echo $product['pormotion'];?>'/>
                                    </div>

                                    <div class="w-full">
                                        <label>Promotion - Ar</label>
                                        <input disabled type="text" name="promotion_ar" class="form-input" placeholder="Enter Promotion - Ar" value='<?php echo $product['pormotion_arabic'];?>'/>
                                    </div>

                                    <div class="w-full">
                                        <label>Promotion Color <small style='color:gray; font-size: 10px;'>Hexa Code</small></label>
                                        <input disabled type="text" name="promotion_color" class="form-input" placeholder="Enter Value..." value='<?php echo $product['pormotion_color'];?>'/>
                                    </div>
                                </div>
                            </div>

                            <!-- Badge Left -->
                            <div x-show="tab === 'Badge Left'" class="panel space-y-2" >
                                <div class="flex gap-3 items-end">
                                    <div class="w-full">
                                        <label>Badge Left - En</label>
                                        <input disabled type="text" name="badge_left_en" class="form-input" placeholder="Enter Badge Left - En" value='<?php echo $product['badge_left'];?>'/>
                                    </div>

                                    <div class="w-full">
                                        <label>Badge Left - Ar</label>
                                        <input disabled type="text" name="badge_left_ar" class="form-input" placeholder="Enter Badge Left - Ar" value='<?php echo $product['badge_left_arabic'];?>'/>
                                    </div>

                                    <div class="w-full">
                                        <label>Badge Left Color <small style='color:gray; font-size: 10px;'>Hexa Code</small></label>
                                        <input disabled type="text" name="badge_left_color" class="form-input" placeholder="Enter Value..." value='<?php echo $product['badge_left_color'];?>'/>
                                    </div>
                                </div>
                            </div>

                            <!-- Badge Right -->
                            <div x-show="tab === 'Badge Right'" class="panel space-y-2" >
                                <div class="flex gap-3 items-end">
                                    <div class="w-full">
                                        <label>Badge Right - En</label>
                                        <input disabled type="text" name="badge_right_en" class="form-input" placeholder="Enter Badge Right - En" value='<?php echo $product['badge_right'];?>'/>
                                    </div>

                                    <div class="w-full">
                                        <label>Badge Right - Ar</label>
                                        <input disabled type="text" name="badge_right_ar" class="form-input" placeholder="Enter Badge Right - Ar" value='<?php echo $product['badge_right_arabic'];?>'/>
                                    </div>

                                    <div class="w-full">
                                        <label>Badge Right Color <small style='color:gray; font-size: 10px;'>Hexa Code</small></label>
                                        <input disabled type="text" name="badge_right_color" class="form-input" placeholder="Enter Value..." value='<?php echo $product['badge_right_color'];?>'/>
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
                                    <input disabled
                                        id="slug"
                                        type="text"
                                        name="slug"
                                        class="form-input disabled:bg-gray-100"
                                        value="http://localhost/php/pim/pages/product/<?php echo $product['slug'] ?>"
                                        readonly
                                    />
                                </div>
                                <div class='my-3 grid grid-cols-4 gap-3'>
                                    <div>
                                        <label for="">Regular Price</label>
                                        <input disabled type="number" name="regular_price" id="regular_price" placeholder='0.00' class="form-input" value='<?php echo $product['regular_price'];?>'>
                                    </div>
                                    <div>
                                        <label for="">Sale Price</label>
                                        <input disabled type="number" name="sale_price" id="sale_price" placeholder='0.00' class="form-input" value='<?php echo $product['sale_price'];?>'>
                                    </div>
                                    <div>
                                        <label for="">Sorting</label>
                                        <input disabled type="number" name="sorting" id="sorting" placeholder='0' class="form-input" value='<?php echo $product['sorting'];?>'>
                                    </div>
                                    <div>
                                        <label for="">Bundle Price Without Vat</label>
                                        <input disabled type="number" name="bundle_price" id="bundle_price" placeholder='0.00' class="form-input" value='<?php echo $product['bundle_price'];?>'>
                                    </div>
                                    <div>
                                        <label for="">Promo Price</label>
                                        <input disabled type="number" name="promo_price" id="promo_price" placeholder='0.00' class="form-input" value='<?php echo $product['promo_price'];?>'>
                                    </div>
                                    <div>
                                        <label for="">Promo Title - En</label>
                                        <input disabled type="text" name="promo_title" id="promo_title" placeholder='Enter Promo Title' class="form-input" value='<?php echo $product['promo_title'];?>'>
                                    </div>
                                    <div>
                                        <label for="">Promo Title - Ar</label>
                                        <input disabled type="text" name="promo_title_ar" id="promo_title_ar" placeholder='Enter Promo Title - Ar' class="form-input" value='<?php echo $product['promo_title_arabic'];?>'>
                                    </div>
                                </div>
                                <div>
                                    <label>Notes</label>
                                    <textarea disabled
                                        id="notes"
                                        placeholder="Write Notes Here..."
                                        name="notes"
                                        class="form-input"
                                        style='height: 96px;'
                                    ><?php echo $product['notes'] ?></textarea>
                                </div>
                            </div>

                            <!-- Inventory Tabs -->
                            <div x-show="tab === 'Inventory'" class="panel space-y-2" >
                                <div class="grid grid-cols-4 gap-3 mb-3 items-end">
                                    <div>
                                        <label for="">SKU</label>
                                        <input disabled type="text" name="sku" id="sku" class="form-input" value='<?php echo $product['sku'];?>'>
                                    </div>
                                    <div>
                                        <label for="">MPN Flix Media</label>
                                        <input disabled type="text" name="mpn_flix_media" id="mpn_flix_media" class="form-input" value='<?php echo $product['mpn_flix_media'];?>'>
                                    </div>
                                    <div>
                                        <label for="">MPN Flix En</label>
                                        <input disabled type="text" name="mpn_flix_media_en" id="mpn_flix_media_en" class="form-input" value='<?php echo $product['mpn_flix_media_english'];?>'>
                                    </div>
                                    <div>
                                        <label for="">MPN Flix Ar</label>
                                        <input disabled type="text" name="mpn_flix_media_ar" id="mpn_flix_media_ar" class="form-input" value='<?php echo $product['mpn_flix_media_arabic'];?>'>
                                    </div>
                                    <div>
                                        <label for="">ln-SKU</label>
                                        <input disabled type="text" name="ln_sku" id="ln_sku" class="form-input" value='<?php echo $product['ln_sku'];?>'>
                                    </div>
                                    <div>
                                        <label for="">Quantity</label>
                                        <input disabled type="number" name="quantity" id="quantity" placeholder='0' class="form-input" value='<?php echo $product['quantity'];?>'>
                                    </div>
                                    <div>
                                        <label for="">Amazon Stock</label>
                                        <input disabled type="number" name="amazon_stock" id="amazon_stock" placeholder='0' class="form-input" value='<?php echo $product['amazon_stock'];?>'>
                                    </div>
                                    <label>
                                        <input disabled
                                            type="checkbox"
                                            name="check_in_sku_qty"
                                            class="form-checkbox"
                                            value="1"
                                            <?php if (isset($_POST['ln_check_quantity']) && $_POST['ln_check_quantity'] == '1' || $product['ln_check_quantity'] == 1) echo 'checked'; ?>
                                        />
                                        <span class="mb-0">In Stock Check Qty</span>
                                    </label>
                                </div>
                            </div>
                            <!-- Linked Products  -->
                            <div x-show="tab === 'Linked Products'" class="panel space-y-2" >
                                <div>
                                    <label for="upsale_items[]">Up-Sale Items</label>
                                    <select disabled name="upsale_items[]" id="upsale_items" class="w-full form-multiselect" multiple>
                                        <option value="" disabled>Select</option>
                                        <?php while ($upsale = mysqli_fetch_assoc($upsaleOptions)): ?>
                                            <option value="<?= $upsale['id'] ?>" <?= in_array($upsale['id'], $selectedUpsaleIds) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($upsale['sku']) ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                    <hr style='border-top-width: 2px; margin:22px 0' />
                                </div>
                                <h4 class='font-bold font-size:12px;'>Related Product Marketing</h4>
                                <?php $selectedType = $_POST['type'] ?? $product['type'] ?? '';?>
                                <div class="grid grid-cols-2 gap-3" style="margin-top:20px" x-data="{ selectedType: '<?= $selectedType ?>' }">
                                    <div>
                                        <label for="">Select Type</label>
                                        <select disabled name="type" id="shipping_class" class="w-full form-select" x-model="selectedType">
                                            <option value="" disabled <?= $selectedType === '' ? 'selected' : '' ?>>
                                                Select
                                            </option>
                                            <option value="1" <?= $selectedType === '1' ? 'selected' : '' ?>>
                                                Brands
                                            </option>
                                            <option value="2" <?= $selectedType === '2' ? 'selected' : '' ?>>
                                                Product Categories
                                            </option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="" x-text="selectedType === '1' ? 'Brands' : selectedType === '2' ? 'Product Categories' : 'Select Type'"></label>
                                        <template x-if="selectedType === '1'">
                                            <select disabled name="brand[]" id="brand" class="w-full form-multiselect" multiple>
                                                <?php while ($brand = mysqli_fetch_assoc($brands_query)): ?>
                                                    <?php
                                                        $isSelected = (isset($_POST['brand']) && in_array($brand['id'], $_POST['brand'])) ||
                                                                    (!isset($_POST['brand']) && in_array($brand['id'], $selectedBrandIds));
                                                    ?>
                                                    <option value="<?= $brand['id'] ?>" <?= $isSelected ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($brand['name']) ?>
                                                    </option>
                                                <?php endwhile; ?>
                                            </select>
                                        </template>
                                        <template x-if="selectedType === '2'">
                                            <select disabled name="category[]" id="category" class="w-full form-multiselect" multiple>
                                                <?php while ($category = mysqli_fetch_assoc($categories_query)): ?>
                                                    <?php
                                                        $isSelected = (isset($_POST['category']) && in_array($category['id'], $_POST['category'])) ||
                                                                    (!isset($_POST['category']) && in_array($category['id'], $selectedCategoryIds));
                                                    ?>
                                                    <option value="<?= $category['id'] ?>" <?= $isSelected ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($category['name']) ?>
                                                    </option>
                                                <?php endwhile; ?>
                                            </select>
                                        </template>
                                    </div>
                                </div>
                            </div>
                            <!-- Setting -->
                            <div x-show="tab === 'Setting'" class="panel space-y-2" >
                                <div class="grid grid-cols-2 gap-3">
                                    <div class="w-full">
                                        <label>Custom Badge - En</label>
                                        <input disabled type="text" name="custom_badge_en" class="form-input" placeholder="Text Goes Here" value='<?php echo $product['custom_badge'];?>'/>
                                    </div>
                                    <div class="w-full">
                                        <label>Custom Badge - Ar</label>
                                        <input disabled type="text" name="custom_badge_ar" class="form-input" placeholder="Text Goes Here" value='<?php echo $product['custom_badge_arabic'];?>'/>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-5" x-data='<?= json_encode([
                        "tab" => "MetaData - En",
                        "metatitle" => $metatitle ?? $product['meta_title'],
                        "metatags" => $metatags ?? $product['meta_tags'],
                        "meta_canonical" => $meta_canonical ?? $product['meta_canonical'],
                        "metadescription" => $metadescription ?? $product['meta_description'],
                        "metatitleAr" => $metatitleAr ?? $product['meta_title_arabic'],
                        "metatagsAr" => $metatagsAr ?? $product['meta_tags_arabic'],
                        "meta_canonicalAr" => $meta_canonicalAr ?? $product['meta_canonical_arabic'],
                        "metadescriptionAr" => $metadescriptionAr ?? $product['meta_description_arabic'],
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
                                        <input disabled
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
                                        <input disabled
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
                                    <input disabled
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
                                    <textarea disabled
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
                                        <input disabled
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
                                        <input disabled
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
                                    <input disabled
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
                                    <textarea disabled
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
                            <a
                                type="button"
                                class="btn btn-danger w-full"
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
                                <input disabled
                                    type="checkbox"
                                    name="status"
                                    class="form-checkbox"
                                    value="1"
                                    <?php if (isset($_POST['status']) && $_POST['status'] == '1' || $product['status'] == 1) echo 'checked'; ?>
                                />
                                <span class="mb-0">Enable</span>
                            </label>
                            <label class="mt-3 mb-0">
                                <input disabled
                                    type="checkbox"
                                    name="enable_pre_product"
                                    class="form-checkbox"
                                    value="1"
                                    <?php if (isset($_POST['enable_pre_order']) && $_POST['enable_pre_order'] == '1' || $product['enable_pre_order'] == 1) echo 'checked'; ?>
                                />
                                <span class="mb-0">Enable as Pre-Order Product</span>
                            </label>
                            <label class="mt-1">
                                <input disabled
                                    type="checkbox"
                                    name="not_fetch_order"
                                    class="form-checkbox"
                                    value="1"
                                    <?php if (isset($_POST['not_fetch_order']) && $_POST['not_fetch_order'] == '1' || $product['not_fetch_order'] == 1) echo 'checked'; ?>
                                />
                                <span class="mb-0">Not Fetch in Order</span>
                            </label>
                            <label class="mt-1">
                                <input disabled
                                    type="checkbox"
                                    name="vat_on_us"
                                    class="form-checkbox"
                                    value="1"
                                    <?php if (isset($_POST['vat_on_us']) && $_POST['vat_on_us'] == '1' || $product['vat_on_us'] == 1) echo 'checked'; ?>
                                />
                                <span class="mb-0">VAT On Us Promo</span>
                            </label>
                        </div>
                    </div>
                    <div class='mb-3 panel space-y-4'>
                        <div>
                            <label>Product FAQ'S</label>
                            <select disabled name="product_faqs[]" id="product_faqs" class="form-multiselect" multiple>
                                <option value="" disabled>Select</option>
                                <?php while ($faqs = mysqli_fetch_assoc($faqs_query)): ?>
                                    <?php
                                        // Check if FAQ is selected
                                        $isSelected = (isset($_POST['product_faqs']) && in_array($faqs['id'], $_POST['product_faqs'])) ||
                                                    (!isset($_POST['product_faqs']) && in_array($faqs['id'], $selectedFaqIds));
                                    ?>
                                    <option value="<?= $faqs['id'] ?>" <?= $isSelected ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($faqs['title']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div>
                            <label>Product Tags</label>
                            <select disabled name="product_tags[]" id="product_tags" class="form-multiselect" multiple>
                            <option value="" disabled>Select</option>
                            <?php foreach ($subTagsOptions as $subTags): ?>
                                <?php
                                    // Check if Tag is selected
                                    $isSelected = (isset($_POST['product_tags']) && in_array($subTags['id'], $_POST['product_tags'])) ||
                                                (!isset($_POST['product_tags']) && in_array($subTags['id'], $selectedTagIds));
                                ?>
                                <option value="<?= $subTags['id'] ?>" <?= $isSelected ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($subTags['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        </div>
                        <div>
                            <label>Product Brands</label>
                            <select disabled name="product_brands" id="product_brands" class="form-select">
                                <option value="" disabled selected>Select</option>
                                <?php
                                    foreach ($brands_query as $brands) {
                                        $selected = (isset($_POST['product_brands']) && $_POST['product_brands'] == $brands['id']) ? 'selected' : '';
                                        echo "<option value='{$brands['id']}' $selected>{$brands['name']}</option>";
                                    }
                                ?>
                            </select>
                        </div>
                        <div>
                            <label>Product Categories</label>
                            <select disabled name="product_categories[]" id="product_categories" class="form-multiselect" multiple>
                                <option value="" disabled>Select</option>
                                <?php foreach ($categories_query as $category): ?>
                                    <?php
                                        $selectedCategories = $_POST['product_categories'] ?? $productCategoryIds;
                                        $isSelected = in_array($category['id'], $selectedCategories);
                                    ?>
                                    <option value="<?= $category['id'] ?>" <?= $isSelected ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($category['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label>Warranty</label>
                            <input disabled type="text" name="warranty" id="warranty" placeholder="Text Goes Here" class="form-input" value='<?php echo $warranty ?? $product['warranty'] ?>'>
                        </div>
                        <?php
                            $selected = '';
                            if (!empty($product['best_selling_product'])) {
                                $selected = 'best';
                            } 
                            if (!empty($product['free_gift_product'])) {
                                $selected = 'free';
                            } 
                            if (!empty($product['low_in_stock'])) {
                                $selected = 'low';
                            } 
                            if (!empty($product['top_selling'])) {
                                $selected = 'top';
                            }
                        ?>
                        <div class="flex flex-wrap gap-3" style="margin-top:28px;" x-data="{ selected: '<?= $selected ?>' }">
                            <label>
                                <input disabled type="checkbox" name="best_selling_product" class="form-checkbox" value="1"
                                    <?= (isset($_POST['best_selling_product']) && $_POST['best_selling_product'] == '1') || $selected === 'best' ? 'checked' : '' ?>
                                    :disabled="selected !== '' && selected !== 'best'"
                                    @change="selected = $event.target.checked ? 'best' : ''"
                                />
                                <span class="mb-0">Best Selling Product</span>
                            </label>

                            <!-- Free Gift -->
                            <label>
                                <input disabled type="checkbox" name="free_gift_product" class="form-checkbox" value="1"
                                    <?= (isset($_POST['free_gift_product']) && $_POST['free_gift_product'] == '1') || $selected === 'free' ? 'checked' : '' ?>
                                    :disabled="selected !== '' && selected !== 'free'"
                                    @change="selected = $event.target.checked ? 'free' : ''"
                                />
                                <span class="mb-0">Free Gift Product</span>
                            </label>

                            <!-- Low in Stock -->
                            <label>
                                <input disabled type="checkbox" name="low_in_stock" class="form-checkbox" value="1"
                                    <?= (isset($_POST['low_in_stock']) && $_POST['low_in_stock'] == '1') || $selected === 'low' ? 'checked' : '' ?>
                                    :disabled="selected !== '' && selected !== 'low'"
                                    @change="selected = $event.target.checked ? 'low' : ''"
                                />
                                <span class="mb-0">Low In Stock</span>
                            </label>

                            <!-- Top Selling -->
                            <label>
                                <input disabled type="checkbox" name="top_selling" class="form-checkbox" value="1"
                                    <?= (isset($_POST['top_selling']) && $_POST['top_selling'] == '1') || $selected === 'top' ? 'checked' : '' ?>
                                    :disabled="selected !== '' && selected !== 'top'"
                                    @change="selected = $event.target.checked ? 'top' : ''"
                                />
                                <span class="mb-0">Top Selling</span>
                            </label>
                            <label>
                                <input disabled type="checkbox" name="installation" class="form-checkbox" value="1"
                                    <?php if (isset($_POST['installation']) && $_POST['installation'] == '1' || $product['installation'] == 1) echo 'checked'; ?> />
                                <span class="mb-0">Installation</span>
                            </label>
                            <label>
                                <input disabled type="checkbox" name="is_bundle" class="form-checkbox" value="1"
                                    <?php if (isset($_POST['is_bundle']) && $_POST['is_bundle'] == '1' || $product['is_bundle'] == 1) echo 'checked'; ?> />
                                <span class="mb-0">Is Bundle</span>
                            </label>

                            <label>
                                <input disabled type="checkbox" name="product_installation" class="form-checkbox" value="1"
                                    <?php if (isset($_POST['product_installation']) && $_POST['product_installation'] == '1' || $product['product_installation'] == 1) echo 'checked'; ?> />
                                <span class="mb-0">Product Installation</span>
                            </label>

                            <label>
                                <input disabled type="checkbox" name="google_merchant" class="form-checkbox" value="1"
                                    <?php if (isset($_POST['google_merchant']) && $_POST['google_merchant'] == '1' || $product['allow_goole_merchant'] == 1) echo 'checked'; ?> />
                                <span class="mb-0">Allow Google Merchant</span>
                            </label>
                        </div>
                    </div>
                    <?php
                        $image = !empty($product['product_featured_image']) ? $product['product_featured_image'] : ($featured_image ?? '');
                    ?>
                    <div class="panel mb-3" x-data="{ imageUrl: '<?= htmlspecialchars($image, ENT_QUOTES) ?>' }">
                        <label>CDN Product Featured Image <small style="color:gray; font-size: 10px;">(https://cdn-image.php.com.pk/)</small></label>
                        <input disabled type="text" name="featured_image" x-model="imageUrl" id="featured_image" placeholder="Enter image URL" class="form-input">
                        <template x-if="imageUrl">
                            <img :src="imageUrl" name="featured_image_preview" id="featured_image_preview" alt="Image Preview" class="mt-2 h-32 w-40 rounded">
                        </template>
                    </div>

                    <div class="panel mb-3">
                        <div class='flex justify-between items-baseline'>
                            <label for="mobileImage" class="block font-semibold mb-2">Upload Mobile Image</label>
                            <div class="text-center mt-2">
                                <button disabled type="button" class="btn btn-dark" onclick="document.getElementById('mobileImage').click()">Update Image</button>
                            </div>
                        </div>
                        <div class="relative rounded-md overflow-hidden w-full max-w-sm" id="mobilePreview">
                            <?php if (!empty($product['upload_featured_image'])): ?>
                                <div class="relative">
                                    <button disabled type="button" class="text-danger text-xl font-bold z-10"
                                        onclick="removeImage('mobile')">×</button>
                                    <img src="<?= htmlspecialchars($product['upload_featured_image']) ?>" class="w-full h-auto object-contain mt-2 rounded-lg">
                                </div>
                            <?php endif; ?>
                        </div>
                        <input disabled type="file" id="mobileImage" name="mobileImage" accept="image/*" class="hidden">
                        <input disabled type="hidden" id="image_mobile_hidden" name="image_mobile" value="<?= htmlspecialchars($product['upload_featured_image']) ?>">
                    </div>

                    <!-- Website Image Upload -->
                    <div class="panel mb-3">
                        <div class="flex justify-between items-baseline">
                            <label for="galleryImage" class="block font-semibold mb-2">Product Image Gallery</label>
                            <div class="text-center mt-2">
                                <button disabled type="button" class="btn btn-dark" onclick="document.getElementById('galleryImage').click()">Update Images</button>
                            </div>
                        </div>

                        <div class="relative rounded-md mt-3 grid grid-cols-3 gap-3" id="websitePreview">
                            <?php
                                $galleryQuery = mysqli_query($conn, "SELECT image_url FROM product_image_gallery WHERE product_id = ".$product['id']."");
                                while ($image = mysqli_fetch_assoc($galleryQuery)) {
                                    echo '<div class="relative inline-block panel">';
                                    echo '<button disabled type="button" style="left:6px;" type="button" class="text-danger text-xl font-bold z-10 absolute top-0" onclick="removeImageElement(this, \'image_website_hidden\', \'' . htmlspecialchars($image['image_url']) . '\')">×</button>';
                                    echo '<img src="' . htmlspecialchars($image['image_url']) . '" class="w-24 h-16 object-contain mt-2 rounded-lg">';
                                    echo '</div>';
                                }
                            ?>
                        </div>
                        <input disabled type="file" id="galleryImage" name="galleryImage[]" accept="image/*" class="hidden" multiple>
                        <input disabled type="hidden" id="image_website_hidden" name="image_website">
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
                        <button disabled style='left:6px;' type="button" class="text-danger text-xl font-bold z-10 absolute top-0"
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
        document.getElementById(type + 'Preview').innerHTML = '';
        document.getElementById('image_' + type + '_hidden').value = '';
        document.getElementById(type + 'Image').value = '';
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