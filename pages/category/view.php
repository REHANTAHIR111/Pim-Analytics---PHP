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
$roleid = $_SESSION['role_id'];
$user_id = $_SESSION['user_id'];
$perm = "SELECT * FROM permission JOIN modules ON modules.id = permission.module_id WHERE role_id = $roleid AND module_id = 4";
$result = mysqli_query($conn, $perm);
$row = mysqli_fetch_assoc($result);
if ($row['create'] != 1) {
    header('Location: /php/pim/');
    exit;
}
include '../../header-main.php';
$rne = $rna = '';

$stquery = "SELECT * FROM permission JOIN modules ON modules.id = permission.module_id WHERE role_id = $roleid AND module_id = 6";
$stquery_result = mysqli_query($conn, $stquery);
$row = mysqli_fetch_assoc($stquery_result);
$st_view_all = $row['view_all'];
$conditionSt = ($st_view_all == 1) ? "" : "AND sub_tags.creator_id = $user_id";
$subTagsOptions = mysqli_query($conn, "SELECT id, name FROM sub_tags where status = 1 $conditionSt");

$slug = $sorting = $name = $h1 = $category_description = $category_description_arabic = $nameAr = $h1Ar = $metatitle = $metatags = $meta_canonical = $metadescription = $metatitleAr = $metatagsAr = $meta_canonicalAr = $metadescriptionAr = $contenttitle = $contentdescriptipon = $contenttitleAr = $contentdescriptiponAr = $upload_icon = $brand_link = $image_link = $image_mobile = $image_website = $selltype = $value = $parent_category = '';
$status = $not_for_export = $show_in_menu = $show_on_arabyads = $no_follow = $no_index = 0;
$parent_category_id = '';
$selectedSubTags = [];

$id = null;
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
        $id = (int)$_GET['id'];
    }
} else {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : null;
}

if(!$id){
    if (!$id) {
        echo "
            <div class='relative top-20 bg-danger p-4 rounded shadow-md shadow-danger text-white text-lg' style='width: 436px; left:32%;'>
                <span class='font-bold'>Warning:</span> <br>
                Please provide a Valid ID!
            </div>
        ";
        exit;
    }
}

$baseURL = "http://localhost/php/pim/pages/category/";
$query = mysqli_query($conn, "SELECT * FROM productcategories WHERE id = $id");
$category = mysqli_fetch_assoc($query);
extract($category);
$slug = $category['slug'];
$sorting = $category['sorting'];
$name = $category['name'];
$h1 = $category['h1_en'];
$category_description = $category['description'];
$category_description_arabic = $category['description_arabic'];
$nameAr = $category['name_arabic'];
$h1Ar = $category['h1_arabic'];
$metatitle = $category['meta_title'];
$metatags = $category['meta_tags'];
$meta_canonical = $category['meta_canonical'];
$metadescription = $category['meta_description'];
$metatitleAr = $category['meta_title_arabic'];
$metatagsAr = $category['meta_tags_arabic'];
$meta_canonicalAr = $category['meta_canonical_arabic'];
$metadescriptionAr = $category['meta_description_arabic'];
$contenttitle = $category['content_title'];
$contentdescriptipon = $category['content_description'];
$contenttitleAr = $category['content_title_arabic'];
$contentdescriptiponAr = $category['content_description_arabic'];
$status = $category['status'];
$not_for_export = $category['not_for_export'];
$show_in_menu = $category['show_in_menu'];
$show_on_arabyads = $category['show_on_arabyads'];
$no_follow = $category['nofollow_analytics'];
$no_index = $category['noindex_analytics'];
$redirection_link = $category['redirection_link'];
$selltype = $category['sell_type'];
$value = $category['value'];
$upload_icon = $category['icon'];
$mobile_image = $category['mobile_image'];
$website_image = $category['website_image'];
$brand_link = $category['brand_link'];
$image_link = $category['image_link'];
$parent_category_id = $_POST['selectCat'] ?? '';

