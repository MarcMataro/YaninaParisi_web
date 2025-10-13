// =============================
// FILTROS ENTRADAS (LISTADO)
// =============================
let filtroTexto = '';
let filtroCategoria = '';
let filtroEstado = '';

function renderTablaFiltrada(entradas, categorias) {
    const container = document.getElementById('entrades-list');
    if (!container) return;
    // Usar el formulario HTML existente
    // Rellenar select de categorías si está vacío
    const selectCat = document.getElementById('filtre-categoria');
    if (selectCat && selectCat.options.length <= 1 && categorias) {
        categorias.forEach(cat => {
            let nombre = cat.nom_es || cat.nom_ca || cat.nom;
            let option = document.createElement('option');
            option.value = nombre;
            option.textContent = nombre;
            selectCat.appendChild(option);
        });
    }
    // Leer valores del formulario
    const categoria = document.getElementById('filtre-categoria')?.value || '';
    const estado = document.getElementById('filtre-estat')?.value || '';
    let filtradas = entradas.filter(e => {
        let categoriaMatch = categoria === '' || (Array.isArray(e.categories_noms) && e.categories_noms.includes(categoria));
        let estadoMatch = estado === '' || e.estat === estado;
        return categoriaMatch && estadoMatch;
    });
    // Tabla
    let html = `<div class="list-container">
        <table class="list-table">
            <thead>
                <tr>
                    <th style="width:30%;">Título (ES)</th>
                    <th style="width:20%;">Título (CA)</th>
                    <th style="width:20%;">Categorías</th>
                    <th style="width:10%;" class="text-center">Estado</th>
                    <th style="width:10%;" class="text-center">Fecha</th>
                    <th style="width:10%;" class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
            ${filtradas.map(entrada => {
                let categories = Array.isArray(entrada.categories_noms) ? entrada.categories_noms.join(', ') : (entrada.categories_noms || '-');
                let estatClass = entrada.estat === 'publicat' ? 'status-activa' : 'status-inactiva';
                let estatText = entrada.estat === 'publicat' ? 'Publicada' : (entrada.estat === 'esborrany' ? 'Borrador' : entrada.estat);
                let dataPub = entrada.data_publicacio ? entrada.data_publicacio.substring(0, 10) : '-';
                return `<tr class="list-row${entrada.estat !== 'publicat' ? ' inactive-row' : ''}">
                    <td><div class="item-name"><i class="fas fa-file-alt" style="color:#a89968;margin-right:8px;"></i><strong>${entrada.titol_es || entrada.titol_ca}</strong></div></td>
                    <td>${entrada.titol_ca || '-'}</td>
                    <td>${categories}</td>
                    <td class="text-center"><span class="status-badge ${estatClass}">${estatText}</span></td>
                    <td class="text-center">${dataPub}</td>
                    <td class="text-center">
                        <div class="action-buttons-inline">
                            <button class="btn-icon-sm" onclick="editarEntrada(${entrada.id_entrada})" title="Editar"><i class="fas fa-edit"></i></button>
                            <button class="btn-icon-sm btn-danger" onclick="eliminarEntrada(${entrada.id_entrada}, '${(entrada.titol_es || entrada.titol_ca).replace(/'/g, "\\'")}')" title="Eliminar"><i class="fas fa-trash"></i></button>
                        </div>
                    </td>
                </tr>`;
            }).join('')}
            </tbody>
        </table>
    </div>`;
    container.innerHTML = html;
    // Eventos del formulario HTML
    window.aplicarFiltresEntrades = function() {
        renderTablaFiltrada(entradas, categorias);
    };
    window.resetFiltresEntrades = function() {
        document.getElementById('filtre-categoria').value = '';
        document.getElementById('filtre-estat').value = '';
        renderTablaFiltrada(entradas, categorias);
    };
    document.getElementById('filtre-categoria').addEventListener('change', () => renderTablaFiltrada(entradas, categorias));
    document.getElementById('filtre-estat').addEventListener('change', () => renderTablaFiltrada(entradas, categorias));
}

