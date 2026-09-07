<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>El Brasero - Acceso</title>
    <link rel="icon" type="image/png" href="{{ asset('images/brasero.webp') }}?v=2">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        'ol-bg': '#09090b',
                        'ol-card': '#18181b',
                        'ol-blue': '#3B82F6',
                        'ol-text-muted': '#a1a1aa'
                    }
                }
            }
        }
    </script>
    <style>
        :root {
            --bg-color: #09090b;
            --card-color: #18181b;
            --text-color: #FAFAFA;
            --visor-bg: #000000;
            --border-color: rgba(255, 255, 255, 0.1);
        }

        body.modo-crema {
            --bg-color: #FDFBF7;
            --card-color: #ffffff;
            --text-color: #18181b;
            --visor-bg: #ffffff;
            --border-color: rgba(0, 0, 0, 0.1);
        }

        html, body {
            height: 100%;
        }

        body { 
            background-color: var(--bg-color); 
            color: var(--text-color); 
            font-family: 'Inter', sans-serif; 
            margin: 0; 
            padding: 0; 
            transition: all 0.4s ease; 
            /* Antes: overflow:hidden + h-screen podía cortar contenido en pantallas bajas. 
               Ahora permitimos scroll de respaldo sin que se note en pantallas normales. */
            min-height: 100vh;
            min-height: 100dvh;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
        }

        .logo-claro { display: none; }
        body.modo-crema .logo-oscuro { display: none; }
        body.modo-crema .logo-claro { display: block; }

        .visor-screen {
            background-color: var(--visor-bg);
            border: 1px solid var(--border-color);
            box-shadow: inset 0 6px 15px rgba(0, 0, 0, 0.2);
            transition: all 0.4s ease;
        }

        .key-btn {
            background-color: var(--card-color);
            color: var(--text-color);
            border: 1px solid var(--border-color);
            border-top: 1px solid rgba(255, 255, 255, 0.08); 
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        body.modo-crema .key-btn {
            border-top: 1px solid rgba(0, 0, 0, 0.05);
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
        }

        .key-btn:hover { transform: translateY(-2px); opacity: 0.9; }
        .key-btn:active { transform: scale(0.95); box-shadow: inset 0 4px 8px rgba(0, 0, 0, 0.2); }

        .btn-ok {
            background: linear-gradient(180deg, #3B82F6 0%, #2563EB 100%);
            border: 1px solid #2563EB;
            border-top: 1px solid #93C5FD;
            box-shadow: 0 6px 15px rgba(59, 130, 246, 0.3);
            color: white !important;
        }

        .theme-toggle {
            background: var(--card-color);
            border: 1px solid var(--border-color);
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }

        .cursor-blink { animation: blink 1s infinite; }
        @keyframes blink { 0%, 100% { opacity: 1; } 50% { opacity: 0; } }

        /* Pantallas bajitas (ej. iPhone SE, Android en landscape): compactamos aire vertical
           para que el teclado completo siga siendo visible sin necesidad de scroll. */
        @media (max-height: 700px) {
            .compact-header { margin-bottom: 0.5rem !important; }
            .compact-header img { width: 5.5rem !important; height: 5.5rem !important; margin-bottom: 0.25rem !important; }
            .compact-header h1 { font-size: 1.25rem !important; }
            .compact-header p { margin-top: 0.25rem !important; }
            .compact-visor { height: 3rem !important; margin-bottom: 0.75rem !important; }
            .compact-visor span:first-child { font-size: 1.5rem !important; }
            .keypad-grid { gap: 0.5rem !important; }
        }
    </style>
</head>
<body class="w-full flex flex-col items-center justify-center relative p-4" style="padding-top: max(1rem, env(safe-area-inset-top)); padding-bottom: max(1rem, env(safe-area-inset-bottom));">

    <script>
        if (localStorage.getItem('tema-ollintem') === 'crema') {
            document.body.classList.add('modo-crema');
        }
    </script>

    <div class="absolute z-50" style="top: max(1rem, env(safe-area-inset-top)); right: max(1rem, env(safe-area-inset-right));">
        <button onclick="toggleTheme()" class="theme-toggle px-4 py-2 rounded-full flex items-center gap-2 text-[9px] sm:text-[10px] font-black tracking-widest uppercase shadow-lg">
            <i id="themeIcon" class="fas fa-moon text-ol-blue"></i>
            <span id="themeText" class="hidden sm:inline">Modo Claro</span>
        </button>
    </div>

    {{-- Contenedor principal con ancho máximo súper controlado --}}
    <div class="w-full max-w-[320px] sm:max-w-[360px] flex flex-col items-center my-auto py-2">
        
       {{-- Cabecera más compacta --}}
<div class="compact-header flex flex-col items-center mb-4 sm:mb-6">
    <div class="mx-auto w-28 h-28 sm:w-36 sm:h-36 mb-2 rounded-2xl overflow-hidden shadow-2xl ring-1 ring-white/10">
        <img src="{{ asset('images/brasero.webp') }}" alt="Logo El Brasero" class="w-full h-full object-cover">
    </div>
    
    <h1 class="text-2xl sm:text-3xl font-black tracking-widest leading-none uppercase">
        EL BRASERO
    </h1>
    <p class="text-[9px] sm:text-[10px] uppercase tracking-[0.4em] font-bold mt-2 opacity-60">Restaurante </p>
</div>

        @if($errors->any())
            <div class="w-full bg-rose-500/10 border border-rose-500/20 py-2 sm:py-3 rounded-xl mb-4 flex items-center justify-center">
                <p class="text-rose-500 text-[10px] font-black uppercase tracking-widest">NIP Incorrecto</p>
            </div>
        @endif

        <form action="{{ route('login.pin') }}" method="POST" id="pinForm" class="hidden">
            @csrf
            <input type="password" name="codigo_empleado" id="pinHidden">
        </form>

        {{-- Visor más chaparrito --}}
        <div class="compact-visor w-full h-14 sm:h-16 visor-screen rounded-2xl mb-5 sm:mb-6 flex items-center justify-center gap-2 relative overflow-hidden">
            <span id="pinDisplay" class="text-3xl sm:text-4xl font-black tracking-[0.4em] mt-1"></span>
            <span class="cursor-blink w-[2px] h-8 bg-ol-blue rounded-full"></span>
        </div>

        {{-- GRID PERFECTO: Usa aspect-square para mantener proporción siempre --}}
        <div class="keypad-grid grid grid-cols-4 gap-3 sm:gap-4 w-full">
            <button type="button" onclick="appendNumber('1')" class="key-btn aspect-square rounded-2xl text-2xl sm:text-3xl font-bold flex items-center justify-center">1</button>
            <button type="button" onclick="appendNumber('2')" class="key-btn aspect-square rounded-2xl text-2xl sm:text-3xl font-bold flex items-center justify-center">2</button>
            <button type="button" onclick="appendNumber('3')" class="key-btn aspect-square rounded-2xl text-2xl sm:text-3xl font-bold flex items-center justify-center">3</button>
            
            {{-- Botón OK ocupa toda la altura disponible de sus 2 filas --}}
            <button type="button" onclick="submitForm()" class="btn-ok col-span-1 row-span-2 rounded-2xl flex flex-col items-center justify-center w-full h-full active:scale-95 transition-transform">
                <span class="font-black text-2xl sm:text-3xl leading-tight">OK</span>
                <span class="text-[8px] sm:text-[9px] font-bold uppercase mt-1 opacity-90">Entrar</span>
            </button>

            <button type="button" onclick="appendNumber('4')" class="key-btn aspect-square rounded-2xl text-2xl sm:text-3xl font-bold flex items-center justify-center">4</button>
            <button type="button" onclick="appendNumber('5')" class="key-btn aspect-square rounded-2xl text-2xl sm:text-3xl font-bold flex items-center justify-center">5</button>
            <button type="button" onclick="appendNumber('6')" class="key-btn aspect-square rounded-2xl text-2xl sm:text-3xl font-bold flex items-center justify-center">6</button>

            <button type="button" onclick="appendNumber('7')" class="key-btn aspect-square rounded-2xl text-2xl sm:text-3xl font-bold flex items-center justify-center">7</button>
            <button type="button" onclick="appendNumber('8')" class="key-btn aspect-square rounded-2xl text-2xl sm:text-3xl font-bold flex items-center justify-center">8</button>
            <button type="button" onclick="appendNumber('9')" class="key-btn aspect-square rounded-2xl text-2xl sm:text-3xl font-bold flex items-center justify-center">9</button>
            
            <button type="button" onclick="deleteNumber()" class="key-btn aspect-square rounded-2xl flex flex-col items-center justify-center">
                <i class="fas fa-backspace text-rose-500 text-lg sm:text-xl mb-1"></i>
            </button>

            <div class="col-span-1"></div>
            <button type="button" onclick="appendNumber('0')" class="key-btn aspect-square rounded-2xl text-2xl sm:text-3xl font-bold flex items-center justify-center">0</button>
            <div class="col-span-2"></div>
        </div>
    </div>

    <script>
        const body = document.body;
        const themeIcon = document.getElementById('themeIcon');
        const themeText = document.getElementById('themeText');
        const pinHidden = document.getElementById('pinHidden');
        const pinDisplay = document.getElementById('pinDisplay');
        const pinForm = document.getElementById('pinForm');

        document.addEventListener('DOMContentLoaded', () => {
            if (body.classList.contains('modo-crema')) {
                themeIcon.classList.replace('fa-moon', 'fa-sun');
                themeIcon.classList.replace('text-ol-blue', 'text-orange-500');
                if(themeText) themeText.innerText = "Modo Oscuro";
            }
        });

        function toggleTheme() {
            body.classList.toggle('modo-crema');
            const esCrema = body.classList.contains('modo-crema');
            localStorage.setItem('tema-ollintem', esCrema ? 'crema' : 'Oscuro');

            if (esCrema) {
                themeIcon.classList.replace('fa-moon', 'fa-sun');
                themeIcon.classList.replace('text-ol-blue', 'text-orange-500');
                if(themeText) themeText.innerText = "Modo Oscuro";
            } else {
                themeIcon.classList.replace('fa-sun', 'fa-moon');
                themeIcon.classList.replace('text-orange-500', 'text-ol-blue');
                if(themeText) themeText.innerText = "Modo Claro";
            }
        }

        function updateDisplay() {
            if (navigator.vibrate) navigator.vibrate(10); 
            pinDisplay.textContent = '•'.repeat(pinHidden.value.length);
        }

        function appendNumber(number) {
            if (pinHidden.value.length < 8) { 
                pinHidden.value += number;
                updateDisplay();
            }
        }

        function deleteNumber() {
            pinHidden.value = pinHidden.value.slice(0, -1);
            updateDisplay();
        }

        function submitForm() {
            if (pinHidden.value.length >= 2) pinForm.submit();
        }

        document.addEventListener('keydown', (e) => {
            if (e.key >= '0' && e.key <= '9') appendNumber(e.key);
            if (e.key === 'Backspace') deleteNumber();
            if (e.key === 'Enter') submitForm();
        });
    </script>
</body>
</html>