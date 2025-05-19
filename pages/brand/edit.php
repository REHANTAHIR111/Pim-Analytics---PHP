<?php 
    include '../../dbcon.php';
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $old = $_SESSION['old'] ?? [];
    $errors = $_SESSION['errors']    ?? [];
    unset($_SESSION['old'], $_SESSION['errors']);

    if (!isset($_SESSION['user_id'])) {
        header('Location: /php/pim/auth/login.php');
        exit;
    }
    $roleid = $_SESSION['role_id'];
    $user_id = $_SESSION['user_id'];

    $perm = "SELECT * FROM permission JOIN modules ON modules.id = permission.module_id WHERE role_id = $roleid AND module_id = 5";
    $result = mysqli_query($conn, $perm);
    $row = mysqli_fetch_assoc($result);
    if ($row['create'] != 1) {
        header('Location: /php/pim/');
        exit;
    }

    include '../../header-main.php';

    $catquery = "SELECT * FROM permission JOIN modules ON modules.id = permission.module_id WHERE role_id = $roleid AND module_id = 4";
    $catquery_result = mysqli_query($conn, $catquery);
    $row = mysqli_fetch_assoc($catquery_result);
    $cat_view_all = $row['view_all'];
    $catCondition = ($cat_view_all == 1) ? "" : "AND productcategories.creator_id = $user_id";
    $catOptions = mysqli_query($conn, "SELECT id, name FROM productcategories where status = 1 $catCondition");
    $slug = $sorting = $name = $h1 = $brand_description = $brand_description_arabic = $nameAr = $h1Ar = $metatitle = $metatags = $metadescription = $metatitleAr = $metatagsAr = $metadescriptionAr = $contenttitle = $contentdescription = $contenttitleAr = $contentdescriptionAr = $image_mobile = $image_website = '';
    $status = $popular_brand = $show_in_front = 0;

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

    $baseURL = "http://localhost/php/pim/pages/brand/";
    $query = mysqli_query($conn, "SELECT * FROM brand WHERE id = $id");
    $brand = mysqli_fetch_assoc($query);
    extract($brand);
    $slug = $brand['slug'];
    $sorting = $brand['sorting'];
    $name = $brand['name'];
    $h1 = $brand['h1_en'];
    $brand_description = $brand['description'];
    $brand_description_arabic = $brand['description_arabic'];
    $nameAr = $brand['name_arabic'];
    $h1Ar = $brand['h1_arabic'];
    $metatitle = $brand['meta_title'];
    $metatags = $brand['meta_tags'];
    $metadescription = $brand['meta_description'];
    $metatitleAr = $brand['meta_title_arabic'];
    $metatagsAr = $brand['meta_tags_arabic'];
    $metadescriptionAr = $brand['meta_description_arabic'];
    $contenttitle = $brand['content_title'];
    $contentdescription = $brand['content_description'];
    $contenttitleAr = $brand['content_title_arabic'];
    $contentdescriptionAr = $brand['content_description_arabic'];
    $status = $brand['status'];
    $popular_brand = $brand['popular_brand'];
    $show_in_front = $brand['show_in_front'];
    $mobile_image = $brand['mobile_image'];
    $website_image = $brand['website_image'];

    $selectedBrandIds = [];
    $brandLinked = mysqli_query($conn, "SELECT category_id FROM brand_categories WHERE brand_id = $id");
    while ($brandRow = mysqli_fetch_assoc($brandLinked)) {
        $selectedBrandIds[] = $brandRow['category_id'];
    }
    $selectedCat = $old['selectCat'] ?? $selectedBrandIds ?? [];

