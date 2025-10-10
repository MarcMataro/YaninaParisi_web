/**
 * Blog Management JavaScript
 * 
 * Funcionalitat per gestionar articles del blog
 */

// Variables globals
let articles = [];
let articleActual = null;
let categoriesData = [
    { nom: 'Salud Mental', articles: 5 },
    { nom: 'Ansiedad', articles: 3 },
    { nom: 'Relaciones', articles: 2 },
    { nom: 'Terapia', articles: 4 },
    { nom: 'Estrés', articles: 3 }
];
let etiquetesData = [
    'salud mental', 'bienestar', 'consejos', 'ansiedad', 'respiración', 'técnicas',
    'pareja', 'comunicación', 'relaciones', 'terapia', 'psicología', 'beneficios',
    'estrés', 'trabajo', 'equilibrio', 'mindfulness', 'meditación', 'autoestima'
];
let selectedTags = [];

// Inicialització
document.addEventListener('DOMContentLoaded', function() {
    initializeEventListeners();
    setupTagsInput();
    setupContentCounter();
});

function initializeEventListeners() {
    // Search functionality
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', filtrarArticles);
    }

    // Filter functionality
    const categoryFilter = document.getElementById('categoryFilter');
    const statusFilter = document.getElementById('statusFilter');
    
    if (categoryFilter) {
        categoryFilter.addEventListener('change', filtrarArticles);
    }
    
    if (statusFilter) {
        statusFilter.addEventListener('change', filtrarArticles);
    }

    // Modal events
    window.addEventListener('click', function(event) {
        const articleModal = document.getElementById('articleModal');
        const categoriesModal = document.getElementById('categoriesModal');
        const etiquetesModal = document.getElementById('etiquetesModal');
        
        if (event.target === articleModal) {
            tancarModal();
        } else if (event.target === categoriesModal) {
            tancarModalCategories();
        } else if (event.target === etiquetesModal) {
            tancarModalEtiquetes();
        }
    });

    // Form submit prevention
    const articleForm = document.getElementById('articleForm');
    if (articleForm) {
        articleForm.addEventListener('submit', function(e) {
            e.preventDefault();
            desarArticle();
        });
    }
}

