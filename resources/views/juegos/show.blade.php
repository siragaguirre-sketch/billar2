<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Bola 8 · {{ $game['playerX'] }}</title>
    @vite(['resources/js/app.js'])
    <style>
        :root { color-scheme: dark; font-family: Inter, ui-sans-serif, system-ui, sans-serif; background: #101713; color: #f2f3e9; }
        * { box-sizing: border-box; }
        body { min-height: 100vh; margin: 0; padding: 22px; background: radial-gradient(ellipse at top, #233c2c, #101713 68%); }
        main { max-width: 1120px; margin: 0 auto; }
        header { display: flex; align-items: center; justify-content: space-between; gap: 15px; margin-bottom: 18px; }
        .brand { color: #d5af62; font-size: .72rem; font-weight: 800; letter-spacing: .22em; text-transform: uppercase; }
        h1 { margin: 5px 0 0; font: 2rem Georgia, serif; }
        a { color: #d5af62; }
        .top-link { color: #b5c2b7; font-size: .9rem; text-decoration: none; }
        .layout { display: grid; grid-template-columns: minmax(0, 1fr) 290px; gap: 16px; align-items: start; }
        .card { background: #18231d; border: 1px solid #34473a; border-radius: 15px; padding: 17px; box-shadow: 0 15px 45px #0003; }
        .table-card { padding: 14px; }
        .players { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 13px; }
        .player { border: 1px solid #3d5243; border-radius: 9px; padding: 10px 12px; background: #202e25; min-width: 0; }
        .player.current { border-color: #d5af62; box-shadow: 0 0 0 1px #d5af6255; }
        .player small { display: block; color: #aab7ac; text-transform: uppercase; font-size: .67rem; letter-spacing: .12em; }
        .player strong { display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; margin-top: 4px; }
        .table-wrap { position: relative; overflow: hidden; border-radius: 10px; border: 8px solid #624a31; box-shadow: inset 0 0 0 3px #c09a58, 0 8px 20px #0008; background: #17633f; }
        canvas { display: block; width: 100%; aspect-ratio: 2 / 1; touch-action: none; cursor: crosshair; }
        .status { color: #e6d7ab; min-height: 23px; margin: 13px 2px 3px; font-size: .9rem; }
        .controls { display: grid; grid-template-columns: 1fr auto; gap: 12px; align-items: end; margin-top: 12px; }
        .controls label { display: block; color: #b8c3b8; font-size: .8rem; margin-bottom: 5px; }
        input[type=range] { width: 100%; accent-color: #d5af62; }
        button { border: 0; border-radius: 8px; background: #d5af62; color: #172018; padding: 11px 16px; font: inherit; font-weight: 800; cursor: pointer; }
        button:hover { background: #e7c77f; } button:disabled { opacity: .45; cursor: not-allowed; }
        .side { display: grid; gap: 14px; }
        .side h2 { margin: 0 0 10px; font: 1.15rem Georgia, serif; }
        .small { color: #aab7ac; font-size: .82rem; line-height: 1.5; }
        .invite { display: flex; gap: 7px; margin-top: 10px; }
        .invite input, .join-form input, .chat-form input { min-width: 0; flex: 1; background: #101813; border: 1px solid #435649; color: white; border-radius: 7px; padding: 9px; font: inherit; }
        .invite button { padding: 9px 12px; font-size: .8rem; }
        .join-form { display: flex; gap: 8px; margin-top: 12px; }
        .join-form button { padding: 9px 12px; white-space: nowrap; }
        .join-notice { border-color: #8d7440; }
        #chat-messages { height: 170px; overflow-y: auto; display: flex; flex-direction: column; gap: 7px; padding: 8px; background: #101813; border-radius: 8px; }
        .message { align-self: flex-start; max-width: 92%; background: #29392e; border-radius: 8px; padding: 7px 9px; font-size: .83rem; overflow-wrap: anywhere; }
        .message.mine { align-self: flex-end; background: #3c3827; }
        .message small { display: block; color: #c6b77f; margin-bottom: 3px; }
        .chat-form { display: flex; gap: 7px; margin-top: 8px; }
        .chat-form button { padding: 8px 12px; }
        .toast { min-height: 18px; color: #d9bd75; font-size: .8rem; margin-top: 8px; }
        #sound-toggle { border: 0; border-radius: 8px; background: #202e25; color: #d5af62; padding: 11px 14px; font: inherit; font-weight: 800; cursor: pointer; font-size: 1.1rem; line-height: 1; }
        #sound-toggle:hover { background: #2c3e32; } #sound-toggle.muted { opacity: .5; }
        #game-toast { position: absolute; top: 14px; left: 50%; transform: translateX(-50%); padding: 8px 16px; border-radius: 8px; font-weight: 700; font-size: .9rem; pointer-events: none; z-index: 40; opacity: 0; transition: opacity .25s; border: 1px solid rgba(255,255,255,.15); }
        #game-toast.show { opacity: 1; }
        #game-toast.pocket { background: #17633f; color: #e6d7ab; border-color: #d5af62; }
        #game-toast.scratch { background: #502b25; color: #ffd1c5; border-color: #c73e36; }
        #game-toast.win { background: #2c3e32; color: #d5af62; border-color: #e7c77f; }
        #turn-timer { position: absolute; top: 14px; right: 14px; padding: 6px 12px; border-radius: 8px; background: rgba(0,0,0,.5); color: #e7c77f; font-weight: 800; font-size: .95rem; border: 1px solid #d5af62; z-index: 40; display: none; }
        #turn-timer.warn { color: #c73e36; border-color: #c73e36; animation: blink .6s infinite; }
        @keyframes blink { 0%,100% { opacity: 1; } 50% { opacity: .45; } }
        @keyframes shake { 0%,100% { transform: translateX(0); } 20%,60% { transform: translateX(-8px); } 40%,80% { transform: translateX(8px); } }
        @keyframes countdown-pulse { 0% { transform: scale(1); opacity: 1; } 50% { transform: scale(1.3); opacity: .8; } 100% { transform: scale(1); opacity: 1; } }
        .victory-overlay { position: fixed; inset: 0; background: rgba(0,0,0,.85); z-index: 100; display: none; flex-direction: column; align-items: center; justify-content: center; color: #f2f3e9; text-align: center; padding: 24px; }
        .victory-overlay.show { display: flex; }
        .victory-overlay h2 { font: 3rem Georgia, serif; margin: 0 0 8px; color: #d5af62; text-shadow: 0 2px 4px #0006; }
        .victory-overlay p { font-size: 1.2rem; margin: 0 0 22px; color: #e6d7ab; }
        .victory-overlay .btn-row { display: flex; gap: 12px; }
        .victory-overlay button { border: 0; border-radius: 8px; background: #d5af62; color: #172018; padding: 12px 24px; font: inherit; font-weight: 800; cursor: pointer; min-width: 140px; }
        .victory-overlay button:hover { background: #e7c77f; }
        .countdown-overlay { position: fixed; inset: 0; background: rgba(0,0,0,.7); z-index: 95; display: none; align-items: center; justify-content: center; color: #d5af62; font: 5rem Georgia, serif; text-shadow: 0 2px 6px #0006; }
        .countdown-overlay.show { display: flex; }
        .countdown-overlay .go { color: #2ecc71; }
        .connection-indicator { position: fixed; bottom: 14px; right: 14px; padding: 6px 12px; border-radius: 6px; background: rgba(0,0,0,.6); color: #2ecc71; font-size: .75rem; font-weight: 800; border: 1px solid #2ecc71; z-index: 60; display: flex; align-items: center; gap: 6px; }
        .connection-indicator.warn { color: #f1c40f; border-color: #f1c40f; }
        .connection-indicator.dot { width: 8px; height: 8px; border-radius: 50%; background: currentColor; animation: ping 1.5s infinite; }
        @keyframes ping { 0% { transform: scale(1); opacity: 1; } 100% { transform: scale(2.5); opacity: 0; } }
        .shake { animation: shake .4s ease-in-out; }
        .score-badge { display: block; margin-top: 6px; font-size: 1.15rem; font-weight: 800; color: #d5af62; }
        .pause-btn { border: 0; border-radius: 8px; background: #202e25; color: #d5af62; padding: 10px 14px; font: inherit; font-weight: 800; cursor: pointer; font-size: 1.2rem; line-height: 1; }
        .pause-btn:hover { background: #2c3e32; } .pause-btn.active { background: #2ecc71; color: #172018; }
        .pause-overlay { position: fixed; inset: 0; background: rgba(0,0,0,.8); z-index: 105; display: none; align-items: center; justify-content: center; flex-direction: column; gap: 18px; color: #f2f3e9; text-align: center; padding: 24px; }
        .pause-overlay.show { display: flex; }
        .pause-overlay h2 { margin: 0; font: 2.5rem Georgia, serif; color: #d5af62; }
        .pause-overlay button { border: 0; border-radius: 8px; background: #d5af62; color: #172018; padding: 12px 26px; font: inherit; font-weight: 800; cursor: pointer; min-width: 150px; }
        .pause-overlay button:hover { background: #e7c77f; }
        [hidden] { display: none !important; }
        .volume-row { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-top: 8px; }
        .volume-row label { display: flex; align-items: center; gap: 6px; font-size: .75rem; color: #b8c3b8; margin: 0; }
        .volume-row input[type=range] { width: 100%; accent-color: #d5af62; cursor: pointer; }
        @media (max-width: 768px) {
            .volume-row { grid-template-columns: 1fr; gap: 6px; }
        }
        @media (max-width: 768px) {
            body { padding: 10px; }
            header { flex-direction: column; align-items: flex-start; gap: 8px; }
            .layout { grid-template-columns: 1fr; gap: 12px; }
            .table-wrap { border-width: 4px; padding: 8px; }
            .controls { grid-template-columns: 1fr; gap: 8px; margin-top: 10px; }
            .controls button { width: 100%; padding: 14px; font-size: 1rem; }
            .controls > div { width: 100%; }
            #turn-timer { top: 8px; right: 8px; font-size: .85rem; padding: 4px 8px; }
            #sound-toggle { width: 44px; height: 44px; padding: 0; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; }
            .victory-overlay h2 { font-size: 2rem; }
            .victory-overlay .btn-row { flex-direction: column; gap: 10px; }
            .victory-overlay button { width: 100%; }
            .connection-indicator { bottom: 8px; right: 8px; font-size: .7rem; padding: 5px 10px; }
            .side { grid-template-columns: 1fr; }
            .players { grid-template-columns: 1fr; gap: 8px; margin-bottom: 10px; }
        }
    </style>
</head>
<body>
<main>
    <header>
        <div><div class="brand">Partida en vivo</div><h1>Bola 8</h1></div>
        <a class="top-link" href="{{ route('juegos.index') }}">← Volver al lobby</a>
    </header>
    <div class="layout">
        <section class="card table-card">
            <div class="players">
                <div class="player" id="player-x"><small>Jugador X · lisas</small><strong id="name-x">{{ $game['playerX'] }}</strong><div class="score-badge" id="score-x">0</div></div>
                <div class="player" id="player-o"><small>{{ $game['mode'] === 'solo' ? 'Computadora' : 'Jugador O · rayadas' }}</small><strong id="name-o">{{ $game['playerO'] ?? 'Esperando rival…' }}</strong><div class="score-badge" id="score-o">0</div></div>
            </div>
            @if ($game['status'] === 'waiting')
                <div class="card join-notice" @if ($role === 'X' || !$isInviteLink) hidden @endif>
                    <strong>Te invitaron a una partida</strong>
                    <p class="small">Escribe tu nombre para ocupar el puesto de rival y empezar.</p>
                    <form class="join-form" method="POST" action="{{ route('juegos.join', $id) }}">
                        @csrf
                        <input name="playerName" maxlength="50" required placeholder="Tu nombre">
                        <button type="submit">Unirme</button>
                    </form>
                    @if ($errors->any()) <p class="small">{{ $errors->first() }}</p> @endif
                </div>
            @endif
            <div class="table-wrap"><canvas id="pool-table" width="1000" height="500" aria-label="Mesa de billar interactiva"></canvas></div>
            <div id="status" class="status" role="status" aria-live="polite"></div>
            <div id="game-toast" class="game-toast" role="alert" aria-live="assertive"></div>
            <div id="turn-timer" class="turn-timer">⏱ <span id="timer-value">20</span></div>
            <div class="controls">
                <div><label for="power">Potencia del tiro: <span id="power-label">55%</span></label><input type="range" id="power" min="10" max="100" value="55"></div>
                <button type="button" id="shoot" disabled>Ejecutar tiro</button>
                <button type="button" id="sound-toggle" title="Sonido/Música">♪</button>
                <div class="volume-row">
                    <label>SFX <span id="sfx-vol-val">80%</span></label><input type="range" id="sfx-vol" min="0" max="100" value="80">
                    <label>Música <span id="music-vol-val">50%</span></label><input type="range" id="music-vol" min="0" max="100" value="50">
                </div>
                <button type="button" id="pause-btn" class="pause-btn" title="Pausa">⏸</button>
            </div>
            <p class="small">Apunta haciendo clic o arrastrando sobre la mesa; ajusta la potencia y ejecuta el tiro.</p>
        </section>
        <aside class="side">
            <section class="card" id="invite-card" @if ($game['mode'] !== 'duel' || $role !== 'X') hidden @endif>
                <h2>Invitar a un rival</h2>
                <p class="small" id="invite-help">{{ $game['status'] === 'waiting' ? 'Copia y comparte este enlace para empezar a jugar.' : 'Tu rival ya se unió a la partida.' }}</p>
                <div class="invite"><input id="invite-link" readonly value="{{ route('juegos.show', ['id' => $id, 'invite' => 1]) }}"><button id="copy-link" type="button">Copiar</button></div>
            </section>
            <section class="card">
                <h2>Reglas de esta mesa</h2>
                <p class="small">Emboca las bolas de tu grupo (lisas o rayadas) y deja la bola 8 para el final. Si embocas una de las tuyas, conservas el turno. La bola blanca se repone tras una falta.</p>
                @if ($game['mode'] === 'solo')
                    <p class="small">Nivel {{ $game['level'] }} de 8</p>
                @else
                    <p class="small">Partida con invitación</p>
                @endif
            </section>
            <section class="card">
                <h2>Chat de la partida</h2>
                <div id="chat-messages" aria-live="polite">
                    @foreach ($game['chat'] as $message)
                        <div class="message"><small>{{ $message['playerName'] }} · {{ $message['time'] }}</small>{{ $message['message'] }}</div>
                    @endforeach
                </div>
                <form id="chat-form" class="chat-form" @if (!$role) hidden @endif><input id="chat-input" maxlength="255" required placeholder="Escribe un mensaje"><button type="submit">Enviar</button></form>
                <p class="small" id="chat-hint" @if ($role) hidden @endif>Únete a la partida para chatear.</p>
                <div id="toast" class="toast" role="status" aria-live="polite"></div>
            </section>
        </aside>
    </div>
</main>

<div id="countdown-overlay" class="countdown-overlay"><div id="countdown-number">3</div></div>
<div id="victory-overlay" class="victory-overlay">
    <h2 id="victory-title">¡Victoria!</h2>
    <p id="victory-text">Ganaste la partida</p>
    <div class="btn-row">
        <button id="victory-lobby" type="button">Volver al lobby</button>
        <button id="victory-rematch" type="button">Revancha</button>
    </div>
</div>
<div id="pause-overlay" class="pause-overlay">
    <h2>Pausa</h2>
    <button id="pause-resume" type="button">Reanudar</button>
</div>
<div id="connection-indicator" class="connection-indicator"><div class="dot"></div><span id="connection-text">Conectado</span></div>

<main>
<script>
    const initialGame = @json($game);
    let localRole = @json($role);
    let seatToken = @json($seatToken);
    const isInviteLink = @json($isInviteLink);
    const seatStorageKey = `pool-seat-${initialGame.id}`;
    if (isInviteLink) {
        sessionStorage.removeItem(seatStorageKey);
        localRole = null;
        seatToken = null;
    } else if (!seatToken) {
        const savedSeat = sessionStorage.getItem(seatStorageKey);
        if (savedSeat) {
            const seat = JSON.parse(savedSeat);
            seatToken = seat.token;
            localRole = seat.role;
        }
    }
    if (seatToken && localRole) {
        sessionStorage.setItem(seatStorageKey, JSON.stringify({ token: seatToken, role: localRole }));
        if (new URLSearchParams(window.location.search).has('seat')) {
            history.replaceState({}, '', window.location.pathname);
        }
    }
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const canvas = document.getElementById('pool-table');
    const context = canvas.getContext('2d');
    const statusElement = document.getElementById('status');
    const shootButton = document.getElementById('shoot');
    const powerInput = document.getElementById('power');
    const powerLabel = document.getElementById('power-label');
    const players = { X: document.getElementById('player-x'), O: document.getElementById('player-o') };
    const pocketCenters = [[45, 45], [500, 39], [955, 45], [45, 455], [500, 461], [955, 455]];
    const ballRadius = 12;
    const colors = ['#fff', '#e5bd35', '#2875bd', '#c73e36', '#713c8e', '#ed7934', '#259466', '#762e28',
        '#181818', '#e5bd35', '#2875bd', '#c73e36', '#713c8e', '#ed7934', '#259466', '#762e28'];
    let game = initialGame;
    let animationBalls = null;
    let queuedGameState = null;
    let aimAngle = 0;
    let aiming = false;
    let animating = false;
    let finishCurrentAnimation = null;
    let pendingLocalIntent = false;
    let aiPending = false;
    const processedShots = new Set();

    /* ---------- Audio Manager ---------- */
    const AUDIO_PATH = '/audio/';
    const sounds = {
        shoot: null,
        hit: null,
        cushion: null,
        pocket: null,
        scratch: null,
        win: null,
        'win-player': null,
        'win-computer': null,
        music: null,
        ping: null,
        click: null,
    };
    let soundEnabled = true;
    let musicEnabled = true;
    let musicStarted = false;
    let sfxVolume = parseFloat(localStorage.getItem('pool-sfx-vol') || '0.8');
    let musicVolume = parseFloat(localStorage.getItem('pool-music-vol') || '0.5');

    function loadSound(name) {
        const audio = new Audio(`${AUDIO_PATH}${name}.wav`);
        audio.preload = 'auto';
        return audio;
    }

    function initSounds() {
        sounds.shoot = loadSound('shoot');
        sounds.hit = loadSound('hit');
        sounds.cushion = loadSound('cushion');
        sounds.pocket = loadSound('pocket');
        sounds.scratch = loadSound('scratch');
        sounds.win = loadSound('win');
        sounds['win-player'] = loadSound('win-player');
        sounds['win-computer'] = loadSound('win-computer');
        sounds.music = loadSound('music');
        sounds.music.loop = true;
        sounds.music.volume = musicVolume;
        sounds.ping = loadSound('ping');
        sounds.click = loadSound('click');
        updateSoundToggle();
    }

    function playSound(name) {
        if (!soundEnabled || !sounds[name]) return;
        const audio = sounds[name];
        audio.volume = sfxVolume;
        audio.currentTime = 0;
        audio.play().catch(() => {});
    }

    function setSFXVolume(v) {
        sfxVolume = Math.max(0, Math.min(1, v));
        localStorage.setItem('pool-sfx-vol', sfxVolume.toString());
    }

    function setMusicVolume(v) {
        musicVolume = Math.max(0, Math.min(1, v));
        localStorage.setItem('pool-music-vol', musicVolume.toString());
        if (sounds.music) sounds.music.volume = musicVolume;
        if (!musicEnabled && musicVolume > 0) toggleMusic();
    }

    function toggleMusic() {
        if (!sounds.music) return;
        musicEnabled = !musicEnabled;
        if (musicEnabled) {
            sounds.music.volume = musicVolume;
            sounds.music.play().catch(() => {});
        } else {
            sounds.music.pause();
        }
        updateSoundToggle();
    }

    function updateSoundToggle() {
        const btn = document.getElementById('sound-toggle');
        if (!btn) return;
        btn.classList.toggle('muted', !soundEnabled || !musicEnabled);
        btn.title = `SFX ${Math.round(sfxVolume * 100)}% · Música ${Math.round(musicVolume * 100)}%`;
    }

    function ensureAudioStarted() {
        if (musicStarted) return;
        musicStarted = true;
        initSounds();
        if (musicEnabled) toggleMusic();
    }

    document.addEventListener('click', ensureAudioStarted, { once: true });
    document.addEventListener('keydown', ensureAudioStarted, { once: true });
    /* ---------- /Audio Manager ---------- */

    function showMessage(message) { statusElement.textContent = message; }

    const particles = [];
    let turnTimer = 20;
    let timerId = null;
    let lastCushionTick = 0;
    let scoreX = 0;
    let scoreO = 0;
    let paused = false;

    function spawnParticles(x, y, color) {
        for (let i = 0; i < 14; i++) {
            const angle = Math.random() * Math.PI * 2;
            const speed = 1.5 + Math.random() * 3;
            particles.push({
                x, y,
                vx: Math.cos(angle) * speed,
                vy: Math.sin(angle) * speed,
                life: 1.0,
                color
            });
        }
    }

    function updateParticles() {
        for (let i = particles.length - 1; i >= 0; i--) {
            const p = particles[i];
            p.x += p.vx; p.y += p.vy;
            p.vx *= 0.92; p.vy *= 0.92;
            p.life -= 0.045;
            if (p.life <= 0) particles.splice(i, 1);
        }
    }

    function drawParticles() {
        if (particles.length === 0) return;
        context.save();
        context.globalCompositeOperation = 'lighter';
        particles.forEach((p) => {
            context.globalAlpha = Math.max(0, p.life);
            context.fillStyle = p.color || '#d5af62';
            context.beginPath();
            context.arc(p.x, p.y, 3 + p.life * 2, 0, Math.PI * 2);
            context.fill();
        });
        context.restore();
    }

    function startTurnTimer() {
        stopTurnTimer();
        turnTimer = 20;
        const el = document.getElementById('turn-timer');
        const val = document.getElementById('timer-value');
        if (!el) return;
        el.style.display = 'block';
        el.classList.remove('warn');
        val.textContent = turnTimer;
        timerId = setInterval(() => {
            turnTimer--;
            val.textContent = turnTimer;
            if (turnTimer <= 5) el.classList.add('warn');
            if (turnTimer <= 0) {
                stopTurnTimer();
                if (isMyTurn() && game.status === 'active' && !animating) {
                    showMessage('Se acabó el tiempo. Se pasa el turno.');
                    game.turn = game.turn === 'X' ? 'O' : 'X';
                    updateControls(); draw();
                }
            }
        }, 1000);
    }

    function stopTurnTimer() {
        if (timerId) { clearInterval(timerId); timerId = null; }
        const el = document.getElementById('turn-timer');
        if (el) el.style.display = 'none';
        turnTimer = 20;
    }

    function showToast(text, type = 'info', ms = 2200) {
        const el = document.getElementById('game-toast');
        if (!el) return;
        el.textContent = text;
        el.className = 'game-toast show' + (type ? ' ' + type : '');
        setTimeout(() => { el.classList.remove('show'); }, ms);
    }

    function calculateScores() {
        scoreX = 0; scoreO = 0;
        if (!game.groups || !game.groups.X || !game.groups.O) {
            document.getElementById('score-x').textContent = '0';
            document.getElementById('score-o').textContent = '0';
            return;
        }
        const xGroup = game.groups.X === 'solids' ? [1,2,3,4,5,6,7] : [9,10,11,12,13,14,15];
        const oGroup = game.groups.O === 'solids' ? [1,2,3,4,5,6,7] : [9,10,11,12,13,14,15];
        game.balls.forEach((b) => {
            if (b.pocketed && b.id !== 0 && b.id !== 8) {
                if (xGroup.includes(b.id)) scoreX++;
                if (oGroup.includes(b.id)) scoreO++;
            }
        });
        document.getElementById('score-x').textContent = scoreX;
        document.getElementById('score-o').textContent = scoreO;
    }

    function shakeScreen() {
        const wrap = document.querySelector('.table-wrap');
        if (wrap) {
            wrap.classList.add('shake');
            setTimeout(() => wrap.classList.remove('shake'), 450);
        }
    }

    function showVictory(title, text) {
        const ov = document.getElementById('victory-overlay');
        const t = document.getElementById('victory-title');
        const txt = document.getElementById('victory-text');
        if (!ov) return;
        if (t) t.textContent = title;
        if (txt) txt.textContent = text;
        ov.classList.add('show');
    }

    function startCountdown() {
        const ov = document.getElementById('countdown-overlay');
        const num = document.getElementById('countdown-number');
        if (!ov) return;
        let n = 3;
        ov.classList.add('show');
        num.classList.remove('go');
        playSound('ping');
        num.textContent = n;
        const iv = setInterval(() => {
            n--;
            if (n > 0) {
                num.textContent = n;
                playSound('ping');
            } else {
                num.textContent = '¡Go!';
                num.classList.add('go');
                playSound('shoot');
                setTimeout(() => {
                    ov.classList.remove('show');
                    num.classList.remove('go');
                }, 700);
                clearInterval(iv);
            }
        }, 1000);
    }

    function updateConnection() {
        const start = performance.now();
        fetch('/juegos/' + initialGame.id, { method: 'HEAD', cache: 'no-store' }).then(() => {
            const ms = Math.round(performance.now() - start);
            const el = document.getElementById('connection-indicator');
            const txt = document.getElementById('connection-text');
            if (!el) return;
            if (ms < 150) { el.classList.remove('warn'); txt.textContent = `Conectado (${ms}ms)`; }
            else { el.classList.add('warn'); txt.textContent = `Lento (${ms}ms)`; }
        }).catch(() => {
            const el = document.getElementById('connection-indicator');
            const txt = document.getElementById('connection-text');
            if (!el) return;
            el.classList.add('warn'); txt.textContent = 'Desconectado';
        });
    }
    setInterval(updateConnection, 4000);
    updateConnection();
    function isMyTurn() {
        return localRole === 'X' && (game.mode === 'solo' || game.turn === 'X')
            || localRole === 'O' && game.mode === 'duel' && game.turn === 'O';
    }
    function updateControls() {
        document.querySelector('.join-notice')?.toggleAttribute('hidden', localRole === 'X' || game.status !== 'waiting');
        document.getElementById('invite-card').hidden = game.mode !== 'duel' || localRole !== 'X';
        document.getElementById('chat-form')?.toggleAttribute('hidden', !localRole);
        document.getElementById('chat-hint')?.toggleAttribute('hidden', !!localRole);
        shootButton.disabled = !isMyTurn() || animating || game.status !== 'active' || game.pendingShot !== null;
        players.X.classList.toggle('current', game.status === 'active' && game.turn === 'X');
        players.O.classList.toggle('current', game.status === 'active' && game.turn === 'O');
        if (game.status === 'waiting') showMessage(localRole === 'X' ? 'Comparte el enlace para invitar a tu rival.' : 'Únete a la partida para empezar.');
        else if (game.status === 'finished') showMessage(game.winner === localRole
            ? '¡Ganaste la partida!'
            : `Ganó ${game.winner === 'X' ? game.playerX : game.playerO}. ¡A jugar otra!`);
        else if (animating) showMessage('Tiro en curso…');
        else if (game.turn === 'O' && game.mode === 'solo') showMessage('La computadora está pensando su tiro…');
        else if (isMyTurn()) {
            showMessage('Es tu turno. Apunta a una bola.');
            if (game.status === 'active' && !animating) startTurnTimer();
        } else {
            showMessage(`Turno de ${game.turn === 'X' ? game.playerX : game.playerO}.`);
            stopTurnTimer();
        }
    }
    function resizeCanvas() {
        const ratio = window.devicePixelRatio || 1;
        const rect = canvas.getBoundingClientRect();
        canvas.width = Math.round(rect.width * ratio);
        canvas.height = Math.round(rect.width / 2 * ratio);
        context.setTransform(canvas.width / 1000, 0, 0, canvas.height / 500, 0, 0);
        draw();
    }
    function draw() {
        context.clearRect(0, 0, 1000, 500);
        context.fillStyle = '#17633f'; context.fillRect(25, 25, 950, 450);
        context.strokeStyle = '#3a9366'; context.lineWidth = 2; context.strokeRect(45, 45, 910, 410);
        pocketCenters.forEach(([x, y]) => { context.beginPath(); context.arc(x, y, 24, 0, Math.PI * 2); context.fillStyle = '#090d0b'; context.fill(); });
        (animationBalls || game.balls).forEach((ball) => {
            if (ball.pocketed) return;
            context.save();
            context.beginPath(); context.arc(ball.x + 2, ball.y + 3, ballRadius + 1, 0, Math.PI * 2); context.fillStyle = '#07110b77'; context.fill();
            context.beginPath(); context.arc(ball.x, ball.y, ballRadius, 0, Math.PI * 2);
            context.fillStyle = colors[ball.id]; context.fill();
            if (ball.id > 8) { context.save(); context.beginPath(); context.arc(ball.x, ball.y, ballRadius, 0, Math.PI * 2); context.clip(); context.fillStyle = '#fff'; context.fillRect(ball.x - 12, ball.y - 5, 24, 10); context.restore(); }
            context.strokeStyle = '#18221b'; context.lineWidth = 1.5; context.stroke();
            if (ball.id !== 0) {
                context.beginPath(); context.arc(ball.x, ball.y, 5.7, 0, Math.PI * 2); context.fillStyle = '#fff'; context.fill();
                context.fillStyle = '#172018'; context.font = 'bold 7px sans-serif'; context.textAlign = 'center'; context.textBaseline = 'middle'; context.fillText(String(ball.id), ball.x, ball.y + .5);
            }
            let highlight = false;
            if (game.groups[localRole]) {
                highlight = belongsTo(game.groups[localRole], ball.id);
            } else if (game.mode === 'solo' && localRole === 'X') {
                highlight = ball.id >= 1 && ball.id <= 7;
            } else if (game.mode === 'solo' && localRole === 'O') {
                highlight = ball.id >= 9 && ball.id <= 15;
            }
            if (localRole && highlight) {
                context.beginPath();
                context.arc(ball.x, ball.y, ballRadius + 4, 0, Math.PI * 2);
                context.strokeStyle = '#d5af62';
                context.lineWidth = 2.5;
                context.stroke();
                context.beginPath();
                context.arc(ball.x, ball.y, ballRadius + 7, 0, Math.PI * 2);
                context.strokeStyle = 'rgba(213,175,98,.35)';
                context.lineWidth = 1;
                context.stroke();
            }
            context.restore();
        });
        updateParticles();
        drawParticles();
        const cue = game.balls[0];
        if (cue && !cue.pocketed && !animating) {
            context.save();
            context.setLineDash([5, 6]); context.strokeStyle = '#f4e6b8aa'; context.lineWidth = 1.5;
            context.beginPath(); context.moveTo(cue.x, cue.y); context.lineTo(cue.x + Math.cos(aimAngle) * 185, cue.y + Math.sin(aimAngle) * 185); context.stroke();
            context.setLineDash([]); context.strokeStyle = '#dfbf7d'; context.lineWidth = 5;
            context.beginPath(); context.moveTo(cue.x - Math.cos(aimAngle) * 45, cue.y - Math.sin(aimAngle) * 45); context.lineTo(cue.x - Math.cos(aimAngle) * 105, cue.y - Math.sin(aimAngle) * 105); context.stroke();
            context.restore();
        }
    }
    function resetCueBall() { game.balls[0] = { id: 0, x: 250, y: 250, pocketed: false }; }
    function belongsTo(group, ball) { return group === 'solids' ? ball >= 1 && ball <= 7 : ball >= 9 && ball <= 15; }
    function resolveShot(shooter, oldPocketed) {
        const newlyPocketed = game.balls.filter((ball) => ball.pocketed && !oldPocketed.has(ball.id)).map((ball) => ball.id);
        const scratched = newlyPocketed.includes(0);

        // Asignar grupos si es la primera vez (según la primera bola no blanca ni 8 embocada)
        const groupBalls = newlyPocketed.filter((id) => id !== 0 && id !== 8);
        if (game.groups.X === null && game.groups.O === null && groupBalls.length) {
            const shooterGroup = groupBalls[0] < 8 ? 'solids' : 'stripes';
            game.groups[shooter] = shooterGroup;
            game.groups[shooter === 'X' ? 'O' : 'X'] = shooterGroup === 'solids' ? 'stripes' : 'solids';
        }

        // Bolas propias del shooter (solo si su grupo ya está definido)
        const ownGroupBalls = newlyPocketed.filter((id) => id !== 0 && id !== 8 && game.groups[shooter] && belongsTo(game.groups[shooter], id));

        // Sonidos y partículas
        if (scratched) {
            playSound('scratch');
            showToast('¡Falta!', 'scratch');
            shakeScreen();
        }
        newlyPocketed.filter((id) => id !== 0).forEach((id) => {
            playSound('pocket');
            const ball = game.balls.find((b) => b.id === id);
            if (ball) spawnParticles(ball.x, ball.y, colors[id]);
        });
        if (ownGroupBalls.length) showToast('Bola embocada', 'pocket');

        const eightBall = newlyPocketed.includes(8);
        let legalPot = ownGroupBalls.length > 0;

        const remaining = game.balls.filter((ball) => !ball.pocketed && game.groups[shooter] && belongsTo(game.groups[shooter], ball.id));

        if (eightBall) {
            game.status = 'finished';
            playSound(shooter === localRole ? 'win-player' : 'win-computer');
            showToast('¡Victoria!', 'win');
            shakeScreen();
            showVictory(game.winner === localRole ? '¡Ganaste la partida!' : `Ganó ${game.winner === 'X' ? game.playerX : game.playerO}.`, game.winner === localRole ? '¡Felicitaciones!' : '¡A jugar otra!');
            game.winner = game.groups[shooter] !== null && remaining.length === 0 && !scratched
                ? shooter
                : shooter === 'X' ? 'O' : 'X';
        } else if (scratched) {
            resetCueBall();
            game.turn = shooter === 'X' ? 'O' : 'X';
        } else if (!legalPot) {
            game.turn = shooter === 'X' ? 'O' : 'X';
        }
        // Si legalPot === true, el turno NO cambia: turno doble
    }
    function simulateShot(shot, localShot) {
        if (processedShots.has(shot.id)) return;
        processedShots.add(shot.id);
        const startingGame = structuredClone(game);
        const oldPocketed = new Set(game.balls.filter((ball) => ball.pocketed).map((ball) => ball.id));
        const velocity = game.balls.map(() => ({ x: 0, y: 0 }));
        const snapshots = [structuredClone(game.balls)];
        const speed = 2.5 + Number(shot.power) * 11.5;
        velocity[0] = { x: Math.cos(shot.angle) * speed, y: Math.sin(shot.angle) * speed };
        let moving = true;
        for (let tick = 0; tick < 1800 && moving; tick++) {
            for (let i = 0; i < game.balls.length; i++) {
                const ball = game.balls[i];
                if (ball.pocketed) continue;
                ball.x += velocity[i].x; ball.y += velocity[i].y;
                if (ball.x < 45 + ballRadius || ball.x > 955 - ballRadius) {
                    velocity[i].x *= -1;
                    if (tick - lastCushionTick > 6) { playSound('cushion'); lastCushionTick = tick; }
                }
                if (ball.y < 45 + ballRadius || ball.y > 455 - ballRadius) {
                    velocity[i].y *= -1;
                    if (tick - lastCushionTick > 6) { playSound('cushion'); lastCushionTick = tick; }
                }
                ball.x = Math.max(45 + ballRadius, Math.min(955 - ballRadius, ball.x));
                ball.y = Math.max(45 + ballRadius, Math.min(455 - ballRadius, ball.y));
                pocketCenters.forEach(([px, py]) => {
                    if (Math.hypot(ball.x - px, ball.y - py) < 19) ball.pocketed = true;
                });
            }
            let collided = false;
            for (let i = 0; i < game.balls.length; i++) {
                const first = game.balls[i];
                if (first.pocketed) continue;
                for (let j = i + 1; j < game.balls.length; j++) {
                    const second = game.balls[j];
                    if (second.pocketed) continue;
                    const dx = second.x - first.x, dy = second.y - first.y, distance = Math.hypot(dx, dy);
                    if (distance >= ballRadius * 2 || distance === 0) continue;
                    const nx = dx / distance, ny = dy / distance;
                    const overlap = (ballRadius * 2 - distance) / 2;
                    first.x -= nx * overlap; first.y -= ny * overlap; second.x += nx * overlap; second.y += ny * overlap;
                    const relative = (velocity[i].x - velocity[j].x) * nx + (velocity[i].y - velocity[j].y) * ny;
                    if (relative > 0) {
                        velocity[i].x -= relative * nx; velocity[i].y -= relative * ny;
                        velocity[j].x += relative * nx; velocity[j].y += relative * ny;
                        collided = true;
                    }
                }
            }
            if (collided) playSound('hit');
            moving = false;
            velocity.forEach((v, index) => {
                if (game.balls[index].pocketed) return;
                v.x *= .988; v.y *= .988;
                if (Math.abs(v.x) < .045) v.x = 0;
                if (Math.abs(v.y) < .045) v.y = 0;
                if (v.x || v.y) moving = true;
            });
            if (tick % 3 === 2 || !moving) snapshots.push(structuredClone(game.balls));
        }
        if (localShot) {
            resolveShot(shot.shooter, oldPocketed);
        }
        const finalGame = structuredClone(game);
        if (localShot) {
            finalGame.status = game.status;
            finalGame.turn = game.turn;
            finalGame.groups = game.groups;
            finalGame.winner = game.winner;
        }
        game = startingGame;
        animationBalls = snapshots[0];
        animating = true;
        updateControls(); draw();
        if (localShot) submitResult(shot.id, finalGame);
        let frameIndex = 0;
        let lastTimestamp = null;
        function finishAnimation() {
            if (!animating) return;
            animationBalls = null;
            animating = false;
            finishCurrentAnimation = null;
            const nextGame = queuedGameState || (localShot ? finalGame : null);
            queuedGameState = null;
            if (nextGame) applyGameState(nextGame);
            else { updateControls(); draw(); }
            if (game.mode === 'solo' && game.status === 'active' && game.turn === 'O' && localRole === 'X') {
                window.setTimeout(computerShot, 750);
            }
        }
        finishCurrentAnimation = finishAnimation;
        function animate(timestamp) {
            const frames = lastTimestamp === null ? 1 : Math.max(1, Math.floor((timestamp - lastTimestamp) / 16.67));
            lastTimestamp = timestamp;
            frameIndex = Math.min(snapshots.length - 1, frameIndex + frames);
            animationBalls = snapshots[frameIndex];
            draw();
            if (frameIndex < snapshots.length - 1) {
                requestAnimationFrame(animate);
                return;
            }
            finishAnimation();
        }
        requestAnimationFrame(animate);
    }
    async function postJson(path, body) {
        const response = await fetch(path, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'X-Player-Token': seatToken || '', 'Accept': 'application/json' }, body: JSON.stringify(body) });
        const result = await response.json();
        if (!response.ok) throw new Error(result.error || 'No se pudo completar la acción.');
        return result;
    }
    async function fireShot(angle, power) {
        if (paused || animating || !isMyTurn() || game.status !== 'active') return;
        stopTurnTimer();
        playSound('shoot');
        pendingLocalIntent = true;
        shootButton.disabled = true;
        try {
            const result = await postJson(`/juego/${initialGame.id}/tiro`, { angle, power });
            simulateShot(result.shot, true);
        } catch (error) {
            showMessage(error.message);
        } finally {
            pendingLocalIntent = false;
        }
    }
    async function submitResult(shotId, finalGame) {
        try {
            const result = await postJson(`/juego/${initialGame.id}/resultado`, {
                shotId,
                state: { balls: finalGame.balls, groups: finalGame.groups, turn: finalGame.turn, status: finalGame.status, winner: finalGame.winner },
            });
            applyGameState(result.game);
        } catch (error) {
            showMessage(`${error.message} Actualiza la página para recuperar la mesa.`);
        }
    }
    function applyGameState(nextGame) {
        if (animating) {
            queuedGameState = nextGame;
            if (document.hidden) finishCurrentAnimation?.();
            return;
        }
        game = nextGame;
        document.getElementById('name-o').textContent = game.playerO || 'Esperando rival…';
        document.getElementById('invite-help').textContent = game.status === 'waiting' ? 'Copia y comparte este enlace para empezar a jugar.' : 'Tu rival ya se unió a la partida.';
        updateControls(); draw();
        calculateScores();
        if (game.mode === 'solo' && game.status === 'active' && game.turn === 'O' && localRole === 'X') window.setTimeout(computerShot, 750);
    }
    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) finishCurrentAnimation?.();
    });
    function computerShot() {
        if (aiPending || animating || game.status !== 'active' || game.turn !== 'O') return;
        aiPending = true;
        const cue = game.balls[0];
        const targets = game.balls.filter((ball) => !ball.pocketed && ball.id !== 0 && ball.id !== 8 && (game.groups.O === null || belongsTo(game.groups.O, ball.id)));
        const target = targets.sort((a, b) => Math.hypot(a.x - cue.x, a.y - cue.y) - Math.hypot(b.x - cue.x, b.y - cue.y))[0] || game.balls.find((ball) => ball.id === 8 && !ball.pocketed);
        const difficulty = Number(game.level || 1);
        const error = (Math.random() - .5) * (1.7 - difficulty * .18);
        const angle = Math.atan2(target.y - cue.y, target.x - cue.x) + error;
        const power = Math.min(.95, .42 + Math.hypot(target.x - cue.x, target.y - cue.y) / 900 + difficulty * .035);
        fireShot(angle, power).finally(() => { aiPending = false; });
    }
    canvas.addEventListener('pointerdown', (event) => {
        if (paused || !isMyTurn() || animating) return;
        aiming = true; canvas.setPointerCapture(event.pointerId);
        aimFromPointer(event);
    });
    canvas.addEventListener('pointermove', (event) => { if (aiming) aimFromPointer(event); });
    canvas.addEventListener('pointerup', () => { aiming = false; draw(); });
    function aimFromPointer(event) {
        const rect = canvas.getBoundingClientRect();
        const x = (event.clientX - rect.left) * 1000 / rect.width;
        const y = (event.clientY - rect.top) * 500 / rect.height;
        const cue = game.balls[0];
        aimAngle = Math.atan2(y - cue.y, x - cue.x);
        draw();
    }
    shootButton.addEventListener('click', () => fireShot(aimAngle, Number(powerInput.value) / 100));
    powerInput.addEventListener('input', () => { powerLabel.textContent = `${powerInput.value}%`; });
    document.getElementById('sound-toggle')?.addEventListener('click', () => {
        soundEnabled = !soundEnabled;
        toggleMusic();
    });

    const sfxVolInput = document.getElementById('sfx-vol');
    const sfxVolVal = document.getElementById('sfx-vol-val');
    const musicVolInput = document.getElementById('music-vol');
    const musicVolVal = document.getElementById('music-vol-val');
    if (sfxVolInput && sfxVolVal) {
        sfxVolInput.addEventListener('input', (e) => {
            const v = e.target.value / 100;
            setSFXVolume(v);
            sfxVolVal.textContent = `${Math.round(v * 100)}%`;
        });
    }
    if (musicVolInput && musicVolVal) {
        musicVolInput.addEventListener('input', (e) => {
            const v = e.target.value / 100;
            setMusicVolume(v);
            musicVolVal.textContent = `${Math.round(v * 100)}%`;
        });
    }

    document.getElementById('copy-link')?.addEventListener('click', async () => {
        try { await navigator.clipboard.writeText(document.getElementById('invite-link').value); showMessage('Enlace de invitación copiado.'); }
        catch { document.getElementById('invite-link').select(); showMessage('Copia el enlace seleccionado.'); }
    });
    document.getElementById('chat-form')?.addEventListener('submit', async (event) => {
        event.preventDefault();
        const input = document.getElementById('chat-input');
        if (!input.value.trim()) return;
        try { await postJson(`/juego/${initialGame.id}/chat`, { message: input.value.trim() }); input.value = ''; }
        catch (error) { document.getElementById('toast').textContent = error.message; }
    });
    function appendChat(message) {
        playSound('ping');
        const item = document.createElement('div'); item.className = 'message';
        const author = document.createElement('small'); author.textContent = `${message.playerName} · ${message.time}`;
        item.append(author, document.createTextNode(message.message));
        document.getElementById('chat-messages').append(item);
        item.scrollIntoView({ block: 'nearest' });
    }
    function subscribeToGameEvents() {
        if (!window.Echo) {
            showMessage('No se pudo conectar con la partida. Recarga la página para volver a intentarlo.');
            return;
        }

        window.Echo.channel(`game.${initialGame.id}`)
            .listen('.PoolShot', (event) => simulateShot(event.shot, pendingLocalIntent))
            .listen('.PoolStateUpdated', (event) => applyGameState(event.game))
            .listen('.ChatMessage', appendChat);
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', subscribeToGameEvents, { once: true });
    } else {
        subscribeToGameEvents();
    }
    window.addEventListener('resize', resizeCanvas);
    resizeCanvas(); updateControls(); calculateScores();    if (game.status === 'finished') {
        showVictory(game.winner === localRole ? '¡Ganaste la partida!' : `Ganó ${game.winner === 'X' ? game.playerX : game.playerO}.`, game.winner === localRole ? '¡Felicitaciones!' : '¡A jugar otra!');
    } else if (game.status === 'active' && !game.pendingShot) {
        startCountdown();
    }
    if (game.pendingShot) {
        const canSettle = game.mode === 'solo'
            ? localRole === 'X'
            : game.pendingShot.shooter === localRole;
        simulateShot(game.pendingShot, canSettle);
    }
    document.getElementById('victory-lobby')?.addEventListener('click', () => { window.location.href = '{{ route('juegos.index') }}'; });
    document.getElementById('victory-rematch')?.addEventListener('click', () => { window.location.reload(); });
    document.getElementById('pause-btn')?.addEventListener('click', () => {
        paused = !paused;
        const btn = document.getElementById('pause-btn');
        const ov = document.getElementById('pause-overlay');
        if (paused) {
            stopTurnTimer();
            btn.textContent = '▶';
            btn.classList.add('active');
            ov?.classList.add('show');
        } else {
            btn.textContent = '⏸';
            btn.classList.remove('active');
            ov?.classList.remove('show');
            if (isMyTurn() && game.status === 'active' && !animating) startTurnTimer();
        }
    });
    document.getElementById('pause-resume')?.addEventListener('click', () => {
        paused = false;
        document.getElementById('pause-btn').textContent = '⏸';
        document.getElementById('pause-btn').classList.remove('active');
        document.getElementById('pause-overlay')?.classList.remove('show');
        if (isMyTurn() && game.status === 'active' && !animating) startTurnTimer();
    });

    document.querySelectorAll('button').forEach((btn) => {
        btn.addEventListener('click', () => playSound('click'));
    });
</script>
</body>
</html>
