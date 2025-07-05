<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hsr_characters_db";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    http_response_code(500); 
    echo json_encode(['success' => false, 'message' => 'Koneksi Database Gagal']);
    exit();
}

$response = ['success' => false, 'message' => 'Permintaan tidak valid atau data tidak lengkap.'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'] ?? null;
    $title = $_POST['title'] ?? null;
    $description = $_POST['description'] ?? null;
    $category = $_POST['category'] ?? null;
    $rarity = isset($_POST['rarity']) ? (int)$_POST['rarity'] : null;
    $event_time = $_POST['event_time'] ?? null;
    
    if (!$name || !$title || !$description || !$category || !$rarity || !$event_time) {
        echo json_encode($response);
        $conn->close();
        exit();
    }

    function uploadFile($file_key, $upload_dir = "../uploads/") { // Path diubah ke ../uploads/
        if (isset($_FILES[$file_key]) && $_FILES[$file_key]["error"] == 0) {
            $original_name = basename($_FILES[$file_key]["name"]);
            $file_extension = pathinfo($original_name, PATHINFO_EXTENSION);
            $safe_filename = uniqid(preg_replace('/[^a-zA-Z0-9-_\.]/', '', pathinfo($original_name, PATHINFO_FILENAME)) . '_', true) . "." . $file_extension;
            $target_file = $upload_dir . $safe_filename;
            if (move_uploaded_file($_FILES[$file_key]["tmp_name"], $target_file)) {
                return $safe_filename;
            }
        }
        return null;
    }

    $image_name = uploadFile('image');
    $path_logo_name = uploadFile('path_logo');

    if (!$image_name) {
        $response['message'] = "Gambar artwork wajib diisi dan berhasil diupload.";
        echo json_encode($response);
        $conn->close();
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO characters (name, title, description, category, rarity, event_time, image, path_logo) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssisss", $name, $title, $description, $category, $rarity, $event_time, $image_name, $path_logo_name);
    
    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Karakter berhasil ditambahkan!';
    } else {
        $response['message'] = "Error saat menyimpan ke database: " . $stmt->error;
    }
    $stmt->close();
}

$conn->close();
echo json_encode($response);
?>