// Funcions de cerca i filtres
function filtrarArticles() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const categoryFilter = document.getElementById('categoryFilter').value;
    const statusFilter = document.getElementById('statusFilter').value;
    
    const rows = document.querySelectorAll('.articles-table tbody tr');
    
    rows.forEach(row => {
        const title = row.querySelector('.title-content h4').textContent.toLowerCase();
        const summary = row.querySelector('.article-summary').textContent.toLowerCase();
        const category = row.querySelector('.category-badge').textContent;
        const status = row.querySelector('.status-badge').textContent;
        
        const matchesSearch = title.includes(searchTerm) || summary.includes(searchTerm);
        const matchesCategory = categoryFilter === 'Totes' || category === categoryFilter;
        const matchesStatus = statusFilter === 'Tots' || status === statusFilter;
        
        if (matchesSearch && matchesCategory && matchesStatus) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

// Funcions del modal
function afegirArticle() {
    articleActual = null;
    document.getElementById('modalTitle').textContent = 'Nuevo Artículo';
    document.getElementById('articleForm').reset();
    document.getElementById('articleModal').style.display = 'block';
}

function editarArticle(id) {
    articleActual = id;
    document.getElementById('modalTitle').textContent = 'Editar Artículo';
    
    // Aquí carregaríem les dades de l'article des de la base de dades
    // Per ara, simulem amb dades de prova
    const dadesProva = {
        titol: 'Artículo de ejemplo',
        resum: 'Este es un resumen de ejemplo',
        categoria: 'Salud Mental',
        estat: 'Borrador',
        etiquetes: 'ejemplo, prueba, test',
        contingut: 'Este es el contenido de ejemplo del artículo...'
    };
    
    // Omplir el formulari
    document.getElementById('articleTitle').value = dadesProva.titol;
    document.getElementById('articleSummary').value = dadesProva.resum;
    document.getElementById('articleCategory').value = dadesProva.categoria;
    document.getElementById('articleStatus').value = dadesProva.estat;
    document.getElementById('articleTags').value = dadesProva.etiquetes;
    document.getElementById('articleContent').value = dadesProva.contingut;
    
    document.getElementById('articleModal').style.display = 'block';
}

function tancarModal() {
    document.getElementById('articleModal').style.display = 'none';
    articleActual = null;
}

function desarArticle() {
    const formData = {
        titol: document.getElementById('articleTitle').value.trim(),
        resum: document.getElementById('articleSummary').value.trim(),
        categoria: document.getElementById('articleCategory').value,
        estat: document.getElementById('articleStatus').value,
        etiquetes: document.getElementById('articleTags').value.trim(),
        contingut: document.getElementById('articleContent').value.trim()
    };
    
    // Validació bàsica
    if (!formData.titol || !formData.resum || !formData.contingut) {
        mostrarNotificacio('Por favor, completa todos los campos obligatorios.', 'error');
        return;
    }
    
    if (articleActual) {
        // Actualitzar article existent
        console.log('Actualitzant article:', articleActual, formData);
        mostrarNotificacio('¡Artículo actualizado correctamente!', 'success');
    } else {
        // Crear nou article
        console.log('Creant nou article:', formData);
        mostrarNotificacio('¡Artículo creado correctamente!', 'success');
    }
    
    tancarModal();
    
    // Aquí es faria la crida AJAX a la base de dades
    // i es recarregaria la taula
}

// Funcions d'accions
function visualitzarArticle(id) {
    console.log('Visualitzant article:', id);
    // Aquí obriríem l'article en una nova finestra o modal de previsualització
    mostrarNotificacio('Funcionalidad de previsualización (en desarrollo)', 'info');
}

function publicarArticle(id) {
    if (confirm('¿Estás seguro de que quieres publicar este artículo?')) {
        console.log('Publicant article:', id);
        mostrarNotificacio('¡Artículo publicado correctamente!', 'success');
        // Aquí es faria la crida AJAX per canviar l'estat
    }
}

function despublicarArticle(id) {
    if (confirm('¿Estás seguro de que quieres despublicar este artículo?')) {
        console.log('Despublicant article:', id);
        mostrarNotificacio('¡Artículo despublicado correctamente!', 'info');
        // Aquí es faria la crida AJAX per canviar l'estat
    }
}

function eliminarArticle(id) {
    if (confirm('¿Estás seguro de que quieres eliminar este artículo? Esta acción no se puede deshacer.')) {
        console.log('Eliminant article:', id);
        mostrarNotificacio('¡Artículo eliminado correctamente!', 'success');
        // Aquí es faria la crida AJAX per eliminar i es recarregaria la taula
    }
}

function importarArticles() {
    console.log('Importar articles');
    mostrarNotificacio('Funcionalidad de importación (en desarrollo)', 'info');
}

// Sistema de notificacions
function mostrarNotificacio(missatge, tipus = 'info') {
    // Crear notificació
    const notificacio = document.createElement('div');
    notificacio.className = `notificacio notificacio-${tipus}`;
    notificacio.innerHTML = `
        <div class="notificacio-content">
            <i class="fas ${getIconPerTipus(tipus)}"></i>
            <span>${missatge}</span>
        </div>
        <button class="notificacio-close" onclick="tancarNotificacio(this)">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    // Afegir a la pàgina
    let container = document.querySelector('.notificacions-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'notificacions-container';
        document.body.appendChild(container);
    }
    
    container.appendChild(notificacio);
    
    // Auto-eliminar després de 5 segons
    setTimeout(() => {
        if (notificacio.parentNode) {
            notificacio.remove();
        }
    }, 5000);
    
    // Animació d'entrada
    setTimeout(() => {
        notificacio.classList.add('show');
    }, 10);
}

function getIconPerTipus(tipus) {
    switch (tipus) {
        case 'success': return 'fa-check-circle';
        case 'error': return 'fa-exclamation-circle';
        case 'warning': return 'fa-exclamation-triangle';
        default: return 'fa-info-circle';
    }
}

function tancarNotificacio(button) {
    const notificacio = button.closest('.notificacio');
    notificacio.remove();
}

// Funcions de teclat
document.addEventListener('keydown', function(e) {
    // Tancar modal amb Escape
    if (e.key === 'Escape') {
        const articleModal = document.getElementById('articleModal');
        const categoriesModal = document.getElementById('categoriesModal');
        const etiquetesModal = document.getElementById('etiquetesModal');
        
        if (articleModal && articleModal.style.display === 'block') {
            tancarModal();
        } else if (categoriesModal && categoriesModal.style.display === 'block') {
            tancarModalCategories();
        } else if (etiquetesModal && etiquetesModal.style.display === 'block') {
            tancarModalEtiquetes();
        }
    }
    
    // Cercar amb Ctrl+F
    if (e.ctrlKey && e.key === 'f') {
        e.preventDefault();
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.focus();
        }
    }
});

// Export per a ús extern
window.gblogFunctions = {
    afegirArticle,
    editarArticle,
    visualitzarArticle,
    publicarArticle,
    despublicarArticle,
    eliminarArticle,
    importarArticles,
    tancarModal,
    desarArticle,
    gestionarCategories,
    gestionarEtiquetes
};

// === GESTIÓ DE CATEGORIES ===
function gestionarCategories() {
    document.getElementById('categoriesModal').style.display = 'block';
    carregarCategories();
}

function tancarModalCategories() {
    document.getElementById('categoriesModal').style.display = 'none';
}

function carregarCategories() {
    const grid = document.getElementById('categoriesGrid');
    grid.innerHTML = '';
    
    categoriesData.forEach((categoria, index) => {
        const item = document.createElement('div');
        item.className = 'category-item';
        item.innerHTML = `
            <div>
                <span class="category-name">${categoria.nom}</span>
                <span class="category-count">${categoria.articles}</span>
            </div>
            <div class="category-actions">
                <button class="btn-mini btn-edit-mini" onclick="editarCategoria(${index})" title="Editar">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn-mini btn-delete-mini" onclick="eliminarCategoria(${index})" title="Eliminar">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;
        grid.appendChild(item);
    });
}

