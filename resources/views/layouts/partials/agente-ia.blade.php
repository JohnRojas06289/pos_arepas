{{-- Widget de Agente IA — Gemini Flash --}}
@if(config('services.gemini.api_key'))
<style>
/* ===== AGENTE IA — BOTÓN FLOTANTE ===== */
#agente-ia-btn {
    position: fixed;
    bottom: 24px;
    right: 24px;
    z-index: 1050;
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: var(--color-accent);
    color: var(--color-secondary);
    border: none;
    box-shadow: 0 4px 20px rgba(240,199,94,0.45);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
    transition: all 0.25s ease;
}
#agente-ia-btn:hover {
    background: var(--color-accent-dark);
    transform: scale(1.08);
    box-shadow: 0 6px 24px rgba(240,199,94,0.55);
}
/* Pulso de disponibilidad */
#agente-ia-btn::before {
    content: '';
    position: absolute;
    inset: -4px;
    border-radius: 50%;
    border: 2px solid var(--color-accent);
    opacity: 0;
    animation: ia-pulse 2.5s ease-in-out infinite;
}
@keyframes ia-pulse {
    0%   { transform: scale(1);   opacity: 0.6; }
    70%  { transform: scale(1.3); opacity: 0; }
    100% { transform: scale(1.3); opacity: 0; }
}

/* ===== PANEL ===== */
#agente-ia-panel {
    position: fixed;
    bottom: 92px;
    right: 24px;
    z-index: 1050;
    width: 380px;
    height: 560px;
    max-height: calc(100vh - 110px);
    border-radius: 20px;
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    box-shadow: 0 20px 60px rgba(0,0,0,0.18), 0 4px 16px rgba(0,0,0,0.10);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    transition: all 0.28s cubic-bezier(0.34,1.56,0.64,1);
    transform-origin: bottom right;
}
#agente-ia-panel.oculto {
    opacity: 0;
    transform: scale(0.85) translateY(12px);
    pointer-events: none;
}

/* ===== HEADER ===== */
#agente-ia-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 18px;
    background: linear-gradient(135deg, var(--color-secondary) 0%, #3d4f4f 100%);
    color: #fff;
    border-radius: 20px 20px 0 0;
    flex-shrink: 0;
}
.ia-header-info { display: flex; align-items: center; gap: 10px; }
.ia-avatar {
    width: 36px; height: 36px;
    border-radius: 50%;
    background: var(--color-accent);
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem;
    color: var(--color-secondary);
    flex-shrink: 0;
}
.ia-header-name  { font-weight: 700; font-size: 0.92rem; line-height: 1.2; }
.ia-header-sub   { font-size: 0.7rem; opacity: 0.75; }
.ia-online-dot {
    width: 8px; height: 8px;
    background: #4CAF7D;
    border-radius: 50%;
    display: inline-block;
    margin-right: 4px;
    animation: ia-pulse 2s ease-in-out infinite;
}
#agente-ia-cerrar {
    background: rgba(255,255,255,0.12);
    border: none;
    color: #fff;
    width: 30px; height: 30px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.1rem;
    cursor: pointer;
    transition: background 0.18s ease;
    line-height: 1;
    padding: 0;
}
#agente-ia-cerrar:hover { background: rgba(255,255,255,0.22); }

/* ===== MENSAJES ===== */
#agente-ia-mensajes {
    flex: 1;
    overflow-y: auto;
    padding: 14px 14px 8px;
    display: flex;
    flex-direction: column;
    gap: 10px;
    scroll-behavior: smooth;
}
#agente-ia-mensajes::-webkit-scrollbar { width: 4px; }
#agente-ia-mensajes::-webkit-scrollbar-thumb { background: var(--border-color); border-radius: 2px; }

/* Burbujas */
.ia-burbuja {
    max-width: 86%;
    padding: 10px 13px;
    border-radius: 14px;
    font-size: 0.86rem;
    line-height: 1.5;
    word-break: break-word;
    animation: ia-slide-in 0.22s ease-out;
}
@keyframes ia-slide-in {
    from { opacity:0; transform: translateY(6px); }
    to   { opacity:1; transform: translateY(0); }
}
.ia-burbuja.ia {
    background: var(--bg-primary);
    color: var(--text-primary);
    border: 1px solid var(--border-color);
    align-self: flex-start;
    border-bottom-left-radius: 4px;
}
.ia-burbuja.usuario {
    background: var(--color-primary);
    color: #fff;
    align-self: flex-end;
    border-bottom-right-radius: 4px;
    margin-left: auto;
}

