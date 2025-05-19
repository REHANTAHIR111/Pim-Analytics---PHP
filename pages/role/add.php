<?php

    include '../../dbcon.php';

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $role_name = '';
    if (isset($_SESSION['old_input'])) {
        $role_name = $_SESSION['old_input']['role_name'] ?? $role_name;
    }
    $fnR = $_SESSION['fnR'] ?? '';
    $errors = $_SESSION['errors'] ?? [];
    $old_input = $_SESSION['old_input'] ?? [];
    unset($_SESSION['fnR'], $_SESSION['old_input'], $_SESSION['errors']);
    
    if (!isset($_SESSION['user_id'])) {
        header('Location: /php/pim/auth/login.php');
        exit;
    }

    $roleid = $_SESSION['role_id'];
    $perm = "SELECT * FROM permission JOIN modules ON modules.id = permission.module_id WHERE role_id = $roleid AND module_id = 1";
    $result = mysqli_query($conn, $perm);
    $row = mysqli_fetch_assoc($result);

    if($row['create'] == 1) {
    }else{
        header('Location: /php/pim/');
    }

    // Include header
    include '../../header-main.php';

    $module_name = '';
    $modules = [];

    $query = "SELECT * FROM `modules`";
    $result = mysqli_query($conn, $query);

    while ($row = mysqli_fetch_assoc($result)) {
        $modules[] = $row;
    }
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="/php/pim/assets/js/nice-select2.js"></script>
    <script src="/php/pim/assets/js/simple-datatables.js"></script>
    <link rel="stylesheet" href="/php/pim/assets/js/nice-select2.css">
    <title>Add Roles</title>
</head>
<body>
    <form action="./save.php" class='form.ajax-form' method="POST">
        <?php if (!empty($fnR)): ?>
            <div id="error-message" class="flex items-start justify-center p-6 rounded text-danger-light bg-danger dark:bg-danger-dark-light fixed z-10 w-80 shadow-md shadow-danger" style='right:12px; min-height: 70px; top:70px;'>
                <div class="flex text-white gap-2">
                    <div class='flex flex-wrap gap-1'>
                        <b>Error!</b>
                        <?= htmlspecialchars($fnR) ?>
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
                        <label for="role_name">Role Name</label>
                        <input
                            id="role_name"
                            type="text"
                            name="role_name"
                            class="form-input disabled:bg-gray-100"
                            placeholder="Enter Role..."
                            value="<?php echo $role_name; ?>"
                        />
                        <small style="color: #b91c1c;" class="mt-1"><?php echo $fnR; ?></small>
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
                            Publish & Close
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
                oldInput: <?= json_encode($old_input) ?>,
                init() {
                    const rows = this.tableData.map((module, index) => {
                        const moduleName = module.name.replace(/([a-z])([A-Z])/g, '$1 $2').toLowerCase().split(" ").map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(" ");

                        const permissions = ['view_all', 'view', 'edit', 'create', 'delete'];

                        const checkboxes = permissions.map(perm => {
                            const name = `${perm}_${index}`;
                            const isChecked = this.oldInput && this.oldInput[name] ? 'checked' : '';
                            return `<input type="checkbox" class="form-checkbox" name="${name}" value="1" ${isChecked}>`;
                        });

                        return [moduleName, ...checkboxes];
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
                            { select: 0, render: data => data }, // Module Name
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

<?php 
    include '../../footer-main.php'; 
?>