// Para usar: cargar entradas y categorías y llamar a renderTablaFiltrada(entradas, categorias)
/**
 * TEST - Gestió d'Entrades - JavaScript SIMPLIFICAT
 */

console.log('🔵 INICI gblog-entrades.js');

let entrades = [];

// Traduccions a castellà
const TEXTS = {
    noEntradas: 'No hay entradas todavía',
    crearPrimera: 'Crear Primera Entrada',
    resumen: 'Sin resumen',
    estadoPublicada: 'Publicada',
    estadoBorrador: 'Borrador',
    estadoOtro: 'Otro',
    editar: 'Editar',
    eliminar: 'Eliminar',
    confirmarEliminar: '¿Estás seguro de que quieres eliminar la entrada',
    accionNoDeshacer: 'Esta acción no se puede deshacer.',
    errorConexion: 'Error de conexión',
    errorGuardar: 'Error al guardar la entrada',
    errorEliminar: 'Error al eliminar la entrada',
    guardada: 'Entrada guardada correctamente',
    eliminada: 'Entrada eliminada correctamente',
    recargando: 'Recargando entradas...',
    cargando: 'Cargando entradas...',
    buscar: 'Buscar',
    tituloOTexto: 'Título o texto...'
};

/**
 * Carrega totes les entrades de la base de dades
 */
