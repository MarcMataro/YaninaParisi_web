/**
 * Gestió de Categories - JavaScript
 */

let categories = [];
let categoriaEditant = null;

// Carregar categories quan es canvia al tab
document.addEventListener('DOMContentLoaded', function() {
    console.log('=== Categories JS carregat ===');
    
    // Si el tab de categories ja està actiu, carregar-les
    const categoriesTab = document.getElementById('tab-categories');
    if (categoriesTab && categoriesTab.classList.contains('active')) {
        console.log('Tab categories ja actiu, carregant...');
        carregarCategories();
    }
});

/**
 * Carrega totes les categories de la base de dades
 */
function carregarCategories() {
    console.log('=== INICIANT CÀRREGA DE CATEGORIES ===');
    console.log('URL:', window.location.href);
    
    const container = document.getElementById('categories-list');
    if (!container) {
        console.error('ERRO: Container categories-list no trobat!');
        return;
    }
    
    // Mostrar loading
    container.innerHTML = `
        <div class="loading-container">
            <i class="fas fa-spinner fa-spin"></i>
            <p>Cargando categorías...</p>
        </div>
    `;
    
    console.log('Fent petició POST a gblog.php...');
    
    fetch('gblog.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=obtenir_categories'
    })
    .then(response => {
        console.log('Resposta rebuda, status:', response.status);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('=== DADES REBUDES ===');
        console.log('Success:', data.success);
        console.log('Categories:', data.categories);
        console.log('Count:', data.count);
        
        if (data.success) {
            categories = data.categories || [];
            console.log('Categories assignades, total:', categories.length);
            mostrarCategories();
        } else {
            // Si no està autenticat, redirigir
            if (data.redirect) {
                console.warn('Sessió expirada, redirigint...');
                window.location.href = data.redirect;
                return;
            }
            console.error('La petició ha fallat:', data.message);
            mostrarError(data.message || 'Error al cargar categorías');
        }
    })
    .catch(error => {
        console.error('=== ERROR EN LA PETICIÓ ===');
        console.error('Error:', error);
        console.error('Error message:', error.message);
        mostrarError('Error de conexión: ' + error.message);
    });
}

/**
 * Mostra les categories al DOM
 */
