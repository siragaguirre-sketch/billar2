<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ranking de jugadores</title>
    <style>
        :root { color-scheme: dark; font-family: Inter, ui-sans-serif, system-ui, sans-serif; background: #101713; color: #f2f3e9; }
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; padding: 36px 18px; }
        main { max-width: 620px; margin: auto; }
        .brand { color: #d5af62; font-size: .8rem; font-weight: 800; letter-spacing: .25em; text-transform: uppercase; }
        h1 { font-family: Georgia, serif; font-size: clamp(2rem, 6vw, 3rem); line-height: 1; margin: 14px 0; }
        .nav { display: flex; gap: 12px; margin-bottom: 24px; }
        .nav a { color: #d5af62; text-decoration: none; padding: 8px 14px; border: 1px solid #34473a; border-radius: 9px; font-size: .9rem; }
        .nav a:hover { background: #18231d; }
        .card { background: #18231d; border: 1px solid #34473a; border-radius: 18px; padding: 25px; box-shadow: 0 18px 60px #0004; }
        h2 { margin: 0 0 16px; font: 600 1.3rem Georgia, serif; color: #d5af62; }
        table { width: 100%; border-collapse: collapse; font-size: .95rem; }
        th { text-align: left; color: #b3beb3; padding: 12px 10px; border-bottom: 1px solid #34473a; font-size: .8rem; text-transform: uppercase; letter-spacing: .05em; }
        td { padding: 12px 10px; border-bottom: 1px solid #233c2c; }
        tr:last-child td { border-bottom: none; }
        .rank { color: #d5af62; font-weight: 900; font-size: 1.2rem; }
        .name { font-weight: 800; color: #f2f3e9; }
        .xp { color: #e6d7ab; font-family: Georgia, serif; font-weight: 800; }
        .level { background: #202e25; border: 1px solid #34473a; color: #d5af62; padding: 2px 8px; border-radius: 6px; font-size: .8rem; font-weight: 800; }
        .stats { color: #aab7ac; font-size: .85rem; }
        .empty { color: #aab7ac; text-align: center; padding: 40px 0; }
        a.back { display: inline-block; margin-top: 16px; color: #d5af62; text-decoration: none; font-weight: 700; }
        a.back:hover { text-decoration: underline; }
        .podium { display: flex; gap: 10px; margin-bottom: 20px; }
        .podium-card { flex: 1; background: #18231d; border: 1px solid #34473a; border-radius: 12px; padding: 12px; text-align: center; }
        .podium-card:nth-child(1) { border-color: #e5bd35; }
        .podium-card:nth-child(2) { border-color: #b8b8b8; }
        .podium-card:nth-child(3) { border-color: #c78a3e; }
        .podium-rank { font-size: 2rem; font-weight: 900; color: #d5af62; }
        .podium-name { font-weight: 800; margin-top: 4px; }
        .podium-xp { color: #aab7ac; font-size: .85rem; margin-top: 2px; }
    </style>
</head>
<body>
<main>
    <div class="brand">Ranking</div>
    <h1>Tabla de posiciones</h1>
    <div class="nav">
        <a href="{{ route('juegos.index') }}">← Volver al lobby</a>
        <a href="{{ route('juegos.history') }}">Ver historial</a>
    </div>
    <div class="card">
        <h2>Top jugadores</h2>
        @if (count($leaderboard))
            <div class="podium">
                @foreach (array_slice($leaderboard, 0, 3) as $i => $p)
                    <div class="podium-card">
                        <div class="podium-rank">{{ $i + 1 }}</div>
                        <div class="podium-name">{{ $p['name'] }}</div>
                        <div class="podium-xp">Nivel {{ $p['level'] }} · {{ $p['xp'] }} XP</div>
                        <div class="stats">{{ $p['wins'] }}G / {{ $p['games'] }}P</div>
                    </div>
                @endforeach
            </div>
            <table>
                <thead>
                    <tr>
                        <th style="width: 8%;">#</th>
                        <th>Jugador</th>
                        <th style="width: 16%;">Nivel</th>
                        <th style="width: 16%;">XP</th>
                        <th style="width: 20%;">Récord</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($leaderboard as $i => $p)
                    <tr>
                        <td><span class="rank">{{ $i + 1 }}</span></td>
                        <td><span class="name">{{ $p['name'] }}</span></td>
                        <td><span class="level">N{{ $p['level'] }}</span></td>
                        <td><span class="xp">{{ $p['xp'] }}</span></td>
                        <td><span class="stats">{{ $p['wins'] }}G · {{ $p['games'] }} jugadas</span></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @else
            <div class="empty">Todavía no hay jugadores en el ranking.</div>
        @endif
    </div>
    <a class="back" href="{{ route('juegos.index') }}">← Volver al lobby</a>
</main>
</body>
</html>
