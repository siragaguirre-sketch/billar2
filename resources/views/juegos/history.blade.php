<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Historial de partidas</title>
    <style>
        :root { color-scheme: dark; font-family: Inter, ui-sans-serif, system-ui, sans-serif; background: #101713; color: #f2f3e9; }
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; padding: 36px 18px; }
        main { max-width: 720px; margin: auto; }
        .brand { color: #d5af62; font-size: .8rem; font-weight: 800; letter-spacing: .25em; text-transform: uppercase; }
        h1 { font-family: Georgia, serif; font-size: clamp(2rem, 6vw, 3rem); line-height: 1; margin: 14px 0; }
        .nav { display: flex; gap: 12px; margin-bottom: 24px; }
        .nav a { color: #d5af62; text-decoration: none; padding: 8px 14px; border: 1px solid #34473a; border-radius: 9px; font-size: .9rem; }
        .nav a:hover { background: #18231d; }
        .card { background: #18231d; border: 1px solid #34473a; border-radius: 18px; padding: 25px; box-shadow: 0 18px 60px #0004; margin-bottom: 18px; }
        h2 { margin: 0 0 16px; font: 600 1.3rem Georgia, serif; color: #d5af62; }
        table { width: 100%; border-collapse: collapse; font-size: .95rem; }
        th { text-align: left; color: #b3beb3; padding: 10px; border-bottom: 1px solid #34473a; font-size: .8rem; text-transform: uppercase; letter-spacing: .05em; }
        td { padding: 12px 10px; border-bottom: 1px solid #233c2c; }
        tr:last-child td { border-bottom: none; }
        .winner { color: #d5af62; font-weight: 800; }
        .score { font-family: Georgia, serif; font-weight: 800; color: #e6d7ab; }
        .empty { color: #aab7ac; text-align: center; padding: 40px 0; }
        .date { color: #aab7ac; font-size: .85rem; }
        .mode { color: #9eac9f; font-size: .8rem; text-transform: uppercase; letter-spacing: .05em; }
        a.back { display: inline-block; margin-top: 16px; color: #d5af62; text-decoration: none; font-weight: 700; }
        a.back:hover { text-decoration: underline; }
    </style>
</head>
<body>
<main>
    <div class="brand">Historial</div>
    <h1>Partidas jugadas</h1>
    <div class="nav">
        <a href="{{ route('juegos.index') }}">← Volver al lobby</a>
        <a href="{{ route('juegos.leaderboard') }}">Ver ranking</a>
    </div>
    <div class="card">
        <h2>Últimas partidas</h2>
        @if (count($history))
            <table>
                <thead>
                    <tr>
                        <th style="width: 20%;">Fecha</th>
                        <th style="width: 26%;">Jugadores</th>
                        <th style="width: 14%;">Resultado</th>
                        <th style="width: 20%;">Puntaje</th>
                        <th>Modo</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($history as $h)
                    <tr>
                        <td><span class="date">{{ \Illuminate\Support\Carbon::parse($h['playedAt'])->format('d/m/Y H:i') }}</span></td>
                        <td>
                            <div>{{ $h['playerX'] }}</div>
                            <div style="color:#aab7ac; font-size:.85rem;">vs {{ $h['playerO'] }}</div>
                        </td>
                        @php
                            $_w = ($h['winner'] === 'X') ? $h['playerX'] : (($h['winner'] === 'O') ? $h['playerO'] : 'Empate');
                        @endphp
                        <td><span class="winner">{{ $_w }}</span></td>
                        <td><span class="score">{{ $h['scoreX'] }} - {{ $h['scoreO'] }}</span></td>
                        <td><span class="mode">{{ $h['mode'] }}</span></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @else
            <div class="empty">Todavía no hay partidas registradas.</div>
        @endif
    </div>
    <a class="back" href="{{ route('juegos.index') }}">← Volver al lobby</a>
</main>
</body>
</html>
