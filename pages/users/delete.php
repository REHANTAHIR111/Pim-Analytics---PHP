<?php
    include "../../dbcon.php";
    if ($_SERVER["REQUEST_METHOD"] === "POST" || $_SERVER["REQUEST_METHOD"] === "GET") {
        $ids = [];
        if (isset($_POST["ids"])) {
            $ids = $_POST["ids"];
        } elseif (isset($_GET["id"])) {
            $ids = [$_GET["id"]];
        }
        if (!empty($ids)) {
            $ids = array_map('intval', $ids);
            $idList = implode(',', $ids);
            mysqli_query($conn, "DELETE FROM users WHERE id IN ($idList)");
            header("Location: /php/pim/pages/users/");
            exit;
        } else {
            echo "No Users selected for deletion.";
        }
    } else {
        echo "Invalid request.";
    }
?>