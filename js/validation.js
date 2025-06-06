// Theme Management
function initTheme() {
    const theme = getCookie('theme') || 'light';
    document.documentElement.setAttribute('data-theme', theme);
    
    const toggleBtn = document.getElementById('theme-toggle');
    if (toggleBtn) {
        toggleBtn.innerHTML = theme === 'dark' ? '☀️' : '🌙';
        toggleBtn.addEventListener('click', toggleTheme);
    }
}

function toggleTheme() {
    const currentTheme = document.documentElement.getAttribute('data-theme');
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    
    document.documentElement.setAttribute('data-theme', newTheme);
    setCookie('theme', newTheme, 365);
    
    const toggleBtn = document.getElementById('theme-toggle');
    toggleBtn.innerHTML = newTheme === 'dark' ? '☀️' : '🌙';
}

// Cookie Helper Functions
function setCookie(name, value, days) {
    const expires = new Date();
    expires.setTime(expires.getTime() + (days * 24 * 60 * 60 * 1000));
    document.cookie = `${name}=${value};expires=${expires.toUTCString()};path=/`;
}

function getCookie(name) {
    const nameEQ = name + "=";
    const ca = document.cookie.split(';');
    for(let i = 0; i < ca.length; i++) {
        let c = ca[i];
        while (c.charAt(0) === ' ') c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
    }
    return null;
}

// Form Validation Functions
function validateLoginForm() {
    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value;
    
    clearErrors();
    
    let isValid = true;
    
    if (!username) {
        showError('username', 'Username is required');
        isValid = false;
    }
    
    if (!password) {
        showError('password', 'Password is required');
        isValid = false;
    }
    
    return isValid;
}

function validateRegisterForm() {
    const username = document.getElementById('username').value.trim();
    const email = document.getElementById('email').value.trim();
    const fullName = document.getElementById('full_name').value.trim();
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    clearErrors();
    
    let isValid = true;
    
    // Username validation
    if (!username) {
        showError('username', 'Username is required');
        isValid = false;
    } else if (username.length < 3) {
        showError('username', 'Username must be at least 3 characters');
        isValid = false;
    } else if (!/^[a-zA-Z0-9_]+$/.test(username)) {
        showError('username', 'Username can only contain letters, numbers, and underscores');
        isValid = false;
    }
    
    // Email validation
    if (!email) {
        showError('email', 'Email is required');
        isValid = false;
    } else if (!isValidEmail(email)) {
        showError('email', 'Please enter a valid email address');
        isValid = false;
    }
    
    // Full name validation
    if (!fullName) {
        showError('full_name', 'Full name is required');
        isValid = false;
    } else if (fullName.length < 2) {
        showError('full_name', 'Full name must be at least 2 characters');
        isValid = false;
    }
    
    // Password validation
    if (!password) {
        showError('password', 'Password is required');
        isValid = false;
    } else if (password.length < 6) {
        showError('password', 'Password must be at least 6 characters');
        isValid = false;
    }
    
    // Confirm password validation
    if (!confirmPassword) {
        showError('confirm_password', 'Please confirm your password');
        isValid = false;
    } else if (password !== confirmPassword) {
        showError('confirm_password', 'Passwords do not match');
        isValid = false;
    }
    
    return isValid;
}

function validateTaskForm() {
    const title = document.getElementById('title').value.trim();
    const dueDate = document.getElementById('due_date').value;
    
    clearErrors();
    
    let isValid = true;
    
    // Title validation
    if (!title) {
        showError('title', 'Task title is required');
        isValid = false;
    } else if (title.length < 3) {
        showError('title', 'Task title must be at least 3 characters');
        isValid = false;
    }
    
    // Due date validation
    if (dueDate) {
        const today = new Date();
        const selectedDate = new Date(dueDate);
        
        if (selectedDate < today.setHours(0, 0, 0, 0)) {
            showError('due_date', 'Due date cannot be in the past');
            isValid = false;
        }
    }
    
    return isValid;
}

