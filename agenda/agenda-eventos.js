// Variáveis globais
let eventos = JSON.parse(localStorage.getItem('agendaEventos')) || [];
let mesAtual = new Date().getMonth();
let anoAtual = new Date().getFullYear();
let eventoEditando = null;
let eventoParaExcluir = null;

// Arrays para nomes de meses e dias da semana
const meses = [
    'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
    'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'
];

const diasSemana = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];

// Inicialização quando a página carrega
document.addEventListener('DOMContentLoaded', function() {
    renderizarCalendario();
    renderizarEventos();
    configurarNotificacoes();
});

// Função para salvar eventos no localStorage
function salvarEventos() {
    localStorage.setItem('agendaEventos', JSON.stringify(eventos));
}

// Função para gerar ID único
function gerarId() {
    return Date.now().toString(36) + Math.random().toString(36).substr(2);
}

// Função para renderizar o calendário
function renderizarCalendario() {
    const calendario = document.getElementById('calendario');
    const mesAnoElement = document.getElementById('mesAno');
    
    // Atualizar o título do mês/ano
    mesAnoElement.textContent = `${meses[mesAtual]} ${anoAtual}`;
    
    // Limpar calendário
    calendario.innerHTML = '';
    
    // Adicionar cabeçalho com dias da semana
    diasSemana.forEach(dia => {
        const diaElement = document.createElement('div');
        diaElement.className = 'dia-semana';
        diaElement.textContent = dia;
        calendario.appendChild(diaElement);
    });
    
    // Calcular primeiro dia do mês e total de dias
    const primeiroDia = new Date(anoAtual, mesAtual, 1).getDay();
    const ultimoDia = new Date(anoAtual, mesAtual + 1, 0).getDate();
    const ultimoDiaMesAnterior = new Date(anoAtual, mesAtual, 0).getDate();
    
    // Dias do mês anterior
    for (let i = primeiroDia - 1; i >= 0; i--) {
        const dia = ultimoDiaMesAnterior - i;
        const diaElement = criarElementoDia(dia, true);
        calendario.appendChild(diaElement);
    }
    
    // Dias do mês atual
    for (let dia = 1; dia <= ultimoDia; dia++) {
        const diaElement = criarElementoDia(dia, false);
        calendario.appendChild(diaElement);
    }
    
    // Completar com dias do próximo mês
    const diasRestantes = 42 - (primeiroDia + ultimoDia);
    for (let dia = 1; dia <= diasRestantes; dia++) {
        const diaElement = criarElementoDia(dia, true);
        calendario.appendChild(diaElement);
    }
}

// Função para criar elemento de dia no calendário
function criarElementoDia(numeroDia, outroMes) {
    const diaElement = document.createElement('div');
    diaElement.className = 'dia';
    diaElement.textContent = numeroDia;
    
    if (outroMes) {
        diaElement.classList.add('outros-mes');
    } else {
        // Verificar se é o dia atual
        const hoje = new Date();
        if (hoje.getDate() === numeroDia && 
            hoje.getMonth() === mesAtual && 
            hoje.getFullYear() === anoAtual) {
            diaElement.classList.add('hoje');
        }
        
        // Verificar se tem eventos neste dia
        const dataString = `${anoAtual}-${String(mesAtual + 1).padStart(2, '0')}-${String(numeroDia).padStart(2, '0')}`;
        const eventosNoDia = eventos.filter(evento => evento.data === dataString);
        
        if (eventosNoDia.length > 0) {
            diaElement.classList.add('com-eventos');
            
            // Adicionar indicadores de eventos
            eventosNoDia.slice(0, 3).forEach(() => {
                const indicator = document.createElement('div');
                indicator.className = 'evento-indicator';
                diaElement.appendChild(indicator);
            });
        }
        
        // Adicionar evento de clique
        diaElement.addEventListener('click', () => {
            abrirModal(dataString);
        });
    }
    
    return diaElement;
}

// Função para navegar entre meses
function navegarMes(direcao) {
    mesAtual += direcao;
    
    if (mesAtual > 11) {
        mesAtual = 0;
        anoAtual++;
    } else if (mesAtual < 0) {
        mesAtual = 11;
        anoAtual--;
    }
    
    renderizarCalendario();
}

