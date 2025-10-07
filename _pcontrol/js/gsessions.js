/**
 * JavaScript para Gestión de Sesiones
 * Funciones para gestionar sesiones terapéuticas
 */

// ============================================
// VARIABLES GLOBALES
// ============================================

let sessionActual = null;

// ============================================
// FUNCIONES DE FILTRADO
// ============================================

/**
 * Filtrar sesiones por estado
 */
function filtrarSessions(filtre) {
    window.location.href = `gsessions.php?filtre=${filtre}`;
}

/**
 * Filtrar por paciente
 */
function filtrarPerPacient(idPacient) {
    const urlParams = new URLSearchParams(window.location.search);
    if (idPacient) {
        urlParams.set('pacient', idPacient);
    } else {
        urlParams.delete('pacient');
    }
    window.location.href = `gsessions.php?${urlParams.toString()}`;
}

// ============================================
// FUNCIONES DE MODAL
// ============================================

/**
 * Mostrar formulario para crear o editar sesión
 */
function mostrarFormulari(tipus, dades = null) {
    const modal = document.getElementById('modalSession');
    const modalTitle = document.getElementById('modalTitle');
    const form = document.getElementById('formSession');
    const accioForm = document.getElementById('accioForm');
    
    // Resetear formulario
    form.reset();
    
    if (tipus === 'nou') {
        modalTitle.innerHTML = '<i class="fas fa-calendar-plus"></i> Nueva Sesión';
        accioForm.value = 'crear';
        document.getElementById('idSession').value = '';
        
        // Establecer fecha de hoy por defecto
        const avui = new Date().toISOString().split('T')[0];
        document.getElementById('data_sessio').value = avui;
        
        // Estado por defecto
        document.getElementById('estat_sessio').value = 'Programada';
        
    } else if (tipus === 'editar' && dades) {
        modalTitle.innerHTML = '<i class="fas fa-edit"></i> Editar Sesión';
        accioForm.value = 'actualitzar';
        
        // Rellenar campos
        document.getElementById('idSession').value = dades.id_sessio;
        document.getElementById('id_pacient').value = dades.id_pacient;
        document.getElementById('data_sessio').value = dades.data_sessio;
        document.getElementById('hora_inici').value = dades.hora_inici;
        document.getElementById('hora_fi').value = dades.hora_fi;
        document.getElementById('tipus_sessio').value = dades.tipus_sessio;
        document.getElementById('ubicacio').value = dades.ubicacio;
        document.getElementById('estat_sessio').value = dades.estat_sessio;
        document.getElementById('preu_sessio').value = dades.preu_sessio;
        document.getElementById('notes_terapeuta').value = dades.notes_terapeuta || '';
    }
    
    modal.style.display = 'flex';
}

/**
 * Cerrar modal de formulario
 */
