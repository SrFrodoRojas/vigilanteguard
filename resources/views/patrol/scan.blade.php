{{-- resources/views/patrol/scan.blade.php --}}
@extends('adminlte::page')
@section('title', 'Escanear Checkpoint')

@push('css')
    <style>
        .hint {
            font-size: .95rem;
        }

        .gps-pill {
            font-size: .9rem;
        }
    </style>
@endpush

@section('content_header')
    <h1>Escanear Checkpoint</h1>
@endsection

@section('content')

    @if (session('success'))
        <x-adminlte-alert theme="success" title="OK">{{ session('success') }}</x-adminlte-alert>
    @endif
    @if (session('warning'))
        <x-adminlte-alert theme="warning" title="Atención">{{ session('warning') }}</x-adminlte-alert>
    @endif
    @if ($errors->any())
        <x-adminlte-alert theme="danger" title="Error">
            <ul class="mb-0">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </x-adminlte-alert>
    @endif

    {{-- Instrucciones + acciones --}}
    <x-adminlte-card theme="light" title="¿Cómo registrar este punto?" icon="fas fa-info-circle">
        <ol class="mb-2">
            <li>Escaneá el código QR físico del punto <span class="text-muted">(o abrí esta pantalla desde ese QR)</span>.
            </li>
            <li>Tocá <b>Tomar ubicación</b> para capturar tu GPS.</li>
            <li>Presioná <b>Registrar paso por el punto</b>.</li>
        </ol>
        <div class="hint text-muted">Nota: en iPhone/Safari es necesario usar HTTPS para acceder a la cámara.</div>

        <div class="mt-3 actions d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-outline-primary btn-sm" id="btnOpenCamera">
                <i class="fas fa-camera"></i> Abrir cámara
            </button>
            <label class="btn btn-outline-secondary btn-sm mb-0">
                <i class="fas fa-image"></i> Subir desde galería
                <input type="file" id="fileFromGallery" accept="image/*" class="d-none">
            </label>
            <button type="button" class="btn btn-outline-success btn-sm" id="btnGetLocation">
                <i class="fas fa-location-arrow"></i> Tomar ubicación
            </button>
            <button type="button" class="btn btn-outline-info btn-sm d-none" id="btnImproveAcc">
                <i class="fas fa-crosshairs"></i> Mejorar precisión
            </button>
        </div>

        {{-- Muestra estado de GPS --}}
        <div class="mt-3">
            <span class="badge bg-secondary gps-pill" id="gpsStatus">Ubicación no capturada</span>
            <div class="small text-muted">Consejo: activá el GPS y los datos. En iPhone se requiere HTTPS.</div>
            <div id="gpsDetails" class="small mt-2 d-none">
                <div><b>Ubicación detectada</b></div>
                <div>Lat: <span id="latLbl">—</span></div>
                <div>Lng: <span id="lngLbl">—</span></div>
                <div>Precisión: <span id="accLbl">—</span></div>
            </div>
        </div>
    </x-adminlte-card>

    {{-- Si no hay checkpoint (entraste sin ?c), mostramos un selector de asignación --}}
    @if (empty($checkpoint) && !empty($myAssignments) && $myAssignments->count())
        <x-adminlte-card theme="light" title="Seleccioná tu patrulla" icon="fas fa-clipboard-list">
            <div class="row">
                <div class="col-md-8">
                    <label class="form-label">Asignación activa</label>
                    <select id="assignmentSelect" class="form-select">
                        <option value="">— Elegí una asignación —</option>
                        @foreach ($myAssignments as $a)
                            <option value="{{ $a->id }}">
                                #{{ $a->id }} • {{ $a->route->name ?? '—' }} ({{ $a->route->branch->name ?? '—' }})
                                — {{ $a->scheduled_start }} → {{ $a->scheduled_end }}
                            </option>
                        @endforeach
                    </select>
                    <div class="form-text">Si abrís desde el QR físico, se selecciona automáticamente.</div>
                </div>
            </div>
        </x-adminlte-card>
    @endif

    {{-- Datos del punto y formulario --}}
    @if (!empty($checkpoint))
        <x-adminlte-card theme="primary" icon="fas fa-map-marker-alt" title="Punto: {{ $checkpoint->name }}">
            <dl class="row mb-0">
                <dt class="col-sm-4">Ruta</dt>
                <dd class="col-sm-8">{{ optional($checkpoint->route)->name }}
                    ({{ optional(optional($checkpoint->route)->branch)->name ?? '—' }})</dd>

                <dt class="col-sm-4">Radio permitido</dt>
                <dd class="col-sm-8">{{ $checkpoint->radius_m }} m</dd>

                <dt class="col-sm-4">Horario de la patrulla</dt>
                <dd class="col-sm-8">
                    @if ($assignment)
                        {{ $assignment->scheduled_start }} — {{ $assignment->scheduled_end }}
                        <span class="badge bg-info ms-1">{{ $assignment->status }}</span>
                    @else
                        <span class="text-muted">No se encontró asignación activa para esta ruta.</span>
                    @endif
                </dd>
            </dl>
        </x-adminlte-card>
    @endif

    {{-- FORM: siempre visible --}}
    <x-adminlte-card>
        <form method="POST" action="{{ route('patrol.scan.store') }}">
            @csrf
            <input type="hidden" name="qr_token" id="qr_token" value="{{ $checkpoint->qr_token ?? '' }}">
            <input type="hidden" name="assignment_id" id="assignment_id" value="{{ $assignment->id ?? '' }}">
            <input type="hidden" name="lat" id="lat">
            <input type="hidden" name="lng" id="lng">
            <input type="hidden" name="accuracy_m" id="accuracy_m">

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-check"></i> Registrar paso por el punto
            </button>
            <a href="{{ route('patrol.index') }}" class="btn btn-outline-secondary">Volver</a>
        </form>
    </x-adminlte-card>