// Função para renderizar lista de eventos
function renderizarEventos() {
    const listaEventos = document.getElementById('listaEventos');
    
    // Filtrar eventos futuros e ordenar por data
    const agora = new Date();
    const eventosProximos = eventos
        .filter(evento => {
            const dataEvento = new Date(evento.data + 'T' + evento.hora);
            return dataEvento >= agora;
        })
        .sort((a, b) => {
            const dataA = new Date(a.data + 'T' + a.hora);
            const dataB = new Date(b.data + 'T' + b.hora);
            return dataA - dataB;
        })
        .slice(0, 10); // Mostrar apenas os próximos 10 eventos
    
    if (eventosProximos.length === 0) {
        listaEventos.innerHTML = '<div class="sem-eventos">Nenhum evento próximo encontrado</div>';
        return;
    }
    
    listaEventos.innerHTML = eventosProximos.map(evento => {
        const dataFormatada = formatarData(evento.data);
        const horaFormatada = evento.hora;
        
        return `
            <div class="evento-item">
                <div class="evento-header">
                    <div class="evento-titulo">${evento.titulo}</div>
                    <div class="evento-acoes">
                        <button class="btn-acao btn-editar" onclick="editarEvento('${evento.id}')">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn-acao btn-excluir" onclick="excluirEvento('${evento.id}')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                <div class="evento-info">
                    <span><i class="fas fa-calendar"></i> ${dataFormatada}</span>
                    <span><i class="fas fa-clock"></i> ${horaFormatada}</span>
                    ${evento.local ? `<span><i class="fas fa-map-marker-alt"></i> ${evento.local}</span>` : ''}
                    <span class="categoria-badge categoria-${evento.categoria}">${capitalizarPrimeira(evento.categoria)}</span>
                </div>
                ${evento.descricao ? `<div class="evento-descricao">${evento.descricao}</div>` : ''}
            </div>
        `;
    }).join('');
}

// Função para formatar data
function formatarData(dataString) {
    const data = new Date(dataString + 'T00:00:00');
    const dia = data.getDate().toString().padStart(2, '0');
    const mes = (data.getMonth() + 1).toString().padStart(2, '0');
    const ano = data.getFullYear();
    return `${dia}/${mes}/${ano}`;
}

// Função para capitalizar primeira letra
function capitalizarPrimeira(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
}

// Função para abrir modal
function abrirModal(data = '') {
    const modal = document.getElementById('modalEvento');
    const modalTitulo = document.getElementById('modalTitulo');
    const form = document.getElementById('formEvento');
    
    if (eventoEditando) {
        modalTitulo.textContent = 'Editar Evento';
        preencherFormulario(eventoEditando);
    } else {
        modalTitulo.textContent = 'Novo Evento';
        form.reset();
        if (data) {
            document.getElementById('dataEvento').value = data;
        }
    }
    
    modal.style.display = 'block';
}

// Função para fechar modal
function fecharModal() {
    const modal = document.getElementById('modalEvento');
    modal.style.display = 'none';
    eventoEditando = null;
    document.getElementById('formEvento').reset();
}

// Função para preencher formulário com dados do evento
function preencherFormulario(evento) {
    document.getElementById('tituloEvento').value = evento.titulo;
    document.getElementById('descricaoEvento').value = evento.descricao || '';
    document.getElementById('dataEvento').value = evento.data;
    document.getElementById('horaEvento').value = evento.hora;
    document.getElementById('categoriaEvento').value = evento.categoria;
    document.getElementById('localEvento').value = evento.local || '';
    document.getElementById('lembrarEvento').checked = evento.lembrete || false;
}

// Função para salvar evento
function salvarEvento(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    
    const evento = {
        id: eventoEditando ? eventoEditando.id : gerarId(),
        titulo: document.getElementById('tituloEvento').value,
        descricao: document.getElementById('descricaoEvento').value,
        data: document.getElementById('dataEvento').value,
        hora: document.getElementById('horaEvento').value,
        categoria: document.getElementById('categoriaEvento').value,
        local: document.getElementById('localEvento').value,
        lembrete: document.getElementById('lembrarEvento').checked,
        criadoEm: eventoEditando ? eventoEditando.criadoEm : new Date().toISOString()
    };
    
    if (eventoEditando) {
        // Atualizar evento existente
        const indice = eventos.findIndex(e => e.id === eventoEditando.id);
        eventos[indice] = evento;
    } else {
        // Adicionar novo evento
        eventos.push(evento);
    }
    
    salvarEventos();
    fecharModal();
    renderizarCalendario();
    renderizarEventos();
    
    // Mostrar mensagem de sucesso
    mostrarMensagem('Evento salvo com sucesso!', 'sucesso');
}