/* Controles de audio */
.ia-audio-controls {
    display: flex;
    gap: 4px;
    margin-top: 7px;
    flex-wrap: wrap;
}
.ia-audio-btn {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 2px 8px;
    border-radius: 10px;
    border: 1px solid var(--border-color);
    background: var(--bg-secondary);
    color: var(--text-secondary);
    font-size: 0.7rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.15s ease;
}
.ia-audio-btn:hover { border-color: var(--color-primary); color: var(--color-primary); }

/* Indicador "Escribiendo..." */
#ia-escribiendo {
    align-self: flex-start;
    padding: 10px 14px;
    background: var(--bg-primary);
    border: 1px solid var(--border-color);
    border-radius: 14px;
    border-bottom-left-radius: 4px;
    display: flex;
    gap: 4px;
    align-items: center;
}
.ia-dot {
    width: 7px; height: 7px;
    background: var(--text-secondary);
    border-radius: 50%;
    animation: ia-bounce 1.2s ease-in-out infinite;
}
.ia-dot:nth-child(2) { animation-delay: 0.2s; }
.ia-dot:nth-child(3) { animation-delay: 0.4s; }
@keyframes ia-bounce {
    0%,80%,100% { transform: translateY(0); opacity:0.5; }
    40%          { transform: translateY(-5px); opacity:1; }
}

/* ===== CHIPS DE SUGERENCIAS ===== */
#ia-sugerencias {
    padding: 8px 12px 4px;
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
    flex-shrink: 0;
    border-top: 1px solid var(--border-color);
}
.ia-chip {
    padding: 4px 11px;
    border-radius: 14px;
    border: 1.5px solid var(--border-color);
    background: var(--bg-primary);
    color: var(--text-secondary);
    font-size: 0.72rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.15s ease;
    white-space: nowrap;
}
.ia-chip:hover {
    border-color: var(--color-primary);
    color: var(--color-primary);
    background: var(--color-primary-subtle);
}

/* ===== FORMULARIO DE INPUT ===== */
#agente-ia-form {
    display: flex;
    align-items: flex-end;
    gap: 6px;
    padding: 10px 12px 14px;
    border-top: 1px solid var(--border-color);
    background: var(--bg-card);
    flex-shrink: 0;
}
#agente-ia-input {
    flex: 1;
    background: var(--bg-primary);
    border: 1.5px solid var(--border-input);
    border-radius: 16px;
    padding: 9px 14px;
    font-size: 0.875rem;
    color: var(--text-primary);
    resize: none;
    max-height: 80px;
    min-height: 38px;
    outline: none;
    font-family: 'Inter', sans-serif;
    transition: border-color 0.18s ease;
    line-height: 1.4;
}
#agente-ia-input:focus { border-color: var(--color-primary); }
#agente-ia-input::placeholder { color: var(--text-muted); }

/* Botón micrófono */
#agente-ia-mic {
    width: 36px; height: 36px;
    border-radius: 50%;
    border: 1.5px solid var(--border-input);
    background: var(--bg-primary);
    color: var(--text-secondary);
    display: flex; align-items: center; justify-content: center;
    font-size: 0.85rem;
    cursor: pointer;
    transition: all 0.18s ease;
    flex-shrink: 0;
}
#agente-ia-mic:hover { border-color: var(--color-primary); color: var(--color-primary); }
#agente-ia-mic.grabando {
    background: var(--color-danger);
    border-color: var(--color-danger);
    color: #fff;
    animation: ia-pulse 1s ease-in-out infinite;
}

/* Botón enviar */
#agente-ia-enviar {
    width: 36px; height: 36px;
    border-radius: 50%;
    background: var(--color-primary);
    color: #fff;
    border: none;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.85rem;
    cursor: pointer;
    transition: all 0.18s ease;
    flex-shrink: 0;
}
#agente-ia-enviar:hover { background: var(--color-primary-dark); transform: scale(1.06); }
#agente-ia-enviar:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }

/* ===== MÓVIL: panel pantalla completa ===== */
@media (max-width: 575.98px) {
    #agente-ia-panel {
        top: 0; left: 0; right: 0; bottom: 0;
        width: 100%; height: 100%;
        max-height: 100%;
        border-radius: 0;
        border: none;
    }
    #agente-ia-panel.oculto { transform: translateY(100%); }
    #agente-ia-header { border-radius: 0; }
    #agente-ia-btn { bottom: 16px; right: 16px; }
}
</style>