@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/jsqr/dist/jsQR.js"></script>
    <script>
        (function() {
            // --- Asignación desde select si no había checkpoint ---
            const assignmentSelect = document.getElementById('assignmentSelect');
            const assignmentInput = document.getElementById('assignment_id');
            if (assignmentSelect && assignmentInput) {
                assignmentSelect.addEventListener('change', () => {
                    assignmentInput.value = assignmentSelect.value || '';
                });
            }

            // --- GPS ---
            const gpsStatus = document.getElementById('gpsStatus');
            const gpsDetails = document.getElementById('gpsDetails');
            const latLbl = document.getElementById('latLbl');
            const lngLbl = document.getElementById('lngLbl');
            const accLbl = document.getElementById('accLbl');

            const latInput = document.getElementById('lat');
            const lngInput = document.getElementById('lng');
            const accInput = document.getElementById('accuracy_m');

            const btnGetLocation = document.getElementById('btnGetLocation');
            const btnImproveAcc = document.getElementById('btnImproveAcc');

            let watchId = null;

            function updateGpsStatus(ok, lat, lng, acc) {
                if (ok) {
                    gpsStatus.className = 'badge bg-success gps-pill';
                    gpsStatus.textContent = 'Ubicación lista (±' + (acc ?? '?') + ' m)';
                    gpsDetails.classList.remove('d-none');
                    latLbl.textContent = (lat ?? '—') && lat.toFixed ? lat.toFixed(6) : lat;
                    lngLbl.textContent = (lng ?? '—') && lng.toFixed ? lng.toFixed(6) : lng;
                    accLbl.textContent = (acc ?? '—') + ' m';
                } else {
                    gpsStatus.className = 'badge bg-secondary gps-pill';
                    gpsStatus.textContent = 'Ubicación no capturada';
                    gpsDetails.classList.add('d-none');
                }
            }

            function startWatch(high = false) {
                if (!navigator.geolocation) {
                    alert('Geolocalización no soportada');
                    return;
                }
                // Reiniciar watcher si ya existía
                if (watchId !== null) {
                    navigator.geolocation.clearWatch(watchId);
                    watchId = null;
                }
                const opts = {
                    enableHighAccuracy: high,
                    timeout: 20000,
                    maximumAge: 0
                };
                watchId = navigator.geolocation.watchPosition(
                    pos => {
                        const {
                            latitude,
                            longitude,
                            accuracy
                        } = pos.coords || {};
                        // Guardar valores reales (¡sin fixedAccuracy!)
                        latInput.value = latitude ?? '';
                        lngInput.value = longitude ?? '';
                        const acc = Math.round(accuracy ?? 0);
                        accInput.value = acc;

                        updateGpsStatus(true, latitude, longitude, acc);

                        // Mostrar botón "Mejorar precisión" si > 50 m; ocultarlo si ya mejoró
                        if ((acc ?? 9999) > 50) {
                            btnImproveAcc?.classList.remove('d-none');
                        } else {
                            btnImproveAcc?.classList.add('d-none');
                        }
                    },
                    err => {
                        console.warn(err);
                        updateGpsStatus(false);
                        // No alert intrusivo en watch; el usuario puede reintentar
                    },
                    opts
                );
            }

            // Iniciar captura al presionar "Tomar ubicación"
            if (btnGetLocation) {
                btnGetLocation.addEventListener('click', () => startWatch(false));
            }

            // Forzar modo de alta precisión
            if (btnImproveAcc) {
                btnImproveAcc.addEventListener('click', () => startWatch(true));
            }

            // Si la pestaña vuelve al frente, re-enganchar (algunos SO pausan sensores)
            document.addEventListener('visibilitychange', () => {
                if (!document.hidden && watchId !== null) {
                    // reinicia en el modo estándar; si el user pidió alta precisión volverá a tocar el botón
                    startWatch(false);
                }
            });

            // Validación suave al enviar: evitar POST sin lat/lng
            document.querySelector('form[action="{{ route('patrol.scan.store') }}"]')
                ?.addEventListener('submit', (e) => {
                    if (!latInput.value || !lngInput.value) {
                        e.preventDefault();
                        alert('Primero tomá la ubicación (botón "Tomar ubicación").');
                    }
                });

            // --- Hooks del lector de QR ---
            const qrTokenInput = document.getElementById('qr_token');
            const btnOpenCamera = document.getElementById('btnOpenCamera');
            const fileFromGallery = document.getElementById('fileFromGallery');

            // Procesar imagen QR
            function processQRImage(image) {
                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');
                canvas.width = image.width;
                canvas.height = image.height;
                ctx.drawImage(image, 0, 0, image.width, image.height);
                const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
                const code = jsQR(imageData.data, canvas.width, canvas.height);
                if (code) {
                    const qrToken = code.data;
                    const urlParams = new URLSearchParams(qrToken.split('?')[1]);
                    const extractedToken = urlParams.get('c');
                    const uuidPattern = /^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/;
                    if (uuidPattern.test(extractedToken)) {
                        document.getElementById('qr_token').value = extractedToken;
                    } else {
                        alert('El código QR no contiene un UUID válido.');
                    }
                } else {
                    alert('No se pudo procesar el código QR.');
                }
            }

            fileFromGallery?.addEventListener('change', function() {
                if (!this.files?.length) return;
                const reader = new FileReader();
                reader.onload = function(event) {
                    const img = new Image();
                    img.onload = function() {
                        processQRImage(img);
                    };
                    img.src = event.target.result;
                };
                reader.readAsDataURL(this.files[0]);
            });
        })();
    </script>
@endpush



{{--            function getLocation(high=false){
                if (!navigator.geolocation) { alert('Geolocalización no soportada'); return; }
                const opts = { enableHighAccuracy: high, timeout: 15000, maximumAge: 0 };
                navigator.geolocation.getCurrentPosition(
                  pos => {
                    const { latitude, longitude, accuracy } = pos.coords;
                    latInput.value = latitude;
                    lngInput.value = longitude;
                    accInput.value = Math.round(accuracy ?? 0);
                    updateGpsStatus(true, latitude, longitude, Math.round(accuracy ?? 0));
                    // mostrar botón "Mejorar precisión" si accuracy > 50 m
                    if ((accuracy ?? 9999) > 50) btnImproveAcc?.classList.remove('d-none');
                  },
                  err => {
                    console.warn(err);
                    updateGpsStatus(false);
                    alert('No se pudo obtener la ubicación. Verificá permisos de GPS y datos.');
                  },
                  opts
                );
              }  --}}