// Função para editar evento
function editarEvento(id) {
    eventoEditando = eventos.find(evento => evento.id === id);
    if (eventoEditando) {
        abrirModal();
    }
}

// Função para excluir evento
function excluirEvento(id) {
    eventoParaExcluir = id;
    const modal = document.getElementById('modalConfirmacao');
    modal.style.display = 'block';
}

// Função para confirmar exclusão
function confirmarExclusao() {
    if (eventoParaExcluir) {
        eventos = eventos.filter(evento => evento.id !== eventoParaExcluir);
        salvarEventos();
        fecharModalConfirmacao();
        renderizarCalendario();
        renderizarEventos();
        mostrarMensagem('Evento excluído com sucesso!', 'sucesso');
        eventoParaExcluir = null;
    }
}

// Função para fechar modal de confirmação
function fecharModalConfirmacao() {
    const modal = document.getElementById('modalConfirmacao');
    modal.style.display = 'none';
    eventoParaExcluir = null;
}

// Função para filtrar eventos
function filtrarEventos() {
    const filtroData = document.getElementById('filtroData').value;
    const filtroCategoria = document.getElementById('filtroCategoria').value;
    
    let eventosFiltrados = eventos;
    
    // Filtrar por data
    if (filtroData) {
        eventosFiltrados = eventosFiltrados.filter(evento => evento.data === filtroData);
    }
    
    // Filtrar por categoria
    if (filtroCategoria) {
        eventosFiltrados = eventosFiltrados.filter(evento => evento.categoria === filtroCategoria);
    }
    
    // Renderizar eventos filtrados
    renderizarEventosFiltrados(eventosFiltrados);
}

// Função para renderizar eventos filtrados
function renderizarEventosFiltrados(eventosFiltrados) {
    const listaEventos = document.getElementById('listaEventos');
    
    if (eventosFiltrados.length === 0) {
        listaEventos.innerHTML = '<div class="sem-eventos">Nenhum evento encontrado com os filtros aplicados</div>';
        return;
    }
    
    // Ordenar por data
    const eventosOrdenados = eventosFiltrados.sort((a, b) => {
        const dataA = new Date(a.data + 'T' + a.hora);
        const dataB = new Date(b.data + 'T' + b.hora);
        return dataA - dataB;
    });
    
    listaEventos.innerHTML = eventosOrdenados.map(evento => {
        const dataFormatada = formatarData(evento.data);
        const horaFormatada = evento.hora;
        
        return `
            <div class="evento-item">
                <div class="evento-header">
                    <div class="evento-titulo">${evento.titulo}</div>
                    <div class="evento-acoes">
                        <button class="btn-acao btn-editar" onclick="editarEvento('${evento.id}')">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn-acao btn-excluir" onclick="excluirEvento('${evento.id}')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                <div class="evento-info">
                    <span><i class="fas fa-calendar"></i> ${dataFormatada}</span>
                    <span><i class="fas fa-clock"></i> ${horaFormatada}</span>
                    ${evento.local ? `<span><i class="fas fa-map-marker-alt"></i> ${evento.local}</span>` : ''}
                    <span class="categoria-badge categoria-${evento.categoria}">${capitalizarPrimeira(evento.categoria)}</span>
                </div>
                ${evento.descricao ? `<div class="evento-descricao">${evento.descricao}</div>` : ''}
            </div>
        `;
    }).join('');
}

// Função para limpar filtros
function limparFiltros() {
    document.getElementById('filtroData').value = '';
    document.getElementById('filtroCategoria').value = '';
    renderizarEventos();
}

