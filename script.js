// ===== INICIALIZAÇÃO ===== //
document.addEventListener('DOMContentLoaded', function() {
    // Loading Screen
    initLoadingScreen();
    
    // Particles Background
    createParticles();
    
    // Navigation
    initNavigation();
    
    // Typed Effect
    initTypedEffect();
    
    // Scroll Animations
    initScrollAnimations();
    
    // Stats Counter
    initStatsCounter();
    
    // Skills Progress
    initSkillsProgress();
    
    // Project Filters
    initProjectFilters();
    
    // Contact Form
    initContactForm();
    
    // Chat Widget
    initChatWidget();
    
    // Smooth Scroll
    initSmoothScroll();
});

// ===== LOADING SCREEN ===== //
function initLoadingScreen() {
    const loadingScreen = document.getElementById('loading-screen');
    
    // Simular carregamento
    setTimeout(() => {
        loadingScreen.classList.add('fade-out');
        setTimeout(() => {
            loadingScreen.style.display = 'none';
        }, 500);
    }, 2000);
}

// ===== PARTICLES BACKGROUND ===== //
function createParticles() {
    const particlesContainer = document.getElementById('particles');
    const particleCount = 50;
    
    for (let i = 0; i < particleCount; i++) {
        const particle = document.createElement('div');
        particle.className = 'particle';
        
        // Posição aleatória
        particle.style.left = Math.random() * 100 + '%';
        particle.style.top = Math.random() * 100 + '%';
        
        // Tamanho aleatório
        const size = Math.random() * 4 + 2;
        particle.style.width = size + 'px';
        particle.style.height = size + 'px';
        
        // Delay de animação aleatório
        particle.style.animationDelay = Math.random() * 6 + 's';
        particle.style.animationDuration = (Math.random() * 4 + 4) + 's';
        
        particlesContainer.appendChild(particle);
    }
}

// ===== NAVIGATION ===== //
function initNavigation() {
    const navbar = document.querySelector('.navbar');
    const hamburger = document.querySelector('.hamburger');
    const navMenu = document.querySelector('.nav-menu');
    const navLinks = document.querySelectorAll('.nav-menu a');
    
    // Scroll effect
    window.addEventListener('scroll', () => {
        if (window.scrollY > 100) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });
    
    // Mobile menu toggle
    hamburger.addEventListener('click', () => {
        navMenu.classList.toggle('active');
        hamburger.classList.toggle('active');
    });
    
    // Close menu when link is clicked
    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            navMenu.classList.remove('active');
            hamburger.classList.remove('active');
        });
    });
    
    // Active link highlighting
    const sections = document.querySelectorAll('section[id]');
    
    function highlightNavLink() {
        const scrollY = window.pageYOffset;
        
        sections.forEach(section => {
            const sectionHeight = section.offsetHeight;
            const sectionTop = section.offsetTop - 100;
            const sectionId = section.getAttribute('id');
            
            if (scrollY > sectionTop && scrollY <= sectionTop + sectionHeight) {
                document.querySelector(`.nav-menu a[href*=${sectionId}]`).classList.add('active');
            } else {
                document.querySelector(`.nav-menu a[href*=${sectionId}]`).classList.remove('active');
            }
        });
    }
    
    window.addEventListener('scroll', highlightNavLink);
}

// ===== TYPED EFFECT ===== //
function initTypedEffect() {
    const typedElement = document.querySelector('.typing-text');
    if (!typedElement) return;
    
    const texts = [
        'Desenvolvedor Full Stack',
        'Especialista em IA',
        'Criador de Soluções',
        'Inovador Digital'
    ];
    
    let textIndex = 0;
    let charIndex = 0;
    let isDeleting = false;
    
    function typeText() {
        const currentText = texts[textIndex];
        
        if (isDeleting) {
            typedElement.textContent = currentText.substring(0, charIndex - 1);
            charIndex--;
        } else {
            typedElement.textContent = currentText.substring(0, charIndex + 1);
            charIndex++;
        }
        
        let typeSpeed = isDeleting ? 50 : 100;
        
        if (!isDeleting && charIndex === currentText.length) {
            typeSpeed = 2000; // Pause at end
            isDeleting = true;
        } else if (isDeleting && charIndex === 0) {
            isDeleting = false;
            textIndex = (textIndex + 1) % texts.length;
            typeSpeed = 500; // Pause before next word
        }
        
        setTimeout(typeText, typeSpeed);
    }
    
    setTimeout(typeText, 1000);
}

// ===== SCROLL ANIMATIONS ===== //
function initScrollAnimations() {
    // Intersection Observer para animações
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.animationPlayState = 'running';
                
                // Trigger skill progress bars
                if (entry.target.classList.contains('skills-section')) {
                    animateSkillBars();
                }
                
                // Trigger stats counter
                if (entry.target.classList.contains('stats')) {
                    animateStats();
                }
            }
        });
    }, observerOptions);
    
    // Observe elements
    document.querySelectorAll('[class*="fadeIn"], .skill-category, .project-card').forEach(el => {
        observer.observe(el);
    });
}

