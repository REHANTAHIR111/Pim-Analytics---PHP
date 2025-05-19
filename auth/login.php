<?php
ob_start();
include '../header-main-auth.php'; 

$em = $ps = $vem = $error = $vps = '';
$email = $password = '';
$successCheck = false;

$pattern = '/^([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}|[0-9]{11})$/';

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (isset($_SESSION['user_id'])) {
    header("location:javascript://history.go(-1)");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    include '../dbcon.php';

    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    if ($email || $password) {
        $successCheck1 = true;
        $successCheck2 = true;

        if (!preg_match($pattern, $email)) {
            $vem = 'Please enter a valid email or Phone';
            $successCheck1 = false;
        }
        if ($password && !preg_match('/^[a-zA-Z0-9._%+-@]{8,16}+$/', $password)) {
            $vps = 'Please enter a valid password';
            $successCheck2 = false;
        }
        if ($successCheck1 && $successCheck2) {
            $successCheck = true;
        }
    } else {
        if (!$email || !$password) {
            $em = !$email ? 'Please enter an email or phone' : '';
            $ps = !$password ? 'Please enter a password' : '';
        }
    }

    if ($successCheck == true) {
        $sql = "SELECT users.id as id, users.first_name as name, users.email as email, users.password as password, role.role as role, users.role as role_id FROM `users` left join `role` on `users`.`role` = `role`.`id` WHERE (`users`.`email` = '$email' OR `users`.`phone_number` = '$email') and `users`.`password` = '" . md5($password) . "'";
        $result = mysqli_query($conn, $sql);

        if ($result && mysqli_num_rows($result) == 1) {
            $row = mysqli_fetch_assoc($result);
        
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['email'] = $row['email'];
            $_SESSION['role'] = $row['role'];
            $_SESSION['role_id'] = $row['role_id'];
            $_SESSION['name'] = $row['name'];
        
            header('Location: http://localhost/php/pim');
            exit();
        }
        
    }
}
ob_end_flush();
?>

