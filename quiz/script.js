// Quiz avançado com timer, progresso, navegação por teclado e tela de resultado
(function() {
  const quizData = [
    { question: "Quem construiu a arca para sobreviver ao dilúvio?", options: ["Moisés", "Abraão", "Noé", "Davi"], correct: 2 },
    { question: "Quem foi lançado na cova dos leões?", options: ["Daniel", "Jonas", "José", "Paulo"], correct: 0 },
    { question: "Quem foi engolido por um grande peixe?", options: ["Pedro", "Jonas", "Paulo", "Elias"], correct: 1 },
    { question: "Qual o primeiro livro da Bíblia?", options: ["Êxodo", "Salmos", "Gênesis", "Levítico"], correct: 2 },
    { question: "Quem traiu Jesus por 30 moedas de prata?", options: ["Pedro", "Judas Iscariotes", "Tomé", "Pilatos"], correct: 1 }
  ];

  // Elements
  const questionEl = document.getElementById('question');
  const optionsEl = document.getElementById('options');
  const nextBtn = document.getElementById('nextBtn');
  const skipBtn = document.getElementById('skipBtn');
  const feedbackEl = document.getElementById('feedback');
  const timerEl = document.getElementById('timer');
  const progressFill = document.getElementById('progressFill');
  const questionCounter = document.getElementById('questionCounter');
  const scoreView = document.getElementById('scoreView');
  const scoreText = document.getElementById('scoreText');
  const scoreIcon = document.getElementById('scoreIcon');
  const restartBtn = document.getElementById('restartBtn');

  // State
  let current = 0;
  let score = 0;
  const total = quizData.length;
  let selectedIndex = null;
  let locked = false;
  let timerId = null;
  const TIME_PER_QUESTION = 20; // seconds
  let timeLeft = TIME_PER_QUESTION;

  function startQuiz() {
    current = 0;
    score = 0;
    selectedIndex = null;
    locked = false;
    scoreView.classList.add('hidden');
    document.querySelector('.question-card').style.display = '';
    document.querySelector('.quiz-controls').style.display = '';
    feedbackEl.textContent = '';
    updateProgress();
    loadQuestion();
  }

  function updateProgress() {
    const percent = Math.round((current / total) * 100);
    progressFill.style.width = percent + '%';
    questionCounter.textContent = `Pergunta ${Math.min(current + 1, total)}/${total}`;
  }

  function loadQuestion() {
    clearTimer();
    timeLeft = TIME_PER_QUESTION;
    updateTimerUI();
    startTimer();

    const card = document.querySelector('.question-card');
    card.classList.remove('card-in');

    const q = quizData[current];
    questionEl.textContent = q.question;
    optionsEl.innerHTML = '';
    feedbackEl.textContent = '';
    nextBtn.disabled = true;
    selectedIndex = null;
    locked = false;

    q.options.forEach((opt, idx) => {
      const div = document.createElement('div');
      div.className = 'option';
      div.setAttribute('tabindex', '0');
      div.innerHTML = `<span>${opt}</span><span class=\"key-hint\">${idx + 1}</span>`;
      div.addEventListener('click', (e) => { ripple(e, div); selectAnswer(idx, div); });
      div.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          selectAnswer(idx, div);
        }
      });
      optionsEl.appendChild(div);
    });

    requestAnimationFrame(() => card.classList.add('card-in'));
  }

  function selectAnswer(index, node) {
    if (locked) return;

    locked = true;
    selectedIndex = index;
    nextBtn.disabled = false;

    const correctIndex = quizData[current].correct;
    const optionNodes = optionsEl.querySelectorAll('.option');

    optionNodes.forEach((opt, i) => {
      opt.classList.add('disabled');
      if (i === correctIndex) opt.classList.add('correct');
      if (i === index && i !== correctIndex) opt.classList.add('wrong');
    });

    if (index === correctIndex) {
      score++;
      feedbackEl.textContent = 'Correto! ✅';
    } else {
      feedbackEl.textContent = `Resposta correta: ${quizData[current].options[correctIndex]}`;
    }

    clearTimer();
  }

  function nextQuestion() {
    if (!locked) return; // exige seleção ou tempo esgotado
    const card = document.querySelector('.question-card');
    card.classList.add('card-out');
    setTimeout(() => {
      card.classList.remove('card-out');
      current++;
      if (current < total) {
        updateProgress();
        loadQuestion();
      } else {
        showResults();
      }
    }, 220);
  }

  function skipQuestion() {
    if (locked) return; // só permite pular enquanto está aberta
    // marca como errada e permite avançar
    selectAnswer(-1, null);
  }

  function showResults() {
    clearTimer();
    updateProgress(); // deve ficar em 100%
    document.querySelector('.question-card').style.display = 'none';
    document.querySelector('.quiz-controls').style.display = 'none';
    scoreView.classList.remove('hidden');

    const pct = Math.round((score / total) * 100);
    let icon = '🎯';
    if (pct >= 80) icon = '🏆';
    else if (pct >= 60) icon = '🥇';
    else if (pct >= 40) icon = '🥈';
    else icon = '🥉';

    scoreIcon.textContent = icon;
    scoreText.textContent = `Você acertou ${score} de ${total} (${pct}%).`;

    // Atualiza anel de progresso
    const ring = document.getElementById('scoreRing');
    const percentText = document.getElementById('scorePercent');
    ring.style.setProperty('--pct', pct);
    percentText.textContent = pct + '%';

    // Confete pra pontuação alta
    if (pct >= 80) { launchConfetti(); }
  }

  function startTimer() {
    timerId = setInterval(() => {
      timeLeft--;
      updateTimerUI();
      // alerta visual quando estiver baixo
      const timerWrap = document.querySelector('.timer');
      if (timeLeft <= 5) timerWrap.classList.add('low'); else timerWrap.classList.remove('low');
      if (timeLeft <= 0) {
        clearTimer();
        // tempo esgotado: revela correta e permite avançar
        const correctIndex = quizData[current].correct;
        optionsEl.querySelectorAll('.option').forEach((opt, i) => {
          opt.classList.add('disabled');
          if (i === correctIndex) opt.classList.add('correct');
        });
        feedbackEl.textContent = 'Tempo esgotado ⏰';
        locked = true;
        nextBtn.disabled = false;
      }
    }, 1000);
  }

  function clearTimer() {
    if (timerId) clearInterval(timerId);
    timerId = null;
  }

  function updateTimerUI() {
    timerEl.textContent = timeLeft;
  }

  function onKeyDown(e) {
    if (scoreView && !scoreView.classList.contains('hidden')) return;
    const key = e.key;
    if (['1','2','3','4'].includes(key)) {
      const idx = parseInt(key, 10) - 1;
      const node = optionsEl.querySelectorAll('.option')[idx];
      if (node) selectAnswer(idx, node);
    }
    if (key === 'Enter') {
      if (!nextBtn.disabled) nextQuestion();
    }
  }

  // Ripple util
  function ripple(e, target) {
    const rect = target.getBoundingClientRect();
    const circle = document.createElement('span');
    const d = Math.max(rect.width, rect.height);
    circle.style.width = circle.style.height = d + 'px';
    circle.style.left = e.clientX - rect.left - d/2 + 'px';
    circle.style.top = e.clientY - rect.top - d/2 + 'px';
    circle.className = 'ripple';
    target.appendChild(circle);
    setTimeout(() => circle.remove(), 600);
  }

  // Simples confete
  function launchConfetti() {
    for (let i = 0; i < 50; i++) {
      const c = document.createElement('div');
      c.style.position = 'fixed';
      c.style.width = '8px';
      c.style.height = '8px';
      c.style.background = `hsl(${Math.random()*360},80%,60%)`;
      c.style.left = Math.random()*100 + 'vw';
      c.style.top = '-10px';
      c.style.borderRadius = '2px';
      c.style.opacity = '0.9';
      c.style.transform = `rotate(${Math.random()*360}deg)`;
      c.style.zIndex = '9999';
      const duration = 2000 + Math.random()*2000;
      c.style.transition = `transform ${duration}ms linear, top ${duration}ms linear, opacity 300ms ease`;
      document.body.appendChild(c);
      setTimeout(() => {
        c.style.top = '110vh';
        c.style.transform = `translateX(${(Math.random()*2-1)*200}px) rotate(${Math.random()*720}deg)`;
      }, 10);
      setTimeout(() => c.remove(), duration + 500);
    }
  }

  // Event bindings
  nextBtn.addEventListener('click', nextQuestion);
  skipBtn.addEventListener('click', skipQuestion);
  restartBtn.addEventListener('click', startQuiz);
  document.addEventListener('keydown', onKeyDown);

  // Init
  startQuiz();
})();
