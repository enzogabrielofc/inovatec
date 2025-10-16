// Carrinho de compras
let cart = JSON.parse(localStorage.getItem('cart')) || [];
let cartCount = 0;
let cartTotal = 0;

// Inicializar carrinho ao carregar a página
document.addEventListener('DOMContentLoaded', function() {
    updateCartDisplay();
    updateCartCount();
    
    // Adicionar listener para fechar modal clicando fora dele
    window.onclick = function(event) {
        const modal = document.getElementById('cart-modal');
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    };
});

// Adicionar produto ao carrinho
function addToCart(productName, price) {
    // Verificar se o produto já existe no carrinho
    const existingItem = cart.find(item => item.name === productName);
    
    if (existingItem) {
        existingItem.quantity += 1;
    } else {
        cart.push({
            name: productName,
            price: price,
            quantity: 1
        });
    }
    
    // Salvar no localStorage
    localStorage.setItem('cart', JSON.stringify(cart));
    
    // Atualizar interface
    updateCartDisplay();
    updateCartCount();
    
    // Mostrar feedback visual
    showAddToCartFeedback(productName);
}

// Remover produto do carrinho
function removeFromCart(productName) {
    cart = cart.filter(item => item.name !== productName);
    localStorage.setItem('cart', JSON.stringify(cart));
    updateCartDisplay();
    updateCartCount();
}

// Alterar quantidade do produto no carrinho
function changeQuantity(productName, change) {
    const item = cart.find(item => item.name === productName);
    if (item) {
        item.quantity += change;
        if (item.quantity <= 0) {
            removeFromCart(productName);
        } else {
            localStorage.setItem('cart', JSON.stringify(cart));
            updateCartDisplay();
            updateCartCount();
        }
    }
}

// Atualizar exibição do carrinho
function updateCartDisplay() {
    const cartItems = document.getElementById('cart-items');
    const cartTotalElement = document.getElementById('cart-total');
    
    if (!cartItems || !cartTotalElement) return;
    
    cartItems.innerHTML = '';
    cartTotal = 0;
    
    if (cart.length === 0) {
        cartItems.innerHTML = '<p style="text-align: center; color: #666; margin: 2rem 0;">Seu carrinho está vazio</p>';
        cartTotalElement.textContent = '0,00';
        return;
    }
    
    cart.forEach(item => {
        const itemTotal = item.price * item.quantity;
        cartTotal += itemTotal;
        
        const cartItem = document.createElement('div');
        cartItem.className = 'cart-item';
        cartItem.innerHTML = `
            <div>
                <h4>${item.name}</h4>
                <p>R$ ${item.price.toLocaleString('pt-BR', { minimumFractionDigits: 2 })}</p>
            </div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <button onclick="changeQuantity('${item.name}', -1)" 
                        style="background: #dc3545; color: white; border: none; border-radius: 50%; width: 30px; height: 30px; cursor: pointer;">-</button>
                <span>${item.quantity}</span>
                <button onclick="changeQuantity('${item.name}', 1)" 
                        style="background: #28a745; color: white; border: none; border-radius: 50%; width: 30px; height: 30px; cursor: pointer;">+</button>
                <button onclick="removeFromCart('${item.name}')" 
                        style="background: #dc3545; color: white; border: none; border-radius: 5px; padding: 5px 10px; cursor: pointer; margin-left: 10px;">🗑️</button>
            </div>
        `;
        cartItems.appendChild(cartItem);
    });
    
    cartTotalElement.textContent = cartTotal.toLocaleString('pt-BR', { minimumFractionDigits: 2 });
}

// Atualizar contador do carrinho
function updateCartCount() {
    cartCount = cart.reduce((total, item) => total + item.quantity, 0);
    const cartCountElement = document.getElementById('cart-count');
    if (cartCountElement) {
        cartCountElement.textContent = cartCount;
    }
}

// Abrir/fechar carrinho
function toggleCart() {
    const modal = document.getElementById('cart-modal');
    if (modal.style.display === 'block') {
        modal.style.display = 'none';
    } else {
        modal.style.display = 'block';
        updateCartDisplay();
    }
}

