<?php
    include "../../../dbcon.php";
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
            mysqli_query($conn, "DELETE FROM sub_tags WHERE id IN ($idList)");
            header("Location: ".$_SERVER['HTTP_REFERER']."");
            exit;
        } else {
            header("Location: ".$_SERVER['HTTP_REFERER']."");
        }
    } else {
        echo "Invalid request.";
    }
?>