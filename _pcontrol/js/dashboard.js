// Dashboard JavaScript

// Toggle Sidebar (Mobile)
const menuToggle = document.getElementById('menuToggle');
const sidebar = document.getElementById('sidebar');
const sidebarToggle = document.getElementById('sidebarToggle');

if (menuToggle) {
    menuToggle.addEventListener('click', function() {
        sidebar.classList.toggle('active');
    });
}

if (sidebarToggle) {
    sidebarToggle.addEventListener('click', function() {
        sidebar.classList.remove('active');
    });
}

// Close sidebar when clicking outside (mobile)
document.addEventListener('click', function(event) {
    if (window.innerWidth < 1024) {
        const isClickInsideSidebar = sidebar.contains(event.target);
        const isClickOnToggle = menuToggle && menuToggle.contains(event.target);
        
        if (!isClickInsideSidebar && !isClickOnToggle && sidebar.classList.contains('active')) {
            sidebar.classList.remove('active');
        }
    }
});

// Active navigation item
const navItems = document.querySelectorAll('.nav-item');
navItems.forEach(item => {
    item.addEventListener('click', function(e) {
        // Remove active class from all items
        navItems.forEach(nav => nav.classList.remove('active'));
        // Add active class to clicked item
        this.classList.add('active');
    });
});

// Format date
function formatDate() {
    const options = { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    };
    const today = new Date();
    const dateElement = document.querySelector('.date-today');
    if (dateElement) {
        dateElement.textContent = today.toLocaleDateString('es-ES', options);
    }
}

// Call on load
formatDate();

// Notification button removed — no-op

// Quick actions buttons
const actionButtons = document.querySelectorAll('.action-btn');
actionButtons.forEach(btn => {
    btn.addEventListener('click', function() {
        const action = this.querySelector('span').textContent;
        console.log('Acción seleccionada:', action);
        // Aquí se implementaría la lógica para cada acción
    });
});

// Calendar navigation
const calendarNavButtons = document.querySelectorAll('.calendar-nav');
calendarNavButtons.forEach(btn => {
    btn.addEventListener('click', function() {
        console.log('Navegación de calendario');
        // Aquí se implementaría la lógica para cambiar de mes
    });
});

// Animate cards on scroll (optional)
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver(function(entries) {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
        }
    });
}, observerOptions);

// Observe all cards
const cards = document.querySelectorAll('.stat-card, .card');
cards.forEach(card => {
    card.style.opacity = '0';
    card.style.transform = 'translateY(20px)';
    card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
    observer.observe(card);
});

// Responsive sidebar handling
function handleResize() {
    if (window.innerWidth >= 1024) {
        sidebar.classList.remove('active');
    }
}

window.addEventListener('resize', handleResize);