{{-- ===== BOTÓN FLOTANTE ===== --}}
<button id="agente-ia-btn" title="Asistente Arepas IA" aria-label="Abrir asistente IA">
    <i class="fas fa-comments"></i>
</button>

{{-- ===== PANEL DE CHAT ===== --}}
<div id="agente-ia-panel" class="oculto" role="dialog" aria-label="Chat asistente IA">

    {{-- Header --}}
    <div id="agente-ia-header">
        <div class="ia-header-info">
            <div class="ia-avatar"><i class="fas fa-robot"></i></div>
            <div>
                <div class="ia-header-name">
                    <span class="ia-online-dot"></span>Asistente Arepas &#127807;
                </div>
                <div class="ia-header-sub">Gemini Flash — siempre disponible</div>
            </div>
        </div>
        <button id="agente-ia-cerrar" title="Cerrar" aria-label="Cerrar chat">
            <i class="fas fa-times"></i>
        </button>
    </div>

    {{-- Mensajes --}}
    <div id="agente-ia-mensajes"></div>

    {{-- Chips de sugerencias --}}
    <div id="ia-sugerencias"></div>

    {{-- Formulario --}}
    <form id="agente-ia-form" autocomplete="off">
        <button type="button" id="agente-ia-mic" title="Hablar" aria-label="Entrada de voz">
            <i class="fas fa-microphone"></i>
        </button>
        <textarea id="agente-ia-input" placeholder="Pregunta algo sobre ventas, inventario..." rows="1" aria-label="Mensaje"></textarea>
        <button type="submit" id="agente-ia-enviar" title="Enviar" aria-label="Enviar mensaje">
            <i class="fas fa-paper-plane"></i>
        </button>
    </form>

</div>