function tancarModal() {
    const modal = document.getElementById('modalSession');
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
 * Cerrar modal de cambiar estado
 */
function tancarModalEstat() {
    const modal = document.getElementById('modalCanviarEstat');
    modal.style.display = 'none';
}

// ============================================
// FUNCIONES DE EDICIÓN
// ============================================

/**
 * Editar sesión
 */
function editarSession(idSession) {
    // Hacer petición AJAX para obtener datos de la sesión
    fetch(`gsessions.php?accio=obtenir&id=${idSession}`)
        .then(response => response.json())
        .then(dades => {
            mostrarFormulari('editar', dades);
        })
        .catch(error => {
            // Si falla AJAX, intentar con recarga de página
            window.location.href = `gsessions.php?vista=editar&id=${idSession}`;
        });
}

/**
 * Ver detalles de la sesión
 */
function veureDetalls(idSession) {
    // Buscar datos en la tabla actual
    const fila = event.target.closest('tr');
    if (!fila) return;
    
    const celdas = fila.querySelectorAll('td');
    
    // Extraer información
    const id = celdas[0].textContent.trim();
    const fecha = celdas[1].querySelector('.session-date-cell').textContent.trim();
    const horario = celdas[2].querySelector('.session-time-cell').textContent.trim();
    const paciente = celdas[3].querySelector('strong').textContent.trim();
    const tipo = celdas[4].querySelector('.tipo-badge').textContent.trim();
    const ubicacion = celdas[5].querySelector('.ubicacio-badge').textContent.trim();
    const precio = celdas[6].textContent.trim();
    const estado = celdas[7].querySelector('.badge').textContent.trim();
    
    // Construir HTML de detalles
    const html = `
        <div class="details-grid">
            <div class="detail-item">
                <span class="detail-label"><i class="fas fa-hashtag"></i> ID:</span>
                <span class="detail-value">${id}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label"><i class="fas fa-user"></i> Paciente:</span>
                <span class="detail-value"><strong>${paciente}</strong></span>
            </div>
            <div class="detail-item">
                <span class="detail-label"><i class="fas fa-calendar"></i> Fecha:</span>
                <span class="detail-value">${fecha}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label"><i class="fas fa-clock"></i> Horario:</span>
                <span class="detail-value">${horario}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label"><i class="fas fa-list"></i> Tipo:</span>
                <span class="detail-value">${tipo}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label"><i class="fas fa-map-marker-alt"></i> Ubicación:</span>
                <span class="detail-value">${ubicacion}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label"><i class="fas fa-euro-sign"></i> Precio:</span>
                <span class="detail-value"><strong>${precio}</strong></span>
            </div>
            <div class="detail-item">
                <span class="detail-label"><i class="fas fa-info-circle"></i> Estado:</span>
                <span class="detail-value">${estado}</span>
            </div>
        </div>
    `;
    
    // Mostrar en modal
    document.getElementById('detallsContent').innerHTML = html;
    document.getElementById('modalDetalls').style.display = 'flex';
}

// ============================================
// FUNCIONES DE CAMBIO DE ESTADO
// ============================================

/**
 * Cambiar estado de sesión
 */
function canviarEstat(idSession, estatActual) {
    const modal = document.getElementById('modalCanviarEstat');
    const selectEstat = document.getElementById('nou_estat');
    
    // Establecer ID de sesión
    document.getElementById('idSessionEstat').value = idSession;
    
    // Limpiar select
    selectEstat.value = '';
    
    // Filtrar opciones (eliminar estado actual)
    const opcions = selectEstat.querySelectorAll('option');
    opcions.forEach(opcio => {
        if (opcio.value === estatActual) {
            opcio.style.display = 'none';
        } else {
            opcio.style.display = 'block';
        }
    });
    
    // Mostrar modal
    modal.style.display = 'flex';
}

// ============================================
// FUNCIONES DE ELIMINACIÓN
// ============================================

/**
 * Confirmar eliminación de sesión
 */
function confirmarEliminar(idSession) {
    if (confirm('¿Estás seguro de que deseas eliminar esta sesión? Esta acción no se puede deshacer.\n\nRecomendación: Es mejor cambiar el estado a "Cancelada".')) {
        // Crear formulario oculto y enviarlo
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'gsessions.php';
        
        const inputAccio = document.createElement('input');
        inputAccio.type = 'hidden';
        inputAccio.name = 'accio';
        inputAccio.value = 'eliminar';
        
        const inputId = document.createElement('input');
        inputId.type = 'hidden';
        inputId.name = 'id_sessio';
        inputId.value = idSession;
        
        form.appendChild(inputAccio);
        form.appendChild(inputId);
        document.body.appendChild(form);
        form.submit();
    }
}

// ============================================
// VALIDACIÓN DE FORMULARIO
// ============================================

/**
 * Validar formulario antes de enviar
 */
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formSession');
    
    if (form) {
        form.addEventListener('submit', function(e) {
            // Validar campos obligatorios
            const idPacient = document.getElementById('id_pacient').value;
            const dataSessiono = document.getElementById('data_sessio').value;
            const horaInici = document.getElementById('hora_inici').value;
            const horaFi = document.getElementById('hora_fi').value;
            const preuSessiono = document.getElementById('preu_sessio').value;
            
            if (!idPacient || !dataSessiono || !horaInici || !horaFi || !preuSessiono) {
                e.preventDefault();
                alert('Por favor, completa todos los campos obligatorios:\n- Paciente\n- Fecha\n- Hora Inicio\n- Hora Fin\n- Precio');
                return false;
            }
            
            // Validar que hora fin sea posterior a hora inicio
            if (horaInici >= horaFi) {
                e.preventDefault();
                alert('La hora de fin debe ser posterior a la hora de inicio.');
                return false;
            }
            
            // Validar precio
            const precio = parseFloat(preuSessiono);
            if (isNaN(precio) || precio < 0) {
                e.preventDefault();
                alert('El precio debe ser un número positivo.');
                return false;
            }
            
            return true;
        });
    }
});

