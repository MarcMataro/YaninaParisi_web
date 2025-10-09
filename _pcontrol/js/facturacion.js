/**
 * JavaScript para Gestión de Facturación
 * Funciones para gestionar pagos de sesiones
 */

// ============================================
// VARIABLES GLOBALES
// ============================================

let pagamentActual = null;

// ============================================
// FUNCIONES DE MODAL
// ============================================

/**
 * Mostrar formulario para crear o editar pago
 */
function mostrarFormulari(tipus, dades = null) {
    const modal = document.getElementById('modalPagament');
    const modalTitle = document.getElementById('modalTitle');
    const form = document.getElementById('formPagament');
    const accioForm = document.getElementById('accioForm');
    
    // Resetear formulario
    form.reset();
    
    if (tipus === 'nou') {
        modalTitle.innerHTML = '<i class="fas fa-money-bill-wave"></i> Nuevo Pago';
        accioForm.value = 'crear';
        document.getElementById('idPagament').value = '';
        
        // Establecer fecha de hoy por defecto
        const avui = new Date().toISOString().split('T')[0];
        document.getElementById('data_pagament').value = avui;
        
    // Estado por defecto
    document.getElementById('estat_pagament').value = 'Completado';
        
        // Ocultar grupo de número de factura
        document.getElementById('grupoNumeroFactura').style.display = 'none';
        document.getElementById('facturat').checked = false;
        
    } else if (tipus === 'editar' && dades) {
        modalTitle.innerHTML = '<i class="fas fa-edit"></i> Editar Pago';
        accioForm.value = 'actualitzar';
        
        // Rellenar campos
        document.getElementById('idPagament').value = dades.id_pagament;
        // Assegurar que l'opció de sessió existeix al select (pot ser que estigui filtrada)
        const selectSessio = document.getElementById('id_sessio');
        let opc = selectSessio.querySelector(`option[value="${dades.id_sessio}"]`);
        if (!opc) {
            // Afegim una opció temporal amb la informació mínima (preu, pacient)
            opc = document.createElement('option');
            opc.value = dades.id_sessio;
            opc.text = (dades.data_sessio ? (new Date(dades.data_sessio)).toLocaleDateString() : '') + ' - ' + (dades.nom_pacient || '') + ' ' + (dades.cognoms_pacient || '');
            opc.setAttribute('data-preu', dades.preu_sessio || dades.import || 0);
            // També afegim data-date i data-tipus per al resum
            if (dades.data_sessio) opc.setAttribute('data-date', dades.data_sessio);
            if (dades.tipus_sessio) opc.setAttribute('data-tipus', dades.tipus_sessio);
            if (dades.nom_pacient && dades.cognoms_pacient) {
                opc.setAttribute('data-pacient', `${dades.nom_pacient} ${dades.cognoms_pacient}`);
            }
            selectSessio.appendChild(opc);
        }
        document.getElementById('id_sessio').value = dades.id_sessio;
        document.getElementById('data_pagament').value = dades.data_pagament;
    document.getElementById('import').value = parseFloat(dades.import).toFixed(2);
    document.getElementById('metode_pagament').value = dades.metode_pagament;
    document.getElementById('estat_pagament').value = dades.estat;
        document.getElementById('facturat').checked = dades.facturat == 1;
        document.getElementById('numero_factura').value = dades.numero_factura || '';
        document.getElementById('observacions').value = dades.observacions || '';
        
    // Carregar preu de la sessió seleccionada (si està disponible), mostrar resum i número factura
    carregarPreuSessio();
    mostrarResumSessio();
    toggleNumeroFactura();
    }
    
    modal.style.display = 'flex';
}

/**
 * Cerrar modal de formulario
 */
function tancarModal() {
    const modal = document.getElementById('modalPagament');
    modal.style.display = 'none';
}

/**
 * Cerrar modal de detalles
 */
function tancarModalDetalls() {
    const modal = document.getElementById('modalDetalls');
    modal.style.display = 'none';
}

/**
 * Cerrar modal de facturado
 */
function tancarModalFacturat() {
    const modal = document.getElementById('modalFacturat');
    modal.style.display = 'none';
}

/**
 * Cerrar modal de anular
 */
function tancarModalAnular() {
    const modal = document.getElementById('modalAnular');
    modal.style.display = 'none';
}

// ============================================
// FUNCIONES DE CARGA DE DATOS
// ============================================

/**
 * Cargar precio de la sesión seleccionada
 */
function carregarPreuSessio() {
    const selectSessio = document.getElementById('id_sessio');
    const importInput = document.getElementById('import');
    const selectedOption = selectSessio.options[selectSessio.selectedIndex];
    
    if (selectedOption.value) {
        const preu = selectedOption.getAttribute('data-preu');
        if (preu) {
            importInput.value = parseFloat(preu).toFixed(2);
        }
    }
}

/**
 * Omple el resum de la sessió seleccionada i el mostra
 */
