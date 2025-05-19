<?php
include '../../dbcon.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: /php/pim/auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$role_id = $_SESSION['role_id'];

$create = false;
$edit = false;
$delete = false;

$perm = "SELECT * FROM permission 
         JOIN modules ON modules.id = permission.module_id 
         WHERE role_id = $role_id AND module_id = 2";
$result = mysqli_query($conn, $perm);

$row = mysqli_fetch_assoc($result);

if (!$row) {
    die("❌ No permission entry found for role_id = $role_id and module_id = 2");
}

$can_view = $row['view'];
$can_view_all = $row['view_all'];
$create = $row['create'] == 1;
$edit = $row['edit'] == 1;
$delete = $row['delete'] == 1;

if ($can_view != 1 && $can_view_all != 1) {
    header('Location: /php/pim/');
    exit;
}

include '../../header-main.php';

$condition = ($can_view_all == 1) ? "" : "WHERE users.creator_id = $user_id";

$query = "SELECT users.id, users.first_name, users.last_name, users.email, users.phone_number, 
                 users.date_of_birth, users.gender, role.role, users.status 
          FROM users 
          LEFT JOIN role ON users.role = role.id 
          $condition 
          ORDER BY users.id DESC";

$result = mysqli_query($conn, $query);

while ($row = mysqli_fetch_assoc($result)) {
    $id = $row['id'];
    $fullName = $row['first_name'] . " " . $row['last_name'];
    $contactDetails = '<div style="color:orange;" class="underline"><a href="">' . $row['email'] . '</a><br><a href="">' . $row['phone_number'] . '</a></div>';
    $dob = !empty($row['date_of_birth']) && $row['date_of_birth'] != '0000-00-00' ? $row['date_of_birth'] : '';
    $age = ($dob && date_diff(date_create($dob), date_create('now'))->y >= 1) 
        ? '<div class="font-semibold">' . date_diff(date_create($dob), date_create('now'))->y . '</div>' 
        : '';
    $gender = ($row['gender'] == 1) ? "Male" : "Female";
    $role = $row['role'];
    $status = $row['status'] == 0 ? '<div class="text-danger font-semibold">Disabled</div>' : '<div class="text-success font-semibold">Enabled</div>';
    $action = '<div style="display: flex; gap: 10px; align-items: center;">';

    // Always show the "view" button
    $action .= '<a href="/php/pim/pages/users/view.php?id=' . $row['id'] . '">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M2 12C2 13.6394 2.42496 14.1915 3.27489 15.2957C4.97196 17.5004 7.81811 20 12 20C16.1819 20 19.028 17.5004 20.7251 15.2957C21.575 14.1915 22 13.6394 22 12C22 10.3606 21.575 9.80853 20.7251 8.70433C19.028 6.49956 16.1819 4 12 4C7.81811 4 4.97196 6.49956 3.27489 8.70433C2.42496 9.80853 2 10.3606 2 12Z" stroke="currentColor"/>
            <path fillRule="evenodd" clipRule="evenodd" d="M8.25 12C8.25 9.92893 9.92893 8.25 12 8.25C14.0711 8.25 15.75 9.92893 15.75 12C15.75 14.0711 14.0711 15.75 12 15.75C9.92893 15.75 8.25 14.0711 8.25 12ZM9.75 12C9.75 10.7574 10.7574 9.75 12 9.75C13.2426 9.75 14.25 10.7574 14.25 12C14.25 13.2426 13.2426 14.25 12 14.25C10.7574 14.25 9.75 13.2426 9.75 12Z" stroke="currentColor"/>
        </svg>
    </a>';

    if ($edit) {
        $action .= '<a href="/php/pim/pages/users/edit.php?id=' . $row['id'] . '">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M15.2869 3.15178L14.3601 4.07866L5.83882 12.5999L5.83881 12.5999C5.26166 13.1771 4.97308 13.4656 4.7249 13.7838C4.43213 14.1592 4.18114 14.5653 3.97634 14.995C3.80273 15.3593 3.67368 15.7465 3.41556 16.5208L2.32181 19.8021L2.05445 20.6042C1.92743 20.9852 2.0266 21.4053 2.31063 21.6894C2.59466 21.9734 3.01478 22.0726 3.39584 21.9456L4.19792 21.6782L7.47918 20.5844L7.47919 20.5844C8.25353 20.3263 8.6407 20.1973 9.00498 20.0237C9.43469 19.8189 9.84082 19.5679 10.2162 19.2751C10.5344 19.0269 10.8229 18.7383 11.4001 18.1612L11.4001 18.1612L19.9213 9.63993L20.8482 8.71306C22.3839 7.17735 22.3839 4.68748 20.8482 3.15178C19.3125 1.61607 16.8226 1.61607 15.2869 3.15178Z" stroke="currentColor" strokeWidth="1.5"/>
                <path d="M14.36 4.07812C14.36 4.07812 14.4759 6.04774 16.2138 7.78564C17.9517 9.52354 19.9213 9.6394 19.9213 9.6394M4.19789 21.6777L2.32178 19.8015" stroke="currentColor" strokeWidth="1.5"/>
            </svg>
        </a>';
    }

    if ($delete){
        $action .= '<a href="/php/pim/pages/users/delete.php?id=' . $row['id'] . '">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M20.5001 6H3.5" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round"/>
                <path d="M18.8334 8.5L18.3735 15.3991C18.1965 18.054 18.108 19.3815 17.243 20.1907C16.378 21 15.0476 21 12.3868 21H11.6134C8.9526 21 7.6222 21 6.75719 20.1907C5.89218 19.3815 5.80368 18.054 5.62669 15.3991L5.16675 8.5" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round"/>
                <path d="M9.5 11L10 16" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round"/>
                <path d="M14.5 11L14 16" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round"/>
                <path d="M6.5 6C6.55588 6 6.58382 6 6.60915 5.99936C7.43259 5.97849 8.15902 5.45491 8.43922 4.68032C8.44784 4.65649 8.45667 4.62999 8.47434 4.57697L8.57143 4.28571C8.65431 4.03708 8.69575 3.91276 8.75071 3.8072C8.97001 3.38607 9.37574 3.09364 9.84461 3.01877C9.96213 3 10.0932 3 10.3553 3H13.6447C13.9068 3 14.0379 3 14.1554 3.01877C14.6243 3.09364 15.03 3.38607 15.2493 3.8072C15.3043 3.91276 15.3457 4.03708 15.4286 4.28571L15.5257 4.57697C15.5433 4.62992 15.5522 4.65651 15.5608 4.68032C15.841 5.45491 16.5674 5.97849 17.3909 5.99936C17.4162 6 17.4441 6 17.5 6" stroke="currentColor" strokeWidth="1.5"/>
            </svg>
        </a>';
    }
    
    $users[] = [$id, $fullName, $contactDetails, $dob, $age, $gender, $role, $status, $action];
}
?>