// ===== STATS COUNTER ===== //
function initStatsCounter() {
    // Stats counter será ativado pelo scroll observer
}

function animateStats() {
    const statNumbers = document.querySelectorAll('.stat-number');
    
    statNumbers.forEach(stat => {
        const target = parseInt(stat.getAttribute('data-target'));
        const increment = target / 50;
        let current = 0;
        
        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                stat.textContent = target;
                clearInterval(timer);
            } else {
                stat.textContent = Math.floor(current);
            }
        }, 40);
    });
}

// ===== SKILLS PROGRESS ===== //
function initSkillsProgress() {
    // Progress bars serão ativados pelo scroll observer
}

function animateSkillBars() {
    const skillBars = document.querySelectorAll('.skill-progress');
    
    skillBars.forEach(bar => {
        const width = bar.getAttribute('data-width');
        setTimeout(() => {
            bar.style.width = width;
        }, 500);
    });
}

// ===== PROJECT FILTERS ===== //
function initProjectFilters() {
    const filterBtns = document.querySelectorAll('.filter-btn');
    const projectCards = document.querySelectorAll('.project-card');
    
    filterBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            // Remove active class from all buttons
            filterBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            
            const filter = btn.getAttribute('data-filter');
            
            projectCards.forEach(card => {
                if (filter === 'all' || card.getAttribute('data-category') === filter) {
                    card.style.display = 'block';
                    card.style.opacity = '0';
                    setTimeout(() => {
                        card.style.opacity = '1';
                        card.style.transform = 'translateY(0)';
                    }, 100);
                } else {
                    card.style.opacity = '0';
                    card.style.transform = 'translateY(20px)';
                    setTimeout(() => {
                        card.style.display = 'none';
                    }, 300);
                }
            });
        });
    });
}

// ===== CONTACT FORM ===== //
function initContactForm() {
    const form = document.getElementById('contact-form');
    if (!form) return;
    
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const submitBtn = form.querySelector('.submit-btn');
        const originalText = submitBtn.innerHTML;
        
        // Loading state
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';
        submitBtn.disabled = true;
        
        // Simular envio
        setTimeout(() => {
            submitBtn.innerHTML = '<i class="fas fa-check"></i> Mensagem Enviada!';
            submitBtn.style.background = '#10b981';
            
            // Reset form
            form.reset();
            
            // Reset button
            setTimeout(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
                submitBtn.style.background = '';
            }, 3000);
            
            // Success message
            showNotification('Mensagem enviada com sucesso!', 'success');
        }, 2000);
    });
}

