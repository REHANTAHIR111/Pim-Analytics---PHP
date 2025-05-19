<?php
    include '../../dbcon.php';
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $old = $_SESSION['old_input'] ?? [];
    $errors = $_SESSION['errors'] ?? [];
    unset($_SESSION['old_input'], $_SESSION['errors']);
    $permissionsMap = [];
    $modules = [];
    $id = $_GET['id'] ?? null;
    include '../../header-main.php';
    
    // Fetch all modules
    $result = mysqli_query($conn, "SELECT * FROM `modules`");
    if (!$result) {
        die("Query failed: " . mysqli_error($conn));
    }

    $modules = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $modules[] = $row; // Correctly pushing associative arrays
    }
    
    // Fetch role data
    $sql = "SELECT * FROM `role` WHERE id = $id";
    $result1 = mysqli_query($conn, $sql);
    if ($row = mysqli_fetch_assoc($result1)) {
        $role_name = $row['role'];
    }
    

    // Fetch existing permissions
    foreach ($modules as $index => $module) {
        $mid = $module['id'];
        $permRes = mysqli_query($conn, "SELECT * FROM `permission` WHERE role_id = $id AND module_id = ".$module['id']."");
        if ($perm = mysqli_fetch_assoc($permRes)) {
            $permissionsMap[$index] = $perm;
        }
    }

    if (!$id) {
        echo "
        <div class='relative top-20 bg-danger p-4 rounded shadow-md shadow-danger text-white text-lg' style='width: 436px; left:32%;'>
        <span class='font-bold'>Warning:</span> <br>
        Please provide a Valid ID!
        </div>
        ";
        exit;
    }
    
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="/php/pim/assets/js/nice-select2.js"></script>
    <script src="/php/pim/assets/js/simple-datatables.js"></script>
    <link rel="stylesheet" href="/php/pim/assets/js/nice-select2.css">
    <title>Edit Role</title>
</head>
<body>

<form action="./update.php?id=<?= $id ?>" method="POST">
    <?php if (!empty($errors['role_name'])): ?>
        <div id="error-message" class="flex items-start justify-center p-6 rounded text-danger-light bg-danger dark:bg-danger-dark-light fixed z-10 w-80 shadow-md shadow-danger" style='right:12px; min-height: 70px; top:70px;'>
            <div class="flex text-white gap-2">
                <div class='flex flex-wrap gap-1'>
                    <b>Error!</b>
                    <?= htmlspecialchars($errors['role_name']) ?>
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
    <div class="flex xl:flex-row flex-col gap-2">
        <div class="px-0 flex-1 py-0 ltr:xl:mr-1 rtl:xl:ml-1">
            <div class="panel mb-3 space-y-3">
                <div>
                    <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>" />
                    <label for="role_name">Role Name</label>
                    <input type="text" name="role_name" class="form-input disabled:bg-gray-100"
                        placeholder="Enter Role Name"
                        value="<?= htmlspecialchars($old['role_name'] ?? $role_name) ?>" />
                    <small style="color: #b91c1c;" class="mt-1">
                        <?= isset($errors['role_name']) ? htmlspecialchars($errors['role_name']) : '' ?>
                    </small>
                </div>
                <div x-data="basic">
                    <table id="myTable" class="whitespace-nowrap"></table>
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
                    <a
                        type="button"
                        class="btn btn-danger w-auto"
                        href="/php/pim/pages/role/"
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
            </div>
        </div>
    </div>
</form>

<script>
    document.addEventListener("alpine:init", () => {
    Alpine.data("basic", () => ({
        datatable: null,
        tableData: <?= json_encode($modules) ?>,
        permissions: <?= json_encode($permissionsMap) ?>,
        oldInput:   <?= json_encode($old) ?>,
        init() {
        const rows = this.tableData.map((module, index) => {
            const moduleName = module.name.replace(/([a-z])([A-Z])/g, '$1 $2').toLowerCase().split(" ").map(w => w[0].toUpperCase()+w.slice(1)).join(" ");
            const perm = this.permissions[index] || {};
            const isChecked = (field) => {
            const key = `${field}_${index}`;
            if (this.oldInput.hasOwnProperty(key)) {
                return this.oldInput[key] == 1 ? 'checked' : '';
            }
            return (+perm[field] === 1) ? 'checked' : '';
            };

            return [
            moduleName,
            `<input type="checkbox" class="form-checkbox" name="view_all_${index}" ${isChecked('view_all')}>`,
            `<input type="checkbox" class="form-checkbox" name="view_${index}"     ${isChecked('view')}>`,
            `<input type="checkbox" class="form-checkbox" name="edit_${index}"     ${isChecked('edit')}>`,
            `<input type="checkbox" class="form-checkbox" name="create_${index}"   ${isChecked('create')}>`,
            `<input type="checkbox" class="form-checkbox" name="delete_${index}"   ${isChecked('delete')}>`
            ];
        });

        this.datatable = new simpleDatatables.DataTable('#myTable', {
            data: {
            headings: ["Module Name", "View All", "View", "Edit", "Create", "Delete"],
            data: rows
            },
            sortable: false,
            searchable: false,
            pagination: false,
            layout: { top: "", bottom: "" },
            columns: [
            { select: 0, render: d => d },
            { select: 1, type: "html" },
            { select: 2, type: "html" },
            { select: 3, type: "html" },
            { select: 4, type: "html" },
            { select: 5, type: "html" }
            ]
        });
        }
    }));
    });
</script>
<script>
    const errorMessage = document.getElementById('error-message');
    setTimeout(() => {
        if (errorMessage) {
        errorMessage.remove();
        }
    }, 2000);
</script>
</body>
</html>

<?php include '../../footer-main.php'; ?>
