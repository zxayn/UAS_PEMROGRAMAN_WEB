$(document).ready(function() {

    const loadingScreen = $('#loading-screen');
    const appWrapper = $('#app-wrapper');

    setTimeout(() => {
        loadingScreen.addClass('fade-out');
        appWrapper.removeClass('hidden');
        loadingScreen.on('transitionend', () => $(this).remove());

        $('#home-section').on('click', function() {
            $(this).addClass('fade-out-up').on('transitionend', () => $(this).hide());
        });

        if (typeof InitialCharacterId !== 'undefined' && InitialCharacterId > 0) {
            renderCharacterDetail(InitialCharacterId);
        }
    }, 1000);

    // --- FUNGSI-FUNGSI BANTUAN ---
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
                
                const newContent = `
                <div class="character-card-wrapper">
                    <div class="character-card">
                        <div class="card-header-info">
                            <div class="character-title">
                                <h2 class="character-name">${escapeHtml(char.name)}</h2>
                                <div class="rarity">${'★'.repeat(char.rarity)}</div>
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
                alert(res.message || 'Terjadi kesalahan.');
                if (res.success && callback) callback(res);
            },
            error: () => alert('Gagal terhubung ke server.'),
            complete: () => button.prop('disabled', false).text(originalText)
        });
    }

    function escapeHtml(text) {
        return text == null ? "" : text.toString().replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'})[m]);
    }

    // --- EVENT LISTENERS ---
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
            const formHtml = `
                <form id="editCharacterForm" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="${char.id}">
                    <input type="hidden" name="current_image" value="${char.image || ''}">
                    <input type="hidden" name="current_path_logo" value="${char.path_logo || ''}">
                    <div class="mb-3"><label class="form-label">Nama</label><input type="text" class="form-control" name="name" value="${escapeHtml(char.name)}" required></div>
                    <div class="mb-3"><label class="form-label">Kategori</label><select class="form-select" name="category" required><option value="Baru" ${char.category == 'Baru' ? 'selected' : ''}>Baru</option><option value="Rerun" ${char.category == 'Rerun' ? 'selected' : ''}>Rerun</option><option value="Kolaborasi" ${char.category == 'Kolaborasi' ? 'selected' : ''}>Kolaborasi</option><option value="Light Cone" ${char.category == 'Light Cone' ? 'selected' : ''}>Light Cone</option></select></div>
                    <div class="mb-3"><label class="form-label">Rarity</label><input type="number" class="form-control" name="rarity" value="${char.rarity}" min="1" max="5" required></div>
                    <div class="mb-3"><label class="form-label">Judul Event</label><input type="text" class="form-control" name="title" value="${escapeHtml(char.title)}"></div>
                    <div class="mb-3"><label class="form-label">Waktu Event</label><input type="text" class="form-control" name="event_time" value="${escapeHtml(char.event_time)}" required></div>
                    <div class="mb-3"><label class="form-label">Deskripsi</label><textarea class="form-control" name="description" rows="3" required>${escapeHtml(char.description)}</textarea></div>
                    <div class="mb-3"><label class="form-label">Artwork</label><div><img src="uploads/${escapeHtml(char.image)}" style="max-width:100px;border-radius:5px;"></div></div>
                    <div class="mb-3"><label class="form-label">Ganti Artwork</label><input class="form-control" type="file" name="image"></div>
                    <hr class="my-3">
                    <div class="mb-3"><label class="form-label">Ikon/Logo</label><div>${char.path_logo ? `<img src="uploads/${escapeHtml(char.path_logo)}" style="max-width:50px;border-radius:5px;">` : 'Tidak ada'}</div></div>
                    <div class="mb-3"><label class="form-label">Ganti Ikon/Logo</label><input class="form-control" type="file" name="path_logo"></div>
                </form>`;
            
            modalBody.html(formHtml);
            $('#saveEditFormButton').attr('form', 'editCharacterForm');
        }, 'json').fail(() => modalBody.html('<p class="text-danger text-center">Gagal mengambil data.</p>'));
    });
    
    $('#editCharacterModal').on('submit', '#editCharacterForm', function(e) {
        e.preventDefault();
        handleAjaxFormSubmit(this, 'api/update_character.php', () => location.reload());
    });

    $('#addCharacterForm').on('submit', function(e) {
        e.preventDefault();
        handleAjaxFormSubmit(this, 'api/add_character.php', () => location.reload());
    });
    
    $('#character-list').on('click', '.btn-delete', function() {
        const charId = $(this).data('id');
        const charName = $(this).data('name');
        if (confirm(`Anda yakin ingin menghapus "${charName}"?`)) {
            $.post('api/delete_character.php', { id: charId }, (res) => {
                alert(res.message);
                if (res.success) location.reload();
            }, 'json').fail(() => alert('Terjadi kesalahan koneksi.'));
        }
    });
});