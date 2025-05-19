<?php
    ob_start();
    session_start();
    include '../../dbcon.php';
    //role:
    $fetchRole = "SELECT * FROM `role`";
    $resultRole = mysqli_query($conn, $fetchRole);
    $roleOptions = [];
    while ($row = mysqli_fetch_assoc($resultRole)) {
        $roleOptions[] = $row;
    }

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['user_id'])) {
        header('Location: /php/pim/auth/login.php');
        exit;
    }
    $errors = $_SESSION['errors'] ?? [];
    $old = $_SESSION['old'] ?? [];
    $validation = $_SESSION['validation'] ?? [];

    unset($_SESSION['errors'], $_SESSION['old'], $_SESSION['validation']);
?>
<?php include '../../header-main.php';?>

<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="/php/pim/assets/js/nice-select2.js"></script>
    <link rel="stylesheet" href="/php/pim/assets/js/nice-select2.css">
    <title>Add Users</title>
</head>

<body>
    <form action="./save.php" class='form.ajax-form' method='POST'>
        <?php if (!empty($errors)): ?>
        <div id="error-message"
            class="flex items-start justify-center p-6 rounded text-danger-light bg-danger dark:bg-danger-dark-light fixed z-10 w-80 shadow-md shadow-danger"
            style='right:12px; min-height: 70px; top:70px;'>
            <div class="flex text-white gap-2">
                <div class='flex gap-1'>
                    <div>
                        <b>Error!</b> Please Fill <?= htmlspecialchars(implode(', ', $errors)) ?>
                    </div>
                </div>
            </div>
            <button type="button" class="ltr:ml-auto rtl:mr-auto hover:opacity-80"
                onclick="this.parentElement.remove()">
                <svg xmlns="http://www.w3.org/2000/svg" width="24px" height="24px" viewBox="0 0 24 24" fill="none"
                    stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <?php endif; ?>
        <div class="flex xl:flex-row flex-col gap-2">
            <div class="px-0 flex-1 py-0 ltr:xl:mr-1 rtl:xl:ml-1">
                <div class="panel mb-3">
                    <div class='grid grid-cols-2 gap-3'>
                        <div class="">
                            <label htmlFor="fname" class="">
                                First Name
                            </label>
                            <input id="fname" type="text" name="fname" class='form-input disabled:bg-gray-100'
                                placeholder='Enter First Name Here'
                                value="<?= htmlspecialchars($old['fname'] ?? '') ?>" />
                            <small style='color: #b91c1c;' class="mt-1">
                                <?= htmlspecialchars($validation['vfn'] ?? '') ?>
                                <?= htmlspecialchars($validation['fnR'] ?? '') ?>
                            </small>
                        </div>
                        <div class="">
                            <label htmlFor="lname" class="">
                                Last Name
                            </label>
                            <input id="lname" type="text" name="lname" class='form-input disabled:bg-gray-100'
                                placeholder='Enter Last Name Here'
                                value="<?= htmlspecialchars($old['lname'] ?? '') ?>" />
                            <small style='color: #b91c1c;' class="mt-1">
                                <?= htmlspecialchars($validation['vln'] ?? '') ?>
                                <?= htmlspecialchars($validation['lnR'] ?? '') ?>
                            </small>
                        </div>
                        <div class="">
                            <label htmlFor="phn" class="">
                                Phone Number
                            </label>
                            <input id="phn" type="number" name="phn" class='form-input disabled:bg-gray-100'
                                placeholder='Enter Phone Here' value="<?= htmlspecialchars($old['phn'] ?? '') ?>" />
                            <small style='color: #b91c1c;' class="mt-1">
                                <?= htmlspecialchars($validation['pnR'] ?? '') ?>
                                <?= htmlspecialchars($validation['vpn'] ?? '') ?>
                                <?= htmlspecialchars($validation['ex2'] ?? '') ?>
                            </small>
                        </div>
                        <div class="">
                            <label htmlFor="email" class="">
                                Email
                            </label>
                            <input id="email" type="text" name="email" class='form-input disabled:bg-gray-100'
                                placeholder='Enter Email Here' value="<?= htmlspecialchars($old['email'] ?? '') ?>" />
                            <small style='color: #b91c1c;' class="mt-1">
                                <?= htmlspecialchars($validation['emR'] ?? '') ?>
                                <?= htmlspecialchars($validation['vem'] ?? '') ?>
                                <?= htmlspecialchars($validation['ex1'] ?? '') ?>
                            </small>
                        </div>
                        <div class="">
                            <label htmlFor="dob" class="">
                                D.O.B
                            </label>
                            <input id="dob" type="date" name="dob" class='form-input disabled:bg-gray-100'
                                placeholder='Enter Date of Birth' value="<?= htmlspecialchars($old['dob'] ?? '') ?>" />
                        </div>
                        <div class="">
                            <label htmlFor="gender" class="">
                                Gender
                            </label>
                            <script>
                            document.addEventListener("DOMContentLoaded", function () {
                                NiceSelect.bind(document.getElementById("gender"), { ... });
                                NiceSelect.bind(document.getElementById("selectRole"), { ... });
                            });
                            </script>
                            <select id="gender" class="form-select" name="gender">
                                <option value="" disabled
                                    <?php if (!isset($_POST['gender']) || $_POST['gender'] === '') echo 'selected'; ?>>
                                    Select Gender</option>
                                    <option value="1" <?= (isset($old['gender']) && $old['gender'] == '1') ? 'selected' : '' ?>>Male</option>
                                    <option value="2" <?= (isset($old['gender']) && $old['gender'] == '2') ? 'selected' : '' ?>>Female</option>
                            </select>
                        </div>
                        <div class="">
                            <label htmlFor="password" class="">
                                Password
                            </label>
                            <input id="password" type="password" name="password" class='form-input disabled:bg-gray-100'
                                placeholder='Enter Password Here' />
                                <small style='color: #b91c1c;' class="mt-1">
                                    <?= htmlspecialchars($validation['pwR'] ?? '') ?>
                                    <?= htmlspecialchars($validation['vpw'] ?? '') ?>
                                </small>
                        </div>
                        <div class="">
                            <label htmlFor="cpassword" class="">
                                Confirm Password
                            </label>
                            <input id="cpassword" type="password" name="cpassword"
                                class='form-input disabled:bg-gray-100' placeholder='Enter Confirm Password Here' />
                                <small style='color: #b91c1c;' class="mt-1">
                                    <?= htmlspecialchars($validation['cpR'] ?? '') ?>
                                    <?= htmlspecialchars($validation['vcp'] ?? '') ?>
                                </small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="xl:w-96 w-full xl:mt-0 mt-6">
                <div class="panel mb-3">
                    <div class="flex xl:grid-cols-1 lg:grid-cols-4 sm:grid-cols-2 grid-cols-1 gap-2">
                        <button class="btn btn-success w-full gap-2" type="submit" name="submit">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg" class="ltr:mr-2 rtl:ml-2 shrink-0">
                                <path
                                    d="M3.46447 20.5355C4.92893 22 7.28595 22 12 22C16.714 22 19.0711 22 20.5355 20.5355C22 19.0711 22 16.714 22 12C22 11.6585 22 11.4878 21.9848 11.3142C21.9142 10.5049 21.586 9.71257 21.0637 9.09034C20.9516 8.95687 20.828 8.83317 20.5806 8.58578L15.4142 3.41944C15.1668 3.17206 15.0431 3.04835 14.9097 2.93631C14.2874 2.414 13.4951 2.08581 12.6858 2.01515C12.5122 2 12.3415 2 12 2C7.28595 2 4.92893 2 3.46447 3.46447C2 4.92893 2 7.28595 2 12C2 16.714 2 19.0711 3.46447 20.5355Z"
                                    stroke="currentColor" strokeWidth="1.5" />
                                <path
                                    d="M17 22V21C17 19.1144 17 18.1716 16.4142 17.5858C15.8284 17 14.8856 17 13 17H11C9.11438 17 8.17157 17 7.58579 17.5858C7 18.1716 7 19.1144 7 21V22"
                                    stroke="currentColor" strokeWidth="1.5" />
                                <path opacity="1" d="M7 8H13" stroke="currentColor" strokeWidth="1.5"
                                    strokeLinecap="round" />
                            </svg>
                            Publish & Close
                        </button>
                        <a type="button" class="btn btn-danger w-auto" href="/php/pim/pages/users/">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round"
                                strokeLinejoin="round" class="shrink-0">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                        </a>
                    </div>
                    <label class="mt-5 mb-0">
                        <input type="checkbox" name="status" class="form-checkbox" value="1"
                            <?php if (isset($old['status']) && $old['status'] == '1') echo 'checked'; ?> />
                        <span class="mb-0">Enable this User</span>
                    </label>
                </div>
                <div class="panel">
                    <label for="users" class="mb-2 block font-medium text-sm">Choose Role</label>
                    <script>
                    NiceSelect.bind(document.getElementById("selectRole"), {
                        searchable: true,
                        placeholder: 'select',
                        searchtext: 'zoek',
                        selectedtext: 'geselecteerd'
                    });
                    </script>
                    <select id="selectRole" class="form-select" name="selectRole">
                        <option value="" disabled
                            <?php if (!isset($old['selectRole']) || $old['selectRole'] === '') echo 'selected'; ?>>
                            Select Role</option>
                        <?php
                            foreach ($roleOptions as $role) {
                                $selected = (isset($old['selectRole']) && $old['selectRole'] == $role['id']) ? 'selected' : '';
                                echo "<option value='{$role['id']}' $selected>{$role['role']}</option>";
                            }
                        ?>
                    </select>
                </div>
            </div>
        </div>
    </form>
</body>

</html>
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