<div x-data="auth">
    <div class="absolute inset-0">
        <img src="/php/pim/assets/images/auth/bg-gradient.png" alt="image" class="h-full w-full object-cover" />
    </div>
    <div
        class="relative flex min-h-screen items-center justify-center bg-[url(/php/pim/assets/images/auth/map.png)] bg-cover bg-center bg-no-repeat px-6 py-10 dark:bg-[#060818] sm:px-16">
        <img src="/php/pim/assets/images/auth/coming-soon-object1.png" alt="image"
            class="absolute left-0 top-1/2 h-full max-h-[893px] -translate-y-1/2" />
        <img src="/php/pim/assets/images/auth/coming-soon-object2.png" alt="image"
            class="absolute left-24 top-0 h-40 md:left-[30%]" />
        <img src="/php/pim/assets/images/auth/coming-soon-object3.png" alt="image"
            class="absolute right-0 top-0 h-[300px]" />
        <img src="/php/pim/assets/images/auth/polygon-object.svg" alt="image" class="absolute bottom-0 end-[28%]" />
        <div
            class="relative flex w-full max-w-[1502px] flex-col justify-between overflow-hidden rounded-md bg-white/60 backdrop-blur-lg dark:bg-black/50 lg:min-h-[758px] lg:flex-row lg:gap-10 xl:gap-0">
            <div
                class="relative hidden w-full items-center justify-center bg-[linear-gradient(225deg,rgba(239,18,98,1)_0%,rgba(67,97,238,1)_100%)] p-5 lg:inline-flex lg:max-w-[835px] xl:-ms-32 ltr:xl:skew-x-[14deg] rtl:xl:skew-x-[-14deg]">
                <div
                    class="absolute inset-y-0 w-8 from-primary/10 via-transparent to-transparent ltr:-right-10 ltr:bg-gradient-to-r rtl:-left-10 rtl:bg-gradient-to-l xl:w-16 ltr:xl:-right-20 rtl:xl:-left-20">
                </div>
                <div class="ltr:xl:-skew-x-[14deg] rtl:xl:skew-x-[14deg]">
                    <div class='flex justify-center gap-2 uppercase items-center mb-20 text-3xl font-bold' style='text-shadow: 3.5px 3.5px 2px black; color: lightblue; font-family:"Gill Sans Extrabold", sans-serif'>
                        Pim Analytics
                    </div>
                    <div class="mt-14 hidden w-full max-w-[430px] lg:block">
                        <img src="/php/pim/assets/images/auth/login.svg" alt="Cover Image" class="w-full" />
                    </div>
                </div>
            </div>
            <div
                class="relative flex w-full flex-col items-center justify-center gap-6 px-4 pb-16 pt-6 sm:px-6 lg:max-w-[667px]">
                <div class="flex w-full max-w-[440px] items-center gap-2 lg:absolute lg:end-6 lg:top-6 lg:max-w-full">
                    <a href="/" class="block w-8 lg:hidden">
                        <img src="/php/pim/assets/images/logo.svg" alt="Logo" class="w-full" />
                    </a>
                    <div class="dropdown ms-auto w-max" x-data="dropdown" @click.outside="open = false">
                        <ul x-cloak x-show="open" x-transition x-transition.duration.300ms
                            class="top-11 grid w-[280px] grid-cols-2 gap-y-2 !px-2 font-semibold text-dark ltr:-right-14 rtl:-left-14 dark:text-white-dark dark:text-white-light/90 sm:ltr:-right-2 sm:rtl:-left-2">
                            <template x-for="item in languages">
                                <li>
                                    <a href="javascript:;" class="hover:text-primary"
                                        @click="$store.app.toggleLocale(item.value),toggle()"
                                        :class="{'bg-primary/10 text-primary' : $store.app.locale == item.value}">
                                        <img class="h-5 w-5 rounded-full object-cover"
                                            :src="`/php/pim/assets/images/flags/${item.value.toUpperCase()}.svg`"
                                            alt="image" />
                                        <span class="ltr:ml-3 rtl:mr-3" x-text="item.key"></span>
                                    </a>
                                </li>
                            </template>
                        </ul>
                    </div>
                </div>
                <div class="w-full max-w-[440px] lg:mt-16">
                    <div class="mb-10">
                        <h1 class="text-3xl font-extrabold uppercase !leading-snug text-primary md:text-4xl">Sign in
                        </h1>
                        <p class="text-base font-bold leading-normal text-white-dark">Enter your email and password to
                            login</p>
                    </div>
                    <form class="space-y-5 dark:text-white" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
                        <div>
                            <label for="Email">Email</label>
                            <div class="relative text-white-dark mb-[3px]">
                                <input id="email" name="email" type="text" placeholder="Enter Email"
                                    value='<?php echo $email; ?>' class="form-input ps-10 placeholder: mb-[3px]" />
                                <span class="absolute start-4 top-1/2 -translate-y-1/2">
                                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                                        <path opacity="0.5"
                                            d="M10.65 2.25H7.35C4.23873 2.25 2.6831 2.25 1.71655 3.23851C0.75 4.22703 0.75 5.81802 0.75 9C0.75 12.182 0.75 13.773 1.71655 14.7615C2.6831 15.75 4.23873 15.75 7.35 15.75H10.65C13.7613 15.75 15.3169 15.75 16.2835 14.7615C17.25 13.773 17.25 12.182 17.25 9C17.25 5.81802 17.25 4.22703 16.2835 3.23851C15.3169 2.25 13.7613 2.25 10.65 2.25Z"
                                            fill="currentColor" />
                                        <path
                                            d="M14.3465 6.02574C14.609 5.80698 14.6445 5.41681 14.4257 5.15429C14.207 4.89177 13.8168 4.8563 13.5543 5.07507L11.7732 6.55931C11.0035 7.20072 10.4691 7.6446 10.018 7.93476C9.58125 8.21564 9.28509 8.30993 9.00041 8.30993C8.71572 8.30993 8.41956 8.21564 7.98284 7.93476C7.53168 7.6446 6.9973 7.20072 6.22761 6.55931L4.44652 5.07507C4.184 4.8563 3.79384 4.89177 3.57507 5.15429C3.3563 5.41681 3.39177 5.80698 3.65429 6.02574L5.4664 7.53583C6.19764 8.14522 6.79033 8.63914 7.31343 8.97558C7.85834 9.32604 8.38902 9.54743 9.00041 9.54743C9.6118 9.54743 10.1425 9.32604 10.6874 8.97558C11.2105 8.63914 11.8032 8.14522 12.5344 7.53582L14.3465 6.02574Z"
                                            fill="currentColor" />
                                    </svg>
                                </span>
                            </div>
                            <small style="color: #b91c1c;" class="text-[13px]"><?php echo $em; echo $vem; ?></small>
                        </div>
                        <div>
                            <label for="Password">Password</label>
                            <div class="relative text-white-dark mb-[3px]">
                                <input id="password" name="password" type="password" placeholder="Enter Password"
                                    class="form-input ps-10 placeholder:text-white-dark" />
                                <span class="absolute start-4 top-1/2 -translate-y-1/2">
                                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                                        <path opacity="0.5"
                                            d="M1.5 12C1.5 9.87868 1.5 8.81802 2.15901 8.15901C2.81802 7.5 3.87868 7.5 6 7.5H12C14.1213 7.5 15.182 7.5 15.841 8.15901C16.5 8.81802 16.5 9.87868 16.5 12C16.5 14.1213 16.5 15.182 15.841 15.841C15.182 16.5 14.1213 16.5 12 16.5H6C3.87868 16.5 2.81802 16.5 2.15901 15.841C1.5 15.182 1.5 14.1213 1.5 12Z"
                                            fill="currentColor" />
                                        <path
                                            d="M6 12.75C6.41421 12.75 6.75 12.4142 6.75 12C6.75 11.5858 6.41421 11.25 6 11.25C5.58579 11.25 5.25 11.5858 5.25 12C5.25 12.4142 5.58579 12.75 6 12.75Z"
                                            fill="currentColor" />
                                        <path
                                            d="M9 12.75C9.41421 12.75 9.75 12.4142 9.75 12C9.75 11.5858 9.41421 11.25 9 11.25C8.58579 11.25 8.25 11.5858 8.25 12C8.25 12.4142 8.58579 12.75 9 12.75Z"
                                            fill="currentColor" />
                                        <path
                                            d="M12.75 12C12.75 12.4142 12.4142 12.75 12 12.75C11.5858 12.75 11.25 12.4142 11.25 12C11.25 11.5858 11.5858 11.25 12 11.25C12.4142 11.25 12.75 11.5858 12.75 12Z"
                                            fill="currentColor" />
                                        <path
                                            d="M5.0625 6C5.0625 3.82538 6.82538 2.0625 9 2.0625C11.1746 2.0625 12.9375 3.82538 12.9375 6V7.50268C13.363 7.50665 13.7351 7.51651 14.0625 7.54096V6C14.0625 3.20406 11.7959 0.9375 9 0.9375C6.20406 0.9375 3.9375 3.20406 3.9375 6V7.54096C4.26488 7.51651 4.63698 7.50665 5.0625 7.50268V6Z"
                                            fill="currentColor" />
                                    </svg>
                                </span>
                            </div>
                            <small style="color: #b91c1c;" class="text-[13px]"><?php echo $ps; echo $vps; ?></small>
                        </div>
                        <button type="submit"
                            class="btn btn-gradient !mt-6 w-full border-0 uppercase shadow-[0_10px_20px_-10px_rgba(67,97,238,0.44)]">
                            Sign in
                        </button>
                    </form>
                </div>
                <p class="absolute bottom-6 w-full text-center dark:text-white">
                    © <span id="footer-year">2025</span>. Pim analytics All Rights Reserved.
                </p>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('auth', () => ({
        languages: [{
            id: 1,
            key: 'Chinese',
            value: 'zh',
        }, {
            id: 2,
            key: 'Danish',
            value: 'da',
        }, {
            id: 3,
            key: 'English',
            value: 'en',
        }, {
            id: 4,
            key: 'French',
            value: 'fr',
        }, {
            id: 5,
            key: 'German',
            value: 'de',
        }, {
            id: 6,
            key: 'Greek',
            value: 'el',
        }, {
            id: 7,
            key: 'Hungarian',
            value: 'hu',
        }, {
            id: 8,
            key: 'Italian',
            value: 'it',
        }, {
            id: 9,
            key: 'Japanese',
            value: 'ja',
        }, {
            id: 10,
            key: 'Polish',
            value: 'pl',
        }, {
            id: 11,
            key: 'Portuguese',
            value: 'pt',
        }, {
            id: 12,
            key: 'Russian',
            value: 'ru',
        }, {
            id: 13,
            key: 'Spanish',
            value: 'es',
        }, {
            id: 14,
            key: 'Swedish',
            value: 'sv',
        }, {
            id: 15,
            key: 'Turkish',
            value: 'tr',
        }, {
            id: 16,
            key: 'Arabic',
            value: 'ae',
        }, ],
    }));
});
</script>
<?php include '../footer-main-auth.php'; ?>