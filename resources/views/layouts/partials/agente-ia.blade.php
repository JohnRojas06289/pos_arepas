{{-- Widget de Agente IA - Gemini Flash --}}
<style>
#agente-ia-btn {
    position: fixed;
    bottom: 24px;
    right: 24px;
    z-index: 1050;
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: #4f46e5;
    color: white;
    border: none;
    box-shadow: 0 4px 12px rgba(79,70,229,0.4);
    font-size: 1.4rem;
    cursor: pointer;
    transition: transform 0.2s, box-shadow 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
}
#agente-ia-btn:hover { transform: scale(1.08); box-shadow: 0 6px 18px rgba(79,70,229,0.5); }

#agente-ia-panel {
    position: fixed;
    bottom: 90px;
    right: 24px;
    z-index: 1049;
    width: 360px;
    max-height: 520px;
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.18);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    transition: opacity 0.2s, transform 0.2s;
}
#agente-ia-panel.oculto { opacity: 0; pointer-events: none; transform: translateY(16px); }

#agente-ia-header {
    background: #4f46e5;
    color: white;
    padding: 14px 16px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 0.95rem;
}
#agente-ia-cerrar {
    background: none;
    border: none;
    color: white;
    font-size: 1.1rem;
    cursor: pointer;
    opacity: 0.8;
    line-height: 1;
}
#agente-ia-cerrar:hover { opacity: 1; }

#agente-ia-mensajes {
    flex: 1;
    overflow-y: auto;
    padding: 12px 14px;
    display: flex;
    flex-direction: column;
    gap: 8px;
    background: #f8f9fa;
}

.ia-burbuja {
    max-width: 85%;
    padding: 9px 13px;
    border-radius: 12px;
    font-size: 0.875rem;
    line-height: 1.45;
    word-break: break-word;
    white-space: pre-wrap;
}
.ia-burbuja.ia { background: #fff; border: 1px solid #e5e7eb; align-self: flex-start; border-bottom-left-radius: 4px; }
.ia-burbuja.usuario { background: #4f46e5; color: white; align-self: flex-end; border-bottom-right-radius: 4px; }
.ia-escribiendo { color: #6b7280; font-size: 0.8rem; align-self: flex-start; padding: 4px 8px; }

#agente-ia-form {
    display: flex;
    padding: 10px 12px;
    border-top: 1px solid #e5e7eb;
    background: #fff;
    gap: 8px;
}
#agente-ia-input {
    flex: 1;
    border: 1px solid #d1d5db;
    border-radius: 24px;
    padding: 8px 14px;
    font-size: 0.875rem;
    outline: none;
    resize: none;
    min-height: 38px;
    max-height: 80px;
    overflow-y: auto;
}
#agente-ia-input:focus { border-color: #4f46e5; }
#agente-ia-enviar {
    background: #4f46e5;
    color: white;
    border: none;
    border-radius: 50%;
    width: 38px;
    height: 38px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.9rem;
    flex-shrink: 0;
    transition: background 0.15s;
}
#agente-ia-enviar:hover { background: #4338ca; }
#agente-ia-enviar:disabled { background: #9ca3af; cursor: not-allowed; }

#agente-ia-mic {
    background: #f3f4f6;
    color: #4b5563;
    border: 1px solid #d1d5db;
    border-radius: 50%;
    width: 38px;
    height: 38px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.9rem;
    flex-shrink: 0;
    transition: all 0.2s;
}
#agente-ia-mic:hover { background: #e5e7eb; color: #1f2937; }
#agente-ia-mic.grabando { 
    background: #ef4444; 
    color: white; 
    border-color: #ef4444;
    animation: pulse-mic 1.5s infinite; 
}
#agente-ia-mic:disabled { opacity: 0.5; cursor: not-allowed; }

@keyframes pulse-mic {
    0% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4); }
    70% { box-shadow: 0 0 0 10px rgba(239, 68, 68, 0); }
    100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
}

/* Mobile: panel ocupa toda la pantalla */
@media (max-width: 576px) {
    #agente-ia-panel {
        width: 100vw;
        max-height: 100dvh;
        bottom: 0;
        right: 0;
        border-radius: 0;
    }
    #agente-ia-btn { bottom: 16px; right: 16px; }
}
</style>

<button id="agente-ia-btn" title="Asistente IA">
    <i class="fas fa-robot"></i>
</button>

