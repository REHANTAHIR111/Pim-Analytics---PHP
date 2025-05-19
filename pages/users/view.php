<?php
ob_start();
include '../../dbcon.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: /php/pim/auth/login.php");
    exit;
}

$roleid = $_SESSION['role_id'];

$perm = "SELECT * FROM permission JOIN modules ON modules.id = permission.module_id WHERE role_id = $roleid AND module_id = 2";
$result = mysqli_query($conn, $perm);
$row = mysqli_fetch_assoc($result);

include '../../header-main.php';

if (headers_sent()) {
    error_log("Headers already sent. Cannot redirect.");
    exit;
}

$fetchRole = "SELECT * FROM `role`";
$resultRole = mysqli_query($conn, $fetchRole);
$roleOptions = [];
while ($row = mysqli_fetch_assoc($resultRole)) {
    $roleOptions[] = $row;
}
$userId = null;
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
        $userId = (int)$_GET['id'] ?? null;
    }
}
if (!$userId) {
    echo "
        <div class='relative top-20 bg-danger p-4 rounded shadow-md shadow-danger text-white text-lg' style='width: 436px; left:32%;'>
            <span class='font-bold'>Warning:</span> <br>
            Please provide a Valid ID!
        </div>
    ";
    exit;
}

$fetchUser = "SELECT * FROM `users` WHERE `id` = $userId";
$resultUser = mysqli_query($conn, $fetchUser);
$user = mysqli_fetch_assoc($resultUser);

ob_end_flush();
?>

    <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <script src="/php/pim/assets/js/nice-select2.js"></script>
            <link rel="stylesheet" href="/php/pim/assets/js/nice-select2.css">
            <title>Add Users</title>
        </head>
        <body>
            <form action="<?php echo $_SERVER['PHP_SELF'];?>" class='form.ajax-form' method='POST'>
                <div class="flex xl:flex-row flex-col gap-2">
                    <div class="px-0 flex-1 py-0 ltr:xl:mr-1 rtl:xl:ml-1">
                        <div class="panel mb-3">
                            <div class='grid grid-cols-2 gap-3'>
                                <div class="">
                                    <label htmlFor="fname" class="">
                                        First Name
                                    </label>
                                    <input
                                        id="fname"
                                        type="text"
                                        name="fname"
                                        class='form-input disabled:bg-gray-100'
                                        placeholder='Enter First Name Here'
                                        value='<?php echo $user['first_name'];?>'
                                        disabled
                                    />
                                    <input type="hidden" name="id" value="<?php echo $userId; ?>">
                                </div>
                                <div class="">
                                    <label htmlFor="lname" class="">
                                        Last Name
                                    </label>
                                    <input
                                        id="lname"
                                        type="text"
                                        name="lname"
                                        class='form-input disabled:bg-gray-100'
                                        placeholder='Enter Last Name Here'
                                        value='<?php echo $user['last_name']; ?>'
                                        disabled
                                    />
                                </div>
                                <div class="">
                                    <label htmlFor="phn" class="">
                                        Phone Number
                                    </label>
                                    <input
                                        id="phn"
                                        type="number"
                                        name="phn"
                                        class='form-input disabled:bg-gray-100'
                                        placeholder='Enter Phone Here'
                                        value='<?php echo $user['phone_number']; ?>'
                                        disabled
                                    />
                                </div>
                                <div class="">
                                    <label htmlFor="email" class="">
                                        Email
                                    </label>
                                    <input
                                        id="email"
                                        type="text"
                                        name="email"
                                        class='form-input disabled:bg-gray-100'
                                        placeholder='Enter Email Here'
                                        value='<?php echo $user['email']; ?>'
                                        disabled
                                    />
                                </div>
                                <div class="">
                                    <label htmlFor="dob" class="">
                                        D.O.B
                                    </label>
                                    <input
                                        id="dob"
                                        type="date"
                                        name="dob"
                                        class='form-input disabled:bg-gray-100'
                                        placeholder='Enter Date of Birth'
                                        value='<?php echo $user['date_of_birth']; ?>'
                                        disabled
                                    />
                                </div>
                                <div class="">
                                    <label htmlFor="gender" class="">
                                        Gender
                                    </label>
                                    <script>NiceSelect.bind(document.getElementById("gender"), {searchable: true, placeholder: 'select', searchtext: 'zoek', selectedtext: 'geselecteerd'});</script>
                                    <select disabled id="gender" class="form-select" name="gender">
                                        <option value="" disabled <?php if (!isset($_POST['gender']) || $_POST['gender'] == '') echo 'selected'; ?>>Select Gender</option>
                                        <option value="1" <?php if ($user['gender'] == '1') echo 'selected'; ?>>Male</option>
                                        <option value="2" <?php if ($user['gender'] == '2') echo 'selected'; ?>>Female</option>
                                    </select>
                                </div>
                                <div class="">
                                    <label htmlFor="password" class="">
                                        Password
                                    </label>
                                    <input
                                        id="password"
                                        type="password"
                                        name="password"
                                        class='form-input disabled:bg-gray-100'
                                        placeholder='Enter Password Here'
                                        disabled
                                    />
                                </div>
                                <div class="">
                                    <label htmlFor="cpassword" class="">
                                        Confirm Password
                                    </label>
                                    <input
                                        id="cpassword"
                                        type="password"
                                        name="cpassword"
                                        class='form-input disabled:bg-gray-100'
                                        placeholder='Enter Confirm Password Here'
                                        disabled
                                    />
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
                                    href="/php/pim/pages/users/"
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
                            <label class="mt-5 mb-0">
                                <input
                                    type="checkbox"
                                    name="status"
                                    class="form-checkbox"
                                    value="1"
                                    disabled
                                    <?php if (isset($_POST['status']) && $_POST['status'] == '1' || $user['status'] == 1) echo 'checked'; ?>
                                />
                                <span class="mb-0">Enable this User</span>
                            </label>
                        </div>
                        <div class="panel">
                            <label for="users" class="mb-2 block font-medium text-sm">Choose Role</label>
                            <script>NiceSelect.bind(document.getElementById("selectRole"), {searchable: true, placeholder: 'select', searchtext: 'zoek', selectedtext: 'geselecteerd'});</script>
                            <select disabled id="selectRole" name="selectRole" class="form-select">
                                <option value="" disabled <?php if (!isset($user['role'])) echo 'selected'; ?>>Select Role</option>
                                <?php foreach ($roleOptions as $role) { ?>
                                    <option value="<?php echo $role['id']; ?>" <?php if ($role['id'] == $user['role']) echo 'selected'; ?>><?php echo $role['role']; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                </div>
            </form>
        </body>
    </html>
<?php 
    include '../../footer-main.php'; 
?>