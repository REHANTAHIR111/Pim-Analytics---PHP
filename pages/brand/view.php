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
        <form action="<?php echo $_SERVER['PHP_SELF'];?>" class='form.ajax-form' method="POST" enctype="multipart/form-data">
            <input disabled type="hidden" name="id" value="<?php echo $id; ?>">
            <div class="flex xl:flex-row flex-col gap-3">
                <div class="px-0 flex-1 py-0 ltr:xl:mr-1 rtl:xl:ml-1">
                    <div class="panel mb-5 flex items-center gap-3">
                        <label for="slug" class='w-72'>Brand Slug</label>
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
                        brand_description: '<?= htmlspecialchars($brand_description) ?>',
                        nameAr: '<?= htmlspecialchars($nameAr) ?>',
                        brand_descriptionAr: '<?= htmlspecialchars($brand_description_arabic) ?>'
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
                                    <input disabled
                                        id="name"
                                        type="text"
                                        name="name"
                                        class="form-input"
                                        placeholder="Enter Brand Name"
                                        x-model="name"
                                        value='<?= htmlspecialchars($name) ?>'
                                        @input="updateSlug(name)"
                                    />
                                </div>
                                <div>
                                    <label class="w-72">Brand Description</label>
                                    <textarea disabled
                                        id="brand_description"
                                        placeholder="Enter Brand Description ..."
                                        name="brand_description"
                                        class="form-input h-20"
                                        x-model="brand_description"
                                        <?= htmlspecialchars($brand_description) ?>
                                    ></textarea>
                                </div>
                            </div>

                            <!-- Arabic Tab -->
                            <div x-show="tab === 'Arabic'" class="panel space-y-3" >
                                <div>
                                    <label class="w-72">Brand Name - Ar</label>
                                    <input disabled
                                        id="nameAr"
                                        type="text"
                                        name="nameAr"
                                        class="form-input"
                                        placeholder="Enter Brand Name - Ar"
                                        x-model="nameAr"
                                        value='<?= htmlspecialchars($nameAr) ?>'
                                    />
                                </div>
                                <div>
                                    <label class="w-72">Brand Description - Ar</label>
                                    <textarea disabled
                                        id="brand_descriptionAr"
                                        placeholder="Enter Brand Description - Ar"
                                        name="brand_descriptionAr"
                                        class="form-input h-20"
                                        x-model="brand_descriptionAr"
                                        value='<?= htmlspecialchars($brand_description_arabic) ?>'
                                    ></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-5" x-data="{
                        tab: 'MetaData - En',
                        metatitle: '<?= htmlspecialchars($metatitle) ?>',
                        metatags: '<?= htmlspecialchars($metatags) ?>',
                        h1: '<?= htmlspecialchars($h1) ?>',
                        metadescription: '<?= htmlspecialchars($metadescription) ?>',
                        metatitleAr: '<?= htmlspecialchars($metatitleAr) ?>',
                        metatagsAr: '<?= htmlspecialchars($metatagsAr) ?>',
                        h1Ar: '<?= htmlspecialchars($h1Ar) ?>',
                        metadescriptionAr: '<?= htmlspecialchars($metadescriptionAr) ?>'
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
                                            x-model="metatitle"
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
                                            x-model="metatags"
                                            value='<?= htmlspecialchars($metatags) ?>'
                                        />
                                    </div>
                                </div>
                                <div>
                                    <label class="w-72">H1 - En</label>
                                    <input disabled
                                        id="h1"
                                        type="text"
                                        name="h1"
                                        class="form-input"
                                        placeholder="H1 - En"
                                        x-model="h1"
                                        value='<?= htmlspecialchars($h1) ?>'
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
                                        x-model="metadescription"
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
                                            x-model="metatitleAr"
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
                                            x-model="metatagsAr"
                                            value='<?= htmlspecialchars($metatagsAr) ?>'
                                        />
                                    </div>
                                </div>
                                <div>
                                    <label class="w-72">H1 - Ar</label>
                                    <input disabled
                                        id="h1Ar"
                                        type="text"
                                        name="h1Ar"
                                        class="form-input"
                                        placeholder="Enter H1 - Ar"
                                        x-model="h1Ar"
                                        value='<?= htmlspecialchars($h1Ar) ?>'
                                    />
                                </div>
                                <div>
                                    <label class="w-72">Meta Description - Ar</label>
                                    <textarea disabled
                                        id="metadescriptionAr"
                                        placeholder="Enter Meta Description - Ar"
                                        name="metadescriptionAr"
                                        class="form-input h-20"
                                        x-model="metadescriptionAr"
                                        value='<?= htmlspecialchars($metadescriptionAr) ?>'
                                    ></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-5" x-data="{
                        tab: 'Content - En',
                        contentTitleEn: '<?= htmlspecialchars($contenttitle) ?>',
                        contentDescEn: '<?= htmlspecialchars($contentdescription) ?>',
                        contentTitleAr: '<?= htmlspecialchars($contenttitleAr) ?>',
                        contentDescAr: '<?= htmlspecialchars($contentdescriptionAr) ?>'
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
                                        x-model="contentTitleEn"
                                    />
                                </div>
                                <div>
                                    <label>Content Description - En</label>
                                    <textarea disabled
                                        class="form-input h-20"
                                        id="contentdescription"
                                        name="contentdescription"
                                        value='<?= htmlspecialchars($contentdescription) ?>'
                                        placeholder="Enter Content Description - En"
                                        x-model="contentDescEn"
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
                                        x-model="contentTitleAr"
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
                                        x-model="contentDescAr"
                                        value='<?= htmlspecialchars($contentdescriptionAr) ?>'
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
                                <input disabled
                                    type="checkbox"
                                    name="status"
                                    class="form-checkbox"
                                    value="1"
                                    <?php if (isset($_POST['status']) && $_POST['status'] == '1' || $brand['status'] == 1) echo 'checked'; ?>
                                />
                                <span class="mb-0">Enable this Brand</span>
                            </label>
                            <label class="mt-3 mb-0">
                                <input disabled
                                    type="checkbox"
                                    name="not_for_export"
                                    class="form-checkbox"
                                    value="1"
                                    <?php if (isset($_POST['this_is_popular_brand']) && $_POST['this_is_popular_brand'] == '1' || $brand['popular_brand'] == 1) echo 'checked'; ?>
                                />
                                <span class="mb-0">This is popular brand?</span>
                            </label>
                            <label class="mt-3">
                                <input disabled
                                    type="checkbox"
                                    name="show_in_menu"
                                    class="form-checkbox"
                                    value="1"
                                    <?php if (isset($_POST['show_in_front']) && $_POST['show_in_front'] == '1' || $brand['show_in_front'] == 1) echo 'checked'; ?>
                                />
                                <span class="mb-0">Show in Front?</span>
                            </label>
                        </div>
                    </div>
                    <div class="mb-3 panel">
                        <label for="">Category</label>
                        <select disabled id="selectCat" class="form-select" name="selectCat[]" multiple>
                            <option value="" disabled>Select</option>
                            <?php while ($cat = mysqli_fetch_assoc($catOptions)): ?>
                                <option value="<?= $cat['id'] ?>" <?= in_array($cat['id'], $selectedBrandIds) ? 'selected' : '' ?>>
                                    <?= $cat['name'] ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
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
                    <button disabled type="button" disabled class="text-danger text-xl font-bold z-10" onclick="removeImage('${fileInputId.replace('Image','')}')">×</button>
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
