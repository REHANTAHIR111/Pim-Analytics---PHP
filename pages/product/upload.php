<?php
header('Content-Type: application/json');

$uploadDir = __DIR__ . '/../uploads/'; // Save in /uploads/ folder
$uploadUrl = 'http://localhost/php/pim/pages/uploads/';

$response = ['success' => false];

if (!empty($_FILES['file']['name'])) {
    $fileName = time() . '_' . basename($_FILES['file']['name']);
    $targetPath = $uploadDir . $fileName;

    if (move_uploaded_file($_FILES['file']['tmp_name'], $targetPath)) {
        $response['success'] = true;
        $response['url'] = $uploadUrl . $fileName;
    } else {
        $response['error'] = 'Failed to move uploaded file.';
    }
} else {
    $response['error'] = 'No file uploaded.';
}

echo json_encode($response);
?>
