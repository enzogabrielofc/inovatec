<?php
/**
 * Página de Contato - InovaTech Store
 */

// Processar formulário se enviado
$mensagemEnviada = false;
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enviar_mensagem'])) {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $assunto = trim($_POST['assunto'] ?? '');
    $mensagem = trim($_POST['mensagem'] ?? '');
    
    // Validação simples
    if (empty($nome) || empty($email) || empty($assunto) || empty($mensagem)) {
        $erro = 'Por favor, preencha todos os campos obrigatórios.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = 'Por favor, insira um e-mail válido.';
    } else {
        // Aqui você pode salvar no banco de dados ou enviar por email
        // Por enquanto, vamos simular sucesso
        $mensagemEnviada = true;
        
        // Limpar variáveis para não mostrar os dados no formulário
        $nome = $email = $telefone = $assunto = $mensagem = '';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contato - InovaTech Store</title>
    <link rel="stylesheet" href="style-clean.css">
</head>
<body>
    <header>
        <div class="container">
            <h1>InovaTech Store</h1>
            <p class="slogan">Entre em contato conosco</p>
        </div>
    </header>

    <nav>
        <div class="container">
            <ul class="nav-menu">
                <li><a href="index.html">Início</a></li>
                <li><a href="computadores.html">Computadores</a></li>
                <li><a href="videogames.html">Videogames</a></li>
                <li><a href="contato.html" class="active">Contato</a></li>
                <li><a href="#" class="cart-btn" onclick="toggleCart()"><i class="fas fa-shopping-cart"></i> Carrinho (<span id="cart-count">0</span>)</a></li>
            </ul>
        </div>
    </nav>

    <main>
        <section class="hero">
            <div class="container">
                <h2>Fale Conosco</h2>
                <p>Estamos aqui para ajudar você com todas as suas necessidades tecnológicas</p>
            </div>
        </section>

        <section class="contact">
            <div class="container">
                <div class="contact-info">
                    <div class="category-grid">
                        <div class="category-card">
                            <div class="card-icon">📞</div>
                            <h3>Telefone</h3>
                            <p><strong>(11) 3456-7890</strong></p>
                            <p>Segunda a Sexta: 9h às 18h<br>
                            Sábado: 9h às 14h</p>
                        </div>
                        
                        <div class="category-card">
                            <div class="card-icon">📧</div>
                            <h3>E-mail</h3>
                            <p><strong>contato@inovatech.com.br</strong></p>
                            <p>Resposta em até 24h<br>
                            Suporte técnico especializado</p>
                        </div>
                        
                        <div class="category-card">
                            <div class="card-icon">📍</div>
                            <h3>Endereço</h3>
                            <p><strong>Rua da Inovação, 123</strong></p>
                            <p>Vila Tecnológica<br>
                            São Paulo - SP, 01234-567</p>
                        </div>
                        
                        <div class="category-card">
                            <div class="card-icon">💬</div>
                            <h3>WhatsApp</h3>
                            <p><strong>(11) 99999-8888</strong></p>
                            <p>Atendimento rápido<br>
                            Segunda a Sábado: 9h às 20h</p>
                        </div>
                    </div>
                </div>

                <?php if ($mensagemEnviada): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <strong>Mensagem enviada com sucesso!</strong>
                        <p>Recebemos sua mensagem e responderemos em breve.</p>
                    </div>
                <?php endif; ?>
                
                <?php if ($erro): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <strong>Erro:</strong> <?= htmlspecialchars($erro) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="contact-form">
                    <h2>Envie sua Mensagem</h2>
                    
                    <div class="form-group">
                        <label for="name">Nome Completo *</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">E-mail *</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Telefone</label>
                        <input type="tel" id="phone" name="phone" placeholder="(11) 99999-9999">
                    </div>
                    
                    <div class="form-group">
                        <label for="subject">Assunto *</label>
                        <select id="subject" name="subject" required>
                            <option value="">Selecione um assunto</option>
                            <option value="duvida-produto">Dúvida sobre Produto</option>
                            <option value="suporte-tecnico">Suporte Técnico</option>
                            <option value="pedido">Status do Pedido</option>
                            <option value="orcamento">Orçamento</option>
                            <option value="reclamacao">Reclamação</option>
                            <option value="sugestao">Sugestão</option>
                            <option value="outro">Outro</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="message">Mensagem *</label>
                        <textarea id="message" name="message" required placeholder="Digite sua mensagem aqui..."></textarea>
                    </div>
                    
                    <button type="submit" name="enviar_mensagem" class="btn">
                        <i class="fas fa-paper-plane"></i> Enviar Mensagem
                    </button>
                </form>
            </div>
        </section>

        <section class="faq">
            <div class="container">
                <h2>Perguntas Frequentes</h2>
                <div class="category-grid">
                    <div class="category-card">
                        <h3>🚚 Entrega</h3>
                        <p><strong>Qual o prazo de entrega?</strong></p>
                        <p>Para São Paulo: 1-2 dias úteis<br>
                        Para outras capitais: 3-5 dias úteis<br>
                        Interior: 5-10 dias úteis</p>
                    </div>
                    
                    <div class="category-card">
                        <h3>💳 Pagamento</h3>
                        <p><strong>Formas de pagamento?</strong></p>
                        <p>• Cartão de crédito (até 12x)<br>
                        • Cartão de débito<br>
                        • PIX (5% desconto)<br>
                        • Boleto bancário</p>
                    </div>
                    
                    <div class="category-card">
                        <h3>🔄 Garantia</h3>
                        <p><strong>Qual a garantia dos produtos?</strong></p>
                        <p>• Computadores: 1 ano<br>
                        • Consoles: 1 ano<br>
                        • Acessórios: 6 meses<br>
                        • Jogos: 7 dias (troca)</p>
                    </div>
                    
                    <div class="category-card">
                        <h3>🔧 Suporte</h3>
                        <p><strong>Oferecem suporte técnico?</strong></p>
                        <p>Sim! Temos equipe especializada para:<br>
                        • Configuração de produtos<br>
                        • Solução de problemas<br>
                        • Manutenção preventiva</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="categories">
            <div class="container">
                <h2>Nossas Categorias</h2>
                <div class="category-grid">
                    <div class="category-card">
                        <div class="card-icon">💻</div>
                        <h3>Computadores</h3>
                        <p>PCs, notebooks e workstations</p>
                        <a href="computadores.html" class="btn">Ver Computadores</a>
                    </div>
                    
                    <div class="category-card">
                        <div class="card-icon">🎮</div>
                        <h3>Videogames</h3>
                        <p>Consoles, jogos e acessórios</p>
                        <a href="videogames.html" class="btn">Ver Videogames</a>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Carrinho Modal -->
    <div id="cart-modal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="toggleCart()">&times;</span>
            <h2>Carrinho de Compras</h2>
            <div id="cart-items"></div>
            <div class="cart-total">
                <strong>Total: R$ <span id="cart-total">0,00</span></strong>
            </div>
            <button class="btn checkout-btn">Finalizar Compra</button>
        </div>
    </div>

    <footer>
        <div class="container">
            <p>&copy; 2024 InovaTech Store. Todos os direitos reservados.</p>
            <p>Desenvolvido com ❤️ para tecnologia</p>
        </div>
    </footer>

    <script src="script.js"></script>
</body>
</html>