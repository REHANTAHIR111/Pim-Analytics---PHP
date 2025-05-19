<?php include './records.php';?>
<script src="/php/pim/assets/js/simple-datatables.js"></script>
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

<div x-data="custom">
    <div class="panel">
        <div class="flex gap-3 items-center absolute top-5">
            <?php if ($delete) : ?>
                <button type="submit" class="btn btn-danger gap-2" disabled id="bulkDeleteBtn" name="bulk_delete">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M20.5001 6H3.5" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round"></path>
                        <path d="M18.8334 8.5L18.3735 15.3991C18.1965 18.054 18.108 19.3815 17.243 20.1907C16.378 21 15.0476 21 12.3868 21H11.6134C8.9526 21 7.6222 21 6.75719 20.1907C5.89218 19.3815 5.80368 18.054 5.62669 15.3991L5.16675 8.5" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round"></path>
                        <path d="M9.5 11L10 16" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round"></path>
                        <path d="M14.5 11L14 16" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round"></path>
                        <path d="M6.5 6C6.55588 6 6.58382 6 6.60915 5.99936C7.43259 5.97849 8.15902 5.45491 8.43922 4.68032C8.44784 4.65649 8.45667 4.62999 8.47434 4.57697L8.57143 4.28571C8.65431 4.03708 8.69575 3.91276 8.75071 3.8072C8.97001 3.38607 9.37574 3.09364 9.84461 3.01877C9.96213 3 10.0932 3 10.3553 3H13.6447C13.9068 3 14.0379 3 14.1554 3.01877C14.6243 3.09364 15.03 3.38607 15.2493 3.8072C15.3043 3.91276 15.3457 4.03708 15.4286 4.28571L15.5257 4.57697C15.5433 4.62992 15.5522 4.65651 15.5608 4.68032C15.841 5.45491 16.5674 5.97849 17.3909 5.99936C17.4162 6 17.4441 6 17.5 6" stroke="currentColor" strokeWidth="1.5"></path>
                    </svg>
                </button>
            <?php endif; ?>
            <?php if ($create) : ?>
                <a href='/php/pim/pages/users/add.php' class="btn btn-dark">Add Users</a>
            <?php endif; ?>
        </div>
        <table id="myTable" class="table-checkbox relative"></table>
    </div>
</div>

<style>
    table.table-checkbox thead tr th:first-child {
        width: 1px !important;
    }
</style>

<script>
    document.addEventListener("alpine:init", () => {
        Alpine.data("custom", () => ({
            ids: [],  // Holds selected IDs
            tableData: <?= json_encode($users) ?>, // Inject PHP data
            init() {
                // Render the data table
                const data = this.tableData.map(user => {
                    const id = user[0];
                    return [
                        `<input type="checkbox" class="form-checkbox item-checkbox" name="check" value="${id}" onclick="updateDis()">`,
                        `<span style='font-weight:800;' class='text-info'>${user[0]}</span>`,
                        user[1],
                        user[2],
                        user[3],
                        user[4],
                        user[5],
                        user[6],
                        user[7],
                        user[8],
                    ];
                });

                const headings = [
                    `<input type="checkbox" id="select-all" class="form-checkbox" onclick="toggleSelectAll(this)">`,
                    "ID",
                    "Full Name",
                    "Contact Details",
                    "Date of Birth",
                    "Age",
                    "Gender",
                    "Role",
                    "Status",
                    "Actions"
                ];

                this.datatable = new simpleDatatables.DataTable("#myTable", {
                    data: {
                        headings,
                        data
                    },
                    perPage: 10,
                    perPageSelect: [10, 20, 30, 50, 100],
                    columns: [{
                        select: 0,
                        sortable: false,
                    }],
                    firstLast: true,
                    labels: {
                        perPage: "{select}"
                    },
                    layout: {
                        top: "{search}",
                        bottom: "{info}{select}{pager}",
                    },
                });

                // Delay to allow table to render
                setTimeout(() => this.setupCheckboxHandlers(), 500);
            },

            setupCheckboxHandlers() {
                const table = document.getElementById("myTable");

                // Master checkbox
                const selectAllCheckbox = table.querySelector("#select-all");
                selectAllCheckbox?.addEventListener("change", function () {
                    const checkboxes = table.querySelectorAll(".form-checkbox");
                    checkboxes.forEach(cb => cb.checked = this.checked);
                });

                // Re-check master if all are checked manually
                table.addEventListener("change", function () {
                    const checkboxes = table.querySelectorAll(".form-checkbox");
                    const allChecked = Array.from(checkboxes).every(cb => cb.checked);
                    selectAllCheckbox.checked = allChecked;
                });
            },
        }));
        // Handle Bulk Delete
        document.getElementById('bulkDeleteBtn').addEventListener('click', function() {
            const checkedBoxes = document.querySelectorAll('.form-checkbox:checked');
            const selectedIds = Array.from(checkedBoxes).map(cb => cb.value);
            if (selectedIds.length === 0) {
                alert("Please select at least one User to delete.");
                return;
            }
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/php/pim/pages/users/delete.php';
            selectedIds.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'ids[]';
                input.value = id;
                form.appendChild(input);
            });
            document.body.appendChild(form);
            form.submit();
        });
    });
</script>
<script>
    function updateDis() {
        const checkedCount = document.querySelectorAll('.item-checkbox:checked').length;
        const bulkDeleteBtn = document.getElementById("bulkDeleteBtn");
        bulkDeleteBtn.disabled = checkedCount === 0;

        const allBoxes = document.querySelectorAll('.item-checkbox');
        const selectAll = document.getElementById("select-all");
        selectAll.checked = checkedCount === allBoxes.length;
    }

    function toggleSelectAll(selectAllCheckbox) {
        const checkboxes = document.querySelectorAll('.item-checkbox');
        checkboxes.forEach(cb => {
            cb.checked = selectAllCheckbox.checked;
        });

        updateDis();
    }
</script>

<?php include '../../footer-main.php'; ?>
