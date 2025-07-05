<?php
header('Content-Type: application/json'); // Set header untuk respons JSON

$response = ['success' => false, 'message' => 'Permintaan tidak valid.'];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    // Konfigurasi & Koneksi Database
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "hsr_characters_db";
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        $response['message'] = 'Koneksi database gagal.';
        echo json_encode($response);
        exit();
    }
    
    $id = (int)$_POST['id'];
    
    // 1. Ambil nama file gambar sebelum menghapus data dari DB
    $stmt_select = $conn->prepare("SELECT image FROM characters WHERE id = ?");
    $stmt_select->bind_param("i", $id);
    $stmt_select->execute();
    $result = $stmt_select->get_result();
    if ($row = $result->fetch_assoc()) {
        $image_to_delete = $row['image'];
        
        // 2. Hapus data dari database
        $stmt_delete = $conn->prepare("DELETE FROM characters WHERE id = ?");
        $stmt_delete->bind_param("i", $id);
        
        if ($stmt_delete->execute()) {
            // 3. Hapus file gambar dari folder 'uploads' jika ada
            $filepath = 'uploads/' . $image_to_delete;
            if (file_exists($filepath)) {
                unlink($filepath);
            }
            $response['success'] = true;
            $response['message'] = 'Karakter berhasil dihapus.';
        } else {
            $response['message'] = 'Gagal menghapus karakter dari database.';
        }
        $stmt_delete->close();
    } else {
        $response['message'] = 'Karakter tidak ditemukan.';
    }
    $stmt_select->close();
    $conn->close();
}

echo json_encode($response);
?>