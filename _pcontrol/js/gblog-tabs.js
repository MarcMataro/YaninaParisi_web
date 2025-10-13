/**
 * Sistema de Tabs per a la Gestió del Blog
 * Gestiona la navegació entre Entrades, Categories i Etiquetes
 */

// Variable global per saber quin tab està actiu
let currentTab = 'entradas';

// Inicialització
document.addEventListener('DOMContentLoaded', function() {
    console.log('=== GBLOG TABS INICIALITZAT ===');
    
    // Event listeners per als botons de tab
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const tabName = this.getAttribute('data-tab');
            switchTab(tabName);
        });
    });
    
    // Esperar que tots els scripts estiguin carregats
    setTimeout(() => {
        console.log('Activant tab per defecte: entradas');
        console.log('carregarEntrades disponible?', typeof carregarEntrades);
        console.log('carregarCategories disponible?', typeof carregarCategories);
        console.log('carregarEtiquetes disponible?', typeof carregarEtiquetes);
        switchTab('entradas');
    }, 300);
});

/**
 * Canvia entre els diferents tabs
 * @param {string} tabName - Nom del tab: 'entradas', 'categories' o 'etiquetes'
 */
function switchTab(tabName) {
    console.log('Canviant a tab:', tabName);
    
    // Actualitzar variable global
    currentTab = tabName;
    
    // Desactivar tots els botons de tab
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Ocultar tots els panells
    document.querySelectorAll('.tab-panel').forEach(panel => {
        panel.classList.remove('active');
    });
    
    // Activar el botó seleccionat
    const activeBtn = document.querySelector(`.tab-btn[data-tab="${tabName}"]`);
    if (activeBtn) {
        activeBtn.classList.add('active');
    }
    
    // Mostrar el panell seleccionat
    const activePanel = document.getElementById(`tab-${tabName}`);
    if (activePanel) {
        activePanel.classList.add('active');
    }
    
    // Carregar contingut específic segons el tab
    loadTabContent(tabName);
}

/**
 * Carrega el contingut específic de cada tab
 * @param {string} tabName - Nom del tab
 */
function loadTabContent(tabName) {
    console.log('Carregant contingut per:', tabName);
    
    switch(tabName) {
        case 'entradas':
            // Carregar entrades
            console.log('Tab Entradas activat');
            if (typeof carregarEntrades === 'function') {
                console.log('Cridant carregarEntrades()...');
                carregarEntrades();
            } else {
                console.error('carregarEntrades() no està definida!');
                console.log('Tipus de carregarEntrades:', typeof carregarEntrades);
                console.log('window.carregarEntrades:', typeof window.carregarEntrades);
                
                // Intentar amb més retards
                let attempts = 0;
                const maxAttempts = 5;
                const tryLoad = () => {
                    attempts++;
                    console.log(`Intent ${attempts}/${maxAttempts} de carregar entrades...`);
                    
                    if (typeof carregarEntrades === 'function') {
                        console.log('✓ carregarEntrades() ara disponible!');
                        carregarEntrades();
                    } else if (attempts < maxAttempts) {
                        setTimeout(tryLoad, 200);
                    } else {
                        console.error('❌ carregarEntrades() no disponible després de ' + maxAttempts + ' intents');
                        const container = document.getElementById('entrades-list');
                        if (container) {
                            container.innerHTML = `
                                <div class="empty-state" style="color: red;">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <p>Error: El script de entradas no se ha cargado correctamente</p>
                                    <button class="btn btn-secondary" onclick="location.reload()">
                                        <i class="fas fa-redo"></i> Recargar página
                                    </button>
                                </div>
                            `;
                        }
                    }
                };
                setTimeout(tryLoad, 200);
            }
            break;
            
        case 'categories':
            // Carregar categories
            console.log('Tab Categories activat');
            if (typeof carregarCategories === 'function') {
                console.log('Cridant carregarCategories()...');
                carregarCategories();
            } else {
                console.error('carregarCategories() no està definida!');
                // Intentar de nou després d'un moment
                setTimeout(() => {
                    if (typeof carregarCategories === 'function') {
                        console.log('Reintentant carregarCategories()...');
                        carregarCategories();
                    } else {
                        console.error('carregarCategories() encara no disponible');
                    }
                }, 500);
            }
            break;
            
        case 'etiquetes':
            // Carregar etiquetes
            console.log('Tab Etiquetes activat');
            if (typeof carregarEtiquetes === 'function') {
                console.log('Cridant carregarEtiquetes()...');
                carregarEtiquetes();
            } else {
                console.error('carregarEtiquetes() no està definida!');
                // Intentar de nou després d'un moment
                setTimeout(() => {
                    if (typeof carregarEtiquetes === 'function') {
                        console.log('Reintentant carregarEtiquetes()...');
                        carregarEtiquetes();
                    } else {
                        console.error('carregarEtiquetes() encara no disponible');
                    }
                }, 500);
            }
            break;
            
        default:
            console.warn('Tab desconegut:', tabName);
    }
}

/**
 * Retorna el tab actual
 * @returns {string} Nom del tab actual
 */
function getCurrentTab() {
    return currentTab;
}

// Fer funcions accessibles globalment
window.switchTab = switchTab;
window.getCurrentTab = getCurrentTab;

console.log('=== GBLOG TABS JS CARREGAT ===');
