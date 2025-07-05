<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hsr_characters_db";
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Koneksi ke database gagal.']);
    exit();
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    // KEMBALI KE QUERY SEDERHANA
    $stmt = $conn->prepare("SELECT * FROM characters WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $char = $result->fetch_assoc();
        echo json_encode($char);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Karakter tidak ditemukan']);
    }
    $stmt->close();
} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID karakter tidak valid']);
}

$conn->close();
?>