function validateProfileForm() {
    const email = document.getElementById('email').value.trim();
    const fullName = document.getElementById('full_name').value.trim();
    const currentPassword = document.getElementById('current_password').value;
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    clearErrors();
    
    let isValid = true;
    
    // Email validation
    if (!email) {
        showError('email', 'Email is required');
        isValid = false;
    } else if (!isValidEmail(email)) {
        showError('email', 'Please enter a valid email address');
        isValid = false;
    }
    
    // Full name validation
    if (!fullName) {
        showError('full_name', 'Full name is required');
        isValid = false;
    }
    
    // Password change validation
    if (newPassword || confirmPassword) {
        if (!currentPassword) {
            showError('current_password', 'Current password is required to change password');
            isValid = false;
        }
        
        if (newPassword.length < 6) {
            showError('new_password', 'New password must be at least 6 characters');
            isValid = false;
        }
        
        if (newPassword !== confirmPassword) {
            showError('confirm_password', 'New passwords do not match');
            isValid = false;
        }
    }
    
    return isValid;
}

// Helper Functions
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

function showError(fieldId, message) {
    const field = document.getElementById(fieldId);
    if (field) {
        field.classList.add('error');
        
        // Remove existing error message
        const existingError = field.parentNode.querySelector('.error-message');
        if (existingError) {
            existingError.remove();
        }
        
        // Add new error message
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.style.color = 'var(--danger-color)';
        errorDiv.style.fontSize = '0.875rem';
        errorDiv.style.marginTop = '0.25rem';
        errorDiv.textContent = message;
        
        field.parentNode.appendChild(errorDiv);
    }
}

function clearErrors() {
    // Remove error classes
    const errorFields = document.querySelectorAll('.error');
    errorFields.forEach(field => field.classList.remove('error'));
    
    // Remove error messages
    const errorMessages = document.querySelectorAll('.error-message');
    errorMessages.forEach(msg => msg.remove());
}

// Task Management Functions
function filterTasks(status) {
    const tasks = document.querySelectorAll('.task-card');
    const filterBtns = document.querySelectorAll('.filter-btn');
    
    // Update active filter button
    filterBtns.forEach(btn => btn.classList.remove('active'));
    event.target.classList.add('active');
    
    tasks.forEach(task => {
        if (status === 'all') {
            task.style.display = 'block';
        } else {
            const taskStatus = task.dataset.status;
            task.style.display = taskStatus === status ? 'block' : 'none';
        }
    });
}

function searchTasks() {
    const searchTerm = document.getElementById('search').value.toLowerCase();
    const tasks = document.querySelectorAll('.task-card');
    
    tasks.forEach(task => {
        const title = task.querySelector('.task-title').textContent.toLowerCase();
        const description = task.querySelector('.task-description')?.textContent.toLowerCase() || '';
        
        if (title.includes(searchTerm) || description.includes(searchTerm)) {
            task.style.display = 'block';
        } else {
            task.style.display = 'none';
        }
    });
}

function confirmDelete(taskId, taskTitle) {
    return confirm(`Are you sure you want to delete the task "${taskTitle}"?`);
}

function toggleTaskStatus(taskId, currentStatus) {
    const newStatus = currentStatus === 'completed' ? 'pending' : 'completed';
    
    // Create form and submit
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'actions/task_actions.php';
    
    const actionInput = document.createElement('input');
    actionInput.type = 'hidden';
    actionInput.name = 'action';
    actionInput.value = 'toggle_status';
    
    const idInput = document.createElement('input');
    idInput.type = 'hidden';
    idInput.name = 'task_id';
    idInput.value = taskId;
    
    const statusInput = document.createElement('input');
    statusInput.type = 'hidden';
    statusInput.name = 'status';
    statusInput.value = newStatus;
    
    form.appendChild(actionInput);
    form.appendChild(idInput);
    form.appendChild(statusInput);
    
    document.body.appendChild(form);
    form.submit();
}

// Modal Functions
function showModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }
}

function hideModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('show');
        document.body.style.overflow = 'auto';
    }
}

