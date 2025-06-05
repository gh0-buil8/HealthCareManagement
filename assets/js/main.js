// Healthcare AMS - Advanced JavaScript with AI Integration

class HealthcareAMS {
    constructor() {
        this.apiBaseUrl = window.location.origin;
        this.aiAssistant = new AIAssistant();
        this.notifications = new NotificationManager();
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.initializeAnimations();
        this.loadUserPreferences();
        this.setupRealTimeUpdates();
        this.initializeAIChat();
    }

    setupEventListeners() {
        // Form validation
        this.setupFormValidation();
        
        // Table interactions
        this.setupTableInteractions();
        
        // Modal interactions
        this.setupModalInteractions();
        
        // Search functionality
        this.setupSearchFunctionality();
        
        // Theme toggle
        this.setupThemeToggle();
    }

    setupFormValidation() {
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            form.addEventListener('submit', this.validateForm.bind(this));
            
            // Real-time validation
            const inputs = form.querySelectorAll('input, textarea, select');
            inputs.forEach(input => {
                input.addEventListener('blur', () => this.validateField(input));
                input.addEventListener('input', () => this.clearFieldError(input));
            });
        });
    }

    validateForm(event) {
        const form = event.target;
        let isValid = true;
        
        // Clear previous errors
        form.querySelectorAll('.error-message').forEach(error => error.remove());
        form.querySelectorAll('.is-invalid').forEach(field => field.classList.remove('is-invalid'));
        
        // Validate required fields
        const requiredFields = form.querySelectorAll('[required]');
        requiredFields.forEach(field => {
            if (!this.validateField(field)) {
                isValid = false;
            }
        });
        
        // Email validation
        const emailFields = form.querySelectorAll('input[type="email"]');
        emailFields.forEach(field => {
            if (field.value && !this.isValidEmail(field.value)) {
                this.showFieldError(field, 'Please enter a valid email address');
                isValid = false;
            }
        });
        
        // Phone validation
        const phoneFields = form.querySelectorAll('input[type="tel"]');
        phoneFields.forEach(field => {
            if (field.value && !this.isValidPhone(field.value)) {
                this.showFieldError(field, 'Please enter a valid phone number');
                isValid = false;
            }
        });
        
        if (!isValid) {
            event.preventDefault();
            this.notifications.show('Please correct the errors below', 'error');
        } else {
            this.showLoadingState(form);
        }
        
        return isValid;
    }

    validateField(field) {
        if (field.hasAttribute('required') && !field.value.trim()) {
            this.showFieldError(field, 'This field is required');
            return false;
        }
        return true;
    }

    showFieldError(field, message) {
        field.classList.add('is-invalid');
        
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message text-danger small mt-1';
        errorDiv.textContent = message;
        
        field.parentNode.appendChild(errorDiv);
    }

    clearFieldError(field) {
        field.classList.remove('is-invalid');
        const errorMessage = field.parentNode.querySelector('.error-message');
        if (errorMessage) {
            errorMessage.remove();
        }
    }

    isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    isValidPhone(phone) {
        const phoneRegex = /^[\+]?[1-9][\d]{0,15}$/;
        return phoneRegex.test(phone.replace(/[\s\-\(\)]/g, ''));
    }

    showLoadingState(form) {
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
        }
    }

    setupTableInteractions() {
        // Enhanced table functionality
        this.setupTableSorting();
        this.setupTableFiltering();
        this.setupTablePagination();
    }

    setupTableSorting() {
        const sortableHeaders = document.querySelectorAll('th[data-sortable]');
        sortableHeaders.forEach(header => {
            header.style.cursor = 'pointer';
            header.addEventListener('click', () => this.sortTable(header));
        });
    }

    sortTable(header) {
        const table = header.closest('table');
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        const columnIndex = Array.from(header.parentNode.children).indexOf(header);
        const isAscending = !header.classList.contains('sorted-asc');
        
        // Clear previous sorting
        header.parentNode.querySelectorAll('th').forEach(th => {
            th.classList.remove('sorted-asc', 'sorted-desc');
        });
        
        // Add sorting class
        header.classList.add(isAscending ? 'sorted-asc' : 'sorted-desc');
        
        // Sort rows
        rows.sort((a, b) => {
            const aValue = a.children[columnIndex].textContent.trim();
            const bValue = b.children[columnIndex].textContent.trim();
            
            if (isAscending) {
                return aValue.localeCompare(bValue, undefined, { numeric: true });
            } else {
                return bValue.localeCompare(aValue, undefined, { numeric: true });
            }
        });
        
        // Re-append sorted rows
        rows.forEach(row => tbody.appendChild(row));
    }

    setupModalInteractions() {
        // Auto-focus first input in modals
        document.addEventListener('shown.bs.modal', (event) => {
            const modal = event.target;
            const firstInput = modal.querySelector('input, textarea, select');
            if (firstInput) {
                firstInput.focus();
            }
        });
    }

    setupSearchFunctionality() {
        const searchInputs = document.querySelectorAll('input[type="search"], input[name="search"]');
        searchInputs.forEach(input => {
            let timeout;
            input.addEventListener('input', () => {
                clearTimeout(timeout);
                timeout = setTimeout(() => {
                    this.performSearch(input);
                }, 300);
            });
        });
    }

    performSearch(input) {
        const searchTerm = input.value.toLowerCase();
        const targetTable = document.querySelector('table tbody');
        
        if (targetTable) {
            const rows = targetTable.querySelectorAll('tr');
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
    }

    setupThemeToggle() {
        const themeToggle = document.getElementById('themeToggle');
        if (themeToggle) {
            themeToggle.addEventListener('click', this.toggleTheme.bind(this));
        }
    }

    toggleTheme() {
        const currentTheme = document.documentElement.getAttribute('data-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        
        document.documentElement.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        
        this.notifications.show(`Switched to ${newTheme} theme`, 'success');
    }

    initializeAnimations() {
        // Intersection Observer for scroll animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('fade-in');
                }
            });
        }, observerOptions);

        // Observe elements for animation
        document.querySelectorAll('.card, .dashboard-card, .stats-card').forEach(el => {
            observer.observe(el);
        });
    }

    loadUserPreferences() {
        // Load theme preference
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
        
        // Load other preferences
        const preferences = JSON.parse(localStorage.getItem('userPreferences') || '{}');
        this.applyUserPreferences(preferences);
    }

    applyUserPreferences(preferences) {
        // Apply saved user preferences
        if (preferences.language) {
            document.documentElement.lang = preferences.language;
        }
        
        if (preferences.fontSize) {
            document.documentElement.style.fontSize = preferences.fontSize;
        }
    }

    setupRealTimeUpdates() {
        // Simulate real-time updates for demonstration
        if (window.location.pathname.includes('dashboard')) {
            this.startRealTimeUpdates();
        }
    }

    startRealTimeUpdates() {
        setInterval(() => {
            this.updateDashboardStats();
        }, 30000); // Update every 30 seconds
    }

    updateDashboardStats() {
        // Update dashboard statistics
        const statsCards = document.querySelectorAll('.stats-number');
        statsCards.forEach(card => {
            // Simulate small changes in numbers
            const currentValue = parseInt(card.textContent.replace(/[^\d]/g, ''));
            if (currentValue > 0) {
                const change = Math.floor(Math.random() * 3) - 1; // -1, 0, or 1
                const newValue = Math.max(0, currentValue + change);
                this.animateNumber(card, currentValue, newValue);
            }
        });
    }

    animateNumber(element, from, to) {
        const duration = 1000;
        const startTime = performance.now();
        
        const animate = (currentTime) => {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            
            const current = Math.floor(from + (to - from) * this.easeOutCubic(progress));
            element.textContent = current.toLocaleString();
            
            if (progress < 1) {
                requestAnimationFrame(animate);
            }
        };
        
        requestAnimationFrame(animate);
    }

    easeOutCubic(t) {
        return 1 - Math.pow(1 - t, 3);
    }

    initializeAIChat() {
        this.createAIChatWidget();
        this.setupAIChatEvents();
    }

    createAIChatWidget() {
        const chatWidget = document.createElement('div');
        chatWidget.className = 'ai-chat-widget';
        chatWidget.id = 'aiChatWidget';
        chatWidget.innerHTML = `
            <div class="ai-chat-header">
                <span><i class="fas fa-robot me-2"></i>AI Assistant</span>
                <button type="button" class="btn-close btn-close-white" onclick="healthcareAMS.toggleAIChat()"></button>
            </div>
            <div class="ai-chat-messages" id="aiChatMessages">
                <div class="ai-message">
                    Hello! I'm your AI assistant. I can help you with:
                    <ul class="mt-2 mb-0">
                        <li>Scheduling appointments</li>
                        <li>Patient information</li>
                        <li>Medical records</li>
                        <li>System navigation</li>
                    </ul>
                </div>
            </div>
            <div class="ai-chat-input">
                <div class="input-group">
                    <input type="text" class="form-control" id="aiChatInput" placeholder="Ask me anything...">
                    <button class="btn btn-primary" onclick="healthcareAMS.sendAIMessage()">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </div>
        `;
        
        document.body.appendChild(chatWidget);
        
        // Create floating AI button
        const aiButton = document.createElement('button');
        aiButton.className = 'btn-floating';
        aiButton.id = 'aiChatButton';
        aiButton.innerHTML = '<i class="fas fa-robot"></i>';
        aiButton.onclick = () => this.toggleAIChat();
        
        document.body.appendChild(aiButton);
    }

    setupAIChatEvents() {
        const chatInput = document.getElementById('aiChatInput');
        if (chatInput) {
            chatInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    this.sendAIMessage();
                }
            });
        }
    }

    toggleAIChat() {
        const chatWidget = document.getElementById('aiChatWidget');
        const aiButton = document.getElementById('aiChatButton');
        
        if (chatWidget.style.display === 'flex') {
            chatWidget.style.display = 'none';
            aiButton.style.display = 'flex';
        } else {
            chatWidget.style.display = 'flex';
            aiButton.style.display = 'none';
            document.getElementById('aiChatInput').focus();
        }
    }

    sendAIMessage() {
        const input = document.getElementById('aiChatInput');
        const message = input.value.trim();
        
        if (!message) return;
        
        // Add user message
        this.addChatMessage(message, 'user');
        input.value = '';
        
        // Show typing indicator
        this.showTypingIndicator();
        
        // Process AI response
        setTimeout(() => {
            this.hideTypingIndicator();
            const response = this.aiAssistant.processMessage(message);
            this.addChatMessage(response, 'ai');
        }, 1000 + Math.random() * 2000);
    }

    addChatMessage(message, sender) {
        const messagesContainer = document.getElementById('aiChatMessages');
        const messageDiv = document.createElement('div');
        messageDiv.className = sender === 'user' ? 'ai-message user-message' : 'ai-message';
        messageDiv.textContent = message;
        
        messagesContainer.appendChild(messageDiv);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    showTypingIndicator() {
        const messagesContainer = document.getElementById('aiChatMessages');
        const typingDiv = document.createElement('div');
        typingDiv.className = 'ai-message typing-indicator';
        typingDiv.id = 'typingIndicator';
        typingDiv.innerHTML = '<i class="fas fa-ellipsis-h"></i> AI is typing...';
        
        messagesContainer.appendChild(typingDiv);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    hideTypingIndicator() {
        const typingIndicator = document.getElementById('typingIndicator');
        if (typingIndicator) {
            typingIndicator.remove();
        }
    }
}

class AIAssistant {
    constructor() {
        this.responses = {
            appointment: [
                "I can help you schedule an appointment. Please provide the patient's name and preferred date.",
                "To book an appointment, I'll need the patient ID and the healthcare provider you'd like to see.",
                "I can check available appointment slots. Which specialty are you looking for?"
            ],
            patient: [
                "I can help you find patient information. Please provide the patient ID or name.",
                "I can assist with patient records. What specific information do you need?",
                "For patient information, I can help you search by name, ID, or email address."
            ],
            payment: [
                "I can help you with payment information. Are you looking for a specific transaction?",
                "For payment assistance, I can help you process payments or check payment status.",
                "I can help you with billing inquiries. Please provide the payment ID or patient name."
            ],
            navigation: [
                "I can help you navigate the system. What section are you looking for?",
                "Need help finding something? I can guide you to any part of the healthcare system.",
                "I can help you locate features in the dashboard. What would you like to access?"
            ],
            default: [
                "I'm here to help with your healthcare management needs. Can you be more specific?",
                "I can assist with appointments, patients, payments, and navigation. What would you like to know?",
                "I'm your AI assistant for healthcare management. How can I help you today?"
            ]
        };
    }

    processMessage(message) {
        const lowercaseMessage = message.toLowerCase();
        
        if (this.containsKeywords(lowercaseMessage, ['appointment', 'schedule', 'book'])) {
            return this.getRandomResponse('appointment');
        } else if (this.containsKeywords(lowercaseMessage, ['patient', 'record', 'medical'])) {
            return this.getRandomResponse('patient');
        } else if (this.containsKeywords(lowercaseMessage, ['payment', 'bill', 'money', 'pay'])) {
            return this.getRandomResponse('payment');
        } else if (this.containsKeywords(lowercaseMessage, ['navigate', 'find', 'where', 'how'])) {
            return this.getRandomResponse('navigation');
        } else {
            return this.getRandomResponse('default');
        }
    }

    containsKeywords(message, keywords) {
        return keywords.some(keyword => message.includes(keyword));
    }

    getRandomResponse(category) {
        const responses = this.responses[category];
        return responses[Math.floor(Math.random() * responses.length)];
    }
}

class NotificationManager {
    constructor() {
        this.container = this.createNotificationContainer();
    }

    createNotificationContainer() {
        const container = document.createElement('div');
        container.id = 'notificationContainer';
        container.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            max-width: 400px;
        `;
        document.body.appendChild(container);
        return container;
    }

    show(message, type = 'info', duration = 5000) {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} alert-dismissible fade show`;
        notification.style.cssText = `
            margin-bottom: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            animation: slideInRight 0.3s ease-out;
        `;
        
        const icon = this.getIcon(type);
        notification.innerHTML = `
            <i class="${icon} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        this.container.appendChild(notification);
        
        // Auto-remove after duration
        setTimeout(() => {
            if (notification.parentNode) {
                notification.classList.remove('show');
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.remove();
                    }
                }, 150);
            }
        }, duration);
    }

    getIcon(type) {
        const icons = {
            success: 'fas fa-check-circle',
            error: 'fas fa-exclamation-triangle',
            warning: 'fas fa-exclamation-circle',
            info: 'fas fa-info-circle'
        };
        return icons[type] || icons.info;
    }
}

// Global functions for backward compatibility
function viewPatient(patientId) {
    healthcareAMS.loadPatientDetails(patientId);
}

function editPatient(patientId) {
    healthcareAMS.editPatient(patientId);
}

function deletePatient(patientId) {
    if (confirm('Are you sure you want to delete this patient?')) {
        healthcareAMS.deletePatient(patientId);
    }
}

function viewProvider(providerId) {
    healthcareAMS.loadProviderDetails(providerId);
}

function editProvider(providerId) {
    healthcareAMS.editProvider(providerId);
}

function deleteProvider(providerId) {
    if (confirm('Are you sure you want to delete this provider?')) {
        healthcareAMS.deleteProvider(providerId);
    }
}

function viewAppointment(appointmentId) {
    healthcareAMS.loadAppointmentDetails(appointmentId);
}

function editAppointmentStatus(appointmentId, currentStatus) {
    healthcareAMS.editAppointmentStatus(appointmentId, currentStatus);
}

function viewPayment(paymentId) {
    healthcareAMS.loadPaymentDetails(paymentId);
}

function markAsPaid(paymentId) {
    healthcareAMS.markPaymentAsPaid(paymentId);
}

function refundPayment(paymentId) {
    if (confirm('Are you sure you want to refund this payment?')) {
        healthcareAMS.refundPayment(paymentId);
    }
}

function exportAppointments() {
    healthcareAMS.exportData('appointments');
}

function exportPayments() {
    healthcareAMS.exportData('payments');
}

// Initialize the application when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.healthcareAMS = new HealthcareAMS();
    
    // Add custom styles for animations
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        .typing-indicator {
            opacity: 0.7;
            animation: pulse 1.5s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 0.7; }
            50% { opacity: 1; }
        }
        
        .sorted-asc::after {
            content: ' ↑';
            color: var(--primary-color);
        }
        
        .sorted-desc::after {
            content: ' ↓';
            color: var(--primary-color);
        }
    `;
    document.head.appendChild(style);
});

// Service Worker for offline functionality
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function() {
        navigator.serviceWorker.register('/sw.js')
            .then(function(registration) {
                console.log('ServiceWorker registration successful');
            })
            .catch(function(err) {
                console.log('ServiceWorker registration failed');
            });
    });
}