function carregarEntrades() {
    const timestamp = new Date().toLocaleTimeString();
    console.log('🔵🔵🔵 carregarEntrades() CRIDADA - ' + timestamp + ' 🔵🔵🔵');
    
    const container = document.getElementById('entrades-list');
    if (!container) {
        console.error('❌ Container entrades-list no trobat!');
        return;
    }

    // Filtros
    let filtroTexto = '';
    let filtroCategoria = '';
    let filtroEstado = '';

    // Renderizar barra de filtros
    let filtrosHtml = `<div class="filters-bar" style="display:flex;gap:16px;align-items:center;margin-bottom:18px;">
        <input id="filtro-texto" type="text" placeholder="${TEXTS.tituloOTexto}" style="padding:6px 12px;border-radius:6px;border:1px solid #ccc;width:220px;">
        <select id="filtro-categoria" style="padding:6px 12px;border-radius:6px;border:1px solid #ccc;">
            <option value="">Todas las categorías</option>
        </select>
        <select id="filtro-estado" style="padding:6px 12px;border-radius:6px;border:1px solid #ccc;">
            <option value="">Todos los estados</option>
            <option value="publicat">Publicada</option>
            <option value="esborrany">Borrador</option>
        </select>
        <button id="filtro-limpiar" style="padding:6px 16px;border-radius:6px;border:none;background:#a89968;color:white;font-weight:600;">Limpiar</button>
    </div>`;

    // Renderizar tabla (función para filtrar y mostrar)
    function renderTabla(entradasFiltradas) {
        let html = `<div class="list-container">
            <table class="list-table">
                <thead>
                    <tr>
                        <th style="width:30%;">Título (ES)</th>
                        <th style="width:20%;">Título (CA)</th>
                        <th style="width:20%;">Categorías</th>
                        <th style="width:10%;" class="text-center">Estado</th>
                        <th style="width:10%;" class="text-center">Fecha</th>
                        <th style="width:10%;" class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                ${entradasFiltradas.map(entrada => {
                    let categories = Array.isArray(entrada.categories_noms) ? entrada.categories_noms.join(', ') : (entrada.categories_noms || '-');
                    let estatClass = entrada.estat === 'publicat' ? 'status-activa' : 'status-inactiva';
                    let estatText = entrada.estat === 'publicat' ? TEXTS.estadoPublicada : (entrada.estat === 'esborrany' ? TEXTS.estadoBorrador : entrada.estat);
                    let dataPub = entrada.data_publicacio ? entrada.data_publicacio.substring(0, 10) : '-';
                    return `
                        <tr class="list-row${entrada.estat !== 'publicat' ? ' inactive-row' : ''}">
                            <td><div class="item-name"><i class="fas fa-file-alt" style="color:#a89968;margin-right:8px;"></i><strong>${entrada.titol_es || entrada.titol_ca}</strong></div></td>
                            <td>${entrada.titol_ca || '-'}</td>
                            <td>${categories}</td>
                            <td class="text-center">
                                <span class="status-badge ${estatClass}">${estatText}</span>
                            </td>
                            <td class="text-center">${dataPub}</td>
                            <td class="text-center">
                                <div class="action-buttons-inline">
                                    <button class="btn-icon-sm" onclick="editarEntrada(${entrada.id_entrada})" title="${TEXTS.editar}">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn-icon-sm btn-danger" onclick="eliminarEntrada(${entrada.id_entrada}, '${(entrada.titol_es || entrada.titol_ca).replace(/'/g, "\\'")}")" title="${TEXTS.eliminar}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `;
                }).join('')}
                </tbody>
            </table>
        </div>`;
        container.innerHTML = filtrosHtml + html;
    }

    // Inicializar filtros y tabla tras cargar entradas
    function inicializarFiltros(entradas, categorias) {
        // Rellenar select de categorías
        const selectCat = document.getElementById('filtro-categoria');
        if (selectCat && categorias) {
            categorias.forEach(cat => {
                let nombre = cat.nom_es || cat.nom_ca || cat.nom;
                let option = document.createElement('option');
                option.value = nombre;
                option.textContent = nombre;
                selectCat.appendChild(option);
            });
        }
        // Eventos de filtro
        document.getElementById('filtro-texto').addEventListener('input', e => {
            filtroTexto = e.target.value.toLowerCase();
            filtrarYMostrar();
        });
        document.getElementById('filtro-categoria').addEventListener('change', e => {
            filtroCategoria = e.target.value;
            filtrarYMostrar();
        });
        document.getElementById('filtro-estado').addEventListener('change', e => {
            filtroEstado = e.target.value;
            filtrarYMostrar();
        });
        document.getElementById('filtro-limpiar').addEventListener('click', () => {
            filtroTexto = '';
            filtroCategoria = '';
            filtroEstado = '';
            document.getElementById('filtro-texto').value = '';
            document.getElementById('filtro-categoria').value = '';
            document.getElementById('filtro-estado').value = '';
            filtrarYMostrar();
        });
    }

    // Filtrar y mostrar entradas
    function filtrarYMostrar() {
        let filtradas = entrades.filter(e => {
            let textoMatch = filtroTexto === '' || (e.titol_es && e.titol_es.toLowerCase().includes(filtroTexto)) || (e.titol_ca && e.titol_ca.toLowerCase().includes(filtroTexto)) || (e.contingut_es && e.contingut_es.toLowerCase().includes(filtroTexto)) || (e.contingut_ca && e.contingut_ca.toLowerCase().includes(filtroTexto));
            let categoriaMatch = filtroCategoria === '' || (Array.isArray(e.categories_noms) && e.categories_noms.includes(filtroCategoria));
            let estadoMatch = filtroEstado === '' || e.estat === filtroEstado;
            return textoMatch && categoriaMatch && estadoMatch;
        });
        renderTabla(filtradas);
    }
    
    console.log('📡 Fent petició a gblog.php...');
    
    fetch('gblog.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=obtenir_entrades'
    })
    .then(response => {
        console.log('✅ Resposta rebuda:', response.status);
        return response.text();
    })
    .then(text => {
        console.log('📄 Text rebut (primers 500 chars):', text.substring(0, 500));
        
        const data = JSON.parse(text);
        console.log('✅ JSON parsejat:', data);
        
        if (data.success) {
            entrades = data.entrades || [];
            // Cargar categorías y mostrar tabla filtrable
            fetch('gblog.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=obtenir_categories'
            })
            .then(res => res.json())
            .then(catData => {
                const categorias = catData.success ? catData.categories : [];
                renderTablaFiltrada(entrades, categorias);
            });
        } else {
            console.error('❌ Error del servidor:', data.message);
            container.innerHTML = `
                <div style="padding: 40px; text-align: center; background: #fee; border-radius: 12px; border: 2px solid #fcc;">
                    <div style="font-size: 48px; margin-bottom: 20px;">⚠️</div>
                    <h3 style="color: #c00;">Error</h3>
                    <p>${data.message || 'Error desconegut'}</p>
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('❌ Error:', error);
        container.innerHTML = `
            <div style="padding: 40px; text-align: center; background: #fee; border-radius: 12px; border: 2px solid #fcc;">
                <div style="font-size: 48px; margin-bottom: 20px;">❌</div>
                <h3 style="color: #c00;">${TEXTS.errorConexion}</h3>
                <p>${error.message}</p>
                <button onclick="carregarEntrades()" style="margin-top: 20px; background: #666; color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer;">
                    🔄 Reintentar
                </button>
            </div>
        `;
    });
}