// Auto-save functionality for forms
function enableAutoSave(formId) {
    const form = document.getElementById(formId);
    if (!form) return;
    
    const inputs = form.querySelectorAll('input, textarea, select');
    inputs.forEach(input => {
        input.addEventListener('input', () => {
            const formData = new FormData(form);
            const data = Object.fromEntries(formData);
            localStorage.setItem(`autosave_${formId}`, JSON.stringify(data));
        });
    });
}

function loadAutoSave(formId) {
    const savedData = localStorage.getItem(`autosave_${formId}`);
    if (!savedData) return;
    
    try {
        const data = JSON.parse(savedData);
        const form = document.getElementById(formId);
        if (!form) return;
        
        Object.keys(data).forEach(key => {
            const input = form.querySelector(`[name="${key}"]`);
            if (input) {
                input.value = data[key];
            }
        });
    } catch (e) {
        console.error('Error loading auto-save data:', e);
    }
}

function clearAutoSave(formId) {
    localStorage.removeItem(`autosave_${formId}`);
}

// Real-time form feedback
function addFormFeedback() {
    const inputs = document.querySelectorAll('.form-input');
    inputs.forEach(input => {
        input.addEventListener('blur', function() {
            validateField(this);
        });
        
        input.addEventListener('input', function() {
            if (this.classList.contains('error')) {
                validateField(this);
            }
        });
    });
}

function validateField(field) {
    const value = field.value.trim();
    const fieldName = field.name;
    
    // Clear previous errors
    field.classList.remove('error');
    const existingError = field.parentNode.querySelector('.error-message');
    if (existingError) {
        existingError.remove();
    }
    
    let isValid = true;
    let errorMessage = '';
    
    // Field-specific validation
    switch (fieldName) {
        case 'email':
            if (value && !isValidEmail(value)) {
                isValid = false;
                errorMessage = 'Please enter a valid email address';
            }
            break;
        case 'username':
            if (value && value.length < 3) {
                isValid = false;
                errorMessage = 'Username must be at least 3 characters';
            } else if (value && !/^[a-zA-Z0-9_]+$/.test(value)) {
                isValid = false;
                errorMessage = 'Username can only contain letters, numbers, and underscores';
            }
            break;
        case 'password':
            if (value && value.length < 6) {
                isValid = false;
                errorMessage = 'Password must be at least 6 characters';
            }
            break;
        case 'confirm_password':
            const passwordField = document.getElementById('password');
            if (passwordField && value !== passwordField.value) {
                isValid = false;
                errorMessage = 'Passwords do not match';
            }
            break;
    }
    
    if (!isValid) {
        showError(field.id, errorMessage);
    }
    
    return isValid;
}

// Initialize everything when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize theme
    initTheme();
    
    // Add form feedback
    addFormFeedback();
    
    // Setup modal close functionality
    document.querySelectorAll('.modal-close').forEach(btn => {
        btn.addEventListener('click', function() {
            const modal = this.closest('.modal');
            if (modal) {
                hideModal(modal.id);
            }
        });
    });
    
    // Close modal when clicking outside
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                hideModal(this.id);
            }
        });
    });
    
    // Setup search functionality
    const searchInput = document.getElementById('search');
    if (searchInput) {
        searchInput.addEventListener('input', searchTasks);
    }
    
    // Setup auto-save for forms
    const forms = document.querySelectorAll('form[id]');
    forms.forEach(form => {
        enableAutoSave(form.id);
        loadAutoSave(form.id);
        
        form.addEventListener('submit', function() {
            clearAutoSave(this.id);
        });
    });
    
    // Add loading states to buttons
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function() {
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="loading"></span> Processing...';
            }
        });
    });
});

// Utility functions for better UX
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type}`;
    notification.style.position = 'fixed';
    notification.style.top = '20px';
    notification.style.right = '20px';
    notification.style.zIndex = '9999';
    notification.style.minWidth = '300px';
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 5000);
}

function animateProgress(element, progress) {
    element.style.width = '0%';
    setTimeout(() => {
        element.style.transition = 'width 0.5s ease';
        element.style.width = progress + '%';
    }, 100);
}