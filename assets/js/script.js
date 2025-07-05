$(document).ready(function() {

    const loadingScreen = $('#loading-screen');
    const appWrapper = $('#app-wrapper');

    setTimeout(() => {
        loadingScreen.addClass('fade-out');
        appWrapper.removeClass('hidden');
        loadingScreen.on('transitionend', () => $(this).remove());

        $('#home-section').on('click', function(e) {
            $(this).addClass('fade-out-up').on('transitionend', () => $(this).hide());
        });

        if (typeof InitialCharacterId !== 'undefined' && InitialCharacterId > 0) {
            renderCharacterDetail(InitialCharacterId);
        }
    }, 1341);
    
    // ========================================================
    // --- FUNGSI-FUNGSI BARU & YANG DIPERBARUI ---
    // ========================================================

    let toastTimeout;

    /**
     * FUNGSI BARU: Menampilkan notifikasi sukses (toast)
     * @param {string} message - Pesan yang ingin ditampilkan.
     */
    function showSuccessToast(message) {
        const toast = $('#success-toast');
        if (!toast.length) return;

        clearTimeout(toastTimeout);

        toast.removeClass('show');
        toast.find('.toast-progress').css('animation', 'none');
        
        toast.find('.toast-message').text(message);
        void toast[0].offsetWidth;

        toast.find('.toast-progress').css('animation', 'progress-countdown 4s linear forwards');
        toast.addClass('show');
        
        toastTimeout = setTimeout(() => {
            toast.removeClass('show');
        }, 5000);
    }


    function renderCharacterDetail(characterId) {
        $.ajax({
            url: 'api/get_character.php',
            type: 'GET', data: { id: characterId }, dataType: 'json',
            beforeSend: () => $('#character-display').css('opacity', 0.5),
            success: (char) => {
                if(!char || char.success === false) {
                    $('#character-display').html(`<div class="text-center text-white"><h2>Error</h2><p>${char.message || 'Data tidak valid.'}</p></div>`).css('opacity', 1);
                    return;
                }
                
                let pathLogoHTML = char.path_logo ? `<div class="character-path-logo"><img src="uploads/${escapeHtml(char.path_logo)}" alt="Path Logo"></div>` : '';
                let elementIconHTML = char.element_icon ? `<div class="element-icon"><img src="uploads/${escapeHtml(char.element_icon)}" alt="Element"></div>` : '';
                let profileImageHTML = char.profile_image ? `<div class="character-profile" data-bs-toggle="modal" data-bs-target="#artworkModal" data-artwork-src="uploads/${escapeHtml(char.image)}"><img src="uploads/${escapeHtml(char.profile_image)}" alt="Profile"></div>` : '';

                const newContent = `
                <div class="character-card-wrapper">
                    <div class="character-card">
                        <div class="card-header-info">
                            <div class="character-title">
                                <h2 class="character-name">${escapeHtml(char.name)}</h2>
                                <div class="rarity-container">
                                    <div class="rarity">${'★'.repeat(char.rarity)}</div>
                                    ${elementIconHTML}
                                </div>
                            </div>
                            ${pathLogoHTML}
                        </div>
                        <div class="event-info">
                            <span class="event-title">${escapeHtml(char.title)}</span>
                            <p class="event-time"><span class="highlight-box">${escapeHtml(char.event_time)}</span></p>
                        </div>
                        <div class="character-description">${escapeHtml(char.description).replace(/\n/g, '<br>')}</div>
                        <div class="card-footer-actions">
                            <a href="api/download.php?file=${encodeURIComponent(char.image)}" class="btn-download">Download</a>
                            ${profileImageHTML}
                        </div>
                    </div>
                    <div class="character-artwork ${char.category === 'Light Cone' ? 'light-cone-artwork' : ''} animated-character">
                        <img src="uploads/${escapeHtml(char.image)}" alt="${escapeHtml(char.name)}">
                    </div>
                </div>`;
                
                $('#character-display').html(newContent).css('opacity', 1);
            },
            error: () => $('#character-display').html('<div class="text-center text-white"><h2>Error</h2><p>Gagal memuat data.</p></div>').css('opacity', 1)
        });
    }
    
    function handleAjaxFormSubmit(form, url, callback) {
        const button = $(form).closest('.modal-content').find('button[type="submit"], #saveEditFormButton');
        const originalText = button.text();

        $.ajax({
            url: url, type: 'POST', data: new FormData(form), dataType: 'json',
            contentType: false, processData: false,
            beforeSend: () => button.prop('disabled', true).text('Menyimpan...'),
            success: (res) => {
                if (res.success) {
                    showSuccessToast(res.message);
                    if (callback) callback(res);
                } else {
                    alert(res.message || 'Terjadi kesalahan.');
                }
            },
            error: () => alert('Gagal terhubung ke server.'),
            complete: () => button.prop('disabled', false).text(originalText)
        });
    }

    /**
     * FUNGSI BARU: Menampilkan modal konfirmasi.
     * @param {string} message - Pesan yang ditampilkan di bawah judul.
     * @returns {Promise<boolean>} - Resolves true jika user klik "Yakin", false jika klik "Batal".
     */

    function showConfirmation(message) {
        return new Promise(resolve => {
            const modal = $('#confirm-modal');
            modal.find('.confirm-message').text(message);
            modal.addClass('show');

            $('#confirm-btn-ok').off('click').on('click', () => {
                modal.removeClass('show');
                resolve(true);
            });

            $('#confirm-btn-cancel').off('click').on('click', () => {
                modal.removeClass('show');
                resolve(false);
            });
        });
    }

    function escapeHtml(text) {
        return text == null ? "" : text.toString().replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'})[m]);
    }

    // ========================================================
    // --- EVENT LISTENERS ---
    // ========================================================

    $('#character-list').on('click', '.nav-link[data-id]', function(e) {
        e.preventDefault();
        if ($(this).hasClass('active')) return;
        $('.sidebar .nav-link').removeClass('active');
        $(this).addClass('active');
        renderCharacterDetail($(this).data('id'));
    });

    $('#character-list').on('click', '.btn-edit', function() {
        const charId = $(this).data('id');
        const modalBody = $('#editCharacterModalBody');
        modalBody.html('<div class="d-flex justify-content-center p-5"><div class="spinner-border text-light"></div></div>');

        $.get('api/get_character.php', { id: charId }, function(char) {
            const currentProfileImgHTML = char.profile_image ? `<div class="mt-2"><img src="uploads/${escapeHtml(char.profile_image)}" style="max-width:50px;border-radius:5px;"></div>` : 'Tidak ada';
            const currentElementIconHTML = char.element_icon ? `<div class="mt-2"><img src="uploads/${escapeHtml(char.element_icon)}" style="max-width:50px;border-radius:5px;"></div>` : 'Tidak ada';

            const formHtml = `
                <form id="editCharacterForm" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="${char.id}">
                    <input type="hidden" name="current_image" value="${char.image || ''}">
                    <input type="hidden" name="current_path_logo" value="${char.path_logo || ''}">
                    <input type="hidden" name="current_profile_image" value="${char.profile_image || ''}">
                    <input type="hidden" name="current_element_icon" value="${char.element_icon || ''}">
                    <div class="mb-3"><label class="form-label">Nama</label><input type="text" class="form-control" name="name" value="${escapeHtml(char.name)}" required></div>
                    <div class="mb-3"><label class="form-label">Kategori</label><select class="form-select" name="category" required><option value="Baru" ${char.category == 'Baru' ? 'selected' : ''}>Baru</option><option value="Rerun" ${char.category == 'Rerun' ? 'selected' : ''}>Rerun</option><option value="Kolaborasi" ${char.category == 'Kolaborasi' ? 'selected' : ''}>Kolaborasi</option><option value="Light Cone" ${char.category == 'Light Cone' ? 'selected' : ''}>Light Cone</option></select></div>
                    <div class="mb-3"><label class="form-label">Bintang</label><input type="number" class="form-control" name="rarity" value="${char.rarity}" min="1" max="5" required></div>
                    <div class="mb-3"><label class="form-label">Judul Event</label><input type="text" class="form-control" name="title" value="${escapeHtml(char.title)}"></div>
                    <div class="mb-3"><label class="form-label">Waktu Event</label><input type="text" class="form-control" name="event_time" value="${escapeHtml(char.event_time)}" required></div>
                    <div class="mb-3"><label class="form-label">Deskripsi</label><textarea class="form-control" name="description" rows="3" required>${escapeHtml(char.description)}</textarea></div>
                    <div class="mb-3"><label class="form-label">Gambar Artwork</label><div><img src="uploads/${escapeHtml(char.image)}" style="max-width:100px;border-radius:5px;"></div></div>
                    <div class="mb-3"><label class="form-label">Ganti Gambar Artwork</label><input class="form-control" type="file" name="image"></div>
                    <hr class="my-3">
                    <div class="mb-3"><label class="form-label">Logo Path</label><div>${char.path_logo ? `<img src="uploads/${escapeHtml(char.path_logo)}" style="max-width:50px;border-radius:5px;">` : 'Tidak ada'}</div></div>
                    <div class="mb-3"><label class="form-label">Ganti Logo Path</label><input class="form-control" type="file" name="path_logo"></div>
                    <hr class="my-3">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3"><label class="form-label">Gambar Profil</label>${currentProfileImgHTML}</div>
                            <div class="mb-3"><label class="form-label">Ganti Gambar Profil</label><input class="form-control" type="file" name="profile_image"></div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3"><label class="form-label">Ikon Elemen</label>${currentElementIconHTML}</div>
                            <div class="mb-3"><label class="form-label">Ganti Ikon Elemen</label><input class="form-control" type="file" name="element_icon"></div>
                        </div>
                    </div>
                </form>`;
            
            modalBody.html(formHtml);
            $('#saveEditFormButton').attr('form', 'editCharacterForm');
        }, 'json').fail(() => modalBody.html('<p class="text-danger text-center">Gagal mengambil data.</p>'));
    });
    
    $('#editCharacterModal').on('submit', '#editCharacterForm', function(e) {
        e.preventDefault();
        handleAjaxFormSubmit(this, 'api/update_character.php', (res) => {
            // Menutup modal setelah sukses
            const editModal = bootstrap.Modal.getInstance(document.getElementById('editCharacterModal'));
            editModal.hide();
            // Menunggu sebentar lalu reload
            setTimeout(() => location.reload(), 1500);
        });
    });

    $('#addCharacterForm').on('submit', function(e) {
        e.preventDefault();
        handleAjaxFormSubmit(this, 'api/add_character.php', (res) => {
             // Menutup modal setelah sukses
            const addModal = bootstrap.Modal.getInstance(document.getElementById('addCharacterModal'));
            addModal.hide();
            // Menunggu sebentar lalu reload
            setTimeout(() => location.reload(), 1500);
        });
    });
    
    $('#character-list').on('click', '.btn-delete', async function() {
        const charId = $(this).data('id');
        const charName = $(this).data('name');
        
        // Memanggil modal konfirmasi baru
        const isConfirmed = await showConfirmation(`Tindakan ini akan menghapus "${charName}" secara permanen.`);

        if (isConfirmed) {
            $.post('api/delete_character.php', { id: charId }, (res) => {
                if (res.success) {
                    showSuccessToast(res.message);
                    setTimeout(() => location.reload(), 1500);
                } else {
                    alert(res.message);
                }
            }, 'json').fail(() => alert('Terjadi kesalahan koneksi.'));
        }
    });

    $('#artworkModal').on('show.bs.modal', function (event) {
        const triggerElement = $(event.relatedTarget);
        const artworkSrc = triggerElement.data('artwork-src');
        $('#fullArtworkImage').attr('src', artworkSrc);
    });

    $('#contactForm').on('submit', function(e) {
        e.preventDefault();
        const name = $('#contact-name').val();
        showSuccessToast(`Terima kasih, ${name}! Pesan Anda telah diterima.`);
        const contactModal = bootstrap.Modal.getInstance(document.getElementById('contactModal'));
        contactModal.hide();
        $(this).trigger('reset');
    });

    
});