/**
 * Obre el modal per crear una nova entrada
 */
async function obrirModalEntrada() {
    console.log('🔵 obrirModalEntrada() CRIDADA');
    
    const modal = document.getElementById('modalEntrada');
    if (!modal) {
        console.error('❌ Modal no trobat!');
        alert('Error: Modal de entrada no encontrado');
        return;
    }
    
    // Reiniciar el formulari
    const form = document.getElementById('formEntrada');
    if (form) {
        form.reset();
    }
    
    // Títol del modal
    const title = document.getElementById('modalEntradaTitle');
    if (title) {
        title.innerHTML = `
            <i class="fas fa-file-alt"></i>
            <span>${TEXTS.crearPrimera}</span>
        `;
    }
    
    // Netejar ID (per crear nova entrada)
    const idInput = document.getElementById('entrada_id');
    if (idInput) {
        idInput.value = '';
    }
    
    // Establir valors per defecte
    const estatSelect = document.getElementById('entrada_estat');
    if (estatSelect) {
        estatSelect.value = 'esborrany';
    }
    
    const visibleSelect = document.getElementById('entrada_visible');
    if (visibleSelect) {
        visibleSelect.value = '1';
    }
    
    // Carregar categories i etiquetes per als checkboxes
    console.log('📡 Carregant categories i etiquetes...');
    await carregarCategoriesEtiquetesModal();
    
    // Mostrar el modal
    modal.classList.add('show');
    document.body.style.overflow = 'hidden';
    
    console.log('✅ Modal obert');
}

/**
 * Tanca el modal d'entrada
 */
function tancarModalEntrada() {
    console.log('🔵 tancarModalEntrada() CRIDADA');
    
    const modal = document.getElementById('modalEntrada');
    if (modal) {
        modal.classList.remove('show');
        document.body.style.overflow = '';
        console.log('✅ Modal tancat');
    }
}

/**
 * Carrega categories i etiquetes per mostrar al modal
 */
async function carregarCategoriesEtiquetesModal(idEntrada = null) {
    try {
        console.log('📡 Obtenint categories...');
        
        // Obtenir categories
        const resCat = await fetch('gblog.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=obtenir_categories'
        });
        const dataCat = await resCat.json();
        const categories = dataCat.success ? dataCat.categories : [];
        console.log('✅ Categories obtingudes:', categories.length);
        
        // Obtenir etiquetes
        console.log('📡 Obtenint etiquetes...');
        const resEti = await fetch('gblog.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=obtenir_etiquetes'
        });
        const dataEti = await resEti.json();
        const etiquetes = dataEti.success ? dataEti.etiquetes : [];
        console.log('✅ Etiquetes obtingudes:', etiquetes.length);
        
        // Renderitzar categories
        const catContainer = document.getElementById('categories-selector');
        if (catContainer) {
            catContainer.innerHTML = categories.length > 0 ? 
                categories
                    .filter(cat => cat.activa == 1) // Només actives
                    .map(cat => `
                        <label class="checkbox-label">
                            <input type="checkbox" 
                                   name="categories[]" 
                                   value="${cat.id_category}">
                            <span>${cat.nom_es}</span>
                        </label>
                    `).join('') :
                '<p style="color: #999; font-size: 0.9rem; padding: 10px;">No hay categorías disponibles</p>';
            console.log('✅ Categories renderitzades');
        }
        
        // Renderitzar etiquetes
        const etiContainer = document.getElementById('etiquetes-selector');
        if (etiContainer) {
            etiContainer.innerHTML = etiquetes.length > 0 ?
                etiquetes
                    .filter(eti => eti.activa == 1) // Només actives
                    .map(eti => `
                        <label class="checkbox-label">
                            <input type="checkbox" 
                                   name="etiquetes[]" 
                                   value="${eti.id_etiqueta}">
                            <span>${eti.nom_es}</span>
                        </label>
                    `).join('') :
                '<p style="color: #999; font-size: 0.9rem; padding: 10px;">No hay etiquetas disponibles</p>';
            console.log('✅ Etiquetes renderitzades');
        }
            
    } catch (error) {
        console.error('❌ Error carregant categories/etiquetes:', error);
    }
}

