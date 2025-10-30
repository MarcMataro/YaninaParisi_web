// main DOMContentLoaded
document.addEventListener('DOMContentLoaded', function () {
    const fileInput = document.getElementById('fileInput');
    const uploadStatus = document.getElementById('uploadStatus');
    const imgGridEl = document.getElementById('mediaGridImages');
    const vidGridEl = document.getElementById('mediaGridVideos');
    const pagImages = document.getElementById('paginationImages');
    const pagVideos = document.getElementById('paginationVideos');
    const btnRefresh = document.getElementById('btnRefresh');
    const dropZone = document.getElementById('dropZone');
    const progressList = document.getElementById('progressList');
    const searchInput = document.getElementById('searchInput');
    const sortSelect = document.getElementById('sortSelect');
    const btnSelectAll = document.getElementById('btnSelectAll');
    const btnDeleteSelected = document.getElementById('btnDeleteSelected');

    const previewModal = document.getElementById('previewModal');
    const previewInner = document.getElementById('previewInner');
    const btnClosePreview = document.getElementById('btnClosePreview');
    const btnCopyUrl = document.getElementById('btnCopyUrl');
    const btnOpenNew = document.getElementById('btnOpenNew');

    let images = [];
    let videos = [];
    let currentPage = 1;
    const pageSize = 24;
    let currentSort = (sortSelect && sortSelect.value) ? sortSelect.value : 'date_desc';

    function sortMedia() {
        // sorts images and videos arrays in-place according to currentSort
        const nameKey = v => (v.title || v.name || '').toString().toLowerCase();
        switch (currentSort) {
            case 'date_asc':
                images.sort((a,b)=> (a.modified||0)-(b.modified||0));
                videos.sort((a,b)=> (a.modified||0)-(b.modified||0));
                break;
            case 'name_asc':
                images.sort((a,b)=> nameKey(a).localeCompare(nameKey(b)));
                videos.sort((a,b)=> nameKey(a).localeCompare(nameKey(b)));
                break;
            case 'name_desc':
                images.sort((a,b)=> nameKey(b).localeCompare(nameKey(a)));
                videos.sort((a,b)=> nameKey(b).localeCompare(nameKey(a)));
                break;
            case 'size_asc':
                images.sort((a,b)=> (a.size||0)-(b.size||0));
                videos.sort((a,b)=> (a.size||0)-(b.size||0));
                break;
            case 'size_desc':
                images.sort((a,b)=> (b.size||0)-(a.size||0));
                videos.sort((a,b)=> (b.size||0)-(a.size||0));
                break;
            case 'date_desc':
            default:
                images.sort((a,b)=> (b.modified||0)-(a.modified||0));
                videos.sort((a,b)=> (b.modified||0)-(a.modified||0));
                break;
        }
    }

    function humanSize(bytes) {
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
    }

    async function listMedia() {
        uploadStatus.textContent = 'Cargando...';
        try {
            const res = await fetch('gmedia.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'action=list' });
            const data = await res.json();
            uploadStatus.textContent = '';
            if (data.success) {
                images = data.images || [];
                videos = data.videos || [];
                // apply current sort
                sortMedia();
                renderImagesGrid();
                renderVideosGrid();
            } else {
                uploadStatus.textContent = 'Error al listar';
            }
        } catch (e) {
            uploadStatus.textContent = 'Error de conexión';
            console.error(e);
        }
    }

    function renderImagesGrid() {
        const query = (searchInput.value || '').toLowerCase();
        let items = images.slice();
        if (query) items = items.filter(it => (it.title || it.name).toLowerCase().includes(query));

        const total = items.length;
        const pages = Math.max(1, Math.ceil(total / pageSize));
        if (currentPage > pages) currentPage = pages;
        const start = (currentPage - 1) * pageSize;
        const pageItems = items.slice(start, start + pageSize);

        imgGridEl.innerHTML = '';
        pageItems.forEach(it => {
            const card = document.createElement('div');
            card.className = 'card';
            card.setAttribute('data-type', 'image');
            card.innerHTML = `
                <div class="card-media">
                    <input type="checkbox" class="card-check" data-name="${encodeURIComponent(it.name)}" />
                    <img class="card-img" src="${it.thumb}" alt="${it.title || it.name}" />
                </div>
                <div class="card-bottom">
                    <span class="card-title" title="${(it.title || it.name)}">${(it.title || it.name)}</span>
                    <div class="card-actions">
                        <button class="btn-small btn-edit" data-name="${encodeURIComponent(it.name)}">Editar</button>
                        <button class="btn-small btn-preview" data-url="${it.url}" data-name="${encodeURIComponent(it.name)}">Ver</button>
                        <button class="btn-small btn-delete" data-name="${encodeURIComponent(it.name)}">Eliminar</button>
                    </div>
                </div>
            `;
            imgGridEl.appendChild(card);
        });

        // pagination for images
        pagImages.innerHTML = '';
        for (let p = 1; p <= pages; p++) {
            const b = document.createElement('button');
            b.className = 'btn-small';
            b.textContent = p;
            if (p === currentPage) b.style.opacity = '0.6';
            b.addEventListener('click', () => { currentPage = p; renderImagesGrid(); });
            pagImages.appendChild(b);
        }

        attachCardHandlers(imgGridEl, 'image');
    }

    function renderVideosGrid() {
        const query = (searchInput.value || '').toLowerCase();
        let items = videos.slice();
        if (query) items = items.filter(it => (it.title || it.name).toLowerCase().includes(query));

        vidGridEl.innerHTML = '';
        items.forEach(it => {
            const card = document.createElement('div');
            card.className = 'card';
            card.innerHTML = `
                <div class="card-media">
                    <input type="checkbox" class="card-check" data-name="${encodeURIComponent(it.name)}" />
                    <div class="card-video-placeholder"><i class="fas fa-video" aria-hidden="true"></i></div>
                </div>
                <div class="card-bottom">
                    <span class="card-title" title="${(it.title || it.name)}">${(it.title || it.name)}</span>
                    <div class="card-actions">
                        <button class="btn-small btn-edit" data-name="${encodeURIComponent(it.name)}">Editar</button>
                        <button class="btn-small btn-preview" data-url="${it.url}" data-name="${encodeURIComponent(it.name)}">Ver</button>
                        <button class="btn-small btn-delete" data-name="${encodeURIComponent(it.name)}">Eliminar</button>
                    </div>
                </div>
            `;
            // mark card as video type so actions (rename) know which folder to target
            card.setAttribute('data-type', 'video');
            vidGridEl.appendChild(card);
        });

        attachCardHandlers(vidGridEl, 'video');
    }

    function attachCardHandlers(container, typeHint) {
        container.querySelectorAll('.btn-delete').forEach(btn => {
            btn.addEventListener('click', async function () {
                if (!confirm('Eliminar archivo?')) return;
                const name = decodeURIComponent(this.dataset.name);
                try {
                    const res = await fetch('gmedia.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: `action=delete&file=${encodeURIComponent(name)}&type=${typeHint}` });
                    const data = await res.json();
                    if (data.success) listMedia(); else alert(data.message || 'Error');
                } catch (e) { alert('Error de conexión'); }
            });
        });
        container.querySelectorAll('.btn-preview').forEach(btn => {
            btn.addEventListener('click', function () {
                const url = this.dataset.url;
                const name = decodeURIComponent(this.dataset.name);
                openPreview(url, name);
            });
        });
        container.querySelectorAll('.btn-edit').forEach(btn => {
            btn.addEventListener('click', function () {
                const name = decodeURIComponent(this.dataset.name);
                const card = this.closest('.card');
                const titleEl = card.querySelector('.card-title');
                const current = titleEl ? titleEl.textContent : name;
                showInlineEditor(card, name, current);
            });
        });
    }

    function showInlineEditor(card, filename, current) {
        const titleEl = card.querySelector('.card-title');
        const actions = card.querySelector('.card-actions');
        // create edit controls
        const input = document.createElement('input'); input.type = 'text'; input.className = 'edit-input'; input.value = current;
        const save = document.createElement('button'); save.className = 'btn-small btn-save'; save.textContent = 'Guardar';
        const cancel = document.createElement('button'); cancel.className = 'btn-small btn-cancel'; cancel.textContent = 'Cancelar';
        // hide title and actions
        titleEl.style.display = 'none'; actions.style.display = 'none';
        const editRow = document.createElement('div'); editRow.className = 'edit-row'; editRow.appendChild(input); editRow.appendChild(save); editRow.appendChild(cancel);
        card.querySelector('.card-bottom').appendChild(editRow);

        cancel.addEventListener('click', () => { editRow.remove(); titleEl.style.display = 'block'; actions.style.display = 'flex'; });
            save.addEventListener('click', async () => {
            const newTitle = input.value.trim();
            const doRename = confirm('Vols renombrar el fitxer i la miniatura al nou títol (si existeix)?\nSi no, només es canviarà el títol mostrable.');
            try {
                    // determine type from card attribute (image|video)
                    const type = card.getAttribute('data-type') || (card.querySelector('img') ? 'image' : 'video');
                    let body = `action=setmeta&file=${encodeURIComponent(filename)}&title=${encodeURIComponent(newTitle)}&type=${encodeURIComponent(type)}`;
                    if (doRename) body += '&rename=1';
                const res = await fetch('gmedia.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body });
                const data = await res.json();
                if (data.success) {
                    if (data.renamed && data.new) {
                        titleEl.textContent = newTitle || data.new;
                        titleEl.title = newTitle || data.new;
                        const encodedNew = encodeURIComponent(data.new || filename);
                        // update data-name attributes in this card
                        card.querySelectorAll('[data-name]').forEach(el => { el.setAttribute('data-name', encodedNew); });
                        // refresh lists to reflect rename
                        listMedia();
                    } else {
                        titleEl.textContent = newTitle || filename;
                        titleEl.title = newTitle || filename;
                    }
                } else {
                    alert(data.message || 'Error al guardar');
                }
            } catch (e) { alert('Error de conexión'); }
            editRow.remove(); titleEl.style.display = 'block'; actions.style.display = 'flex';
        });
    }

    function openPreview(url, name) {
        previewInner.innerHTML = '';
        if (url.match(/\.(mp4|webm|ogg|mov|mkv)$/i)) {
            previewInner.innerHTML = `<video controls src="${url}"></video>`;
        } else {
            previewInner.innerHTML = `<img src="${url}" alt="${name}">`;
        }
        btnOpenNew.href = url;
        btnCopyUrl.onclick = () => { navigator.clipboard.writeText(url); alert('URL copiada'); };
        previewModal.classList.add('show');
    }

    btnClosePreview.addEventListener('click', () => previewModal.classList.remove('show'));

    // upload via XHR with progress
    function uploadFiles(files) {
        if (!files || files.length === 0) return;
        Array.from(files).forEach(file => {
            const row = document.createElement('div');
            row.className = 'progress-row';
            row.innerHTML = `<div style="display:flex;justify-content:space-between;align-items:center;"><div>${file.name} (${humanSize(file.size)})</div><div><span class="status">0%</span></div></div><div style="height:8px;background:#eee;border-radius:6px;margin-top:6px;"><div class="bar" style="height:100%;width:0;background:var(--color-accent);border-radius:6px;"></div></div>`;
            progressList.appendChild(row);
            const bar = row.querySelector('.bar');
            const status = row.querySelector('.status');

            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'gmedia.php');
            xhr.upload.onprogress = function (e) { if (e.lengthComputable) { const pct = Math.round(e.loaded / e.total * 100); bar.style.width = pct + '%'; status.textContent = pct + '%'; } };
            xhr.onload = function () {
                try {
                    const data = JSON.parse(xhr.responseText);
                    if (data.success) { status.textContent = 'Completado'; }
                    else { status.textContent = 'Error'; }
                } catch (e) { status.textContent = 'Error'; }
                setTimeout(() => { progressList.removeChild(row); listMedia(); }, 800);
            };
            xhr.onerror = function () { status.textContent = 'Error'; };
            const fd = new FormData();
            fd.append('file[]', file, file.name);
            // required by server to trigger upload handler
            fd.append('action', 'upload');
            xhr.send(fd);
        });
    }

    // drag and drop
    dropZone.addEventListener('click', () => fileInput.click());
    dropZone.addEventListener('dragover', e => { e.preventDefault(); dropZone.classList.add('dragover'); });
    dropZone.addEventListener('dragleave', e => { e.preventDefault(); dropZone.classList.remove('dragover'); });
    dropZone.addEventListener('drop', e => { e.preventDefault(); dropZone.classList.remove('dragover'); const dt = e.dataTransfer; if (dt && dt.files) uploadFiles(dt.files); });

    fileInput.addEventListener('change', function () { uploadFiles(this.files); this.value = ''; });

    btnRefresh.addEventListener('click', listMedia);
    searchInput.addEventListener('input', () => { currentPage = 1; renderImagesGrid(); renderVideosGrid(); });
    if (sortSelect) {
        sortSelect.addEventListener('change', () => {
            currentSort = sortSelect.value;
            sortMedia();
            // re-render keeping current page
            renderImagesGrid();
            renderVideosGrid();
        });
    }

    btnSelectAll.addEventListener('click', () => {
        const checks = document.querySelectorAll('#mediaGridImages input[type=checkbox], #mediaGridVideos input[type=checkbox]');
        const anyUnchecked = Array.from(checks).some(c => !c.checked);
        checks.forEach(c => c.checked = anyUnchecked);
    });

    btnDeleteSelected.addEventListener('click', async () => {
        const checks = Array.from(document.querySelectorAll('#mediaGridImages input[type=checkbox]:checked, #mediaGridVideos input[type=checkbox]:checked'));
        if (checks.length === 0) return alert('No hay seleccionados');
        if (!confirm(`Eliminar ${checks.length} archivos?`)) return;
        for (const c of checks) {
            const name = decodeURIComponent(c.getAttribute('data-name'));
            try {
                await fetch('gmedia.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: `action=delete&file=${encodeURIComponent(name)}&type=${c.closest('.card').querySelector('img') ? 'image' : 'video'}` });
            } catch (e) { console.error(e); }
        }
        listMedia();
    });

    // initial
    listMedia();
});