if ($id && isset($category['parent_category'])) {
    $result = mysqli_query($conn, "SELECT id FROM productcategories WHERE name = '" . mysqli_real_escape_string($conn, $category['parent_category']) . "'");
    if ($row = mysqli_fetch_assoc($result)) {
        $parent_category_id = $row['id'];
    }
}

$selectedSubtagIds = [];
$result = mysqli_query($conn, "SELECT category_filter.filter_id as filter_id, sub_tags.name as name FROM category_filter JOIN sub_tags ON sub_tags.id = category_filter.filter_id WHERE category_filter.category_id = $id");
while ($row = mysqli_fetch_assoc($result)) {
    $filter = mysqli_real_escape_string($conn, $row['name']);
    $subtagQuery = mysqli_query($conn, "SELECT id FROM sub_tags WHERE name = '$filter'");
    if ($subtagRow = mysqli_fetch_assoc($subtagQuery)) {
        $selectedSubtagIds[] = $subtagRow['id'];
    }
}

?>

<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <script src="/php/pim/assets/js/nice-select2.js"></script>
        <script src="/php/pim/assets/js/simple-datatables.js"></script>
        <link rel="stylesheet" href="/php/pim/assets/js/nice-select2.css">
        <title>Edit Category</title>
    </head>
    <body>
        <form action="<?php echo $_SERVER['PHP_SELF'];?>" class='form.ajax-form' method="POST" enctype="multipart/form-data">
            <input disabled type="hidden" name="id" value="<?php echo $id; ?>">
            <div class="flex xl:flex-row flex-col gap-3">
                <div class="px-0 flex-1 py-0 ltr:xl:mr-1 rtl:xl:ml-1">
                    <div class="panel mb-5 flex items-center gap-3">
                        <label for="slug" class='w-72'>Category Slug</label>
                        <input disabled
                            id="slug"
                            type="text"
                            name="slug"
                            class="form-input disabled:bg-gray-100"
                            value='<?= htmlspecialchars($baseURL . $slug) ?>'
                            readonly
                        />
                        <button disabled id="copyBtn" class="btn btn-success" disabled type="button" onclick="copySlug()">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M6 11C6 8.17157 6 6.75736 6.87868 5.87868C7.75736 5 9.17157 5 12 5H15C17.8284 5 19.2426 5 20.1213 5.87868C21 6.75736 21 8.17157 21 11V16C21 18.8284 21 20.2426 20.1213 21.1213C19.2426 22 17.8284 22 15 22H12C9.17157 22 7.75736 22 6.87868 21.1213C6 20.2426 6 18.8284 6 16V11Z"
                                    stroke="currentColor"
                                    strokeWidth="1.5"
                                />
                                <path
                                    d="M6 19C4.34315 19 3 17.6569 3 16V10C3 6.22876 3 4.34315 4.17157 3.17157C5.34315 2 7.22876 2 11 2H15C16.6569 2 18 3.34315 18 5"
                                    stroke="currentColor"
                                    strokeWidth="1.5"
                                />
                            </svg>
                        </button>
                        <input disabled type="number" name="sorting" id="sorting" value='<?= htmlspecialchars($sorting) ?>' class="form-input w-24" placeholder='0'>
                    </div>
                    <div class="mb-5" x-data="{
                        tab: 'English',
                        name: '<?= htmlspecialchars($name) ?>',
                        h1: '<?= htmlspecialchars($h1) ?>',
                        category_description: '<?= htmlspecialchars($category_description) ?>',
                        nameAr: '<?= htmlspecialchars($nameAr) ?>',
                        h1Ar: '<?= htmlspecialchars($h1Ar) ?>',
                        category_descriptionAr: '<?= htmlspecialchars($category_description_arabic) ?>'
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
                            <div x-show="tab === 'English'" class="panel space-y-3" >
                                <div>
                                    <label class="w-72">Category Name</label>
                                    <input disabled
                                        id="name"
                                        type="text"
                                        name="name"
                                        class="form-input"
                                        placeholder="Enter Category Name"
                                        x-model="name"
                                        value='<?= htmlspecialchars($name) ?>'
                                        @input="updateSlug(name)"
                                    />
                                    <small style="color: #b91c1c;" class="mt-1"><?php echo $rne?></small>
                                </div>
                                <div>
                                    <label class="w-72">H1 - En</label>
                                    <input disabled
                                        id="h1"
                                        type="text"
                                        name="h1"
                                        class="form-input"
                                        placeholder="Enter H1 - En"
                                        value='<?= htmlspecialchars($h1) ?>'
                                        x-model="h1"
                                    />
                                </div>
                                <div>
                                    <label class="w-72">Category Description</label>
                                    <textarea disabled
                                        id="category_description"
                                        placeholder="Enter Category Description ..."
                                        name="category_description"
                                        class="form-input h-20"
                                        x-model="category_description"
                                        <?= htmlspecialchars($category_description) ?>
                                    ></textarea>
                                </div>
                            </div>

                            <!-- Arabic Tab -->
                            <div x-show="tab === 'Arabic'" class="panel space-y-3" >
                                <div>
                                    <label class="w-72">Category Name - Ar</label>
                                    <input disabled
                                        id="nameAr"
                                        type="text"
                                        name="nameAr"
                                        class="form-input"
                                        placeholder="Enter Category Name - Ar"
                                        x-model="nameAr"
                                        value='<?= htmlspecialchars($category_description_arabic) ?>'
                                    />
                                    <small style="color: #b91c1c;" class="mt-1"><?php echo $rna?></small>
                                </div>
                                <div>
                                    <label class="w-72">H1 - Ar</label>
                                    <input disabled
                                        id="h1Ar"
                                        type="text"
                                        name="h1Ar"
                                        class="form-input"
                                        placeholder="H1 - Ar"
                                        x-model="h1Ar"
                                        value='<?= htmlspecialchars($h1Ar) ?>'
                                    />
                                </div>
                                <div>
                                    <label class="w-72">Category Description - Ar</label>
                                    <textarea disabled
                                        id="category_descriptionAr"
                                        placeholder="Enter Category Description - Ar"
                                        name="category_descriptionAr"
                                        class="form-input h-20"
                                        x-model="category_descriptionAr"
                                        value='<?= htmlspecialchars($category_description_arabic) ?>'
                                    ></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-5" x-data="{
                        tab: 'MetaData - En',
                    }">
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
                            <div x-show="tab !== 'MetaData - Ar'" class="panel space-y-3" >
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="w-72">Meta Title</label>
                                        <input disabled
                                            id="metatitle"
                                            type="text"
                                            name="metatitle"
                                            class="form-input"
                                            placeholder="Enter Meta Title"
                                            value='<?= htmlspecialchars($metatitle) ?>'
                                        />
                                    </div>
                                    <div>
                                        <label class="w-72">Meta Tags</label>
                                        <input disabled
                                            id="metatags"
                                            type="text"
                                            name="metatags"
                                            class="form-input"
                                            placeholder="Enter Meta Tags"
                                            value='<?= htmlspecialchars($metatags) ?>'
                                        />
                                    </div>
                                </div>
                                <div>
                                    <label class="w-72">Meta Canonical</label>
                                    <input disabled
                                        id="meta_canonical"
                                        type="text"
                                        name="meta_canonical"
                                        class="form-input"
                                        placeholder="Enter Meta Canonical"
                                        value='<?= htmlspecialchars($meta_canonical) ?>'
                                    />
                                </div>
                                <div>
                                    <label class="w-72">Meta Description</label>
                                    <textarea disabled
                                        id="metadescription"
                                        placeholder="Enter Meta Description ..."
                                        name="metadescription"
                                        class="form-input h-20"
                                        value='<?= htmlspecialchars($metadescription) ?>'
                                        
                                    ></textarea>
                                </div>
                            </div>

                            <!-- MetaData - Ar Tab -->
                            <div x-show="tab === 'MetaData - Ar'" class="panel space-y-3" >
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="w-72">Meta Title - Ar</label>
                                        <input disabled
                                            id="metatitleAr"
                                            type="text"
                                            name="metatitleAr"
                                            class="form-input"
                                            placeholder="Enter Meta Title - Ar"
                                            value='<?= htmlspecialchars($metatitleAr) ?>'
                                        />
                                    </div>
                                    <div>
                                        <label class="w-72">Meta Tags - Ar</label>
                                        <input disabled
                                            id="metatagsAr"
                                            type="text"
                                            name="metatagsAr"
                                            class="form-input"
                                            placeholder="Enter Meta Tags - Ar"
                                            value='<?= htmlspecialchars($metatagsAr) ?>'
                                        />
                                    </div>
                                </div>
                                <div>
                                    <label class="w-72">Meta Canonical - Ar</label>
                                    <input disabled
                                        id="meta_canonicalAr"
                                        type="text"
                                        name="meta_canonicalAr"
                                        class="form-input"
                                        placeholder="Enter Meta Canonical - Ar"
                                        value='<?= htmlspecialchars($meta_canonicalAr) ?>'
                                    />
                                </div>
                                <div>
                                    <label class="w-72">Meta Description - Ar</label>
                                    <textarea disabled
                                        id="metadescriptionAr"
                                        placeholder="Enter Meta Description - Ar"
                                        name="metadescriptionAr"
                                        class="form-input h-20"
                                        value='<?= htmlspecialchars($metadescriptionAr) ?>'
                                    ></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-5" x-data="{
                        tab: 'Content - En',
                    }">
                        <div>
                            <ul class="flex flex-wrap mt-3">
                                <li>
                                    <a href="javascript:;" class="p-7 py-3 flex items-center bg-[#f6f7f8] dark:bg-transparent border-transparent border-t-2 dark:hover:bg-[#191e3a] hover:border-secondary hover:text-secondary" :class="{'!border-secondary text-secondary dark:bg-[#191e3a]' : tab !== 'Content - Ar'}" @click="tab = 'Content - En'">
                                        Content - En
                                    </a>
                                </li>
                                <li>
                                    <a href="javascript:;" class="p-7 py-3 flex items-center bg-[#f6f7f8] dark:bg-transparent dark:hover:bg-[#191e3a] border-transparent border-t-2 hover:border-secondary hover:text-secondary" :class="{'!border-secondary text-secondary dark:bg-[#191e3a]' : tab === 'Content - Ar'}" @click="tab = 'Content - Ar'">
                                        Content - Ar
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <div class="flex-1 text-sm">
                            <!-- Content in English -->
                            <div x-show="tab === 'Content - En'" class="panel space-y-3" >
                                <div>
                                    <label>Content Title - En</label>
                                    <input disabled
                                        id="contenttitle"
                                        type="text"
                                        name="contenttitle"
                                        class="form-input"
                                        value='<?= htmlspecialchars($contenttitle) ?>'
                                        placeholder="Enter Content Title - En"
                                    />
                                </div>
                                <div>
                                    <label>Content Description - En</label>
                                    <textarea disabled
                                        class="form-input h-20"
                                        id="contentdescription"
                                        name="contentdescription"
                                        value='<?= htmlspecialchars($contentdescriptipon) ?>'
                                        placeholder="Enter Content Description - En"
                                    ></textarea>
                                </div>
                            </div>


                            <!-- Content in Arabic -->
                            <div x-show="tab === 'Content - Ar'" class="panel space-y-3" >
                                <div>
                                    <label>Content Title - Ar</label>
                                    <input disabled
                                        id="contenttitleAr"
                                        type="text"
                                        name="contenttitleAr"
                                        class="form-input"
                                        placeholder="Enter Content Title - Ar"
                                        value='<?= htmlspecialchars($contenttitleAr) ?>'
                                    />
                                </div>
                                <div>
                                    <label>Content Description - Ar</label>
                                    <textarea disabled
                                        class="form-input h-20"
                                        id="contentdescriptionAr"
                                        name="contentdescriptionAr"
                                        placeholder="Enter Content Description - Ar"
                                        value='<?= htmlspecialchars($contentdescriptiponAr) ?>'
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
                                href="/php/pim/pages/category/"
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
                        <div class="grid grid-cols-2 gap-3 mt-3">
                            <label class="mt-3 mb-0">
                                <input disabled
                                    type="checkbox"
                                    name="status"
                                    class="form-checkbox"
                                    value="1"
                                    <?php if (isset($_POST['status']) && $_POST['status'] == '1' || $category['status'] == 1) echo 'checked'; ?>
                                />
                                <span class="mb-0">Status</span>
                            </label>
                            <label class="mt-3 b-0">
                                <input disabled
                                    type="checkbox"
                                    name="not_for_export"
                                    class="form-checkbox"
                                    value="1"
                                    <?php if (isset($_POST['not_for_export']) && $_POST['not_for_export'] == '1' || $category['not_for_export'] == 1) echo 'checked'; ?>
                                />
                                <span class="mb-0">Not For Export</span>
                            </label>
                            <label class="mt-3 b-0">
                                <input disabled
                                    type="checkbox"
                                    name="show_in_menu"
                                    class="form-checkbox"
                                    value="1"
                                    <?php if (isset($_POST['show_in_menu']) && $_POST['show_in_menu'] == '1' || $category['show_in_menu'] == 1) echo 'checked'; ?>
                                />
                                <span class="mb-0">Show In Menu</span>
                            </label>
                            <label class="mt-3 b-0">
                                <input disabled
                                    type="checkbox"
                                    name="show_on_arabyads"
                                    class="form-checkbox"
                                    value="1"
                                    <?php if (isset($_POST['show_on_arabyads']) && $_POST['show_on_arabyads'] == '1' || $category['show_on_arabyads'] == 1) echo 'checked'; ?>
                                />
                                <span class="mb-0">Show on Arabyads</span>
                            </label>
                        </div>
                    </div>
                    <div class="mb-3 panel">
                        <div class="grid grid-cols-2 gap-3">
                            <label class="mb-0">
                                <input disabled
                                    type="checkbox"
                                    name="no_follow"
                                    class="form-checkbox"
                                    value="1"
                                    <?php if (isset($_POST['no_follow']) && $_POST['no_follow'] == '1' || $category['nofollow_analytics'] == 1) echo 'checked'; ?>
                                />
                                <span class="mb-0">No Follow</span>
                            </label>
                            <label class="mb-0">
                                <input disabled
                                    type="checkbox"
                                    name="no_index"
                                    class="form-checkbox"
                                    value="1"
                                    <?php if (isset($_POST['no_index']) && $_POST['no_index'] == '1' || $category['noindex_analytics'] == 1) echo 'checked'; ?>
                                />
                                <span class="mb-0">No Index</span>
                            </label>
                        </div>
                        <div class='mt-5'>
                            <label for="">Redirection Link</label>
                            <input disabled type="text" value='<?= htmlspecialchars($redirection_link) ?>' name="redirection_link" id="redirection_link" class="form-input" placeholder='Enter Redirection Link'>
                        </div>
                    </div>
                    <div class="mb-3 panel space-y-3">
                        <div>
                            <label for="">Sell Type</label>
                            <select disabled class='form-select' name='selltype' id='selltype' placeholder='Select'>
                                <option value="" disabled <?php if (!isset($_POST['sell_type']) || $_POST['sell_type'] == '') echo 'selected'; ?>>
                                    Select
                                </option>
                                <option value="1" <?php if (isset($_POST['sell_type']) && $_POST['sell_type'] == '1' || $category['sell_type'] == '1') echo 'selected'; ?>>
                                    Percentage
                                </option>
                                <option value="2" <?php if (isset($_POST['sell_type']) && $_POST['sell_type'] == '2' || $category['sell_type'] == '2') echo 'selected'; ?>>
                                    Save
                                </option>
                            </select>
                        </div>
                        <div>
                            <label for="">Value</label>
                            <input disabled type="text" value='<?= htmlspecialchars($value) ?>' name='value' id='value' class='form-input'>
                        </div>
                    </div>
                    <div class="mb-3 panel space-y-3">
                        <div>
                            <label for="">Its a Parent Category</label>
                            <select disabled id="selectCat" class="form-select" name="selectCat">
                                <option value="" <?= (!isset($parent_category_id) || $parent_category_id == '') ? 'selected' : '' ?>>Select</option>
                                <?php
                                    $catQuery = "SELECT id, name FROM productcategories";
                                    $catPerm = "SELECT * FROM permission JOIN modules ON modules.id = permission.module_id WHERE role_id = $roleid AND module_id = 4";
                                    $catquery_result = mysqli_query($conn, $catPerm);
                                    $row = mysqli_fetch_assoc($catquery_result);
                                    $cat_view_all = $row['view_all'];
                                    $catCondition = ($cat_view_all == 1) ? "" : "AND productcategories.creator_id = $user_id";
                                    if ($id) {
                                        $catQuery .= " WHERE id != " . intval($id) . " AND status = 1 $catCondition";
                                    }
                                    $catOptionsResult = mysqli_query($conn, $catQuery);
                                    while ($cat = mysqli_fetch_assoc($catOptionsResult)) {
                                        $selected = ($cat['id'] == $parent_category_id) ? 'selected' : '';
                                        echo '<option value="' . $cat['id'] . '" ' . $selected . '>' . htmlspecialchars($cat['name']) . '</option>';
                                    }
                                ?>
                            </select>
                        </div>
                        <div>
                            <label for="">Top Filter’s Category Page</label>
                            <select disabled id="selectSubtag" class="form-select" name="selectSubtag[]" multiple>
                                <option value="" disabled>Select</option>
                                <?php while ($subTag = mysqli_fetch_assoc($subTagsOptions)): ?>
                                    <option value="<?= $subTag['id'] ?>" <?= in_array($subTag['id'], $selectedSubtagIds) ? 'selected' : '' ?>>
                                        <?= $subTag['name'] ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3 panel space-y-3">
                        <div>
                            <label for="upload_icon">Upload Icon:</label>
                            <textarea disabled name="upload_icon" id="upload_icon" class="form-input h-20" placeholder='Icon...' ><?= htmlspecialchars($upload_icon) ?></textarea>
                        </div>
                        <div>
                            <label for="brand_link">Brand Link</label>
                            <input disabled type="text" name="brand_link" value='<?= htmlspecialchars($brand_link) ?>' id="brand_link" class="form-input" placeholder='Link'>
                        </div>
                        <div>
                            <label for="image_link" class=''>Image Link (Application) <small style='color:gray;'>(the link should be start without / i.e = washing-machine/)</small></label>
                            <input disabled type="text" name="image_link" value='<?= htmlspecialchars($image_link) ?>' id="image_link" class="form-input" placeholder='Link'>
                        </div>
                    </div>
                    <div class="panel mb-3">
                        <div class='flex justify-between items-baseline'>
                            <label for="mobileImage" class="block font-semibold mb-2">Upload Mobile Image</label>
                            <div class="text-center mt-2">
                                <button disabled type="button" class="btn btn-dark" onclick="document.getElementById('mobileImage').click()">Update Image</button>
                            </div>
                        </div>
                        <div class="relative rounded-md overflow-hidden w-full max-w-sm" id="mobilePreview">
                            <?php if (!empty($mobile_image)): ?>
                                <div class="relative">
                                    <button disabled type="button" class="text-danger text-xl font-bold z-10"
                                        onclick="removeImage('mobile')">×</button>
                                    <img src="<?= htmlspecialchars($mobile_image) ?>" class="w-full h-auto object-contain mt-2 rounded-lg">
                                </div>
                            <?php endif; ?>
                        </div>
                        <input disabled type="file" id="mobileImage" name="mobileImage" accept="image/*" class="hidden">
                        <input disabled type="hidden" id="image_mobile_hidden" name="image_mobile" value="<?= htmlspecialchars($mobile_image) ?>">
                    </div>

                    <div class="panel mb-3">
                        <div class='flex justify-between items-baseline'>
                            <label for="websiteImage" class="block font-semibold mb-2">Upload Website Image</label>
                            <div class="text-center mt-2">
                                <button disabled type="button" class="btn btn-dark" onclick="document.getElementById('websiteImage').click()">Update Image</button>
                            </div>
                        </div>
                        <div class="relative rounded-md overflow-hidden w-full max-w-sm" id="websitePreview">
                            <?php if (!empty($website_image)): ?>
                                <div class="relative">
                                    <button disabled type="button" class="text-danger text-xl font-bold z-10"
                                        onclick="removeImage('website')">×</button>
                                    <img src="<?= htmlspecialchars($website_image) ?>" class="w-full h-auto object-contain mt-2 rounded-lg">
                                </div>
                            <?php endif; ?>
                        </div>
                        <input disabled type="file" id="websiteImage" name="websiteImage" accept="image/*" class="hidden">
                        <input disabled type="hidden" id="image_website_hidden" name="image_website" value="<?= htmlspecialchars($website_image) ?>">
                    </div>
                </div>
            </div>
        </form>
    </body>
