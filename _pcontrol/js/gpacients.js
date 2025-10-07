// Gestión de Pacientes - JavaScr        modalTitle.innerHTML = '<i class="fas fa-user-edit"></i> Editar Paciente';
        accioForm.value = 'actualitzar';
        
        // Omplir el formulari amb les dades
        document.getElementById('idPacient').value = dades.id;
        document.getElementById('nom').value = dades.nom || '';// Variables globales
let pacientActual = null;

// Inicialización
document.addEventListener('DOMContentLoaded', function() {
    // Auto-dismiss alerts after 5 seconds
    const alert = document.getElementById('alertMessage');
    if (alert) {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-20px)';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    }
});

// Mostrar formulari per nou pacient o editar
function mostrarFormulari(tipus, dades = null) {
    const modal = document.getElementById('modalPacient');
    const form = document.getElementById('formPacient');
    const modalTitle = document.getElementById('modalTitle');
    const accioForm = document.getElementById('accioForm');
    
    // Reset form
    form.reset();
    
    if (tipus === 'nou') {
        modalTitle.innerHTML = '<i class="fas fa-user-plus"></i> Nuevo Paciente';
        accioForm.value = 'crear';
        document.getElementById('idPacient').value = '';
    } else if (tipus === 'editar' && dades) {
        modalTitle.innerHTML = '<i class="fas fa-user-edit"></i> Editar Paciente';
        accioForm.value = 'actualitzar';
        
        // Omplir el formulari amb les dades
        document.getElementById('idPacient').value = dades.id_pacient || '';
        document.getElementById('nom').value = dades.nom || '';
        document.getElementById('cognoms').value = dades.cognoms || '';
        document.getElementById('data_naixement').value = dades.data_naixement || '';
        document.getElementById('sexe').value = dades.sexe || '';
        document.getElementById('telefon').value = dades.telefon || '';
        document.getElementById('email').value = dades.email || '';
        document.getElementById('adreca').value = dades.adreca || '';
        document.getElementById('ciutat').value = dades.ciutat || '';
        document.getElementById('codi_postal').value = dades.codi_postal || '';
        document.getElementById('antecedents_medics').value = dades.antecedents_medics || '';
        document.getElementById('medicacio_actual').value = dades.medicacio_actual || '';
        document.getElementById('alergies').value = dades.alergies || '';
        document.getElementById('contacte_emergencia_nom').value = dades.contacte_emergencia_nom || '';
        document.getElementById('contacte_emergencia_telefon').value = dades.contacte_emergencia_telefon || '';
        document.getElementById('contacte_emergencia_relacio').value = dades.contacte_emergencia_relacio || '';
        document.getElementById('estat').value = dades.estat || 'Activo';
        document.getElementById('observacions').value = dades.observacions || '';
    }
    
    modal.classList.add('show');
}

// Tancar modal
function tancarModal() {
    const modal = document.getElementById('modalPacient');
    modal.classList.remove('show');
}

// Tancar modal de detalls
function tancarModalDetalls() {
    const modal = document.getElementById('modalDetalls');
    modal.classList.remove('show');
}

// Tancar modal quan es clica fora
window.onclick = function(event) {
    const modalPacient = document.getElementById('modalPacient');
    const modalDetalls = document.getElementById('modalDetalls');
    
    if (event.target === modalPacient) {
        tancarModal();
    }
    if (event.target === modalDetalls) {
        tancarModalDetalls();
    }
}

