// Configuración Page JavaScript

document.addEventListener('DOMContentLoaded', function() {
    
    // Form validation
    const form = document.querySelector('.config-form');
    const currentPassword = document.getElementById('current_password');
    const newPassword = document.getElementById('new_password');
    const confirmPassword = document.getElementById('confirm_password');
    
    if (form) {
        form.addEventListener('submit', function(e) {
            // Validate password change if fields are filled
            if (currentPassword.value || newPassword.value || confirmPassword.value) {
                
                // Check all password fields are filled
                if (!currentPassword.value || !newPassword.value || !confirmPassword.value) {
                    e.preventDefault();
                    showAlert('Por favor, completa todos los campos de contraseña', 'error');
                    return;
                }
                
                // Check password length
                if (newPassword.value.length < 8) {
                    e.preventDefault();
                    showAlert('La nueva contraseña debe tener al menos 8 caracteres', 'error');
                    return;
                }
                
                // Check passwords match
                if (newPassword.value !== confirmPassword.value) {
                    e.preventDefault();
                    showAlert('Las contraseñas no coinciden', 'error');
                    return;
                }
            }
            
            // Validate email format
            const email = document.getElementById('email');
            if (email && !isValidEmail(email.value)) {
                e.preventDefault();
                showAlert('Por favor, introduce un email válido', 'error');
                return;
            }
            
            // Validate phone format
            const telefono = document.getElementById('telefono');
            if (telefono && !isValidPhone(telefono.value)) {
                e.preventDefault();
                showAlert('Por favor, introduce un teléfono válido', 'error');
                return;
            }
            
            // Validate time range
            const horarioInicio = document.getElementById('horario_inicio');
            const horarioFin = document.getElementById('horario_fin');
            if (horarioInicio.value && horarioFin.value) {
                if (horarioInicio.value >= horarioFin.value) {
                    e.preventDefault();
                    showAlert('La hora de inicio debe ser anterior a la hora de fin', 'error');
                    return;
                }
            }
        });
    }
    
    // Input formatting
    const phoneInput = document.getElementById('telefono');
    if (phoneInput) {
        phoneInput.addEventListener('blur', function() {
            formatPhoneNumber(this);
        });
    }
    
    // Auto-dismiss success message after 5 seconds
    const successAlert = document.querySelector('.alert-success');
    if (successAlert) {
        setTimeout(() => {
            successAlert.style.opacity = '0';
            successAlert.style.transform = 'translateY(-20px)';
            setTimeout(() => {
                successAlert.remove();
            }, 300);
        }, 5000);
    }
    
    // Add animation to sections on scroll
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
    
    document.querySelectorAll('.config-section').forEach(section => {
        section.style.opacity = '0';
        section.style.transform = 'translateY(20px)';
        section.style.transition = 'all 0.4s ease';
        observer.observe(section);
    });
    
    // Toggle password visibility
    addPasswordToggle();
    
    // Unsaved changes warning
    let formChanged = false;
    const formInputs = form.querySelectorAll('input, select');
    
    formInputs.forEach(input => {
        input.addEventListener('change', function() {
            formChanged = true;
        });
    });
    
    window.addEventListener('beforeunload', function(e) {
        if (formChanged) {
            e.preventDefault();
            e.returnValue = '';
        }
    });
    
    form.addEventListener('submit', function() {
        formChanged = false;
    });
});

// Validation functions
function isValidEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

function isValidPhone(phone) {
    // Allow various phone formats
    const re = /^[\+]?[(]?[0-9]{1,4}[)]?[-\s\.]?[(]?[0-9]{1,4}[)]?[-\s\.]?[0-9]{1,9}$/;
    return re.test(phone.replace(/\s/g, ''));
}

function formatPhoneNumber(input) {
    let value = input.value.replace(/\s/g, '');
    // Format Spanish phone numbers: +34 972 123 45 67
    if (value.startsWith('+34') && value.length === 12) {
        input.value = value.substring(0, 3) + ' ' + 
                      value.substring(3, 6) + ' ' + 
                      value.substring(6, 9) + ' ' + 
                      value.substring(9, 11) + ' ' + 
                      value.substring(11);
    }
}

// Show alert message
function showAlert(message, type = 'error') {
    const existingAlert = document.querySelector('.alert');
    if (existingAlert) {
        existingAlert.remove();
    }
    
    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`;
    alert.innerHTML = `
        <i class="fas fa-${type === 'error' ? 'exclamation-circle' : 'check-circle'}"></i>
        <span>${message}</span>
        <button class="alert-close" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    const container = document.querySelector('.config-container');
    container.insertBefore(alert, container.firstChild);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        if (alert.parentElement) {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-20px)';
            setTimeout(() => alert.remove(), 300);
        }
    }, 5000);
}

// Add password visibility toggle
function addPasswordToggle() {
    const passwordFields = document.querySelectorAll('input[type="password"]');
    
    passwordFields.forEach(field => {
        const wrapper = field.closest('.input-icon');
        if (wrapper) {
            const toggleBtn = document.createElement('button');
            toggleBtn.type = 'button';
            toggleBtn.className = 'password-toggle';
            toggleBtn.innerHTML = '<i class="fas fa-eye"></i>';
            toggleBtn.style.cssText = `
                position: absolute;
                right: 15px;
                background: none;
                border: none;
                cursor: pointer;
                color: #999;
                padding: 5px;
                display: flex;
                align-items: center;
            `;
            
            toggleBtn.addEventListener('click', function() {
                const type = field.type === 'password' ? 'text' : 'password';
                field.type = type;
                this.querySelector('i').className = type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
            });
            
            wrapper.appendChild(toggleBtn);
        }
    });
}