// ============================================
// CERRAR MODALES AL HACER CLIC FUERA
// ============================================

window.onclick = function(event) {
    const modals = ['modalSession', 'modalDetalls', 'modalCanviarEstat'];
    modals.forEach(modalId => {
        const modal = document.getElementById(modalId);
        if (modal && event.target === modal) {
            modal.style.display = 'none';
        }
    });
}

// ============================================
// FUNCIONES AUXILIARES
// ============================================

/**
 * Formatear fecha
 */
function formatarData(data) {
    const date = new Date(data);
    const dia = String(date.getDate()).padStart(2, '0');
    const mes = String(date.getMonth() + 1).padStart(2, '0');
    const any = date.getFullYear();
    return `${dia}/${mes}/${any}`;
}

/**
 * Formatear hora
 */
function formatarHora(hora) {
    return hora.substring(0, 5);
}

/**
 * Calcular duración entre dos horas
 */
function calcularDuracio(horaInici, horaFi) {
    const [hInici, mInici] = horaInici.split(':').map(Number);
    const [hFi, mFi] = horaFi.split(':').map(Number);
    
    const minutsInici = hInici * 60 + mInici;
    const minutsFi = hFi * 60 + mFi;
    
    return minutsFi - minutsInici;
}

/**
 * Auto-desaparecer alertas después de 5 segundos
 */
document.addEventListener('DOMContentLoaded', function() {
    const alert = document.getElementById('alertMessage');
    if (alert) {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    }
});

// ============================================
// SUGERENCIAS DE HORARIO
// ============================================

/**
 * Sugerir hora de fin basada en tipo de sesión
 */
document.addEventListener('DOMContentLoaded', function() {
    const horaIniciInput = document.getElementById('hora_inici');
    const tipusSessionoSelect = document.getElementById('tipus_sessio');
    const horaFiInput = document.getElementById('hora_fi');
    
    if (horaIniciInput && tipusSessionoSelect && horaFiInput) {
        function suggerirHoraFi() {
            const horaInici = horaIniciInput.value;
            const tipusSessiono = tipusSessionoSelect.value;
            
            if (!horaInici) return;
            
            // Duraciones típicas por tipo de sesión (en minutos)
            const durades = {
                'Individual': 60,
                'Pareja': 90,
                'Familiar': 90,
                'Grupo': 120
            };
            
            const durada = durades[tipusSessiono] || 60;
            
            // Calcular hora de fin
            const [hores, minuts] = horaInici.split(':').map(Number);
            const totalMinuts = hores * 60 + minuts + durada;
            const noveHores = Math.floor(totalMinuts / 60);
            const nousMinuts = totalMinuts % 60;
            
            const horaFi = `${String(noveHores).padStart(2, '0')}:${String(nousMinuts).padStart(2, '0')}`;
            
            // Establecer hora de fin solo si está vacía
            if (!horaFiInput.value) {
                horaFiInput.value = horaFi;
            }
        }
        
        horaIniciInput.addEventListener('change', suggerirHoraFi);
        tipusSessionoSelect.addEventListener('change', suggerirHoraFi);
    }
});

console.log('✅ gsessions.js cargado correctamente');
