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
    <title>View Role</title>
</head>
<body>

<form action="" method="POST" class='form.ajax-form'>
    <div class="flex xl:flex-row flex-col gap-2">
        <div class="px-0 flex-1 py-0 ltr:xl:mr-1 rtl:xl:ml-1">
            <div class="panel mb-3 space-y-3">
                <div>
                    <label for="role_name">Role Name</label>
                    <input
                        id="role_name"
                        type="text"
                        name="role_name"
                        class="form-input disabled:bg-gray-100"
                        placeholder="Enter Role..."
                        disabled
                        value="<?php echo htmlspecialchars($role_name); ?>"
                    />
                </div>
                <div x-data="basic">
                    <table id="myTable" class="whitespace-nowrap"></table>
                </div>
            </div>
        </div>

        <div class="xl:w-96 w-full xl:mt-0 mt-6">
            <div class="panel mb-3">
                <div class="flex xl:grid-cols-1 lg:grid-cols-4 sm:grid-cols-2 grid-cols-1 gap-2">
                    <a
                        type="button"
                        class="btn btn-danger w-full"
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
        init() {
            const rows = this.tableData.map((module, index) => {
                const moduleName = module.name.replace(/([a-z])([A-Z])/g, '$1 $2').toLowerCase().split(" ").map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(" ");
                const perm = this.permissions[index] || {};
                return [
                    moduleName,
                    `<input type="checkbox" disabled class="form-checkbox" name="view_all_${index}" ${+perm.view_all === 1 ? 'checked' : ''}>`,
                    `<input type="checkbox" disabled class="form-checkbox" name="view_${index}" ${+perm.view === 1 ? 'checked' : ''}>`,
                    `<input type="checkbox" disabled class="form-checkbox" name="edit_${index}" ${+perm.edit === 1 ? 'checked' : ''}>`,
                    `<input type="checkbox" disabled class="form-checkbox" name="create_${index}" ${+perm.create === 1 ? 'checked' : ''}>`,
                    `<input type="checkbox" disabled class="form-checkbox" name="delete_${index}" ${+perm.delete === 1 ? 'checked' : ''}>`
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
                    { select: 0, render: data => data },
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

</body>
</html>

<?php include '../../footer-main.php'; ?>
