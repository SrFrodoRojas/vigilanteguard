<footer class="main-footer py-2 small">
    <div class="container-fluid">
        <div class="row align-items-center">
            {{-- Columna izquierda: marca + estado + sucursal --}}
            <div class="col-12 col-md-4 text-center text-md-left mb-2 mb-md-0">
                <strong>{{ config('app.name', 'Vigilante') }}</strong>
                <span class="mx-2 text-muted d-none d-sm-inline">•</span>

                {{-- Estado de red --}}
                <span id="netStatus" class="badge badge-secondary">…</span>

                {{-- Sucursal actual (si la tiene el usuario) --}}
                @php
                    $u = auth()->user();
                    $branch = $u?->branch;
                    $branchColor = optional($branch)->color ?: '#6c757d';
                @endphp
                @if ($branch)
                    <span class="badge ml-2" style="background-color: {{ $branchColor }}; color: #fff;">
                        {{ $branch->name }}
                    </span>
                @endif

                @env(['local', 'staging'])
                    <span class="badge badge-light text-muted ml-2">{{ app()->environment() }}</span>
                @endenv
            </div>

            {{-- Columna centro: accesos rápidos --}}
            <div class="col-12 col-md-4 text-center my-1 my-md-0">
                <a href="{{ route('access.create') }}" class="text-muted mx-2">
                    <i class="fas fa-sign-in-alt"></i> Entrada
                </a>
                <a href="{{ route('access.active') }}" class="text-muted mx-2">
                    <i class="fas fa-user-check"></i> Activos
                </a>
                <a href="{{ route('access.exit.form') }}" class="text-muted mx-2">
                    <i class="fas fa-sign-out-alt"></i> Salida
                </a>
                @can('reports.view')
                    <a href="{{ route('reports.index') }}" class="text-muted mx-2">
                        <i class="fas fa-chart-bar"></i> Reportes
                    </a>
                @endcan
            </div>

            {{-- Columna derecha: tema, PWA, contacto usuario, sesión --}}
            <div class="col-12 col-md-4 text-center text-md-right">
                <button class="btn btn-sm btn-outline-secondary mr-2" title="Modo oscuro"
                    onclick="window.toggleDarkMode && window.toggleDarkMode()">
                    <i class="fas fa-moon"></i>
                </button>

                <button id="btnPwaInstall" class="btn btn-sm btn-outline-primary mr-2 d-none">
                    <i class="fas fa-download"></i> Instalar app
                </button>

                @if ($u)
                    @if ($u->whatsapp_url)
                        <a href="{{ $u->whatsapp_url }}" target="_blank" rel="noopener"
                            class="btn btn-sm btn-success mr-2">
                            <i class="fab fa-whatsapp"></i> {{ $u->phone }}
                        </a>
                    @elseif($u->phone)
                        <span class="text-muted mr-2">
                            <i class="fas fa-phone"></i> {{ $u->phone }}
                        </span>
                    @endif
                @endif

                {{-- Sesión: contador + extender --}}
                <span id="sessionTimer" class="badge badge-dark align-middle" title="Tiempo restante de sesión">
                    Sesión: --:--
                </span>
                <button id="btnExtendSession" class="btn btn-sm btn-outline-success ml-2 d-none">
                    <i class="fas fa-sync-alt"></i> Continuar sesión
                </button>
            </div>
        </div>

        {{-- Línea inferior: derechos, versiones, desarrollador --}}
        <div class="row mt-2">
            <div class="col-12 col-lg-8 text-center text-lg-left">
                <span class="text-muted">
                    © {{ date('Y') }} · {{ config('app.name', 'Vigilante') }}
                    @php $appVer = config('app.version'); @endphp
                    @if ($appVer)
                        <span class="mx-2">•</span> v{{ $appVer }}
                    @endif
                    <span class="mx-2">•</span> BETA
                </span>
            </div>
            <div class="col-12 col-lg-4 text-center text-lg-right mt-1 mt-lg-0">
                @php
                    $dev = config('app.developer', []);
                    $devName = $dev['name'] ?? null;
                    $devEmail = $dev['email'] ?? null;
                    $devPhone = $dev['phone'] ?? null;
                    $devSite = $dev['site'] ?? null;
                    $devWa = $dev['wa'] ?? null;
                    if (!$devWa && $devPhone) {
                        $digits = preg_replace('/\D+/', '', $devPhone);
                        $devWa = $digits ? "https://wa.me/{$digits}" : null;
                    }
                @endphp

                @if ($devName || $devEmail || $devWa || $devSite)
                    <span class="text-muted">Desarrollado por</span>
                    @if ($devSite && $devName)
                        <a href="{{ $devSite }}" target="_blank" rel="noopener"
                            class="ml-1">{{ $devName }}</a>
                    @elseif($devName)
                        <span class="ml-1">{{ $devName }}</span>
                    @endif
                    @if ($devEmail)
                        <span class="mx-1">•</span>
                        <a href="mailto:{{ $devEmail }}"><i class="fas fa-envelope"></i></a>
                    @endif
                    @if ($devWa)
                        <span class="mx-1">•</span>
                        <a href="{{ $devWa }}" target="_blank" rel="noopener"><i
                                class="fab fa-whatsapp"></i></a>
                    @endif
                    @if ($devSite && !$devName)
                        <span class="mx-1">•</span>
                        <a href="{{ $devSite }}" target="_blank" rel="noopener"><i class="fas fa-globe"></i></a>
                    @endif
                @endif
            </div>
        </div>
    </div>

    {{-- Scroll to top (flotante) --}}
    <button id="btnScrollTop" class="btn btn-primary scrolltop shadow" title="Volver arriba" aria-label="Volver arriba">
        <i class="fas fa-arrow-up"></i>
    </button>

    {{-- Form oculto para auto-logout --}}
    <form id="autologoutForm" method="POST" action="{{ route('logout') }}" class="d-none">
        @csrf
    </form>
