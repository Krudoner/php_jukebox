<?php
declare(strict_types=1);

// Bloques 9 y 11: home privada con el reproductor MUSICFY de 0612.
// La comprobación de acceso va antes de cualquier HTML.

require_once __DIR__ . '/lib/acceso.php';
require_once __DIR__ . '/lib/canciones.php';
?>
<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reproductor Musical · <?= e(APP_NAME) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Howler se carga antes del código que lo utiliza; script.js usa defer. -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/howler/2.2.4/howler.min.js"></script>
    <script src="scripts/script.js" defer></script>
</head>
<body class="bg-[#050505] text-gray-100 min-h-screen flex flex-col justify-between font-sans selection:bg-red-600 selection:text-white">

  <!-- Cabecera con la sesión del usuario -->
  <header class="p-6 border-b border-red-950/60 flex flex-wrap gap-4 items-center justify-between bg-[#0a0a0a]/80 backdrop-blur">
    <div class="flex items-center gap-3">
      <div class="bg-red-600 p-2.5 rounded-xl shadow-lg shadow-red-950/50 text-white">🎵</div>
      <div>
        <h1 class="text-xl font-bold tracking-tight text-red-500">MUSICFY</h1>
        <p class="text-xs text-gray-400"><?= e(APP_NAME) ?></p>
      </div>
    </div>
    <!-- Sesión del usuario: alias, enlaces privados y cierre de sesión por POST -->
    <nav class="flex flex-wrap items-center gap-4 text-sm">
      <span class="text-gray-400">
        Hola, <strong class="text-gray-100"><?= e((string) $_SESSION['alias']) ?></strong>
      </span>
      <a href="canciones.php" class="text-red-300 hover:text-red-400">Catálogo musical</a>
      <a href="cancion-nueva.php" class="text-red-300 hover:text-red-400">Añadir canción</a>
      <form method="post" action="logout.php">
        <?= campoCsrf() ?>
        <button type="submit"
                class="px-3 py-1.5 text-xs rounded-lg bg-[#181818] text-gray-300
                       border border-red-950/30 hover:bg-[#222222] transition">
          Cerrar sesión
        </button>
      </form>
    </nav>
  </header>

  <!-- Contenido Principal (2 Columnas) -->
  <main class="flex-1 max-w-7xl w-full mx-auto p-6 grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

    <!-- Columna Izquierda: Lista de reproducción y filtros -->
    <section class="lg:col-span-7 bg-[#101010]/80 border border-red-950/50 rounded-2xl p-5 flex flex-col h-[520px] shadow-2xl">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold text-red-200">Lista de reproducción</h2>
        <span id="total-canciones" class="text-xs text-red-400 bg-red-950/60 border border-red-900/40 px-2.5 py-1 rounded-full">0 canciones</span>
      </div>

      <!-- Botones de filtrado: se crean con los géneros que devuelve el servidor -->
      <div id="filtros-genero" class="flex flex-wrap gap-2 mb-4"></div>

      <div id="contenedor-canciones" class="flex-1 overflow-y-auto space-y-2 pr-1">
        <p class="text-gray-500 text-center py-4">Cargando canciones...</p>
      </div>
    </section>

    <!-- Columna Derecha: Sonando Ahora y Ecualizador -->
    <section class="lg:col-span-5 flex flex-col gap-6">

      <!-- Tarjeta Sonando Ahora -->
      <div class="bg-[#101010]/80 border border-red-950/50 rounded-2xl p-5 shadow-2xl">
        <p class="text-xs font-semibold text-red-500 tracking-wider uppercase mb-3">Sonando Ahora</p>
        <div class="flex items-center gap-4">
          <img id="side-img" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" alt="Carátula" class="w-20 h-20 rounded-xl object-cover border border-red-950/60 shadow-md bg-[#181818]">
          <div class="overflow-hidden">
            <h3 id="side-titulo" class="font-bold text-base truncate text-gray-100">Selecciona una canción</h3>
            <p id="side-artista" class="text-sm text-gray-400 truncate">Artista</p>
            <span id="side-genero" class="inline-block mt-1 px-2 py-0.5 bg-red-950/60 text-[11px] text-red-300 border border-red-900/40 rounded">Género</span>
          </div>
        </div>
      </div>

      <!-- Panel de Ecualizador -->
      <div class="bg-[#101010]/80 border border-red-950/50 rounded-2xl p-5 shadow-2xl">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-sm font-semibold text-red-200">Ecualizador</h3>
          <span class="text-[11px] text-red-500/70">espectro en vivo</span>
        </div>

        <!-- Visualizador con barras rojas -->
        <div id="visualizer" class="flex items-end gap-[2px] h-32 mb-6 bg-black p-3 rounded-xl border border-red-950/60 overflow-hidden shadow-inner">
        </div>

        <!-- Sliders verticales -->
        <div class="grid grid-cols-3 gap-4 text-center">
          <div class="flex flex-col items-center">
            <span class="text-xs text-gray-400 mb-2">Graves</span>
            <input id="val-graves-input" type="range" min="-15" max="15" value="0" step="1" oninput="ajustarGraves(this.value)" class="h-20 accent-red-600 cursor-pointer [writing-mode:vertical-lr] [direction:rtl]">
            <span id="val-graves" class="text-[11px] text-red-400 mt-2">0 dB</span>
          </div>
          <div class="flex flex-col items-center">
            <span class="text-xs text-gray-400 mb-2">Medios</span>
            <input id="val-medios-input" type="range" min="-15" max="15" value="0" step="1" oninput="ajustarMedios(this.value)" class="h-20 accent-red-600 cursor-pointer [writing-mode:vertical-lr] [direction:rtl]">
            <span id="val-medios" class="text-[11px] text-red-400 mt-2">0 dB</span>
          </div>
          <div class="flex flex-col items-center">
            <span class="text-xs text-gray-400 mb-2">Agudos</span>
            <input id="val-agudos-input" type="range" min="-15" max="15" value="0" step="1" oninput="ajustarAgudos(this.value)" class="h-20 accent-red-600 cursor-pointer [writing-mode:vertical-lr] [direction:rtl]">
            <span id="val-agudos" class="text-[11px] text-red-400 mt-2">0 dB</span>
          </div>
        </div>
      </div>
    </section>
  </main>

  <!-- Barra inferior fija -->
  <footer class="bg-[#080808] border-t border-red-950/80 px-6 py-4 sticky bottom-0 z-50 shadow-2xl">
    <div class="max-w-7xl mx-auto flex flex-col gap-2">
      <div class="flex items-center gap-3 text-xs text-gray-400">
        <span id="current-time">0:00</span>
        <input type="range" id="seek-bar" value="0" min="0" max="100" step="0.1" oninput="cambiarPosicion(this.value)" class="w-full h-1 bg-red-950 rounded-lg appearance-none cursor-pointer accent-red-600">
        <span id="total-time">0:00</span>
      </div>

      <div class="flex items-center justify-between">
        <div class="flex items-center gap-3 w-1/4">
          <img id="footer-img" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" alt="" class="w-11 h-11 rounded-lg object-cover border border-red-950/60 bg-[#181818]">
          <div class="truncate">
            <h4 id="footer-titulo" class="text-sm font-semibold truncate text-gray-100">Cargando...</h4>
            <p id="footer-artista" class="text-xs text-gray-400 truncate">-</p>
          </div>
        </div>

        <div class="flex items-center gap-4">
          <button id="btn-prev" onclick="cancionAnterior()" class="text-gray-400 hover:text-red-400 transition text-lg disabled:opacity-30" disabled>⏮</button>
          <button id="btn-play-pause" onclick="alternarPlayPause()" class="w-10 h-10 bg-red-600 text-white rounded-full flex items-center justify-center font-bold hover:bg-red-500 hover:scale-105 transition shadow-lg shadow-red-950 disabled:opacity-30" disabled>▶</button>
          <button id="btn-next" onclick="cancionSiguiente()" class="text-gray-400 hover:text-red-400 transition text-lg disabled:opacity-30" disabled>⏭</button>
        </div>

        <div class="flex items-center gap-3 w-1/4 justify-end">
          <span class="text-gray-400 text-sm">🔊</span>
          <input type="range" id="volumen-bar" min="0" max="1" step="0.01" value="1" oninput="cambiarVolumen(this.value)" class="w-24 h-1 bg-red-950 rounded-lg appearance-none cursor-pointer accent-red-600">
        </div>
      </div>
    </div>
  </footer>

</body>
</html>
