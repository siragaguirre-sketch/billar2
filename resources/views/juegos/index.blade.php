<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Billar en vivo</title>
    <style>
        :root { color-scheme: dark; font-family: Inter, ui-sans-serif, system-ui, sans-serif; background: #101713; color: #f2f3e9; }
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; background: radial-gradient(ellipse at top, #233c2c, #101713 65%); padding: 36px 18px; }
        main { max-width: 920px; margin: auto; }
        .brand { color: #d5af62; font-size: .8rem; font-weight: 800; letter-spacing: .25em; text-transform: uppercase; }
        h1 { font-family: Georgia, serif; font-size: clamp(2.5rem, 7vw, 4.7rem); line-height: 1; margin: 14px 0; }
        .intro { color: #b3beb3; max-width: 620px; line-height: 1.7; margin-bottom: 30px; }
        .layout { display: grid; grid-template-columns: 1.1fr .9fr; gap: 18px; }
        .card { background: #18231d; border: 1px solid #34473a; border-radius: 18px; padding: 25px; box-shadow: 0 18px 60px #0004; }
        h2 { margin: 0 0 18px; font: 600 1.3rem Georgia, serif; }
        label { display: block; margin: 14px 0 7px; color: #c6d0c5; font-size: .9rem; }
        input, select { background: #101813; border: 1px solid #435649; color: #fff; border-radius: 9px; width: 100%; padding: 12px; font: inherit; }
        .modes { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        .mode { display: flex; gap: 9px; align-items: start; padding: 13px; background: #202e25; border: 1px solid #405547; border-radius: 11px; cursor: pointer; }
        .mode input { width: auto; margin-top: 4px; accent-color: #d5af62; }
        .mode span { font-size: .9rem; } .mode small { display: block; color: #aab7ac; margin-top: 4px; line-height: 1.4; }
        .mode.locked { background: #141b16; border-color: #34473a; color: #6b7a6f; cursor: not-allowed; opacity: .6; }
        .mode.locked span { color: #6b7a6f; }
        .mode.locked .lock { color: #8a948c; }
        .mode.unlocked { border-color: #d5af62; background: #202e25; }
        .mode.unlocked .level-number { color: #d5af62; font-weight: 800; }
        .level-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-top: 10px; }
        .level-number { font-size: 1.2rem; font-weight: 900; margin-right: 6px; }
        .level-name { font-size: .85rem; }
        .lock { font-size: 1rem; margin-left: 4px; }
        button { width: 100%; margin-top: 19px; } button:hover { background: #e7c77f; }
        .level { margin-top: 16px; }
        .hint { color: #9eac9f; font-size: .82rem; line-height: 1.5; margin: 12px 0 0; }
        .room { padding: 14px 0; border-top: 1px solid #34473a; display: flex; align-items: center; justify-content: space-between; gap: 12px; }
        .room:first-of-type { border: 0; }
        .room strong { display: block; } .room small { display: block; color: #aab7ac; margin-top: 4px; }
        .join { text-decoration: none; white-space: nowrap; padding: 9px 12px; font-size: .84rem; background: #d5af62; color: #172018; border-radius: 9px; font-weight: 800; display: inline-block; }
        .join:hover { background: #e7c77f; }
        .nav { display: flex; gap: 12px; margin-bottom: 24px; flex-wrap: wrap; }
        .nav a { color: #d5af62; text-decoration: none; padding: 8px 14px; border: 1px solid #34473a; border-radius: 9px; font-size: .9rem; }
        .nav a:hover { background: #18231d; }
        .open-btn { width: 100%; margin-top: 19px; border: 0; border-radius: 9px; background: #d5af62; color: #172018; padding: 13px 16px; font: inherit; font-weight: 800; cursor: pointer; text-align: center; }
        .open-btn:hover { background: #e7c77f; }
        button { width: 100%; margin-top: 19px; } button:hover { background: #e7c77f; }
        .error { background: #502b25; border: 1px solid #9b5243; border-radius: 9px; color: #ffd1c5; padding: 10px 12px; margin-bottom: 14px; }
        @keyframes waiting-pulse { 0%,100% { opacity: 1; } 50% { opacity: .55; } }
        .waiting-pulse { animation: waiting-pulse 2s infinite; color: #d5af62; font-weight: 700; }
        @media (max-width: 720px) { .layout { grid-template-columns: 1fr; } body { padding: 25px 14px; } .card { padding: 20px; } }
    </style>
</head>
<body>
<main>
    <div class="brand">La mesa está lista</div>
    <h1>¿Jugamos una<br>partida?</h1>
    <p class="intro">Juega a tu ritmo o invita a alguien a la mesa. Elige entre ocho niveles y demuestra tu puntería.</p>
    <div class="nav">
        <a href="{{ route('juegos.history') }}">📜 Historial</a>
        <a href="{{ route('juegos.leaderboard') }}">🏆 Ranking</a>
    </div>
    <div class="layout">
        <section class="card">
            <h2>Preparar partida</h2>
            @if ($errors->any())
                <div class="error">{{ $errors->first() }}</div>
            @endif
            <form action="{{ route('juegos.store') }}" method="POST">
                @csrf
                <label for="playerX">Tu nombre</label>
                <input id="playerX" name="playerX" maxlength="50" required placeholder="Ej.: Alex" value="{{ old('playerX') }}">
                <label>Modo de juego</label>
                <div class="modes">
                    <label class="mode"><input type="radio" name="mode" value="solo" checked><span>Individual<small>Practica y mejora tu juego.</small></span></label>
                    <label class="mode"><input type="radio" name="mode" value="duel"><span>Con rival<small>Invita a alguien con un enlace.</small></span></label>
                </div>
                <div class="level" id="level-field">
                    <label for="level">Dificultad</label>
                    <div class="level-grid">
                        @php
                            $levelNames = ['Aprendiz', 'Principiante', 'Aficionado', 'Intermedio', 'Competitivo', 'Avanzado', 'Experto', 'Maestro'];
                            $maxLevel = $maxLevel ?? 1;
                        @endphp
                        @foreach (range(1, 8) as $level)
                            @php $unlocked = $level <= $maxLevel; @endphp
                            <label class="mode {{ $unlocked ? 'unlocked' : 'locked' }}" {{ $unlocked ? '' : 'title="Bloqueado: gana el nivel anterior"' }}>
                                <input type="radio" name="level" value="{{ $level }}"
                                    {{ $level === 1 && !$unlocked ? '' : ($unlocked ? 'checked' : '') }}
                                    {{ $unlocked ? '' : 'disabled' }}
                                >
                                <span>
                                    <span class="level-number">{{ $level }}</span>
                                    <span class="level-name">{{ $levelNames[$level - 1] }}</span>
                                    @if (!$unlocked) <span class="lock">🔒</span> @endif
                                </span>
                            </label>
                        @endforeach
                    </div>
                    <p class="hint">Desbloquea los niveles ganando el anterior. Nivel máximo disponible: {{ $maxLevel }}.</p>
                </div>
                <button type="submit" class="open-btn">Abrir la mesa</button>
            </form>
        </section>
        <section class="card">
            <h2>Invitaciones abiertas</h2>
            @forelse ($games as $game)
                <div class="room">
                    <div><strong>{{ $game['playerX'] }}</strong><small class="waiting-pulse">Esperando un rival · {{ \Illuminate\Support\Carbon::parse($game['createdAt'])->format('H:i') }}</small></div>
                    <a class="join" href="{{ route('juegos.show', ['id' => $game['id'], 'invite' => 1]) }}">Unirse</a>
                </div>
            @empty
                <p class="empty">Todavía no hay partidas esperando. Crea una e invita a alguien a jugar.</p>
            @endforelse
            <p class="hint">Los enlaces de invitación duran ocho horas.</p>
        </section>
    </div>
</main>
<script>
    const levelField = document.getElementById('level-field');
    document.querySelectorAll('input[name="mode"]').forEach((radio) => {
        radio.addEventListener('change', () => {
            levelField.hidden = document.querySelector('input[name="mode"]:checked').value !== 'solo';
        });
    });

    function playClick() {
        try { new Audio('/audio/click.wav').play().catch(() => {}); } catch {}
    }
    document.querySelectorAll('button, a.join, .open-btn, input[type=radio]').forEach((el) => {
        el.addEventListener('click', playClick);
    });
</script>
</body>
</html>