</footer>

@push('js')
    <script>
        (function() {
            // ===== Estado de red =====
            const statusBadge = document.getElementById('netStatus');

            function updateStatus() {
                if (!statusBadge) return;
                const online = navigator.onLine;
                statusBadge.textContent = online ? 'Online' : 'Sin conexión';
                statusBadge.className = 'badge ' + (online ? 'badge-success' : 'badge-danger');
            }
            window.addEventListener('online', updateStatus);
            window.addEventListener('offline', updateStatus);
            updateStatus();

            // ===== PWA: botón instalar =====
            const pwaBtn = document.getElementById('btnPwaInstall');
            let deferredPrompt = null;
            window.addEventListener('beforeinstallprompt', (e) => {
                e.preventDefault();
                deferredPrompt = e;
                if (pwaBtn) pwaBtn.classList.remove('d-none');
            });
            if (pwaBtn) {
                pwaBtn.addEventListener('click', async () => {
                    if (!deferredPrompt) {
                        alert('En iOS: toque "Compartir" → "Añadir a pantalla de inicio".');
                        return;
                    }
                    deferredPrompt.prompt();
                    try {
                        await deferredPrompt.userChoice;
                    } catch (e) {}
                    deferredPrompt = null;
                    pwaBtn.classList.add('d-none');
                });
            }
            const isIOS = /iphone|ipad|ipod/i.test(navigator.userAgent);
            const isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone;
            if (isIOS && !isStandalone && pwaBtn) {
                pwaBtn.classList.remove('d-none');
                pwaBtn.innerHTML = '<i class="fas fa-download"></i> Agregar a inicio';
            }

            // ===== Scroll to top =====
            const topBtn = document.getElementById('btnScrollTop');
            const toggleTopBtn = () => {
                if (!topBtn) return;
                if (window.scrollY > 180) topBtn.classList.add('show');
                else topBtn.classList.remove('show');
            };
            window.addEventListener('scroll', toggleTopBtn, {
                passive: true
            });
            toggleTopBtn();
            topBtn && topBtn.addEventListener('click', () => window.scrollTo({
                top: 0,
                behavior: 'smooth'
            }));

            // ===== Contador de sesión (idle) =====
            const SESSION_SECONDS = ({{ (int) config('session.lifetime', 120) }} || 120) * 60;
            const WARNING_SECONDS = Math.min(120, Math.floor(SESSION_SECONDS / 4)) || 60;
            const KEEPALIVE_URL = @json(route('dashboard.summary') . '?keepalive=1');
            const timerEl = document.getElementById('sessionTimer');
            const extendBtn = document.getElementById('btnExtendSession');
            const logoutForm = document.getElementById('autologoutForm');

            let remaining = SESSION_SECONDS;
            let lastPingAt = Date.now();

            function fmt(sec) {
                sec = Math.max(0, Math.floor(sec));
                const m = String(Math.floor(sec / 60)).padStart(2, '0');
                const s = String(sec % 60).padStart(2, '0');
                return `${m}:${s}`;
            }

            function paint() {
                if (!timerEl) return;
                timerEl.textContent = 'Sesión: ' + fmt(remaining);
                const danger = remaining <= WARNING_SECONDS;
                timerEl.className = 'badge ' + (danger ? 'badge-danger' : 'badge-dark');
                if (extendBtn) {
                    if (danger) extendBtn.classList.remove('d-none');
                    else extendBtn.classList.add('d-none');
                }
            }

            function keepAlive() {
                if (Date.now() - lastPingAt < 60 * 1000) return;
                lastPingAt = Date.now();
                fetch(KEEPALIVE_URL, {
                    credentials: 'same-origin',
                    cache: 'no-store'
                }).catch(() => {});
            }

            function resetIdle() {
                remaining = SESSION_SECONDS;
                keepAlive();
                paint();
            }
            ['click', 'keydown', 'mousemove', 'touchstart'].forEach(ev =>
                window.addEventListener(ev, resetIdle, {
                    passive: true
                })
            );
            if (extendBtn) {
                extendBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    resetIdle();
                });
            }
            setInterval(() => {
                remaining -= 1;
                paint();
                if (remaining <= 0) {
                    if (logoutForm) logoutForm.submit();
                }
            }, 1000);
            paint();
        })();
    </script>
@endpush

@push('css')
    <style>
        .main-footer {
            border-top: 1px solid rgba(0, 0, 0, .06);
            background: var(--footer-bg, #fbfbfc);
        }

        .dark-mode .main-footer {
            background: #1f2937;
            color: #d1d5db;
            border-top-color: rgba(255, 255, 255, .06);
        }

        .main-footer a {
            text-decoration: none;
        }

        /* Scroll to top */
        .scrolltop {
            position: fixed;
            right: 16px;
            bottom: 18px;
            width: 44px;
            height: 44px;
            border-radius: 9999px;
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1039;
        }

        .scrolltop.show {
            display: flex;
        }

        .dark-mode .scrolltop {
            background: #111827;
            border-color: #374151;
            color: #e5e7eb;
        }
    </style>
@endpush