?>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <script src="/php/pim/assets/js/nice-select2.js"></script>
        <script src="/php/pim/assets/js/simple-datatables.js"></script>
        <link rel="stylesheet" href="/php/pim/assets/js/nice-select2.css">
        <title>Edit Brand</title>
    </head>
    <body>
        <form action="./update.php" class='form.ajax-form' method="POST" enctype="multipart/form-data">
            <?php if (!empty($errors)): ?>
                <div id="error-message" class="flex items-start justify-center p-6 rounded text-danger-light bg-danger dark:bg-danger-dark-light fixed z-10 w-80 shadow-md shadow-danger" style='right:12px; min-height: 70px; top:70px;'>
                    <div class="flex text-white gap-2">
                        <div>
                        Please Fill <?= htmlspecialchars(implode(', ', $errors)) ?>
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
                    <div class="panel mb-5 flex items-center gap-3">
                        <label for="slug" class='w-72'>Brand Slug</label>
                        <input
                            id="slug"
                            type="text"
                            name="slug"
                            class="form-input disabled:bg-gray-100"
                            value='<?php echo $old['slug'] ?? 'http://localhost/php/pim/pages/brand/' . $slug ?>'
                            readonly
                        />
                        <button id="copyBtn" class="btn btn-success" disabled type="button" onclick="copySlug()">
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
                        <div>
                            <input type="number" name="sorting" id="sorting" value='<?= htmlspecialchars($old['sorting'] ?? $sorting) ?>' class="form-input w-24" placeholder='0'>
                            <small style="color: #b91c1c;" class="mt-1"><?= htmlspecialchars($errors['sorting'] ?? '') ?></small>
                        </div>
                    </div>
                    <div class="mb-5" x-data="{
                        tab: 'English',
                        name: '<?= htmlspecialchars($old['name'] ?? $name) ?>',
                        brand_description: '<?= htmlspecialchars($old['brand_description'] ?? $brand_description) ?>',
                        nameAr: '<?= htmlspecialchars($old['nameAr'] ?? $nameAr) ?>',
                        brand_descriptionAr: '<?= htmlspecialchars($old['brand_descriptionAr'] ?? $brand_description_arabic) ?>'
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
                                    <label class="w-72">Brand Name</label>
                                    <input
                                        id="name"
                                        type="text"
                                        name="name"
                                        class="form-input"
                                        placeholder="Enter Brand Name"
                                        x-model="name"
                                        @input="updateSlug(name)"
                                    />
                                    <small style="color: #b91c1c;" class="mt-1"><?= htmlspecialchars($errors['name'] ?? '') ?></small>
                                </div>
                                <div>
                                    <label class="w-72">Brand Description</label>
                                    <textarea
                                        id="brand_description"
                                        placeholder="Enter Brand Description ..."
                                        name="brand_description"
                                        class="form-input h-20"
                                        x-model="brand_description"
                                    ></textarea>
                                </div>
                            </div>

                            <!-- Arabic Tab -->
                            <div x-show="tab === 'Arabic'" class="panel space-y-3" >
                                <div>
                                    <label class="w-72">Brand Name - Ar</label>
                                    <input
                                        id="nameAr"
                                        type="text"
                                        name="nameAr"
                                        class="form-input"
                                        placeholder="Enter Brand Name - Ar"
                                        x-model="nameAr"
                                    />
                                    <small style="color: #b91c1c;" class="mt-1"><?= htmlspecialchars($errors['nameAr'] ?? '') ?></small>
                                </div>
                                <div>
                                    <label class="w-72">Brand Description - Ar</label>
                                    <textarea
                                        id="brand_descriptionAr"
                                        placeholder="Enter Brand Description - Ar"
                                        name="brand_descriptionAr"
                                        class="form-input h-20"
                                        x-model="brand_descriptionAr"
                                    ></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-5" x-data="{
                        tab: 'MetaData - En',
                        metatitle: '<?= htmlspecialchars($old['metatitle'] ?? $metatitle) ?>',
                        metatags: '<?= htmlspecialchars($old['metatags'] ?? $metatags) ?>',
                        h1: '<?= htmlspecialchars($old['h1'] ?? $h1) ?>',
                        metadescription: '<?= htmlspecialchars($old['metadescription'] ?? $metadescription) ?>',
                        metatitleAr: '<?= htmlspecialchars($old['metatitleAr'] ?? $metatitleAr) ?>',
                        metatagsAr: '<?= htmlspecialchars($old['metatagsAr'] ?? $metatagsAr) ?>',
                        h1Ar: '<?= htmlspecialchars($old['h1Ar'] ?? $h1Ar) ?>',
                        metadescriptionAr: '<?= htmlspecialchars($old['metadescriptionAr'] ?? $metadescriptionAr) ?>'
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
                                        <label class="w-72">Meta Tags</label>
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
                                    <label class="w-72">H1 - En</label>
                                    <input
                                        id="h1"
                                        type="text"
                                        name="h1"
                                        class="form-input"
                                        placeholder="H1 - En"
                                        x-model="h1"
                                    />
                                </div>
                                <div>
                                    <label class="w-72">Meta Description</label>
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
                            <div x-show="tab === 'MetaData - Ar'" class="panel space-y-3" >
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="w-72">Meta Title - Ar</label>
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
                                        <label class="w-72">Meta Tags - Ar</label>
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
                                    <label class="w-72">H1 - Ar</label>
                                    <input
                                        id="h1Ar"
                                        type="text"
                                        name="h1Ar"
                                        class="form-input"
                                        placeholder="Enter H1 - Ar"
                                        x-model="h1Ar"
                                    />
                                </div>
                                <div>
                                    <label class="w-72">Meta Description - Ar</label>
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

                    <div class="mb-5" x-data="{
                        tab: 'Content - En',
                        contentTitleEn: '<?= htmlspecialchars($old['contenttitle'] ?? $contenttitle) ?>',
                        contentDescEn: '<?= htmlspecialchars($old['contentdescription'] ?? $contentdescription) ?>',
                        contentTitleAr: '<?= htmlspecialchars($old['contenttitleAr'] ?? $contenttitleAr) ?>',
                        contentDescAr: '<?= htmlspecialchars($old['contentdescriptionAr'] ?? $contentdescriptionAr) ?>'
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
                                    <input
                                        id="contenttitle"
                                        type="text"
                                        name="contenttitle"
                                        class="form-input"
                                        placeholder="Enter Content Title - En"
                                        x-model="contentTitleEn"
                                    />
                                </div>
                                <div>
                                    <label>Content Description - En</label>
                                    <textarea
                                        class="form-input h-20"
                                        id="contentdescription"
                                        name="contentdescription"
                                        placeholder="Enter Content Description - En"
                                        x-model="contentDescEn"
                                    ></textarea>
                                </div>
                            </div>


                            <!-- Content in Arabic -->
                            <div x-show="tab === 'Content - Ar'" class="panel space-y-3" >
                                <div>
                                    <label>Content Title - Ar</label>
                                    <input
                                        id="contenttitleAr"
                                        type="text"
                                        name="contenttitleAr"
                                        class="form-input"
                                        placeholder="Enter Content Title - Ar"
                                        x-model="contentTitleAr"
                                    />
                                </div>
                                <div>
                                    <label>Content Description - Ar</label>
                                    <textarea
                                        class="form-input h-20"
                                        id="contentdescriptionAr"
                                        name="contentdescriptionAr"
                                        placeholder="Enter Content Description - Ar"
                                        x-model="contentDescAr"
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
                                href="/php/pim/pages/brand/"
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
                                <input
                                    type="checkbox"
                                    name="status"
                                    class="form-checkbox"
                                    value="1"
                                    <?= (isset($old['status']) && $old['status'] == '1') || (!isset($old['status']) && isset($status) && $status == 1) ? 'checked' : '' ?>
                                />
                                <span class="mb-0">Enable this Brand</span>
                            </label>
                            <label class="mt-3 mb-0">
                                <input
                                    type="checkbox"
                                    name="not_for_export"
                                    class="form-checkbox"
                                    value="1"
                                    <?= (isset($old['not_for_export']) && $old['not_for_export'] == '1') || (!isset($old['not_for_export']) && isset($not_for_export) && $not_for_export == 1) ? 'checked' : '' ?>
                                />
                                <span class="mb-0">This is popular brand?</span>
                            </label>
                            <label class="mt-3">
                                <input
                                    type="checkbox"
                                    name="show_in_menu"
                                    class="form-checkbox"
                                    value="1"
                                    <?= (isset($old['show_in_menu']) && $old['show_in_menu'] == '1') || (!isset($old['show_in_menu']) && isset($show_in_menu) && $show_in_menu == 1) ? 'checked' : '' ?>
                                />
                                <span class="mb-0">Show in Front?</span>
                            </label>
                        </div>
                    </div>
                    <div class="mb-3 panel">
                        <label for="">Category</label>
                        <select id="selectCat" class="form-select" name="selectCat[]" multiple>
                            <option value="" disabled>Select</option>
                            <?php foreach ($catOptions as $cat): ?>
                                <?php $isSelected = in_array($cat['id'], $selectedCat); ?>
                                <option value="<?= $cat['id'] ?>" <?= $isSelected ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="panel mb-3">
                        <div class='flex justify-between items-baseline'>
                            <label for="mobileImage" class="block font-semibold mb-2">Upload Mobile Image</label>
                            <div class="text-center mt-2">
                                <button type="button" class="btn btn-dark" onclick="document.getElementById('mobileImage').click()">Update Image</button>
                            </div>
                        </div>
                        <div class="relative rounded-md overflow-hidden w-full max-w-sm" id="mobilePreview">
                            <?php $image_mobile = $old['image_mobile'] ?? $mobile_image ?? ''; ?>
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

                    <div class="panel mb-3">
                        <div class='flex justify-between items-baseline'>
                            <label for="websiteImage" class="block font-semibold mb-2">Upload Website Image</label>
                            <div class="text-center mt-2">
                                <button type="button" class="btn btn-dark" onclick="document.getElementById('websiteImage').click()">Update Image</button>
                            </div>
                        </div>
                        <div class="relative rounded-md overflow-hidden w-full max-w-sm" id="websitePreview">
                            <?php $image_website = $old['image_website'] ?? $website_image ?? ''; ?>
                            <?php if (!empty($image_website)): ?>
                                <div class="relative">
                                    <button type="button" class="text-danger text-xl font-bold z-10"
                                        onclick="removeImage('website')">×</button>
                                    <img src="<?= htmlspecialchars($image_website) ?>" class="w-full h-auto object-contain mt-2 rounded-lg">
                                </div>
                            <?php endif; ?>
                        </div>
                        <input type="file" id="websiteImage" name="websiteImage" accept="image/*" class="hidden">
                        <input type="hidden" id="image_website_hidden" name="image_website" value="<?= htmlspecialchars($image_website) ?>">
                    </div>
                </div>
            </div>
        </form>
    </body>
</html>
<script>
const baseSlug = "http://localhost/php/pim/pages/brand/";

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

        fetch('/php/pim/pages/brand/upload.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                hiddenInput.value = data.url;
                preview.innerHTML = `
                    <button type="button" class="text-danger text-xl font-bold z-10" onclick="removeImage('${fileInputId.replace('Image','')}')">×</button>
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
        document.getElementById(`${type}Preview`).innerHTML = '';
        document.getElementById(`image_${type}_hidden`).value = '';
        const fileInput = document.getElementById(`${type}Image`);
        fileInput.value = '';
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

<?php 
    include '../../footer-main.php'; 
?>