function afegirCategoria() {
    const input = document.getElementById('newCategory');
    const nom = input.value.trim();
    
    if (!nom) {
        mostrarNotificacio('Por favor, introduce un nombre para la categoría.', 'error');
        return;
    }
    
    if (categoriesData.some(cat => cat.nom.toLowerCase() === nom.toLowerCase())) {
        mostrarNotificacio('Esta categoría ya existe.', 'warning');
        return;
    }
    
    categoriesData.push({ nom: nom, articles: 0 });
    input.value = '';
    carregarCategories();
    actualitzarSelectCategories();
    mostrarNotificacio('Categoría añadida correctamente.', 'success');
}

function editarCategoria(index) {
    const categoria = categoriesData[index];
    const nouNom = prompt('Nuevo nombre para la categoría:', categoria.nom);
    
    if (nouNom && nouNom.trim() !== categoria.nom) {
        categoriesData[index].nom = nouNom.trim();
        carregarCategories();
        actualitzarSelectCategories();
        mostrarNotificacio('Categoría actualizada correctamente.', 'success');
    }
}

function eliminarCategoria(index) {
    const categoria = categoriesData[index];
    if (categoria.articles > 0) {
        if (!confirm(`La categoría "${categoria.nom}" tiene ${categoria.articles} artículos. ¿Estás seguro de eliminarla?`)) {
            return;
        }
    }
    
    categoriesData.splice(index, 1);
    carregarCategories();
    actualitzarSelectCategories();
    mostrarNotificacio('Categoría eliminada correctamente.', 'success');
}

function actualitzarSelectCategories() {
    const select = document.getElementById('articleCategory');
    if (select) {
        const valorActual = select.value;
        select.innerHTML = '';
        categoriesData.forEach(categoria => {
            const option = document.createElement('option');
            option.value = categoria.nom;
            option.textContent = categoria.nom;
            select.appendChild(option);
        });
        select.value = valorActual;
    }
}

// === GESTIÓ D'ETIQUETES ===
function gestionarEtiquetes() {
    document.getElementById('etiquetesModal').style.display = 'block';
    carregarEtiquetes();
}

function tancarModalEtiquetes() {
    document.getElementById('etiquetesModal').style.display = 'none';
}

function carregarEtiquetes() {
    const cloud = document.getElementById('tagsCloud');
    cloud.innerHTML = '';
    
    if (etiquetesData.length === 0) {
        cloud.innerHTML = '<p style="color: #666; text-align: center; margin: 20px;">No hay etiquetas creadas</p>';
        return;
    }
    
    etiquetesData.forEach((etiqueta, index) => {
        const item = document.createElement('div');
        item.className = 'tag-item';
        item.innerHTML = `
            <span>${etiqueta}</span>
            <button class="tag-remove" onclick="eliminarEtiqueta(${index})" title="Eliminar">
                <i class="fas fa-times"></i>
            </button>
        `;
        cloud.appendChild(item);
    });
}