// Editar pacient
function editarPacient(id) {
    // Fer petició AJAX per obtenir dades del pacient
    fetch(`api/get_pacient.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                mostrarFormulari('editar', data.pacient);
            } else {
                alert('Error al cargar los datos del paciente');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            // Si no hay API, redirigir con parámetro
            window.location.href = `gpacients.php?vista=editar&id=${id}`;
        });
}

// Veure detalls del pacient
function veureDetalls(id) {
    // Fer petició AJAX per obtenir dades completes
    fetch(`api/get_pacient.php?id=${id}&detalls=1`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                mostrarDetalls(data.pacient);
            } else {
                alert('Error al cargar los detalles del paciente');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error de conexión. Por favor, inténtalo de nuevo.');
        });
}

// Mostrar detalls del pacient en modal
function mostrarDetalls(pacient) {
    const modal = document.getElementById('modalDetalls');
    const content = document.getElementById('detallsContent');
    
    // Calcular edat
    let edat = '-';
    if (pacient.data_naixement) {
        const dataNaix = new Date(pacient.data_naixement);
        const avui = new Date();
        edat = Math.floor((avui - dataNaix) / (365.25 * 24 * 60 * 60 * 1000)) + ' anys';
    }
    
    content.innerHTML = `
        <div class="detall-section">
            <h4><i class="fas fa-user"></i> Datos Personales</h4>
            <div class="detall-grid">
                <div class="detall-item">
                    <div class="detall-label">Nombre Completo</div>
                    <div class="detall-value"><strong>${pacient.nom} ${pacient.cognoms}</strong></div>
                </div>
                <div class="detall-item">
                    <div class="detall-label">Fecha de Nacimiento</div>
                    <div class="detall-value">${pacient.data_naixement ? formatDate(pacient.data_naixement) : '-'}</div>
                </div>
                <div class="detall-item">
                    <div class="detall-label">Edad</div>
                    <div class="detall-value">${edat}</div>
                </div>
                <div class="detall-item">
                    <div class="detall-label">Sexo</div>
                    <div class="detall-value">${pacient.sexe || '-'}</div>
                </div>
                <div class="detall-item">
                    <div class="detall-label">Estado</div>
                    <div class="detall-value">
                        <span class="badge badge-${pacient.estat.toLowerCase()}">${pacient.estat}</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="detall-section">
            <h4><i class="fas fa-address-book"></i> Datos de Contacto</h4>
            <div class="detall-grid">
                <div class="detall-item">
                    <div class="detall-label">Teléfono</div>
                    <div class="detall-value">${pacient.telefon || '-'}</div>
                </div>
                <div class="detall-item">
                    <div class="detall-label">Email</div>
                    <div class="detall-value">${pacient.email || '-'}</div>
                </div>
                <div class="detall-item full-width">
                    <div class="detall-label">Dirección</div>
                    <div class="detall-value">${pacient.adreca || '-'}</div>
                </div>
                <div class="detall-item">
                    <div class="detall-label">Ciudad</div>
                    <div class="detall-value">${pacient.ciutat || '-'}</div>
                </div>
                <div class="detall-item">
                    <div class="detall-label">Código Postal</div>
                    <div class="detall-value">${pacient.codi_postal || '-'}</div>
                </div>
            </div>
        </div>
        
        ${pacient.antecedents_medics || pacient.medicacio_actual || pacient.alergies ? `
        <div class="detall-section">
            <h4><i class="fas fa-notes-medical"></i> Información Médica</h4>
            <div class="detall-grid">
                ${pacient.antecedents_medics ? `
                <div class="detall-item full-width">
                    <div class="detall-label">Antecedentes Médicos</div>
                    <div class="detall-value">${pacient.antecedents_medics}</div>
                </div>
                ` : ''}
                ${pacient.medicacio_actual ? `
                <div class="detall-item full-width">
                    <div class="detall-label">Medicación Actual</div>
                    <div class="detall-value">${pacient.medicacio_actual}</div>
                </div>
                ` : ''}
                ${pacient.alergies ? `
                <div class="detall-item full-width">
                    <div class="detall-label">Alergias</div>
                    <div class="detall-value">${pacient.alergies}</div>
                </div>
                ` : ''}
            </div>
        </div>
        ` : ''}
        
        ${pacient.contacte_emergencia_nom ? `
        <div class="detall-section">
            <h4><i class="fas fa-phone-alt"></i> Contacto de Emergencia</h4>
            <div class="detall-grid">
                <div class="detall-item">
                    <div class="detall-label">Nombre</div>
                    <div class="detall-value">${pacient.contacte_emergencia_nom || '-'}</div>
                </div>
                <div class="detall-item">
                    <div class="detall-label">Teléfono</div>
                    <div class="detall-value">${pacient.contacte_emergencia_telefon || '-'}</div>
                </div>
                <div class="detall-item">
                    <div class="detall-label">Relación</div>
                    <div class="detall-value">${pacient.contacte_emergencia_relacio || '-'}</div>
                </div>
            </div>
        </div>
        ` : ''}
        
        ${pacient.observacions ? `
        <div class="detall-section">
            <h4><i class="fas fa-sticky-note"></i> Observaciones</h4>
            <div class="detall-item full-width">
                <div class="detall-value">${pacient.observacions}</div>
            </div>
        </div>
        ` : ''}
        
        <div class="detall-section">
            <h4><i class="fas fa-clock"></i> Información del Sistema</h4>
            <div class="detall-grid">
                <div class="detall-item">
                    <div class="detall-label">Fecha de Registro</div>
                    <div class="detall-value">${formatDate(pacient.data_registre)}</div>
                </div>
                <div class="detall-item">
                    <div class="detall-label">Última Actualización</div>
                    <div class="detall-value">${formatDate(pacient.data_ultima_actualitzacio)}</div>
                </div>
            </div>
        </div>
    `;
    
    modal.classList.add('show');
}

// Canviar estat del pacient
function canviarEstat(id, estatActual) {
    // Mostrar opcions d'estat
    const estats = ['Activo', 'Inactivo', 'Alta', 'Seguimiento'];
    const opcions = estats.filter(e => e !== estatActual);
    
    let html = '<div style="padding: 20px;">';
    html += '<h3 style="margin-bottom: 15px;">Selecciona el nuevo estado:</h3>';
    html += '<div style="display: flex; flex-direction: column; gap: 10px;">';
    
    opcions.forEach(estat => {
        html += `
            <button onclick="confirmarCanviEstat(${id}, '${estat}')" 
                    style="padding: 12px; border: 1px solid #ddd; border-radius: 8px; 
                           background: white; cursor: pointer; text-align: left; font-size: 1rem;
                           transition: all 0.3s ease;"
                    onmouseover="this.style.background='#f5f5f5'"
                    onmouseout="this.style.background='white'">
                <i class="fas fa-${getIconEstat(estat)}"></i> ${estat}
            </button>
        `;
    });
    
    html += '</div></div>';
    
    // Crear modal temporal
    const modal = document.createElement('div');
    modal.className = 'modal show';
    modal.innerHTML = `
        <div class="modal-content" style="max-width: 400px; animation: slideUp 0.3s ease;">
            <div class="modal-header">
                <h2><i class="fas fa-exchange-alt"></i> Cambiar Estado</h2>
                <button class="modal-close" onclick="this.closest('.modal').remove()">&times;</button>
            </div>
            <div class="modal-body">
                ${html}
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="this.closest('.modal').remove()">
                    <i class="fas fa-times"></i> Cancelar
                </button>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
}

// Obtener icono según estado
function getIconEstat(estat) {
    const icons = {
        'Activo': 'check-circle',
        'Inactivo': 'times-circle',
        'Alta': 'user-check',
        'Seguimiento': 'heartbeat'
    };
    return icons[estat] || 'circle';
}

// Confirmar canvi d'estat
function confirmarCanviEstat(id, nouEstat) {
    // Crear formulari i enviar
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'gpacients.php';
    
    const inputAccio = document.createElement('input');
    inputAccio.type = 'hidden';
    inputAccio.name = 'accio';
    inputAccio.value = 'canviar_estat';
    
    const inputId = document.createElement('input');
    inputId.type = 'hidden';
    inputId.name = 'id_pacient';
    inputId.value = id;
    
    const inputEstat = document.createElement('input');
    inputEstat.type = 'hidden';
    inputEstat.name = 'nou_estat';
    inputEstat.value = nouEstat;
    
    form.appendChild(inputAccio);
    form.appendChild(inputId);
    form.appendChild(inputEstat);
    
    document.body.appendChild(form);
    form.submit();
}

// Filtrar pacients
function filtrarPacients(filtre) {
    window.location.href = `gpacients.php?filtre=${filtre}`;
}

// Buscar pacient
function buscarPacient() {
    const cerca = document.getElementById('searchInput').value;
    if (cerca.trim()) {
        window.location.href = `gpacients.php?cerca=${encodeURIComponent(cerca)}`;
    } else {
        window.location.href = 'gpacients.php';
    }
}

// Formatar data
function formatDate(dateString) {
    if (!dateString) return '-';
    
    const date = new Date(dateString);
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');
    
    // Si té hora (no és 00:00), mostrar-la
    if (hours !== '00' || minutes !== '00') {
        return `${day}/${month}/${year} ${hours}:${minutes}`;
    }
    
    return `${day}/${month}/${year}`;
}

// Validació de formulari
document.getElementById('formPacient')?.addEventListener('submit', function(e) {
    const nom = document.getElementById('nom').value;
    const cognoms = document.getElementById('cognoms').value;
    
    if (!nom || !cognoms) {
        e.preventDefault();
        alert('Por favor, completa todos los campos obligatorios (Nombre, Apellidos)');
        return false;
    }
});