function mostrarResumSessio() {
    const selectSessio = document.getElementById('id_sessio');
    const selectedOption = selectSessio.options[selectSessio.selectedIndex];
    const resum = document.getElementById('resumSessio');

    if (selectedOption && selectedOption.value) {
        const date = selectedOption.getAttribute('data-date') || '';
        const pacient = selectedOption.getAttribute('data-pacient') || '';
        const tipus = selectedOption.getAttribute('data-tipus') || '';
        const preu = selectedOption.getAttribute('data-preu') || '';

        document.getElementById('resumSessioData').textContent = date ? (new Date(date)).toLocaleDateString() : '-';
        document.getElementById('resumSessioPacient').textContent = pacient || '-';
        document.getElementById('resumSessioTipus').textContent = tipus || '-';
        document.getElementById('resumSessioPreu').textContent = preu ? parseFloat(preu).toFixed(2) : '-';

        resum.style.display = 'block';
    } else {
        resum.style.display = 'none';
    }
}

/**
 * Editar pago
 */
async function editarPagament(idPagament) {
    try {
        const response = await fetch(`facturacion.php?accio=obtenir&id=${idPagament}`);
        const data = await response.json();
        
        if (data && !data.error) {
            mostrarFormulari('editar', data);
        } else {
            alert('Error al cargar los datos del pago');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al cargar los datos del pago');
    }
}

/**
 * Ver detalles del pago
 */
async function veureDetalls(idPagament) {
    try {
        const response = await fetch(`ajax/get_pagament_detalls.php?id=${idPagament}`);
        const data = await response.json();
        
        if (data.success) {
            mostrarDetalls(data.pagament);
        } else {
            alert('Error al cargar los detalles del pago');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al cargar los detalles del pago');
    }
}

/**
 * Mostrar detalles del pago en modal
 */
function mostrarDetalls(pagament) {
    const modal = document.getElementById('modalDetalls');
    const content = document.getElementById('detallsContent');
    
    const html = `
        <div class="detalls-pagament">
            <div class="detall-section">
                <h4><i class="fas fa-info-circle"></i> Información del Pago</h4>
                <div class="detall-grid">
                    <div class="detall-item">
                        <span class="detall-label">ID Pago:</span>
                        <span class="detall-value">#${pagament.id_pagament}</span>
                    </div>
                    <div class="detall-item">
                        <span class="detall-label">Fecha de Pago:</span>
                        <span class="detall-value">${formatarData(pagament.data_pagament)}</span>
                    </div>
                    <div class="detall-item">
                        <span class="detall-label">Importe:</span>
                        <span class="detall-value amount-large">${parseFloat(pagament.import).toFixed(2)} €</span>
                    </div>
                    <div class="detall-item">
                        <span class="detall-label">Método de Pago:</span>
                        <span class="detall-value">
                            <span class="badge badge-metode badge-${pagament.metode_pagament.toLowerCase()}">
                                ${pagament.metode_pagament}
                            </span>
                        </span>
                    </div>
                    <div class="detall-item">
                        <span class="detall-label">Estado:</span>
                        <span class="detall-value">
                            <span class="badge badge-${pagament.estat.toLowerCase()}">
                                ${pagament.estat}
                            </span>
                        </span>
                    </div>
                    <div class="detall-item">
                        <span class="detall-label">Facturado:</span>
                        <span class="detall-value">
                            ${pagament.facturat == 1 
                                ? `<span class="badge badge-success"><i class="fas fa-check"></i> Sí</span>` 
                                : `<span class="badge badge-secondary"><i class="fas fa-times"></i> No</span>`
                            }
                        </span>
                    </div>
                    ${pagament.numero_factura ? `
                    <div class="detall-item">
                        <span class="detall-label">Nº Factura:</span>
                        <span class="detall-value"><strong>${pagament.numero_factura}</strong></span>
                    </div>
                    ` : ''}
                </div>
            </div>
            
            <div class="detall-section">
                <h4><i class="fas fa-calendar-check"></i> Información de la Sesión</h4>
                <div class="detall-grid">
                    <div class="detall-item">
                        <span class="detall-label">Fecha Sesión:</span>
                        <span class="detall-value">${formatarData(pagament.data_sessio)}</span>
                    </div>
                    <div class="detall-item">
                        <span class="detall-label">Hora:</span>
                        <span class="detall-value">${pagament.hora_inici ? pagament.hora_inici.substring(0,5) : '-'}</span>
                    </div>
                    <div class="detall-item">
                        <span class="detall-label">Tipo de Sesión:</span>
                        <span class="detall-value">
                            <span class="badge badge-info">${pagament.tipus_sessio}</span>
                        </span>
                    </div>
                    <div class="detall-item">
                        <span class="detall-label">Precio Sesión:</span>
                        <span class="detall-value">${parseFloat(pagament.preu_sessio).toFixed(2)} €</span>
                    </div>
                </div>
            </div>
            
            <div class="detall-section">
                <h4><i class="fas fa-user"></i> Información del Paciente</h4>
                <div class="detall-grid">
                    <div class="detall-item">
                        <span class="detall-label">Nombre:</span>
                        <span class="detall-value"><strong>${pagament.nom_pacient} ${pagament.cognoms_pacient}</strong></span>
                    </div>
                    ${pagament.email_pacient ? `
                    <div class="detall-item">
                        <span class="detall-label">Email:</span>
                        <span class="detall-value">
                            <a href="mailto:${pagament.email_pacient}">${pagament.email_pacient}</a>
                        </span>
                    </div>
                    ` : ''}
                    ${pagament.telefon_pacient ? `
                    <div class="detall-item">
                        <span class="detall-label">Teléfono:</span>
                        <span class="detall-value">
                            <a href="tel:${pagament.telefon_pacient}">${pagament.telefon_pacient}</a>
                        </span>
                    </div>
                    ` : ''}
                </div>
            </div>
            
            ${pagament.observacions ? `
            <div class="detall-section">
                <h4><i class="fas fa-sticky-note"></i> Observaciones</h4>
                <div class="observacions-box">
                    ${pagament.observacions}
                </div>
            </div>
            ` : ''}
            
            <div class="detall-section">
                <h4><i class="fas fa-clock"></i> Registro del Sistema</h4>
                <div class="detall-grid">
                    <div class="detall-item">
                        <span class="detall-label">Fecha de Registro:</span>
                        <span class="detall-value">${formatarDataHora(pagament.data_registre)}</span>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    content.innerHTML = html;
    modal.style.display = 'flex';
}

// ============================================
// FUNCIONES DE ACCIONES
// ============================================

/**
 * Mostrar modal para marcar como facturado
 */
function marcarFacturat(idPagament) {
    document.getElementById('idPagamentFacturat').value = idPagament;
    document.getElementById('numero_factura_modal').value = '';
    document.getElementById('modalFacturat').style.display = 'flex';
}

/**
 * Mostrar modal para anular pago
 */
function anularPagament(idPagament) {
    document.getElementById('idPagamentAnular').value = idPagament;
    document.getElementById('motiu').value = '';
    document.getElementById('modalAnular').style.display = 'flex';
}

/**
 * Confirmar eliminación de pago
 */
function confirmarEliminar(idPagament) {
    if (confirm('¿Estás seguro de que quieres eliminar este pago? Esta acción no se puede deshacer.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'facturacion.php';
        
        const accioInput = document.createElement('input');
        accioInput.type = 'hidden';
        accioInput.name = 'accio';
        accioInput.value = 'eliminar';
        
        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'id_pagament';
        idInput.value = idPagament;
        
        form.appendChild(accioInput);
        form.appendChild(idInput);
        document.body.appendChild(form);
        form.submit();
    }
}

/**
 * Registrar pago rápido desde sesiones sin pagar
 */
function registrarPagamentRapid(idSessio, preu) {
    mostrarFormulari('nou');
    
    // Esperar a que el modal se haya renderizado
    setTimeout(() => {
        document.getElementById('id_sessio').value = idSessio;
        document.getElementById('import').value = parseFloat(preu).toFixed(2);
    }, 100);
}

// ============================================
// FUNCIONES DE UTILIDAD
// ============================================

/**
 * Toggle del campo número de factura
 */
function toggleNumeroFactura() {
    const checkbox = document.getElementById('facturat');
    const grupo = document.getElementById('grupoNumeroFactura');
    const input = document.getElementById('numero_factura');
    
    if (checkbox.checked) {
        grupo.style.display = 'block';
        input.required = true;
    } else {
        grupo.style.display = 'none';
        input.required = false;
        input.value = '';
    }
}

/**
 * Formatear fecha (YYYY-MM-DD a DD/MM/YYYY)
 */
function formatarData(dataStr) {
    if (!dataStr) return '-';
    const parts = dataStr.split('-');
    return `${parts[2]}/${parts[1]}/${parts[0]}`;
}

/**
 * Formatear fecha y hora
 */
function formatarDataHora(dataHoraStr) {
    if (!dataHoraStr) return '-';
    const date = new Date(dataHoraStr);
    const dia = String(date.getDate()).padStart(2, '0');
    const mes = String(date.getMonth() + 1).padStart(2, '0');
    const any = date.getFullYear();
    const hora = String(date.getHours()).padStart(2, '0');
    const minut = String(date.getMinutes()).padStart(2, '0');
    return `${dia}/${mes}/${any} ${hora}:${minut}`;
}

// ============================================
// EVENT LISTENERS
// ============================================

// Cerrar modales al hacer clic fuera
window.onclick = function(event) {
    const modals = ['modalPagament', 'modalDetalls', 'modalFacturat', 'modalAnular'];
    modals.forEach(modalId => {
        const modal = document.getElementById(modalId);
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
}

// Cerrar modales con la tecla ESC
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        tancarModal();
        tancarModalDetalls();
        tancarModalFacturat();
        tancarModalAnular();
    }
});

// Validación del formulario
document.getElementById('formPagament')?.addEventListener('submit', function(e) {
    const import_val = parseFloat(document.getElementById('import').value);
    
    if (import_val <= 0) {
        e.preventDefault();
        alert('El importe debe ser mayor que cero');
        return false;
    }
    
    return true;
});
