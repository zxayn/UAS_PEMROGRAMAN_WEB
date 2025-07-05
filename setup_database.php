<?php
// --- Konfigurasi Database ---
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hsr_characters_db";

// --- Membuat Koneksi ---
$conn = new mysqli($servername, $username, $password);

// Cek Koneksi
if ($conn->connect_error) {
    die("Koneksi Gagal: " . $conn->connect_error);
}

// Membuat Database jika belum ada
$sql_create_db = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql_create_db) === TRUE) {
    echo "Database '$dbname' berhasil dibuat atau sudah ada.<br>";
} else {
    die("Error saat membuat database: " . $conn->error);
}

// Memilih Database
$conn->select_db($dbname);

// --- Membuat Tabel `characters` ---
$sql_create_table = "
CREATE TABLE IF NOT EXISTS characters (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    title VARCHAR(200) DEFAULT 'Waktu Event Warp Karakter',
    description TEXT NOT NULL,
    category VARCHAR(200) NOT NULL, -- 'Baru', 'Rerun', 'Kolaborasi'
    rarity INT(1) NOT NULL DEFAULT 5,
    event_time VARCHAR(200),
    image VARCHAR(255) NOT NULL,
    path_icon VARCHAR(255) DEFAULT NULL, -- Untuk ikon path/element
    reg_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if ($conn->query($sql_create_table) === TRUE) {
    echo "Tabel 'characters' berhasil dibuat atau sudah ada.<br>";
} else {
    die("Error saat membuat tabel: " . $conn->error);
}

echo "<h3>Setup Database Selesai!</h3>";
echo "Silakan hapus atau ganti nama file ini demi keamanan.";

$conn->close();
?>