// ===== CHAT WIDGET ===== //
function initChatWidget() {
    const chatToggle = document.getElementById('openChat');
    const chatClose = document.getElementById('closeChat');
    const chatbox = document.getElementById('chatbox');
    const chatMessages = document.getElementById('chat-messages');
    const userInput = document.getElementById('userInput');
    const sendBtn = document.getElementById('sendBtn');
    const chatNotification = document.querySelector('.chat-notification');
    
    if (!chatToggle || !chatbox) return;
    
    // Toggle chat
    chatToggle.addEventListener('click', () => {
        chatbox.classList.toggle('active');
        if (chatbox.classList.contains('active')) {
            chatNotification.style.display = 'none';
            // Welcome message
            if (chatMessages.children.length === 0) {
                addChatMessage('Olá! 👋 Sou o assistente virtual do Picolé. Como posso te ajudar hoje?', 'bot');
            }
        }
    });
    
    // Close chat
    if (chatClose) {
        chatClose.addEventListener('click', () => {
            chatbox.classList.remove('active');
        });
    }
    
    // Send message
    function sendChatMessage() {
        const message = userInput.value.trim();
        if (!message) return;
        
        addChatMessage(message, 'user');
        userInput.value = '';
        
        // Show typing indicator
        const typingMsg = addChatMessage('Digitando...', 'bot');
        
        // Simulate AI response
        setTimeout(() => {
            typingMsg.remove();
            const response = generateChatResponse(message);
            addChatMessage(response, 'bot');
        }, 1000 + Math.random() * 2000);
    }
    
    sendBtn.addEventListener('click', sendChatMessage);
    userInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            sendChatMessage();
        }
    });
    
    // Add message to chat
    function addChatMessage(text, sender) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${sender}`;
        messageDiv.textContent = text;
        chatMessages.appendChild(messageDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;
        return messageDiv;
    }
    
    // Generate AI response
    function generateChatResponse(userMessage) {
        const responses = {
            'oi': 'Olá! Como está seu dia? 😊',
            'olá': 'Oi! Que bom te ver por aqui! ✨',
            'projeto': 'Tenho vários projetos interessantes! Você pode conferir na seção de projetos acima. Qual tipo de projeto te interessa mais?',
            'contato': 'Você pode me encontrar no email enzogabrieldomiciano1@gmail.com ou no GitHub. Também estou sempre disposto a conversar sobre novos projetos!',
            'habilidades': 'Minhas principais habilidades incluem desenvolvimento web com HTML, CSS, JavaScript e especialmente Inteligência Artificial. Estou sempre aprendendo coisas novas!',
            'sobre': 'Sou um desenvolvedor apaixonado por tecnologia e IA. Adoro criar soluções inovadoras e sempre busco estar na vanguarda das novas tecnologias!',
            'ajuda': 'Posso te ajudar com informações sobre meus projetos, habilidades, experiência ou qualquer dúvida sobre desenvolvimento e IA!',
            'obrigado': 'De nada! Fico feliz em ajudar! 🚀',
            'tchau': 'Até logo! Foi ótimo conversar com você! 👋'
        };
        
        const message = userMessage.toLowerCase();
        
        // Check for keywords
        for (const key in responses) {
            if (message.includes(key)) {
                return responses[key];
            }
        }
        
        // Default responses
        const defaultResponses = [
            'Interessante! Conte-me mais sobre isso.',
            'Que legal! Posso te ajudar com mais alguma coisa?',
            'Hmm, entendo. Você gostaria de saber mais sobre meus projetos?',
            'Ótima pergunta! Dê uma olhada na minha seção de habilidades.',
            'Bacana! Se quiser conversar sobre desenvolvimento ou IA, estou aqui!',
            'Legal! Que tal conferir meus projetos? Tenho certeza que você vai gostar!',
            'Entendo! Se tiver dúvidas sobre programação, pode perguntar!'
        ];
        
        return defaultResponses[Math.floor(Math.random() * defaultResponses.length)];
    }
    
    // Show chat notification after some time
    setTimeout(() => {
        if (!chatbox.classList.contains('active')) {
            chatNotification.style.display = 'flex';
            // Add bounce animation
            chatToggle.style.animation = 'bounce 1s ease-in-out 3';
        }
    }, 10000);
}

// ===== SMOOTH SCROLL ===== //
function initSmoothScroll() {
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                const offsetTop = target.offsetTop - 80; // Account for fixed navbar
                
                window.scrollTo({
                    top: offsetTop,
                    behavior: 'smooth'
                });
            }
        });
    });
}

// ===== UTILITY FUNCTIONS ===== //
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 1rem 1.5rem;
        border-radius: 12px;
        color: white;
        font-weight: 500;
        z-index: 10000;
        transform: translateX(100%);
        transition: transform 0.3s ease;
    `;
    
    // Set colors based on type
    const colors = {
        success: '#10b981',
        error: '#ef4444',
        warning: '#f59e0b',
        info: '#3b82f6'
    };
    
    notification.style.background = colors[type] || colors.info;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    // Show notification
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 100);
    
    // Hide notification
    setTimeout(() => {
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 4000);
}

// Add bounce animation to CSS dynamically
const style = document.createElement('style');
style.textContent = `
    @keyframes bounce {
        0%, 20%, 50%, 80%, 100% { transform: translateY(0) scale(1); }
        40% { transform: translateY(-10px) scale(1.05); }
        60% { transform: translateY(-5px) scale(1.02); }
    }
    
    .notification {
        box-shadow: 0 10px 25px rgba(0,0,0,0.2);
    }
`;
document.head.appendChild(style);

// ===== PERFORMANCE OPTIMIZATIONS ===== //
// Throttle scroll events
function throttle(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Apply throttling to scroll events
const originalScroll = window.addEventListener;
window.addEventListener = function(type, listener, options) {
    if (type === 'scroll') {
        listener = throttle(listener, 16); // ~60fps
    }
    return originalScroll.call(this, type, listener, options);
};

// ===== EASTER EGG ===== //
let clickCount = 0;
document.querySelector('.logo').addEventListener('click', () => {
    clickCount++;
    if (clickCount === 5) {
        showNotification('🎉 Easter egg encontrado! Você é curioso! 🕵️', 'success');
        clickCount = 0;
        
        // Add some fun particles
        for (let i = 0; i < 20; i++) {
            createConfetti();
        }
    }
});

function createConfetti() {
    const confetti = document.createElement('div');
    confetti.style.cssText = `
        position: fixed;
        width: 10px;
        height: 10px;
        background: hsl(${Math.random() * 360}, 70%, 60%);
        border-radius: 50%;
        pointer-events: none;
        z-index: 9999;
        left: ${Math.random() * 100}vw;
        animation: confettiFall 3s linear forwards;
    `;
    
    document.body.appendChild(confetti);
    
    setTimeout(() => confetti.remove(), 3000);
}

// Add confetti animation
const confettiStyle = document.createElement('style');
confettiStyle.textContent = `
    @keyframes confettiFall {
        0% {
            transform: translateY(-100vh) rotate(0deg);
            opacity: 1;
        }
        100% {
            transform: translateY(100vh) rotate(360deg);
            opacity: 0;
        }
    }
`;
document.head.appendChild(confettiStyle);