/**
 * Guarda l'entrada (crea o actualitza)
 */
function guardarEntrada() {
    console.log('🔵 guardarEntrada() CRIDADA');
    
    const form = document.getElementById('formEntrada');
    if (!form) {
        alert('Error: Formulario no encontrado');
        return;
    }
    
    const formData = new FormData(form);
    
    const id = document.getElementById('entrada_id').value;
    const action = id ? 'actualitzar_entrada' : 'crear_entrada';
    
    formData.append('action', action);
    
    // Comentaris sempre desactivats (ja ve com a hidden input amb valor 0)
    // No cal afegir res més
    
    // DEBUG: Mostrar tots els camps que s'estan enviant
    console.log('📋 FormData contingut:');
    for (let pair of formData.entries()) {
        console.log(`  ${pair[0]}: ${pair[1]}`);
    }
    
    // Validació bàsica
    const titolCa = formData.get('titol_ca');
    const titolEs = formData.get('titol_es');
    const contingutCa = formData.get('contingut_ca');
    const contingutEs = formData.get('contingut_es');
    
    if (!titolCa || !titolEs) {
        alert('Por favor, introduce los títulos en ambos idiomas');
        return;
    }
    
    if (!contingutCa || !contingutEs) {
        alert('Por favor, introduce el contenido en ambos idiomas');
        return;
    }
    
    console.log('📡 Guardant entrada...');
    
    fetch('gblog.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('📡 Response status:', response.status);
        console.log('📡 Response ok:', response.ok);
        
        // Llegir la resposta com a text primer
        return response.text().then(text => {
            console.log('📄 Resposta RAW:', text);
            
            try {
                const data = JSON.parse(text);
                return data;
            } catch (e) {
                console.error('❌ Error parseant JSON:', e);
                console.error('❌ Text rebut:', text);
                throw new Error('Respuesta no es JSON válido: ' + text.substring(0, 100));
            }
        });
    })
    .then(data => {
        console.log('✅ Resposta parseada:', data);
        if (data.success) {
            const message = data.message || TEXTS.guardada;
            alert(message);
            
            // Tancar modal
            tancarModalEntrada();
            
            // Recarregar la pàgina sencera per actualitzar tot
            console.log('🔄 Recarregant pàgina...');
            setTimeout(() => {
                window.location.reload();
            }, 500);
        } else {
            alert(data.message || 'Error al guardar la entrada');
        }
    })
    .catch(error => {
        console.error('❌ Error complet:', error);
        alert('Error: ' + error.message);
    });
}

/**
 * Editar una entrada existent
 */
