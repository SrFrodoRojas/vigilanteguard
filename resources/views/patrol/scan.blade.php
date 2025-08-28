{{-- resources/views/patrol/scan.blade.php --}}
@extends('adminlte::page')
@section('title', 'Escanear Checkpoint')

@push('css')
<style>
  .hint { font-size:.95rem; }
  .gps-pill { font-size:.9rem; }

  /* Acciones: grid táctil en móvil, fila en desktop */
  @media (max-width: 767.98px) {
    .actions { display:grid !important; grid-template-columns: 1fr 1fr; gap:.5rem; }
    .actions .btn { width:100%; }
  }

  /* Video QR responsive */
  #qrVideo { max-width:100%; border-radius:.5rem; background:#000; }
  #qrHint { margin-top:.25rem; }

  /* Mensaje inline contextual */
  #localMsg.d-none { display:none !important; }
</style>
@endpush

@section('content_header')
  <h1>Escanear Checkpoint</h1>
@endsection

@section('content')
  {{-- Bloque de mensajes (servidor) --}}
  @if (session('success'))
    <x-adminlte-alert theme="success" title="Éxito" class="mb-2" dismissable>
      {{ session('success') }}
    </x-adminlte-alert>
  @endif
  @if (session('warning'))
    <x-adminlte-alert theme="warning" title="Atención" class="mb-2" dismissable>
      {{ session('warning') }}
    </x-adminlte-alert>
  @endif
  @if (session('info'))
    <x-adminlte-alert theme="info" title="Info" class="mb-2" dismissable>
      {{ session('info') }}
    </x-adminlte-alert>
  @endif
  @if (session('error'))
    <x-adminlte-alert theme="danger" title="Error" class="mb-2" dismissable>
      {{ session('error') }}
    </x-adminlte-alert>
  @endif
  @if ($errors->any())
    <x-adminlte-alert theme="danger" title="Errores de validación" class="mb-3">
      <ul class="mb-0 pl-3">
        @foreach ($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </x-adminlte-alert>
  @endif

  <x-adminlte-card theme="light" title="¿Cómo registrar este punto?" icon="fas fa-info-circle">
    <ol class="mb-2">
      <li>Escaneá el código QR físico del punto <span class="text-muted">(o abrí esta pantalla desde ese QR)</span>.</li>
      <li>Tocá <b>Tomar ubicación</b> para capturar tu GPS.</li>
      <li>Presioná <b>Registrar paso por el punto</b>.</li>
    </ol>
    <div class="hint text-muted">Nota: en iPhone/Safari es necesario usar HTTPS para acceder a la cámara.</div>

    <div class="mt-3 actions d-flex flex-wrap gap-2" aria-label="Acciones de escaneo">
      <button type="button" class="btn btn-outline-primary btn-sm" id="btnOpenCamera"
              title="Abrir cámara" aria-label="Abrir cámara">
        <i class="fas fa-camera"></i> Abrir cámara
      </button>
      <label class="btn btn-outline-secondary btn-sm mb-0" title="Subir foto con QR" aria-label="Subir desde galería">
        <i class="fas fa-image"></i> Subir desde galería
        <input type="file" id="fileFromGallery" accept="image/*" class="d-none">
      </label>
      <button type="button" class="btn btn-outline-success btn-sm" id="btnGetLocation"
              title="Tomar ubicación" aria-label="Tomar ubicación">
        <i class="fas fa-location-arrow"></i> Tomar ubicación
      </button>
      <button type="button" class="btn btn-outline-info btn-sm d-none" id="btnImproveAcc"
              title="Mejorar precisión del GPS" aria-label="Mejorar precisión">
        <i class="fas fa-crosshairs"></i> Mejorar precisión
      </button>
    </div>

    {{-- Zona de mensajes inline (sin pop-ups) --}}
    <div id="localMsg" class="alert d-none mt-2" role="alert"></div>

    {{-- Previsualización/ayuda QR --}}
    <video id="qrVideo" class="d-none mt-2" playsinline muted autoplay></video>
    <canvas id="qrCanvas" class="d-none"></canvas>
    <div id="qrHint" class="small text-muted d-none">Apuntá al QR. Se completará automáticamente.</div>

    {{-- Estado GPS --}}
    <div class="mt-3">
      <span class="badge bg-secondary gps-pill" id="gpsStatus" role="status" aria-live="polite">
        Ubicación no capturada
      </span>
      <div class="small text-muted">Consejo: activá el GPS y los datos. En iPhone se requiere HTTPS.</div>
      <div id="gpsDetails" class="small mt-2 d-none">
        <div><b>Ubicación detectada</b></div>
        <div>Lat: <span id="latLbl">—</span></div>
        <div>Lng: <span id="lngLbl">—</span></div>
        <div>Precisión: <span id="accLbl">—</span></div>
      </div>
    </div>
  </x-adminlte-card>

  {{-- Selector de asignación si no hay checkpoint en la URL --}}
  @if (empty($checkpoint) && !empty($myAssignments) && $myAssignments->count())
    <x-adminlte-card theme="light" title="Seleccioná tu patrulla" icon="fas fa-clipboard-list">
      <div class="row">
        <div class="col-md-8">
          <label class="form-label" for="assignmentSelect">Asignación activa</label>
          <select id="assignmentSelect" class="form-select" aria-label="Elegir asignación activa">
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
        <dd class="col-sm-8">
          {{ optional($checkpoint->route)->name }} ({{ optional(optional($checkpoint->route)->branch)->name ?? '—' }})
        </dd>

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

  {{-- FORM --}}
  <x-adminlte-card>
    @php $alreadyScanned = $alreadyScanned ?? false; @endphp

    @if ($alreadyScanned)
      <x-adminlte-alert theme="success" title="Punto ya registrado" class="mb-3" dismissable>
        Este checkpoint ya fue registrado para esta asignación.
      </x-adminlte-alert>
    @endif

    <form method="POST" action="{{ route('patrol.scan.store') }}" id="scan-form">
      @csrf
      <input type="hidden" name="qr_token" id="qr_token" value="{{ $checkpoint->qr_token ?? '' }}">
      <input type="hidden" name="assignment_id" id="assignment_id" value="{{ $assignment->id ?? '' }}">
      <input type="hidden" name="lat" id="lat">
      <input type="hidden" name="lng" id="lng">
      <input type="hidden" name="accuracy_m" id="accuracy_m">

      <button type="submit" class="btn btn-primary" id="btnSubmit" @disabled($alreadyScanned)
              title="Registrar paso por el punto" aria-label="Registrar paso por el punto">
        @if ($alreadyScanned)
          <i class="fas fa-check-double"></i> Ya registrado
        @else
          <i class="fas fa-check"></i> Registrar paso por el punto
        @endif
      </button>
      <a href="{{ route('patrol.index') }}" class="btn btn-outline-secondary" title="Volver a Mis Patrullas">Volver</a>
    </form>
  </x-adminlte-card>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/jsqr/dist/jsQR.js"></script>
<script>
(function() {
  // Utilidad: mensaje inline (sin alert())
  const localMsgEl = document.getElementById('localMsg');
  function showLocalMsg(msg, theme = 'info') {
    if (!localMsgEl) return;
    localMsgEl.classList.remove('d-none', 'alert-info', 'alert-success', 'alert-warning', 'alert-danger');
    localMsgEl.classList.add('alert', `alert-${theme}`);
    localMsgEl.innerHTML = msg;
    // auto-hide después de 6s para no ensuciar la UI (excepto errores)
    if (theme !== 'danger') {
      clearTimeout(showLocalMsg._t);
      showLocalMsg._t = setTimeout(() => localMsgEl.classList.add('d-none'), 6000);
    }
  }
  function hideLocalMsg(){ localMsgEl?.classList.add('d-none'); }

  // ----- Asignación select → hidden
  const assignmentSelect = document.getElementById('assignmentSelect');
  const assignmentInput  = document.getElementById('assignment_id');
  if (assignmentSelect && assignmentInput) {
    assignmentSelect.addEventListener('change', () => assignmentInput.value = assignmentSelect.value || '');
    const choices = Array.from(assignmentSelect?.options || []).filter(o => o.value);
    if (choices.length === 1) {
      assignmentSelect.value = choices[0].value;
      assignmentInput.value  = choices[0].value;
    }
  }

  // ----- GPS (watchPosition)
  const gpsStatus  = document.getElementById('gpsStatus');
  const gpsDetails = document.getElementById('gpsDetails');
  const latLbl = document.getElementById('latLbl');
  const lngLbl = document.getElementById('lngLbl');
  const accLbl = document.getElementById('accLbl');

  const latInput = document.getElementById('lat');
  const lngInput = document.getElementById('lng');
  const accInput = document.getElementById('accuracy_m');

  const btnGetLocation = document.getElementById('btnGetLocation');
  const btnImproveAcc  = document.getElementById('btnImproveAcc');

  let watchId = null;
  let gpsErrOnceTs = 0;

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
      showLocalMsg('Tu dispositivo/navegador no soporta geolocalización.', 'warning');
      return;
    }
    if (watchId !== null) {
      navigator.geolocation.clearWatch(watchId);
      watchId = null;
    }
    const opts = { enableHighAccuracy: high, timeout: 20000, maximumAge: 0 };
    watchId = navigator.geolocation.watchPosition(
      pos => {
        const { latitude, longitude, accuracy } = pos.coords || {};
        latInput.value = latitude ?? '';
        lngInput.value = longitude ?? '';
        const acc = Math.round(accuracy ?? 0);
        accInput.value = acc;
        updateGpsStatus(true, latitude, longitude, acc);
        if ((acc ?? 9999) > 50) btnImproveAcc?.classList.remove('d-none');
        else btnImproveAcc?.classList.add('d-none');
      },
      err => {
        updateGpsStatus(false);
        const now = Date.now();
        if (now - gpsErrOnceTs > 3000) {
          gpsErrOnceTs = now;
          const msg = (err && err.message) ? err.message : 'No pudimos obtener tu ubicación. Verificá permisos de GPS.';
          showLocalMsg(msg, 'warning');
        }
      },
      opts
    );
  }

  btnGetLocation?.addEventListener('click', () => { hideLocalMsg(); startWatch(false); });
  btnImproveAcc?.addEventListener('click', () => { hideLocalMsg(); startWatch(true); });
  document.addEventListener('visibilitychange', () => {
    if (!document.hidden && watchId !== null) startWatch(false);
  });

  // ----- Cámara en vivo + jsQR
  const btnCam = document.getElementById('btnOpenCamera');
  const video  = document.getElementById('qrVideo');
  const canvas = document.getElementById('qrCanvas');
  const hint   = document.getElementById('qrHint');
  const qrInput= document.getElementById('qr_token');

  let stream = null, rafId = null, cameraBusy = false, camErrOnceTs = 0;

  function setHint(text, theme /* 'muted'|'success'|'danger' */) {
    hint.textContent = text;
    hint.classList.remove('d-none', 'text-muted', 'text-success', 'text-danger');
    hint.classList.add(theme === 'success' ? 'text-success' : theme === 'danger' ? 'text-danger' : 'text-muted');
  }

  function resetHint() {
    hint.classList.add('d-none');
    hint.classList.remove('text-success','text-danger');
    hint.classList.add('text-muted');
    hint.textContent = 'Apuntá al QR. Se completará automáticamente.';
  }

  async function startCamera() {
    if (cameraBusy || stream) return;
    cameraBusy = true;
    btnCam?.setAttribute('aria-busy', 'true');
    try {
      stream = await navigator.mediaDevices.getUserMedia({
        video: { facingMode: { ideal: 'environment' } },
        audio: false
      });
      video.srcObject = stream;
      await video.play();
      video.classList.remove('d-none');
      setHint('Apuntá al QR. Se completará automáticamente.', 'muted');

      const ctx = canvas.getContext('2d', { willReadFrequently: true });
      const tick = () => {
        if (!video.videoWidth || !video.videoHeight) { rafId = requestAnimationFrame(tick); return; }
        canvas.width  = video.videoWidth;
        canvas.height = video.videoHeight;
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
        const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
        const res = jsQR(imageData.data, canvas.width, canvas.height, { inversionAttempts: 'dontInvert' });
        if (res && res.data) {
          const token = extractQrToken(res.data);
          if (token) {
            qrInput.value = token;
            setHint('QR capturado ✓', 'success');
            showLocalMsg('QR leído correctamente.', 'success');
            stopCamera(); // cerrar cámara al capturar
            return;
          }
        }
        rafId = requestAnimationFrame(tick);
      };
      rafId = requestAnimationFrame(tick);
      btnCam.innerHTML = '<i class="fas fa-camera"></i> Cerrar cámara';
    } catch (e) {
      const now = Date.now();
      if (now - camErrOnceTs > 2500) {
        camErrOnceTs = now;
        showLocalMsg('No se pudo abrir la cámara. Revisá permisos o intentá nuevamente.<br><small>'+ (e?.message || '') +'</small>', 'danger');
      }
      stopCamera(true); // true => no limpies localMsg/hint si fue error, solo reseteá estados
    } finally {
      cameraBusy = false;
      btnCam?.removeAttribute('aria-busy');
    }
  }

  function stopCamera(keepMsgs = false) {
    if (rafId) cancelAnimationFrame(rafId), rafId = null;
    if (stream) { stream.getTracks().forEach(t => t.stop()); stream = null; }
    video.classList.add('d-none');
    if (!keepMsgs) { resetHint(); }
    btnCam.innerHTML = '<i class="fas fa-camera"></i> Abrir cámara';
  }

  function extractQrToken(raw) {
    if (!raw) return null;
    const str = String(raw).trim();
    let token = null;
    try {
      if (str.includes('?')) {
        const qs = new URLSearchParams(str.split('?')[1]);
        token = qs.get('c');
      }
    } catch (_) {}
    const uuidRe = /^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/;
    if (!token && uuidRe.test(str)) token = str;
    return token;
  }

  btnCam?.addEventListener('click', () => {
    hideLocalMsg();
    if (!stream) startCamera(); else { stopCamera(); }
  });
  document.addEventListener('visibilitychange', () => { if (document.hidden) stopCamera(); });

  // ----- Galería (decodifica QR sin pop-ups)
  const fileFromGallery = document.getElementById('fileFromGallery');
  fileFromGallery?.addEventListener('change', function() {
    hideLocalMsg();
    if (!this.files?.length) return;
    const reader = new FileReader();
    reader.onload = function(e) {
      const img = new Image();
      img.onload = function() {
        const c = document.createElement('canvas');
        const cx = c.getContext('2d');
        c.width = img.width; c.height = img.height;
        cx.drawImage(img, 0, 0);
        const data = cx.getImageData(0, 0, c.width, c.height);
        const code = jsQR(data.data, c.width, c.height);
        if (code && code.data) {
          const token = extractQrToken(code.data);
          if (token) {
            document.getElementById('qr_token').value = token;
            showLocalMsg('QR cargado desde imagen ✓', 'success');
            setHint('QR capturado ✓', 'success');
          } else {
            showLocalMsg('El código QR no contiene un token válido.', 'warning');
            setHint('No se detectó un token válido en el QR.', 'danger');
          }
        } else {
          showLocalMsg('No pudimos leer el QR de la imagen seleccionada. Probá con otra foto más nítida.', 'warning');
          setHint('No se pudo procesar el QR.', 'danger');
        }
      };
      img.src = e.target.result;
    };
    reader.readAsDataURL(this.files[0]);
  });

  // ----- Guard de formulario y anti-doble submit (sin alert())
  const form = document.getElementById('scan-form');
  const btn  = document.getElementById('btnSubmit');
  const alreadyScanned = @json($alreadyScanned ?? false);

  form?.addEventListener('submit', (e) => {
    hideLocalMsg();
    if (alreadyScanned) { e.preventDefault(); return; }
    const missing = [];
    if (!document.getElementById('assignment_id')?.value) missing.push('Seleccionar una asignación');
    if (!document.getElementById('qr_token')?.value)        missing.push('Escanear el QR del punto');
    if (!document.getElementById('lat')?.value || !document.getElementById('lng')?.value) missing.push('Tomar ubicación (GPS)');
    if (missing.length) {
      e.preventDefault();
      const list = '<ul class="mb-0"><li>' + missing.join('</li><li>') + '</li></ul>';
      showLocalMsg('Faltan datos para registrar:' + list, 'warning');
      return;
    }
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';
  });
})();
</script>
@endpush