<script>
(function () {
    const userId     = '{{ auth()->id() ?? "guest" }}';
    const userName   = '{{ auth()->user() ? auth()->user()->name : "Usuario" }}';
    const userRole   = '{{ auth()->user() ? (auth()->user()->getRoleNames()->first() ?? "usuario") : "usuario" }}';
    const SESSION_KEY = 'agente_ia_historial_' + userId;

    const btn        = document.getElementById('agente-ia-btn');
    const panel      = document.getElementById('agente-ia-panel');
    const cerrar     = document.getElementById('agente-ia-cerrar');
    const mensajes   = document.getElementById('agente-ia-mensajes');
    const form       = document.getElementById('agente-ia-form');
    const input      = document.getElementById('agente-ia-input');
    const enviar     = document.getElementById('agente-ia-enviar');
    const micBtn     = document.getElementById('agente-ia-mic');
    const sugsEl     = document.getElementById('ia-sugerencias');
    const csrfToken  = document.querySelector('meta[name="csrf-token"]').content;
    const chatUrl    = '{{ route("agente-ia.chat") }}';

    // ---- Chips de sugerencias ----
    const chipsAdmin = [
        '¿Cuánto vendimos hoy?',
        '¿Qué productos tienen stock bajo?',
        '¿Cuál es el producto más vendido?',
    ];
    const chipsVendedor = [
        '¿Hay stock disponible?',
        '¿Cómo registro una venta?',
        '¿Cuánto llevo vendido hoy?',
    ];
    let sugerenciasActuales = userRole === 'administrador' ? chipsAdmin : chipsVendedor;

    function renderSugerencias(sugerenciasList) {
        sugsEl.innerHTML = '';
        if (!sugerenciasList || sugerenciasList.length === 0) return;

        sugerenciasList.forEach(function(c) {
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'ia-chip';
            btn.textContent = c;
            btn.addEventListener('click', function(e) {
                e.stopPropagation(); // Evitar que el click cierre el panel
                input.value = c;
                form.dispatchEvent(new Event('submit'));
            });
            sugsEl.appendChild(btn);
        });
    }

    // ---- Web Speech API ----
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    let recognition = null;
    let isRecording = false;

    if (SpeechRecognition) {
        recognition = new SpeechRecognition();
        recognition.lang = 'es-CO';
        recognition.interimResults = false;
        recognition.maxAlternatives = 1;
        recognition.onstart = function() {
            isRecording = true;
            micBtn.classList.add('grabando');
            input.placeholder = 'Escuchando...';
        };
        recognition.onresult = function(event) {
            input.value = event.results[0][0].transcript;
            form.dispatchEvent(new Event('submit'));
        };
        recognition.onerror = function() { detenerGrabacion(); };
        recognition.onend   = function() { detenerGrabacion(); };
    } else {
        micBtn.style.display = 'none';
    }

    function detenerGrabacion() {
        isRecording = false;
        micBtn.classList.remove('grabando');
        input.placeholder = 'Pregunta algo sobre ventas, inventario...';
    }

    micBtn.addEventListener('click', function() {
        if (!recognition) return;
        isRecording ? recognition.stop() : recognition.start();
    });

    // ---- Historial en sessionStorage ----
    function cargarHistorial() {
        try {
            var guardado = sessionStorage.getItem(SESSION_KEY);
            if (guardado) {
                var items = JSON.parse(guardado);
                mensajes.innerHTML = '';
                if (items.length === 0) { agregarBienvenida(); return; }
                items.forEach(function(m) { 
                    agregarBurbuja(m.texto, m.tipo, false); 
                });
            } else {
                agregarBienvenida();
            }
        } catch(e) { agregarBienvenida(); }
    }

    function guardarHistorial() {
        try {
            var burbujas = mensajes.querySelectorAll('.ia-burbuja');
            var items = [];
            burbujas.forEach(function(b) {
                items.push({ texto: b.getAttribute('data-raw') || b.textContent, tipo: b.classList.contains('usuario') ? 'usuario' : 'ia' });
            });
            sessionStorage.setItem(SESSION_KEY, JSON.stringify(items));
        } catch(e) {}
    }

    function agregarBienvenida() {
        var esAdmin = userRole === 'administrador';
        var msg = '¡Hola ' + userName + '! Soy tu asistente IA de Arepas Boyacenses. ' +
            (esAdmin
                ? 'Como administrador puedo mostrarte ventas, inventario, estadísticas y más.'
                : 'Puedo ayudarte con ventas, stock de productos y navegación del sistema.') +
            ' ¿En qué te ayudo hoy?';
        agregarBurbuja(msg, 'ia', false);
    }

    function formatIaText(texto) {
        var t = texto.replace(/#/g, '');
        t = t.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
        t = t.replace(/\*(.*?)\*/g, '<em>$1</em>');
        t = t.replace(/\n- (.*?)(?=\n|$)/g, '<br>&bull; $1');
        t = t.replace(/\n/g, '<br>');
        return t;
    }

    function agregarBurbuja(texto, tipo, guardar) {
        var div = document.createElement('div');
        div.classList.add('ia-burbuja', tipo);
        div.setAttribute('data-raw', texto);

        if (tipo === 'ia') {
            var formatted = formatIaText(texto);
            div.innerHTML = '<div style="flex-grow:1;">' + formatted + '</div>' +
                '<div class="ia-audio-controls">' +
                  '<button class="ia-audio-btn ia-play-btn" type="button"><i class="fas fa-volume-up"></i> Leer</button>' +
                  '<button class="ia-audio-btn ia-pause-btn" type="button" style="display:none;"><i class="fas fa-pause"></i> Pausar</button>' +
                  '<button class="ia-audio-btn ia-stop-btn"  type="button" style="display:none;"><i class="fas fa-stop"></i> Detener</button>' +
                '</div>';

            var btnPlay  = div.querySelector('.ia-play-btn');
            var btnPause = div.querySelector('.ia-pause-btn');
            var btnStop  = div.querySelector('.ia-stop-btn');

            btnPlay.addEventListener('click', function() {
                if (!window.speechSynthesis) return;
                window.speechSynthesis.cancel();
                document.querySelectorAll('.ia-pause-btn,.ia-stop-btn').forEach(function(b){ b.style.display='none'; });
                btnPause.style.display = 'inline-flex';
                btnStop.style.display  = 'inline-flex';
                btnPause.innerHTML = '<i class="fas fa-pause"></i> Pausar';

                var textoLimpio = texto.replace(/[*#\-]/g,'');
                var utter = new SpeechSynthesisUtterance(textoLimpio);
                utter.lang = 'es-CO'; utter.rate = 1.05; utter.pitch = 1.1;
                var voices = window.speechSynthesis.getVoices();
                var sv = voices.find(function(v){ return v.lang.startsWith('es-') && (v.name.includes('Google')||v.name.includes('Microsoft')); });
                if (sv) utter.voice = sv;
                utter.onend = function() { btnPause.style.display='none'; btnStop.style.display='none'; };
                window.speechSynthesis.speak(utter);
            });

            btnPause.addEventListener('click', function() {
                if (!window.speechSynthesis) return;
                if (window.speechSynthesis.paused) {
                    window.speechSynthesis.resume();
                    btnPause.innerHTML = '<i class="fas fa-pause"></i> Pausar';
                } else if (window.speechSynthesis.speaking) {
                    window.speechSynthesis.pause();
                    btnPause.innerHTML = '<i class="fas fa-play"></i> Reanudar';
                }
            });

            btnStop.addEventListener('click', function() {
                if (!window.speechSynthesis) return;
                window.speechSynthesis.cancel();
                btnPause.style.display='none'; btnStop.style.display='none';
            });
        } else {
            div.textContent = texto;
        }

        mensajes.appendChild(div);
        mensajes.scrollTop = mensajes.scrollHeight;
        if (guardar !== false) guardarHistorial();
    }

    function mostrarEscribiendo() {
        var div = document.createElement('div');
        div.id = 'ia-escribiendo';
        div.innerHTML = '<span class="ia-dot"></span><span class="ia-dot"></span><span class="ia-dot"></span>';
        mensajes.appendChild(div);
        mensajes.scrollTop = mensajes.scrollHeight;
    }
    function quitarEscribiendo() {
        var e = document.getElementById('ia-escribiendo');
        if (e) e.remove();
    }

    // ---- Toggle del panel ----
    btn.addEventListener('click', function(e) {
        e.stopPropagation(); // Evitar que el click en el botón cierre el panel inmediatamente
        panel.classList.toggle('oculto');
        if (!panel.classList.contains('oculto')) {
            cargarHistorial();
            renderSugerencias(sugerenciasActuales);
            input.focus();
            if (window.speechSynthesis) window.speechSynthesis.getVoices();
        }
    });

    cerrar.addEventListener('click', function() {
        panel.classList.add('oculto');
        if (window.speechSynthesis) window.speechSynthesis.cancel();
    });

    // ---- Cerrar al hacer clic fuera del panel ----
    document.addEventListener('click', function(event) {
        if (!panel.classList.contains('oculto')) {
            // Si el clic NO es dentro del panel Y NO es en el botón de abrir/cerrar
            if (!panel.contains(event.target) && !btn.contains(event.target)) {
                panel.classList.add('oculto');
                if (window.speechSynthesis) window.speechSynthesis.cancel();
            }
        }
    });

    // ---- Textarea auto-resize ----
    input.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 80) + 'px';
    });
    input.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            form.dispatchEvent(new Event('submit'));
        }
    });

    // ---- Envío del mensaje ----
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        e.stopPropagation(); // Evitar propagación que cierre el panel
        var texto = input.value.trim();
        if (!texto) return;

        agregarBurbuja(texto, 'usuario', true);
        sugsEl.innerHTML = ''; // Ocultar chips mientras se espera
        input.value = '';
        input.style.height = 'auto';
        enviar.disabled = true;
        micBtn.disabled  = true;
        mostrarEscribiendo();
        if (window.speechSynthesis) window.speechSynthesis.cancel();

        try {
            var res  = await fetch(chatUrl, {
                method: 'POST',
                headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN':csrfToken, 'Accept':'application/json' },
                body: JSON.stringify({ mensaje: texto })
            });
            var data = await res.json();
            quitarEscribiendo();
            
            if (data.respuesta) {
                agregarBurbuja(data.respuesta, 'ia', true);
            } else if (data.error) {
                agregarBurbuja(data.error, 'ia', true);
            } else {
                agregarBurbuja('Error desconocido.', 'ia', true);
            }

            if (data.sugerencias && Array.isArray(data.sugerencias)) {
                sugerenciasActuales = data.sugerencias;
            } else {
                sugerenciasActuales = userRole === 'administrador' ? chipsAdmin : chipsVendedor;
            }

        } catch(err) {
            quitarEscribiendo();
            agregarBurbuja('Error de conexión. Verifica tu internet e intenta de nuevo.', 'ia', true);
        } finally {
            enviar.disabled = false;
            micBtn.disabled  = false;
            renderSugerencias(sugerenciasActuales); // Mostrar chips contextuales
            input.focus();
        }
    });

})();
</script>
@endif