async function editarEntrada(idEntrada) {
    console.log('🔵 editarEntrada() CRIDADA amb ID:', idEntrada);
    
    try {
        // Primer obrim el modal buit
        const modal = document.getElementById('modalEntrada');
        if (!modal) {
            alert('Error: Modal no encontrado');
            return;
        }
        
        // Canviar títol del modal
        const title = document.getElementById('modalEntradaTitle');
        if (title) {
            title.innerHTML = `
                <i class="fas fa-edit"></i>
                <span>Editar Entrada</span>
            `;
        }
        
        // Mostrar el modal
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
        
        // Carregar categories i etiquetes
        await carregarCategoriesEtiquetesModal();
        
        // Obtenir les dades de l'entrada
        const formData = new FormData();
        formData.append('action', 'obtenir_entrada');
        formData.append('id', idEntrada);
        
        console.log('📡 Enviant petició obtenir_entrada amb ID:', idEntrada);
        
        const response = await fetch('gblog.php', {
            method: 'POST',
            body: formData
        });
        
        console.log('📡 Response status:', response.status);
        console.log('📡 Response headers:', response.headers.get('content-type'));
        
        const text = await response.text();
        console.log('📄 Response text (primers 500 chars):', text.substring(0, 500));
        
        let data;
        try {
            data = JSON.parse(text);
            console.log('✅ JSON parsed correctament:', data);
        } catch (parseError) {
            console.error('❌ Error parsejar JSON:', parseError);
            console.error('📄 Text complet rebut:', text);
            alert('Error al cargar la entrada: ' + parseError.message + '\n\nRevisa la consola per més detalls.');
            return;
        }
        
        if (data.success && data.entrada) {
            const entrada = data.entrada;
            console.log('✅ Entrada obtinguda:', entrada);
            console.log('📸 Imatge portada (imatge_portada):', entrada.imatge_portada);
            console.log('📸 Imatge portada (imatge):', entrada.imatge);
            console.log('📝 Alt CA (alt_imatge_ca):', entrada.alt_imatge_ca);
            console.log('📝 Alt CA (alt_ca):', entrada.alt_ca);
            console.log('📝 Alt ES (alt_imatge_es):', entrada.alt_imatge_es);
            console.log('📝 Alt ES (alt_es):', entrada.alt_es);
            
            // Ara sí, omplir el formulari amb les dades
            const idInput = document.getElementById('entrada_id');
            const titolCaInput = document.getElementById('entrada_titol_ca');
            const titolEsInput = document.getElementById('entrada_titol_es');
            const contingutCaInput = document.getElementById('entrada_contingut_ca');
            const contingutEsInput = document.getElementById('entrada_contingut_es');
            const resumCaInput = document.getElementById('entrada_resum_ca');
            const resumEsInput = document.getElementById('entrada_resum_es');
            const estatSelect = document.getElementById('entrada_estat');
            const visibleSelect = document.getElementById('entrada_visible');
            const imatgeInput = document.getElementById('entrada_imatge');
            const altCaInput = document.getElementById('entrada_alt_ca');
            const altEsInput = document.getElementById('entrada_alt_es');
            const metaTitleCaInput = document.getElementById('entrada_meta_title_ca');
            const metaTitleEsInput = document.getElementById('entrada_meta_title_es');
            const metaDescCaInput = document.getElementById('entrada_meta_desc_ca');
            const metaDescEsInput = document.getElementById('entrada_meta_desc_es');
            const metaKeyCaInput = document.getElementById('entrada_meta_keywords_ca');
            const metaKeyEsInput = document.getElementById('entrada_meta_keywords_es');
            const dataPublicacioInput = document.getElementById('entrada_data_publicacio');
            
            if (idInput) idInput.value = entrada.id_entrada;
            if (titolCaInput) titolCaInput.value = entrada.titol_ca || '';
            if (titolEsInput) titolEsInput.value = entrada.titol_es || '';
            if (contingutCaInput) contingutCaInput.value = entrada.contingut_ca || '';
            if (contingutEsInput) contingutEsInput.value = entrada.contingut_es || '';
            if (resumCaInput) resumCaInput.value = entrada.resum_ca || '';
            if (resumEsInput) resumEsInput.value = entrada.resum_es || '';
            if (estatSelect) estatSelect.value = entrada.estat || 'esborrany';
            if (visibleSelect) visibleSelect.value = entrada.visible || '1';
            // Comentaris sempre desactivats - no cal fer res
            if (imatgeInput) imatgeInput.value = entrada.imatge_portada || '';
            if (altCaInput) altCaInput.value = entrada.alt_imatge_ca || '';
            if (altEsInput) altEsInput.value = entrada.alt_imatge_es || '';
            if (metaTitleCaInput) metaTitleCaInput.value = entrada.meta_title_ca || '';
            if (metaTitleEsInput) metaTitleEsInput.value = entrada.meta_title_es || '';
            if (metaDescCaInput) metaDescCaInput.value = entrada.meta_description_ca || '';
            if (metaDescEsInput) metaDescEsInput.value = entrada.meta_description_es || '';
            if (metaKeyCaInput) metaKeyCaInput.value = entrada.meta_keywords_ca || '';
            if (metaKeyEsInput) metaKeyEsInput.value = entrada.meta_keywords_es || '';
            
            if (entrada.data_publicacio && dataPublicacioInput) {
                dataPublicacioInput.value = entrada.data_publicacio;
            }
            
            // Marcar categories seleccionades
            if (entrada.categories && Array.isArray(entrada.categories)) {
                entrada.categories.forEach(catId => {
                    const checkbox = document.querySelector(`input[name="categories[]"][value="${catId}"]`);
                    if (checkbox) checkbox.checked = true;
                });
            }
            
            // Marcar etiquetes seleccionades
            if (entrada.etiquetes && Array.isArray(entrada.etiquetes)) {
                entrada.etiquetes.forEach(etiId => {
                    const checkbox = document.querySelector(`input[name="etiquetes[]"][value="${etiId}"]`);
                    if (checkbox) checkbox.checked = true;
                });
            }
            
            console.log('✅ Formulari omplert correctament');
            
        } else {
            tancarModalEntrada();
            alert(data.message || 'Error al obtener los datos de la entrada');
        }
        
    } catch (error) {
        console.error('❌ Error:', error);
        tancarModalEntrada();
        alert('Error al cargar la entrada: ' + error.message);
    }
}