// Função para mostrar mensagem
function mostrarMensagem(texto, tipo = 'info') {
    // Remover mensagem anterior se existir
    const mensagemExistente = document.querySelector('.mensagem');
    if (mensagemExistente) {
        mensagemExistente.remove();
    }
    
    const mensagem = document.createElement('div');
    mensagem.className = `mensagem mensagem-${tipo}`;
    mensagem.textContent = texto;
    
    // Estilos da mensagem
    mensagem.style.position = 'fixed';
    mensagem.style.top = '20px';
    mensagem.style.right = '20px';
    mensagem.style.padding = '15px 20px';
    mensagem.style.borderRadius = '8px';
    mensagem.style.color = 'white';
    mensagem.style.fontWeight = '500';
    mensagem.style.zIndex = '10000';
    mensagem.style.boxShadow = '0 4px 20px rgba(0, 0, 0, 0.2)';
    mensagem.style.transition = 'all 0.3s ease';
    
    if (tipo === 'sucesso') {
        mensagem.style.background = '#4caf50';
    } else if (tipo === 'erro') {
        mensagem.style.background = '#f44336';
    } else {
        mensagem.style.background = '#2196f3';
    }
    
    document.body.appendChild(mensagem);
    
    // Animar entrada
    setTimeout(() => {
        mensagem.style.transform = 'translateX(0)';
    }, 10);
    
    // Remover após 3 segundos
    setTimeout(() => {
        mensagem.style.transform = 'translateX(100%)';
        setTimeout(() => {
            if (mensagem.parentNode) {
                mensagem.parentNode.removeChild(mensagem);
            }
        }, 300);
    }, 3000);
}

// Função para configurar notificações
function configurarNotificacoes() {
    // Verificar se o navegador suporta notificações
    if ('Notification' in window) {
        // Pedir permissão se não foi concedida
        if (Notification.permission === 'default') {
            Notification.requestPermission();
        }
        
        // Verificar eventos com lembrete a cada minuto
        setInterval(verificarLembretes, 60000);
    }
}

// Função para verificar lembretes
function verificarLembretes() {
    const agora = new Date();
    
    eventos.forEach(evento => {
        if (evento.lembrete) {
            const dataEvento = new Date(evento.data + 'T' + evento.hora);
            const tempoRestante = dataEvento.getTime() - agora.getTime();
            
            // Notificar 15 minutos antes (900000 ms = 15 min)
            if (tempoRestante <= 900000 && tempoRestante > 840000) {
                if (Notification.permission === 'granted') {
                    new Notification(`Lembrete: ${evento.titulo}`, {
                        body: `Evento em 15 minutos - ${evento.hora}`,
                        icon: 'data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="%23667eea"><path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM7 10h5v5H7z"/></svg>'
                    });
                }
            }
        }
    });
}

// Event listeners para fechar modais ao clicar fora
window.addEventListener('click', function(event) {
    const modalEvento = document.getElementById('modalEvento');
    const modalConfirmacao = document.getElementById('modalConfirmacao');
    
    if (event.target === modalEvento) {
        fecharModal();
    }
    
    if (event.target === modalConfirmacao) {
        fecharModalConfirmacao();
    }
});

// Event listener para tecla ESC
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        const modalEvento = document.getElementById('modalEvento');
        const modalConfirmacao = document.getElementById('modalConfirmacao');
        
        if (modalEvento.style.display === 'block') {
            fecharModal();
        }
        
        if (modalConfirmacao.style.display === 'block') {
            fecharModalConfirmacao();
        }
    }
});

// Funções de exemplo para demonstração
function adicionarEventosExemplo() {
    const eventosExemplo = [
        {
            id: gerarId(),
            titulo: 'Reunião de equipe',
            descricao: 'Reunião semanal da equipe de desenvolvimento',
            data: new Date(Date.now() + 86400000).toISOString().split('T')[0], // Amanhã
            hora: '14:00',
            categoria: 'reuniao',
            local: 'Sala de reuniões',
            lembrete: true,
            criadoEm: new Date().toISOString()
        },
        {
            id: gerarId(),
            titulo: 'Apresentação do projeto',
            descricao: 'Apresentação do novo sistema para o cliente',
            data: new Date(Date.now() + 2 * 86400000).toISOString().split('T')[0], // Depois de amanhã
            hora: '10:30',
            categoria: 'trabalho',
            local: 'Auditório principal',
            lembrete: true,
            criadoEm: new Date().toISOString()
        }
    ];
    
    // Adicionar apenas se não houver eventos
    if (eventos.length === 0) {
        eventos = eventosExemplo;
        salvarEventos();
        renderizarCalendario();
        renderizarEventos();
    }
}