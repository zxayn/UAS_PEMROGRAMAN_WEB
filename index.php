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

    <div id="loading-screen">
        <video autoplay loop muted playsinline class="loading-media">
            <source src="assets/img/loading_screen.mp4" type="video/mp4">
            Browser Anda tidak mendukung tag video.
        </video>
            <p>Sabar Yah...</p>
    </div>

    <div id="app-wrapper" class="hidden">
        <section id="home-section">
            <header class="top-bar">
                <div class="logo"><img src="assets/img/starrail_logo.png" alt="Star Rail Logo"></div>
                <div class="top-icons">
                    <i class="fas fa-globe"></i>
                    <i class="fas fa-user" id="contact-us-icon" data-bs-toggle="modal" data-bs-target="#contactModal">
                        <!-- <img src="assets/img/contact_us.png" alt="Contact Us"> -->
                    </i>
                    <a href="https://github.com/zxayn" title="zxayn" style="color: inherit;"><i class="fab fa-github"></i></a>
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
                                $current_category = htmlspecialchars($char_item['category']);
                                echo "<li class=\"nav-item category-header\">".strtoupper($current_category)."</li>";
                            }
                            $active_class = ($char_item['id'] == $initial_character_id) ? 'active' : '';
                            $char_name = htmlspecialchars($char_item['name']);
                            echo <<<HTML
                            <li class="nav-item">
                                <a class="nav-link $active_class" href="#" data-id="{$char_item['id']}">
                                    <i class="arrow-icon"></i> {$char_name}
                                </a>
                                <div class="nav-actions">
                                    <span class="btn-edit" data-id="{$char_item['id']}" data-bs-toggle="modal" data-bs-target="#editCharacterModal"><i class="fas fa-pencil-alt"></i></span>
                                    <span class="btn-delete" data-id="{$char_item['id']}" data-name="{$char_name}"><i class="fas fa-trash-alt"></i></span>
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
            <main class="content-area">
                <div class="top-icons db-top-icons">
                    <i class="fas fa-globe"></i>
                    <i class="fas fa-user" id="contact-us-icon" data-bs-toggle="modal" data-bs-target="#contactModal">
                        <!-- <img src="assets/img/contact_us.png" alt="Contact Us"> -->
                    </i>
                    <a href="https://github.com/zxayn" title="zxayn" style="color: inherit;"><i class="fab fa-github"></i></a>
                </div>
                
                <div id="character-display"></div>
            </main>
        </section>
    </div>

    <div class="modal fade" id="addCharacterModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Tambah Banner</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <form id="addCharacterForm" enctype="multipart/form-data">
                        <div class="mb-3"><label for="add-name" class="form-label">Nama Karakter/Light Cone</label><input type="text" class="form-control" id="add-name" name="name" required></div>
                        <div class="mb-3"><label for="add-category" class="form-label">Kategori</label><select class="form-select" id="add-category" name="category" required><option value="Baru">Baru</option><option value="Rerun">Rerun</option><option value="Kolaborasi">Kolaborasi</option><option value="Light Cone">Light Cone</option></select></div>
                        <div class="mb-3"><label for="add-rarity" class="form-label">Bintang</label><input type="number" class="form-control" id="add-rarity" name="rarity" min="1" max="5" value="5" required></div>
                        <div class="mb-3"><label for="add-title" class="form-label">Judul Event</label><input type="text" class="form-control" id="add-title" name="title" value="Waktu Event Warp Karakter"></div>
                        <div class="mb-3"><label for="add-event_time" class="form-label">Waktu Event</label><input type="text" class="form-control" id="add-event_time" name="event_time" placeholder="Contoh: 2024/01/01 - 2024/01/15" required></div>
                        <div class="mb-3"><label for="add-description" class="form-label">Deskripsi</label><textarea class="form-control" id="add-description" name="description" rows="3" required></textarea></div>
                        <div class="mb-3"><label for="add-image" class="form-label">Gambar Karakter/Light Cone</label><input class="form-control" type="file" id="add-image" name="image" required></div>
                        <div class="mb-3"><label for="add-path_logo" class="form-label">Gambar Logo Path</label><input class="form-control" type="file" id="add-path_logo" name="path_logo"></div>
                        <div class="mb-3"><label for="add-profile_image" class="form-label">Gambar Profil</label><input class="form-control" type="file" id="add-profile_image" name="profile_image"></div>
                        <div class="mb-3"><label for="add-element_icon" class="form-label">Ikon Elemen</label><input class="form-control" type="file" id="add-element_icon" name="element_icon"></div>
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

    <div class="modal fade" id="artworkModal" tabindex="-1" aria-labelledby="artworkModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content-artwork">
                <button type="button" class="btn-close-artwork" data-bs-dismiss="modal" aria-label="Close"></button>
                <img src="" id="fullArtworkImage" alt="Full Artwork">
            </div>
        </div>
    </div>

    <div class="modal fade" id="contactModal" tabindex="-1" aria-labelledby="contactModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="contactModalLabel">Hubungi Kami</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="contactForm">
                        <div class="mb-3">
                            <label for="contact-name" class="form-label">Nama</label>
                            <input type="text" class="form-control" id="contact-name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="contact-description" class="form-label">Deskripsi</label>
                            <textarea class="form-control" id="contact-description" name="description" rows="4" required></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary" form="contactForm">Kirim</button>
                </div>
            </div>
        </div>
    </div>

    <div id="success-toast" class="toast-notification">
        <div class="toast-icon">
            <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                <circle class="checkmark-circle" cx="26" cy="26" r="25" fill="none"/>
                <path class="checkmark-check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
            </svg>
        </div>
        <div class="toast-content">
            <p class="toast-title">Berhasil</p>
            <p class="toast-message"></p>
        </div>
        <div class="toast-progress"></div>
    </div>

    <div id="confirm-modal" class="confirm-overlay">
        <div class="confirm-box">
            <div class="confirm-icon">
                <svg class="question-mark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                    <circle class="question-mark-circle" cx="26" cy="26" r="25" fill="none"/>
                    <path class="question-mark-path" fill="none" d="M16.2,19.9c0-3.9,3.2-7.1,7.1-7.1s7.1,3.2,7.1,7.1c0,2.3-1.1,4.3-2.8,5.6 c-1.8,1.3-3,3.2-3,5.2v1.1 M26,38.2h0.1"/>
                </svg>
            </div>
            <h3 class="confirm-title">Apakah Anda Yakin?</h3>
            <p class="confirm-message"></p>
            <div class="confirm-buttons">
                <button id="confirm-btn-cancel" class="btn-confirm btn-cancel">Batal</button>
                <button id="confirm-btn-ok" class="btn-confirm btn-ok">Yakin</button>
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