/**
 * Eliminar una entrada
 */
function eliminarEntrada(idEntrada, titol) {
    console.log('🔵 eliminarEntrada() CRIDADA amb ID:', idEntrada);
    
    if (!confirm(`${TEXTS.confirmarEliminar} "${titol}"?\n\n${TEXTS.accionNoDeshacer}`)) {
        console.log('❌ Eliminació cancel·lada per l\'usuari');
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'eliminar_entrada');
    formData.append('id', idEntrada);
    
    console.log('📡 Eliminant entrada ID:', idEntrada);
    
    fetch('gblog.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('📡 Response status:', response.status);
        return response.text();
    })
    .then(text => {
        console.log('📄 Resposta RAW:', text);
        
        try {
            const data = JSON.parse(text);
            console.log('✅ Resposta parseada:', data);
            
            if (data.success) {
                console.log('✅✅✅ Entrada eliminada correctament! Recarregant pàgina... ✅✅✅');
                alert(data.message || TEXTS.eliminada);
                
                // Recarregar la pàgina sencera
                setTimeout(() => {
                    console.log('🔄 Recarregant pàgina...');
                    window.location.reload();
                }, 500);
            } else {
                console.error('❌ Error del servidor:', data.message);
                alert(data.message || TEXTS.errorEliminar);
            }
        } catch (e) {
            console.error('❌ Error parseant JSON:', e);
            console.error('❌ Text rebut:', text);
            alert('Error: Respuesta no válida del servidor');
        }
    })
    .catch(error => {
        console.error('❌ Error de xarxa:', error);
        alert('Error de conexión: ' + error.message);
    });
}

// Assignar a window
window.carregarEntrades = carregarEntrades;
window.obrirModalEntrada = obrirModalEntrada;
window.tancarModalEntrada = tancarModalEntrada;
window.guardarEntrada = guardarEntrada;
window.editarEntrada = editarEntrada;
window.eliminarEntrada = eliminarEntrada;

console.log('✅ FI gblog-entrades.js - Funcions assignades a window');
console.log('✅ window.carregarEntrades:', typeof window.carregarEntrades);
console.log('✅ window.obrirModalEntrada:', typeof window.obrirModalEntrada);
console.log('✅ window.tancarModalEntrada:', typeof window.tancarModalEntrada);
console.log('✅ window.guardarEntrada:', typeof window.guardarEntrada);
console.log('✅ window.editarEntrada:', typeof window.editarEntrada);
console.log('✅ window.eliminarEntrada:', typeof window.eliminarEntrada);

