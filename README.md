# Billar en vivo

Aplicación Laravel con una mesa de billar interactiva y sincronización en tiempo real mediante Laravel Echo y Reverb.

## Puesta en marcha

1. Instala las dependencias de PHP y JavaScript:

   ```sh
   composer install
   npm install
   ```

2. Configura `.env` para la base de datos, la caché y Reverb. La caché guarda las partidas (ocho horas); ejecuta las migraciones para crear las tablas necesarias:

   ```sh
   php artisan migrate
   ```

3. Configura `BROADCAST_CONNECTION=reverb`, las variables `REVERB_*` del servidor y sus equivalentes `VITE_REVERB_*` para el cliente. `APP_URL` debe apuntar a la dirección desde donde se compartirán las invitaciones.

4. Compila los recursos y arranca Laravel y Reverb en terminales separadas:

   ```sh
   npm run build
   php artisan serve
   php artisan reverb:start
   ```

## Cómo jugar

- En `/juegos`, crea una partida individual y elige una dificultad del nivel 1 al 8, o crea un duelo por invitación.
- En el duelo, comparte el enlace de invitación. El rival se une indicando su nombre; los tiros, el estado de la mesa y el chat se actualizan para ambos.
- Apunta con el puntero sobre la mesa, ajusta la potencia y ejecuta el tiro. Las bolas lisas corresponden al jugador X, las rayadas al jugador O y la bola 8 se deja para el final.
