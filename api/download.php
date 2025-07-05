<?php
if (isset($_GET['file'])) {
    $filename = basename($_GET['file']);
    $filepath = '../uploads/' . $filename;

    if (file_exists($filepath)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filepath));
        flush();
        readfile($filepath);
        exit;
    } else {
        http_response_code(404);
        die('File not found.');
    }
} else {
    http_response_code(400);
    die('Invalid request.');
}
?>