// Mostrar feedback ao adicionar produto
function showAddToCartFeedback(productName) {
    // Criar elemento de feedback
    const feedback = document.createElement('div');
    feedback.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: #28a745;
        color: white;
        padding: 15px 20px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 1001;
        transform: translateX(100%);
        transition: transform 0.3s ease;
    `;
    feedback.innerHTML = `✅ ${productName} adicionado ao carrinho!`;
    
    document.body.appendChild(feedback);
    
    // Animação de entrada
    setTimeout(() => {
        feedback.style.transform = 'translateX(0)';
    }, 100);
    
    // Remover após 3 segundos
    setTimeout(() => {
        feedback.style.transform = 'translateX(100%)';
        setTimeout(() => {
            document.body.removeChild(feedback);
        }, 300);
    }, 3000);
}

// Finalizar compra (simulação)
function checkout() {
    if (cart.length === 0) {
        alert('Seu carrinho está vazio!');
        return;
    }
    
    const total = cartTotal.toLocaleString('pt-BR', { 
        style: 'currency', 
        currency: 'BRL' 
    });
    
    const itemsList = cart.map(item => 
        `${item.quantity}x ${item.name} - R$ ${(item.price * item.quantity).toLocaleString('pt-BR', { minimumFractionDigits: 2 })}`
    ).join('\\n');
    
    const confirmMessage = `Resumo do seu pedido:\\n\\n${itemsList}\\n\\nTotal: ${total}\\n\\nDeseja finalizar a compra?`;
    
    if (confirm(confirmMessage)) {
        alert('Pedido realizado com sucesso! ✅\\n\\nVocê receberá um e-mail com os detalhes da compra.');
        
        // Limpar carrinho
        cart = [];
        localStorage.removeItem('cart');
        updateCartDisplay();
        updateCartCount();
        toggleCart(); // Fechar modal
    }
}

// Adicionar listener para o botão de checkout
document.addEventListener('DOMContentLoaded', function() {
    const checkoutBtns = document.querySelectorAll('.checkout-btn');
    checkoutBtns.forEach(btn => {
        btn.addEventListener('click', checkout);
    });
});

// Enviar mensagem de contato (simulação)
function sendMessage(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    
    const name = formData.get('name');
    const email = formData.get('email');
    const phone = formData.get('phone');
    const subject = formData.get('subject');
    const message = formData.get('message');
    
    // Simulação de envio
    const loadingMsg = 'Enviando mensagem...';
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    
    submitBtn.textContent = loadingMsg;
    submitBtn.disabled = true;
    
    setTimeout(() => {
        alert(`Mensagem enviada com sucesso! ✅\\n\\nOlá ${name}, recebemos sua mensagem sobre "${getSubjectText(subject)}" e responderemos em breve no e-mail ${email}.`);
        
        // Limpar formulário
        form.reset();
        
        // Restaurar botão
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    }, 2000);
}

// Converter valor do select em texto legível
function getSubjectText(value) {
    const subjects = {
        'duvida-produto': 'Dúvida sobre Produto',
        'suporte-tecnico': 'Suporte Técnico',
        'pedido': 'Status do Pedido',
        'orcamento': 'Orçamento',
        'reclamacao': 'Reclamação',
        'sugestao': 'Sugestão',
        'outro': 'Outro'
    };
    return subjects[value] || value;
}

// Smooth scroll para links internos
document.addEventListener('DOMContentLoaded', function() {
    const links = document.querySelectorAll('a[href^="#"]');
    links.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href').substring(1);
            const targetElement = document.getElementById(targetId);
            
            if (targetElement) {
                targetElement.scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });
    });
});

// Adicionar animação aos cards quando visíveis
function isElementInViewport(el) {
    const rect = el.getBoundingClientRect();
    return (
        rect.top >= 0 &&
        rect.left >= 0 &&
        rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
        rect.right <= (window.innerWidth || document.documentElement.clientWidth)
    );
}

function checkVisibility() {
    const cards = document.querySelectorAll('.product-card, .category-card');
    cards.forEach(card => {
        if (isElementInViewport(card)) {
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }
    });
}

// Inicializar animações
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.product-card, .category-card');
    cards.forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'all 0.6s ease';
    });
    
    // Verificar visibilidade inicial
    checkVisibility();
    
    // Verificar visibilidade no scroll
    window.addEventListener('scroll', checkVisibility);
});

// Busca simples (pode ser expandida no futuro)
function initSearch() {
    const searchInput = document.getElementById('search');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const products = document.querySelectorAll('.product-card');
            
            products.forEach(product => {
                const productName = product.querySelector('h3').textContent.toLowerCase();
                const productSpecs = product.querySelector('.specs')?.textContent.toLowerCase() || '';
                
                if (productName.includes(searchTerm) || productSpecs.includes(searchTerm)) {
                    product.style.display = 'block';
                } else {
                    product.style.display = searchTerm === '' ? 'block' : 'none';
                }
            });
        });
    }
}

// Inicializar busca se existir
document.addEventListener('DOMContentLoaded', initSearch);

// Função para debug (pode ser removida em produção)
function debugCart() {
    console.log('Cart contents:', cart);
    console.log('Cart count:', cartCount);
    console.log('Cart total:', cartTotal);
}

// Tornar debugCart disponível globalmente para desenvolvimento
window.debugCart = debugCart;