function afegirEtiqueta() {
    const input = document.getElementById('newTag');
    const nom = input.value.trim().toLowerCase();
    
    if (!nom) {
        mostrarNotificacio('Por favor, introduce una etiqueta.', 'error');
        return;
    }
    
    if (etiquetesData.includes(nom)) {
        mostrarNotificacio('Esta etiqueta ya existe.', 'warning');
        return;
    }
    
    etiquetesData.push(nom);
    input.value = '';
    carregarEtiquetes();
    mostrarNotificacio('Etiqueta añadida correctamente.', 'success');
}

function eliminarEtiqueta(index) {
    const etiqueta = etiquetesData[index];
    if (confirm(`¿Estás seguro de eliminar la etiqueta "${etiqueta}"?`)) {
        etiquetesData.splice(index, 1);
        carregarEtiquetes();
        mostrarNotificacio('Etiqueta eliminada correctamente.', 'success');
    }
}

// === GESTIÓ D'ETIQUETES EN ARTICLES ===
function setupTagsInput() {
    const tagsInput = document.getElementById('articleTags');
    if (!tagsInput) return;
    
    tagsInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            afegirEtiquetaArticle();
        }
    });
    
    tagsInput.addEventListener('input', function() {
        mostrarSuggestions(this.value);
    });
}

function afegirEtiquetaArticle() {
    const input = document.getElementById('articleTags');
    const tag = input.value.trim().toLowerCase();
    
    if (!tag) return;
    
    if (selectedTags.length >= 8) {
        mostrarNotificacio('Máximo 8 etiquetas por artículo.', 'warning');
        return;
    }
    
    if (selectedTags.includes(tag)) {
        mostrarNotificacio('Esta etiqueta ya está añadida.', 'warning');
        return;
    }
    
    selectedTags.push(tag);
    input.value = '';
    actualitzarEtiquetesSeleccionades();
    amagarSuggestions();
}

function actualitzarEtiquetesSeleccionades() {
    const container = document.getElementById('selectedTags');
    container.innerHTML = '';
    
    selectedTags.forEach((tag, index) => {
        const tagElement = document.createElement('div');
        tagElement.className = 'selected-tag';
        tagElement.innerHTML = `
            <span>${tag}</span>
            <button class="remove-tag" onclick="eliminarEtiquetaSeleccionada(${index})">
                <i class="fas fa-times"></i>
            </button>
        `;
        container.appendChild(tagElement);
    });
}

function eliminarEtiquetaSeleccionada(index) {
    selectedTags.splice(index, 1);
    actualitzarEtiquetesSeleccionades();
}

function mostrarSuggestions(text) {
    const suggestionsContainer = document.getElementById('tagsSuggestions');
    
    if (!text.trim()) {
        amagarSuggestions();
        return;
    }
    
    const filtered = etiquetesData.filter(tag => 
        tag.toLowerCase().includes(text.toLowerCase()) && 
        !selectedTags.includes(tag)
    );
    
    if (filtered.length === 0) {
        amagarSuggestions();
        return;
    }
    
    suggestionsContainer.innerHTML = '';
    filtered.slice(0, 5).forEach(tag => {
        const suggestion = document.createElement('div');
        suggestion.className = 'tag-suggestion';
        suggestion.textContent = tag;
        suggestion.onclick = () => seleccionarSuggestion(tag);
        suggestionsContainer.appendChild(suggestion);
    });
    
    suggestionsContainer.style.display = 'block';
}

function seleccionarSuggestion(tag) {
    document.getElementById('articleTags').value = tag;
    afegirEtiquetaArticle();
}

function amagarSuggestions() {
    document.getElementById('tagsSuggestions').style.display = 'none';
}

// === EDITOR DE CONTINGUT ===
function setupContentCounter() {
    const contentTextarea = document.getElementById('articleContent');
    if (!contentTextarea) return;
    
    contentTextarea.addEventListener('input', function() {
        const text = this.value;
        const words = text.trim().split(/\s+/).filter(word => word.length > 0).length;
        const readTime = Math.ceil(words / 200); // 200 paraules per minut
        
        document.getElementById('wordCount').textContent = `${words} palabras`;
        document.getElementById('readTime').textContent = `${readTime} min lectura`;
    });
}

function insertarTexto(abans, despres) {
    const textarea = document.getElementById('articleContent');
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const selectedText = textarea.value.substring(start, end);
    const newText = abans + selectedText + despres;
    
    textarea.value = textarea.value.substring(0, start) + newText + textarea.value.substring(end);
    textarea.focus();
    textarea.setSelectionRange(start + abans.length, start + abans.length + selectedText.length);
}