<div id="agente-ia-panel" class="oculto">
    <div id="agente-ia-header">
        <span><i class="fas fa-robot me-2"></i>Asistente Arepas IA</span>
        <button id="agente-ia-cerrar" title="Cerrar">&times;</button>
    </div>
    <div id="agente-ia-mensajes">
        <div class="ia-burbuja ia">¡Hola! Soy tu asistente IA. Puedo ayudarte con inventario, ventas, precios y navegación del sistema. ¿En qué te ayudo?</div>
    </div>
    <form id="agente-ia-form" autocomplete="off">
        <button type="button" id="agente-ia-mic" title="Hablar por micrófono">
            <i class="fas fa-microphone"></i>
        </button>
        <textarea id="agente-ia-input" placeholder="Escribe tu pregunta..." rows="1"></textarea>
        <button type="submit" id="agente-ia-enviar" title="Enviar">
            <i class="fas fa-paper-plane"></i>
        </button>
    </form>
</div>

<script>
(function () {
    const userId     = '{{ auth()->id() ?? "guest" }}';
    const userName   = '{{ auth()->user() ? auth()->user()->name : "Usuario" }}';
    const userRole   = '{{ auth()->user() ? (auth()->user()->getRoleNames()->first() ?? "Usuario") : "Usuario" }}';
    const SESSION_KEY = 'agente_ia_historial_' + userId;

    const btn        = document.getElementById('agente-ia-btn');
    const panel      = document.getElementById('agente-ia-panel');
    const cerrar     = document.getElementById('agente-ia-cerrar');
    const mensajes   = document.getElementById('agente-ia-mensajes');
    const form       = document.getElementById('agente-ia-form');
    const input      = document.getElementById('agente-ia-input');
    const enviar     = document.getElementById('agente-ia-enviar');
    const micBtn     = document.getElementById('agente-ia-mic');
    const csrfToken  = document.querySelector('meta[name="csrf-token"]').content;
    const chatUrl    = '{{ route("agente-ia.chat") }}';

    // --- Web Speech API (Reconocimiento y Síntesis) ---
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    let recognition = null;
    let isRecording = false;

    if (SpeechRecognition) {
        recognition = new SpeechRecognition();
        recognition.lang = 'es-CO'; // Español Colombia
        recognition.interimResults = false;
        recognition.maxAlternatives = 1;

        recognition.onstart = function() {
            isRecording = true;
            micBtn.classList.add('grabando');
            input.placeholder = "Escuchando...";
        };

        recognition.onresult = function(event) {
            const transcript = event.results[0][0].transcript;
            input.value = transcript;
            form.dispatchEvent(new Event('submit')); // Enviar automáticamente
        };

        recognition.onerror = function(event) {
            console.error("Error en reconocimiento de voz: ", event.error);
            detenerGrabacion();
        };

        recognition.onend = function() {
            detenerGrabacion();
        };
    } else {
        micBtn.style.display = 'none'; // Navegador no soporta Speech API
    }

    function detenerGrabacion() {
        isRecording = false;
        micBtn.classList.remove('grabando');
        input.placeholder = "Escribe o habla tu pregunta...";
    }

    micBtn.addEventListener('click', function() {
        if (!recognition) return;
        if (isRecording) {
            recognition.stop();
        } else {
            recognition.start();
        }
    });

    // Leer respuesta en voz alta
    function hablarTexto(texto) {
        if (!window.speechSynthesis) return;

        // Cancelar cualquier locución previa
        window.speechSynthesis.cancel();
        
        // Limpiar el texto de asteriscos (markdown negrita) para que no los lea
        const textoLimpio = texto.replace(/\*/g, '');

        const utterance = new SpeechSynthesisUtterance(textoLimpio);
        utterance.lang = 'es-CO';
        utterance.rate = 1.05; // Velocidad ligeramente rápida
        utterance.pitch = 1.1; // Tono más amigable

        // Intentar buscar una voz en español
        const voices = window.speechSynthesis.getVoices();
        const spanishVoice = voices.find(v => v.lang.startsWith('es-') && (v.name.includes('Google') || v.name.includes('Microsoft')));
        if (spanishVoice) {
            utterance.voice = spanishVoice;
        }

        window.speechSynthesis.speak(utterance);
    }
    // --- Fin API de Voz ---


    // Restaurar historial de la sesión
    function cargarHistorial() {
        try {
            const guardado = sessionStorage.getItem(SESSION_KEY);
            if (guardado) {
                const items = JSON.parse(guardado);
                mensajes.innerHTML = '';
                items.forEach(function(m) { agregarBurbuja(m.texto, m.tipo, false); });
                if (items.length === 0) agregarBienvenida();
            }
        } catch (e) { /* sessionStorage no disponible */ }
    }

    function guardarHistorial() {
        try {
            const burbujas = mensajes.querySelectorAll('.ia-burbuja');
            const items = [];
            burbujas.forEach(function(b) {
                items.push({ texto: b.textContent, tipo: b.classList.contains('usuario') ? 'usuario' : 'ia' });
            });
            sessionStorage.setItem(SESSION_KEY, JSON.stringify(items));
        } catch (e) {}
    }

    function agregarBienvenida() {
        const saludo = `¡Hola, ${userName}! Soy tu asistente IA. Como ${userRole}, puedo ayudarte con tus tareas en Arepas Boyacenses. ¿En qué te puedo ayudar hoy?`;
        agregarBurbuja(saludo, 'ia', false);
    }

    function agregarBurbuja(texto, tipo, guardar) {
        const div = document.createElement('div');
        div.classList.add('ia-burbuja', tipo);

        if (tipo === 'ia') {
            // Eliminar los # para limpiar los titulares
            let formattedText = texto.replace(/#/g, '');
            // Negritas markdown a etiqueta HTML
            formattedText = formattedText.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
            // Cursivas (asterisco simple)
            formattedText = formattedText.replace(/\*(.*?)\*/g, '<em>$1</em>');
            // Listas (guion convertido a li)
            formattedText = formattedText.replace(/\n- (.*?)(?=\n|$)/g, '<br>&bull; $1');
            // Saltos de línea
            formattedText = formattedText.replace(/\n/g, '<br>');

            div.innerHTML = `
                <div style="flex-grow: 1;">${formattedText}</div>
                <button class="ia-play-btn" title="Escuchar en voz alta" style="margin-top: 8px; background: none; border: none; color: #28a745; cursor: pointer;">
                    <i class="fas fa-volume-up"></i> Leer
                </button>
            `;

            const btnPlay = div.querySelector('.ia-play-btn');
            btnPlay.addEventListener('click', function() {
                // Removemos la etiqueta completa para que narre todo en limpio
                hablarTexto(texto.replace(/\*/g, '').replace(/#/g, '').replace(/-/g, ''));
            });
        } else {
            div.textContent = texto;
        }

        mensajes.appendChild(div);
        mensajes.scrollTop = mensajes.scrollHeight;
        if (guardar !== false) guardarHistorial();
    }

    function mostrarEscribiendo() {
        const div = document.createElement('div');
        div.classList.add('ia-escribiendo');
        div.id = 'ia-escribiendo';
        div.textContent = 'Escribiendo...';
        mensajes.appendChild(div);
        mensajes.scrollTop = mensajes.scrollHeight;
    }

    function quitarEscribiendo() {
        const e = document.getElementById('ia-escribiendo');
        if (e) e.remove();
    }

    btn.addEventListener('click', function () {
        panel.classList.toggle('oculto');
        if (!panel.classList.contains('oculto')) {
            cargarHistorial();
            input.focus();
            
            // Forzar carga de voces (en algunos navegadores tarda o necesita activación por interacción)
            if (window.speechSynthesis) {
                window.speechSynthesis.getVoices();
            }
        }
    });

    cerrar.addEventListener('click', function () { 
        panel.classList.add('oculto'); 
        if (window.speechSynthesis) window.speechSynthesis.cancel(); // Callar al cerrar
    });

    // Auto-resize del textarea
    input.addEventListener('input', function () {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 80) + 'px';
    });

    // Enter envía (Shift+Enter nueva línea)
    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            form.dispatchEvent(new Event('submit'));
        }
    });

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        const texto = input.value.trim();
        if (!texto) return;

        agregarBurbuja(texto, 'usuario', true);
        input.value = '';
        input.style.height = 'auto';
        enviar.disabled = true;
        micBtn.disabled = true;
        mostrarEscribiendo();

        // Si estaba hablando, lo callamos para procesar la nueva pregunta
        if (window.speechSynthesis) window.speechSynthesis.cancel();

        try {
            const res = await fetch(chatUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ mensaje: texto }),
            });
            const data = await res.json();
            quitarEscribiendo();
            
            if (data.respuesta) {
                agregarBurbuja(data.respuesta, 'ia', true);
            } else {
                agregarBurbuja(data.error || 'Error al obtener respuesta.', 'ia', true);
            }
        } catch (err) {
            quitarEscribiendo();
            agregarBurbuja('Error de conexión. Verifica tu internet e intenta de nuevo.', 'ia', true);
        } finally {
            enviar.disabled = false;
            micBtn.disabled = false;
            input.focus();
        }
    });
})();
</script>
