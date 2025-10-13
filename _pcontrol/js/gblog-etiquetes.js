/**
 * Gestió d'Etiquetes - JavaScript
 */

let etiquetes = [];
let etiquetaEditant = null;

// Carregar etiquetes quan es canvia al tab
document.addEventListener('DOMContentLoaded', function() {
    console.log('=== Etiquetes JS carregat ===');
    
    // Si el tab d'etiquetes ja està actiu, carregar-les
    const etiquetesTab = document.getElementById('tab-etiquetes');
    if (etiquetesTab && etiquetesTab.classList.contains('active')) {
        console.log('Tab etiquetes ja actiu, carregant...');
        carregarEtiquetes();
    }
});

/**
 * Carrega totes les etiquetes de la base de dades
 */
function carregarEtiquetes() {
    console.log('=== INICIANT CÀRREGA D\'ETIQUETES ===');
    
    const container = document.getElementById('etiquetes-list');
    if (!container) {
        console.error('ERROR: Container etiquetes-list no trobat!');
        return;
    }
    
    // Mostrar loading
    container.innerHTML = `
        <div class="loading-container">
            <i class="fas fa-spinner fa-spin"></i>
            <p>Cargando etiquetas...</p>
        </div>
    `;
    
    console.log('Fent petició POST a gblog.php...');
    
    fetch('gblog.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=obtenir_etiquetes'
    })
    .then(response => {
        console.log('Resposta rebuda, status:', response.status);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('=== DADES REBUDES (ETIQUETES) ===');
        console.log('Success:', data.success);
        console.log('Etiquetes:', data.etiquetes);
        
        if (data.success) {
            etiquetes = data.etiquetes || [];
            console.log('Etiquetes assignades, total:', etiquetes.length);
            mostrarEtiquetes();
        } else {
            // Si no està autenticat, redirigir
            if (data.redirect) {
                console.warn('Sessió expirada, redirigint...');
                window.location.href = data.redirect;
                return;
            }
            console.error('La petició ha fallat:', data.message);
            mostrarErrorEtiquetes(data.message || 'Error al cargar etiquetas');
        }
    })
    .catch(error => {
        console.error('=== ERROR EN LA PETICIÓ (ETIQUETES) ===');
        console.error('Error:', error);
        console.error('Error message:', error.message);
        mostrarErrorEtiquetes('Error de conexión: ' + error.message);
    });
}

/**
 * Mostra les etiquetes al DOM
 */
