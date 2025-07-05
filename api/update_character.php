<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hsr_characters_db";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Koneksi Gagal']);
    exit();
}

$response = ['success' => false, 'message' => 'Permintaan tidak valid.'];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    $name = $_POST['name'] ?? '';
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $category = $_POST['category'] ?? '';
    $rarity = (int)($_POST['rarity'] ?? 0);
    $event_time = $_POST['event_time'] ?? '';
    
    $current_image = $_POST['current_image'] ?? '';
    $current_path_logo = $_POST['current_path_logo'] ?? '';
    // KODE BARU: Mengambil nama file saat ini dari form
    $current_profile_image = $_POST['current_profile_image'] ?? '';
    $current_element_icon = $_POST['current_element_icon'] ?? '';
    
    function uploadFile($file_key, $current_filename, $upload_dir = "../uploads/") {
        if (isset($_FILES[$file_key]) && $_FILES[$file_key]["error"] == 0) {
            $original_name = basename($_FILES[$file_key]["name"]);
            $file_extension = pathinfo($original_name, PATHINFO_EXTENSION);
            $safe_filename = uniqid(preg_replace('/[^a-zA-Z0-9-_\.]/', '', pathinfo($original_name, PATHINFO_FILENAME)) . '_', true) . "." . $file_extension;
            $target_file = $upload_dir . $safe_filename;
            if (move_uploaded_file($_FILES[$file_key]["tmp_name"], $target_file)) {
                if (!empty($current_filename) && file_exists($upload_dir . $current_filename)) {
                    unlink($upload_dir . $current_filename);
                }
                return $safe_filename;
            }
        }
        return $current_filename;
    }

    $image_name_to_save = uploadFile('image', $current_image);
    $path_logo_name_to_save = uploadFile('path_logo', $current_path_logo);
    // KODE BARU: Memproses upload untuk field baru
    $profile_image_to_save = uploadFile('profile_image', $current_profile_image);
    $element_icon_to_save = uploadFile('element_icon', $current_element_icon);
    
    $stmt = $conn->prepare("UPDATE characters SET name=?, title=?, description=?, category=?, rarity=?, event_time=?, image=?, path_logo=?, profile_image=?, element_icon=? WHERE id=?");
    // Perhatikan penambahan 'ss' sebelum 'i' terakhir
    $stmt->bind_param("ssssisssssi", $name, $title, $description, $category, $rarity, $event_time, $image_name_to_save, $path_logo_name_to_save, $profile_image_to_save, $element_icon_to_save, $id);

    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Karakter berhasil diperbarui!';
    } else {
        $response['message'] = "Error saat memperbarui database: " . $stmt->error;
    }
    $stmt->close();
}

$conn->close();
echo json_encode($response);
?>