function mostrarCategories() {
    console.log('=== Mostrant categories ===');
    console.log('Total categories:', categories.length);
    console.log('Categories:', categories);
    
    const container = document.getElementById('categories-list');
    
    if (!container) {
        console.error('Container categories-list no trobat');
        return;
    }
    
    if (categories.length === 0) {
        console.log('No hi ha categories, mostrant estat buit');
        container.innerHTML = `
            <div class="empty-state" style="padding: 60px 30px;">
                <i class="fas fa-folder-open"></i>
                <p>No hay categorías todavía</p>
                <button class="btn btn-primary" onclick="obrirModalCategoria()">
                    <i class="fas fa-plus"></i> Crear Primera Categoría
                </button>
            </div>
        `;
        return;
    }
    
    console.log('Creant taula per', categories.length, 'categories');
    
    // Ordenar per ordre, després per nom
    const categoriesOrdenades = [...categories].sort((a, b) => {
        if (a.ordre !== b.ordre) {
            return (a.ordre || 999) - (b.ordre || 999);
        }
        return a.nom_es.localeCompare(b.nom_es);
    });
    
    container.innerHTML = `
        <div class="list-container">
            <table class="list-table">
                <thead>
                    <tr>
                        <th style="width: 5%;" class="text-center">Orden</th>
                        <th style="width: 25%;">Nombre (ES)</th>
                        <th style="width: 25%;">Nom (CA)</th>
                        <th style="width: 20%;">Descripción</th>
                        <th style="width: 10%;" class="text-center">Estado</th>
                        <th style="width: 10%;" class="text-center">Entradas</th>
                        <th style="width: 5%;" class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    ${categoriesOrdenades.map(cat => `
                        <tr class="list-row ${cat.activa == 0 ? 'inactive-row' : ''}">
                            <td class="text-center">
                                <span class="order-badge">${cat.ordre || '-'}</span>
                            </td>
                            <td>
                                <div class="item-name">
                                    <i class="fas fa-folder" style="color: #a89968; margin-right: 8px;"></i>
                                    <strong>${cat.nom_es}</strong>
                                </div>
                            </td>
                            <td>
                                <span style="color: #666;">${cat.nom_ca}</span>
                            </td>
                            <td>
                                <span style="color: #666; font-size: 0.85rem;">
                                    ${cat.descripcio_es ? (cat.descripcio_es.length > 50 ? cat.descripcio_es.substring(0, 50) + '...' : cat.descripcio_es) : '-'}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="status-badge ${cat.activa == 1 ? 'status-activa' : 'status-inactiva'}">
                                    ${cat.activa == 1 ? 'Activa' : 'Inactiva'}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="count-badge">${cat.num_entrades || 0}</span>
                            </td>
                            <td class="text-center">
                                <div class="action-buttons-inline">
                                    <button class="btn-icon-sm" onclick="editarCategoria(${cat.id_category})" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn-icon-sm btn-danger" onclick="eliminarCategoria(${cat.id_category})" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>
    `;
    
    console.log('Taula creada al DOM');
}

/**
 * Crea una card HTML per a una categoria
 */
function crearCategoriaCard(categoria) {
    const div = document.createElement('div');
    div.className = `category-card ${categoria.activa == 0 ? 'inactive' : ''}`;
    div.dataset.id = categoria.id_category;
    
    const statusText = categoria.activa == 1 ? 'Activa' : 'Inactiva';
    const statusClass = categoria.activa == 1 ? '' : 'inactive';
    
    div.innerHTML = `
        <div class="item-header">
            <div class="item-title">
                <h4>${categoria.nom_es} / ${categoria.nom_ca}</h4>
                <span class="slug">${categoria.slug_es}</span>
            </div>
            <div class="item-actions">
                <button class="btn-icon-sm" onclick="editarCategoria(${categoria.id_category})" title="Editar">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn-icon-sm btn-danger" onclick="eliminarCategoria(${categoria.id_category})" title="Eliminar">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
        
        ${categoria.descripcion_es || categoria.descripcio_ca ? `
            <div class="item-description">
                ${categoria.descripcion_es || categoria.descripcio_ca || ''}
            </div>
        ` : ''}
        
        <div class="item-footer">
            <div class="item-status">
                <span class="status-dot ${statusClass}"></span>
                <span>${statusText}</span>
            </div>
            <span>Orden: ${categoria.ordre || 0}</span>
        </div>
    `;
    
    return div;
}

/**
 * Obre el modal per crear una nova categoria
 */
function obrirModalCategoria() {
    categoriaEditant = null;
    
    document.getElementById('modalCategoriaTitle').innerHTML = `
        <i class="fas fa-folder"></i>
        <span>Nueva Categoría</span>
    `;
    
    document.getElementById('formCategoria').reset();
    document.getElementById('categoria_id').value = '';
    document.getElementById('categoria_activa').checked = true;
    
    document.getElementById('modalCategoria').classList.add('show');
    document.body.style.overflow = 'hidden';
}

/**
 * Edita una categoria existent
 */
function editarCategoria(id) {
    const categoria = categories.find(c => c.id_category == id);
    
    if (!categoria) {
        alert('Categoría no encontrada');
        return;
    }
    
    categoriaEditant = categoria;
    
    document.getElementById('modalCategoriaTitle').innerHTML = `
        <i class="fas fa-edit"></i>
        <span>Editar Categoría</span>
    `;
    
    document.getElementById('categoria_id').value = categoria.id_category;
    document.getElementById('categoria_nom_ca').value = categoria.nom_ca || '';
    document.getElementById('categoria_nom_es').value = categoria.nom_es || '';
    document.getElementById('categoria_desc_ca').value = categoria.descripcio_ca || '';
    document.getElementById('categoria_desc_es').value = categoria.descripcion_es || '';
    document.getElementById('categoria_ordre').value = categoria.ordre || 0;
    document.getElementById('categoria_activa').checked = categoria.activa == 1;
    
    document.getElementById('modalCategoria').classList.add('show');
    document.body.style.overflow = 'hidden';
}

/**
 * Tanca el modal de categoria
 */
function tancarModalCategoria() {
    document.getElementById('modalCategoria').classList.remove('show');
    document.body.style.overflow = '';
    categoriaEditant = null;
}

/**
 * Guarda la categoria (crea o actualitza)
 */
function guardarCategoria() {
    const form = document.getElementById('formCategoria');
    const formData = new FormData(form);
    
    const id = document.getElementById('categoria_id').value;
    const action = id ? 'actualitzar_categoria' : 'crear_categoria';
    
    formData.append('action', action);
    formData.append('activa', document.getElementById('categoria_activa').checked ? 1 : 0);
    
    // Validació bàsica
    const nomCa = formData.get('nom_ca');
    const nomEs = formData.get('nom_es');
    
    if (!nomCa || !nomEs) {
        alert('Por favor, introduce los nombres en ambos idiomas');
        return;
    }
    
    console.log('Guardant categoria...');
    
    fetch('gblog.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        console.log('Resposta:', data);
        if (data.success) {
            alert(data.message || 'Categoría guardada correctamente');
            tancarModalCategoria();
            carregarCategories();
        } else {
            alert(data.message || 'Error al guardar la categoría');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error de conexión al guardar la categoría');
    });
}

/**
 * Elimina una categoria
 */
function eliminarCategoria(id) {
    if (!confirm('¿Estás seguro de que quieres eliminar esta categoría?')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'eliminar_categoria');
    formData.append('id', id);
    
    fetch('gblog.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message || 'Categoría eliminada correctamente');
            carregarCategories();
        } else {
            alert(data.message || 'Error al eliminar la categoría');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error de conexión al eliminar la categoría');
    });
}

/**
 * Mostra un missatge d'error
 */
function mostrarError(missatge) {
    const container = document.getElementById('categories-list');
    if (container) {
        container.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-exclamation-triangle"></i>
                <p style="color: #dc3545;">${missatge}</p>
                <button class="btn btn-secondary" onclick="carregarCategories()">
                    <i class="fas fa-redo"></i> Reintentar
                </button>
            </div>
        `;
    }
}

// Fer funcions globals
window.carregarCategories = carregarCategories;
window.obrirModalCategoria = obrirModalCategoria;
window.editarCategoria = editarCategoria;
window.tancarModalCategoria = tancarModalCategoria;
window.guardarCategoria = guardarCategoria;
window.eliminarCategoria = eliminarCategoria;

console.log('=== Categories JS inicialitzat ===');
