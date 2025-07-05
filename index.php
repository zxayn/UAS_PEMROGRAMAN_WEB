<?php
// --- KONEKSI & PENGAMBILAN DATA ---
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hsr_characters_db";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) { die("Koneksi Database Gagal: " . $conn->connect_error); }

// Set karakteristik koneksi
$characters = [];
$sql = "SELECT * FROM characters ORDER BY category, name";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $characters[$row['id']] = $row;
    }
}

$initial_character_id = 0;
if (!empty($characters)) {
    $initial_character_id = array_key_first($characters);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Honkai: Star Rail</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" href="assets/img/icon.ico" type="image/x-icon">
</head>
<body>

    <div id="loading-screen"><div class="spinner"></div><p>Sabar Yah...</p></div>

    <div id="app-wrapper" class="hidden">
        <section id="home-section">
            <header class="top-bar">
                <div class="logo"><img src="assets/img/starrail_logo.png" alt="Star Rail Logo"></div>
                <div class="top-icons">
                    <i class="fas fa-globe"></i>
                    <i class="fas fa-download"></i>
                    <a href="#" title="Bagikan" style="color: inherit;"><i class="fas fa-share-alt"></i></a>
                </div>
            </header>
            <div class="hero-text"><img src="assets/img/kelak_mentari.png" alt="Hero Title" class="hero-title-image"></div>
            <div class="enter-prompt"><span>Ketuk di mana saja untuk melanjutkan</span></div>
        </section>

        <section id="database-section">
            <nav class="sidebar">
                <h3 class="sidebar-title">Honkai: Star Rail</h3>
                <ul class="nav flex-column" id="character-list">
                    <?php
                    $current_category = '';
                    if (!empty($characters)):
                        foreach($characters as $char_item):
                            if ($char_item['category'] != $current_category) {
                                $current_category = $char_item['category'];
                                echo "<li class=\"nav-item category-header\">".htmlspecialchars(strtoupper($current_category))."</li>";
                            }
                            $active_class = ($char_item['id'] == $initial_character_id) ? 'active' : '';
                            echo <<<HTML
                            <li class="nav-item">
                                <a class="nav-link $active_class" href="#" data-id="{$char_item['id']}">
                                    <i class="arrow-icon"></i> {$char_item['name']}
                                </a>
                                <div class="nav-actions">
                                    <span class="btn-edit" data-id="{$char_item['id']}" data-bs-toggle="modal" data-bs-target="#editCharacterModal"><i class="fas fa-pencil-alt"></i></span>
                                    <span class="btn-delete" data-id="{$char_item['id']}" data-name="{$char_item['name']}"><i class="fas fa-trash-alt"></i></span>
                                </div>
                            </li>
                            HTML;
                        endforeach;
                    else:
                        echo '<li class="nav-item text-white-50 no-char-message">Belum ada karakter.</li>';
                    endif;
                    ?>
                    <li class="nav-item category-header">Kelola Karakter</li>
                    <li class="nav-item"><a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#addCharacterModal"><i class="arrow-icon"></i> Tambah Karakter</a></li>
                </ul>
            </nav>
            <main class="content-area"><div id="character-display"></div></main>
        </section>
    </div>

    <div class="modal fade" id="addCharacterModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Tambah Karakter Baru</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <form id="addCharacterForm" enctype="multipart/form-data">
                        <div class="mb-3"><label for="add-name" class="form-label">Nama</label><input type="text" class="form-control" id="add-name" name="name" required></div>
                        <div class="mb-3"><label for="add-category" class="form-label">Kategori</label><select class="form-select" id="add-category" name="category" required><option value="Baru">Baru</option><option value="Rerun">Rerun</option><option value="Kolaborasi">Kolaborasi</option><option value="Light Cone">Light Cone</option></select></div>
                        <div class="mb-3"><label for="add-rarity" class="form-label">Rarity</label><input type="number" class="form-control" id="add-rarity" name="rarity" min="1" max="5" value="5" required></div>
                        <div class="mb-3"><label for="add-title" class="form-label">Judul Event</label><input type="text" class="form-control" id="add-title" name="title" value="Waktu Event Warp Karakter"></div>
                        <div class="mb-3"><label for="add-event_time" class="form-label">Waktu Event</label><input type="text" class="form-control" id="add-event_time" name="event_time" placeholder="Contoh: 2024/01/01 - 2024/01/15" required></div>
                        <div class="mb-3"><label for="add-description" class="form-label">Deskripsi</label><textarea class="form-control" id="add-description" name="description" rows="3" required></textarea></div>
                        <div class="mb-3"><label for="add-image" class="form-label">Gambar Artwork</label><input class="form-control" type="file" id="add-image" name="image" required></div>
                        <div class="mb-3"><label for="add-path_logo" class="form-label">Gambar Ikon/Logo</label><input class="form-control" type="file" id="add-path_logo" name="path_logo"></div>
                        </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary" form="addCharacterForm">Simpan</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editCharacterModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Edit Karakter</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
                <div class="modal-body" id="editCharacterModalBody"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-warning" id="saveEditFormButton">Simpan Perubahan</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        const InitialCharacterId = <?php echo json_encode($initial_character_id); ?>;
    </script>
    <script src="assets/js/script.js"></script>
    
</body>
</html>