function mostrarEtiquetes() {
    console.log('=== Mostrant etiquetes ===');
    console.log('Total etiquetes:', etiquetes.length);
    
    const container = document.getElementById('etiquetes-list');
    
    if (!container) {
        console.error('Container etiquetes-list no trobat');
        return;
    }
    
    if (etiquetes.length === 0) {
        console.log('No hi ha etiquetes, mostrant estat buit');
        container.innerHTML = `
            <div class="empty-state" style="padding: 60px 30px;">
                <i class="fas fa-tags"></i>
                <p>No hay etiquetas todavía</p>
                <button class="btn btn-primary" onclick="obrirModalEtiqueta()">
                    <i class="fas fa-plus"></i> Crear Primera Etiqueta
                </button>
            </div>
        `;
        return;
    }
    
    console.log('Creant taula per', etiquetes.length, 'etiquetes');
    
    // Ordenar per nom en espanyol
    const etiquetesOrdenades = [...etiquetes].sort((a, b) => 
        a.nom_es.localeCompare(b.nom_es)
    );
    
    container.innerHTML = `
        <div class="list-container">
            <table class="list-table">
                <thead>
                    <tr>
                        <th style="width: 30%;">Nombre (ES)</th>
                        <th style="width: 30%;">Nom (CA)</th>
                        <th style="width: 15%;" class="text-center">Estado</th>
                        <th style="width: 15%;" class="text-center">Entradas</th>
                        <th style="width: 10%;" class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    ${etiquetesOrdenades.map(etiq => `
                        <tr class="list-row ${etiq.activa == 0 ? 'inactive-row' : ''}">
                            <td>
                                <div class="item-name">
                                    <i class="fas fa-tag" style="color: #a89968; margin-right: 8px;"></i>
                                    <strong>${etiq.nom_es}</strong>
                                </div>
                            </td>
                            <td>
                                <span style="color: #666;">${etiq.nom_ca}</span>
                            </td>
                            <td class="text-center">
                                <span class="status-badge ${etiq.activa == 1 ? 'status-activa' : 'status-inactiva'}">
                                    ${etiq.activa == 1 ? 'Activa' : 'Inactiva'}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="count-badge">${etiq.num_entrades || 0}</span>
                            </td>
                            <td class="text-center">
                                <div class="action-buttons-inline">
                                    <button class="btn-icon-sm" onclick="editarEtiqueta(${etiq.id_etiqueta})" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn-icon-sm btn-danger" onclick="eliminarEtiqueta(${etiq.id_etiqueta})" title="Eliminar">
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
 * Crea una card HTML per a una etiqueta
 */
function crearEtiquetaCard(etiqueta) {
    const div = document.createElement('div');
    div.className = `tag-card ${etiqueta.activa == 0 ? 'inactive' : ''}`;
    div.dataset.id = etiqueta.id_etiqueta;
    
    const statusText = etiqueta.activa == 1 ? 'Activa' : 'Inactiva';
    const statusClass = etiqueta.activa == 1 ? '' : 'inactive';
    
    div.innerHTML = `
        <div class="item-header">
            <div class="item-title">
                <h4>${etiqueta.nom_es} / ${etiqueta.nom_ca}</h4>
                <span class="slug">${etiqueta.slug_es || etiqueta.slug_ca}</span>
            </div>
            <div class="item-actions">
                <button class="btn-icon-sm" onclick="editarEtiqueta(${etiqueta.id_etiqueta})" title="Editar">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn-icon-sm btn-danger" onclick="eliminarEtiqueta(${etiqueta.id_etiqueta})" title="Eliminar">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
        
        ${etiqueta.descripcion_es || etiqueta.descripcio_ca ? `
            <div class="item-description">
                ${etiqueta.descripcion_es || etiqueta.descripcio_ca || ''}
            </div>
        ` : ''}
        
        <div class="item-footer">
            <div class="item-status">
                <span class="status-dot ${statusClass}"></span>
                <span>${statusText}</span>
            </div>
            <span>Orden: ${etiqueta.ordre || 0}</span>
        </div>
    `;
    
    return div;
}

/**
 * Obre el modal per crear una nova etiqueta
 */
function obrirModalEtiqueta() {
    etiquetaEditant = null;
    
    document.getElementById('modalEtiquetaTitle').innerHTML = `
        <i class="fas fa-tag"></i>
        <span>Nueva Etiqueta</span>
    `;
    
    document.getElementById('formEtiqueta').reset();
    document.getElementById('etiqueta_id').value = '';
    document.getElementById('etiqueta_activa').checked = true;
    
    document.getElementById('modalEtiqueta').classList.add('show');
    document.body.style.overflow = 'hidden';
}

/**
 * Edita una etiqueta existent
 */
function editarEtiqueta(id) {
    const etiqueta = etiquetes.find(e => e.id_etiqueta == id);
    
    if (!etiqueta) {
        alert('Etiqueta no encontrada');
        return;
    }
    
    etiquetaEditant = etiqueta;
    
    document.getElementById('modalEtiquetaTitle').innerHTML = `
        <i class="fas fa-edit"></i>
        <span>Editar Etiqueta</span>
    `;
    
    document.getElementById('etiqueta_id').value = etiqueta.id_etiqueta;
    document.getElementById('etiqueta_nom_ca').value = etiqueta.nom_ca || '';
    document.getElementById('etiqueta_nom_es').value = etiqueta.nom_es || '';
    document.getElementById('etiqueta_desc_ca').value = etiqueta.descripcio_ca || '';
    document.getElementById('etiqueta_desc_es').value = etiqueta.descripcion_es || '';
    document.getElementById('etiqueta_ordre').value = etiqueta.ordre || 0;
    document.getElementById('etiqueta_activa').checked = etiqueta.activa == 1;
    
    document.getElementById('modalEtiqueta').classList.add('show');
    document.body.style.overflow = 'hidden';
}

/**
 * Tanca el modal d'etiqueta
 */
function tancarModalEtiqueta() {
    document.getElementById('modalEtiqueta').classList.remove('show');
    document.body.style.overflow = '';
    etiquetaEditant = null;
}

/**
 * Guarda l'etiqueta (crea o actualitza)
 */
function guardarEtiqueta() {
    const form = document.getElementById('formEtiqueta');
    const formData = new FormData(form);
    
    const id = document.getElementById('etiqueta_id').value;
    const action = id ? 'actualitzar_etiqueta' : 'crear_etiqueta';
    
    formData.append('action', action);
    formData.append('activa', document.getElementById('etiqueta_activa').checked ? 1 : 0);
    
    // Validació bàsica
    const nomCa = formData.get('nom_ca');
    const nomEs = formData.get('nom_es');
    
    if (!nomCa || !nomEs) {
        alert('Por favor, introduce los nombres en ambos idiomas');
        return;
    }
    
    console.log('Guardant etiqueta...');
    
    fetch('gblog.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        console.log('Resposta:', data);
        if (data.success) {
            alert(data.message || 'Etiqueta guardada correctamente');
            tancarModalEtiqueta();
            carregarEtiquetes();
        } else {
            alert(data.message || 'Error al guardar la etiqueta');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error de conexión al guardar la etiqueta');
    });
}

/**
 * Elimina una etiqueta
 */
function eliminarEtiqueta(id) {
    if (!confirm('¿Estás seguro de que quieres eliminar esta etiqueta?')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'eliminar_etiqueta');
    formData.append('id', id);
    
    fetch('gblog.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message || 'Etiqueta eliminada correctamente');
            carregarEtiquetes();
        } else {
            alert(data.message || 'Error al eliminar la etiqueta');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error de conexión al eliminar la etiqueta');
    });
}

/**
 * Mostra un missatge d'error
 */
function mostrarErrorEtiquetes(missatge) {
    const container = document.getElementById('etiquetes-list');
    if (container) {
        container.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-exclamation-triangle"></i>
                <p style="color: #dc3545;">${missatge}</p>
                <button class="btn btn-secondary" onclick="carregarEtiquetes()">
                    <i class="fas fa-redo"></i> Reintentar
                </button>
            </div>
        `;
    }
}

// Fer funcions globals
window.carregarEtiquetes = carregarEtiquetes;
window.obrirModalEtiqueta = obrirModalEtiqueta;
window.editarEtiqueta = editarEtiqueta;
window.tancarModalEtiqueta = tancarModalEtiqueta;
window.guardarEtiqueta = guardarEtiqueta;
window.eliminarEtiqueta = eliminarEtiqueta;

console.log('=== Etiquetes JS inicialitzat ===');