</html>
<script>
const baseSlug = "http://localhost/php/pim/pages/category/";

    function slugify(text) {
        return text.toString().toLowerCase()
            .trim()
            .replace(/[^a-z0-9\s-]/g, '') // Remove invalid chars
            .replace(/\s+/g, '-')         // Replace spaces with -
            .replace(/-+/g, '-');         // Collapse multiple -
    }

    function updateSlug(value) {
        const slugInput = document.getElementById("slug");
        const copyBtn = document.getElementById("copyBtn");
        const slugPart = slugify(value);
        slugInput.value = baseSlug + slugPart;

        copyBtn.disabled = slugPart.length === 0;
    }

    function copySlug() {
        const slug = document.getElementById("slug").value;
        navigator.clipboard.writeText(slug).then(() => {
            alert("Slug copied!");
        });
    }
</script>

<script>
    function uploadImage(fileInputId, hiddenInputId, previewId) {
        const fileInput = document.getElementById(fileInputId);
        const hiddenInput = document.getElementById(hiddenInputId);
        const preview = document.getElementById(previewId);

        const file = fileInput.files[0];
        if (!file) return;

        const formData = new FormData();
        formData.append('file', file);

        fetch('/php/pim/pages/category/upload.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                hiddenInput.value = data.url;
                preview.innerHTML = `
                    <button disabled type="button" class="text-danger text-xl font-bold z-10" onclick="removeImage('${fileInputId.replace('Image','')}')">×</button>
                    <img src="${data.url}" class="w-full rounded-lg" />
                `;
            } else {
                alert("Upload failed: " + data.error);
            }
        })
        .catch(err => alert("Upload error: " + err));
    }

    document.getElementById("mobileImage").addEventListener("change", () => {
        uploadImage('mobileImage', 'image_mobile_hidden', 'mobilePreview');
    });

    document.getElementById("websiteImage").addEventListener("change", () => {
        uploadImage('websiteImage', 'image_website_hidden', 'websitePreview');
    });

    function removeImage(type) {
        document.getElementById(type + 'Preview').src = '';
        document.getElementById(type + 'Preview').style.display = 'none';
        document.getElementById('image_' + type + '_hidden').value = '';
        document.getElementById(type + 'Image').value = '';
    }
</script>

<?php 
    include '../../footer-main.php'; 
?>