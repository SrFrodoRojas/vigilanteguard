{{-- resources/views/admin/patrol/checkpoints/create.blade.php --}}
@extends('adminlte::page')
@section('title','Nuevo Checkpoint')

@section('content_header')
    <h1>Nuevo Checkpoint • {{ $route->name }}</h1>
@endsection

@section('content')
    @if ($errors->any())
        <x-adminlte-alert theme="danger" title="Error">
            <ul class="mb-0">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </x-adminlte-alert>
    @endif

    @if (session('success'))
        <x-adminlte-alert theme="success" title="OK">
            {{ session('success') }}
        </x-adminlte-alert>
    @endif

    <form method="POST" action="{{ route('admin.patrol.routes.checkpoints.store',$route) }}">
        @csrf
        <x-adminlte-input name="name" label="Nombre" required />
        <div class="row">
            <div class="col-md-4">
                <x-adminlte-input name="latitude" label="Latitud" required />
            </div>
            <div class="col-md-4">
                <x-adminlte-input name="longitude" label="Longitud" required />
            </div>
            <div class="col-md-4">
                <x-adminlte-input type="number" name="radius_m" label="Radio (m)" value="25" min="5" max="200" required />
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Seleccionar en el mapa</label>
            <div id="map" style="height: 380px;"></div>
            <small class="text-muted d-block mt-1">
                Hacé clic en el mapa para fijar la ubicación, o usá el botón <strong>📍 Mi ubicación</strong>.
            </small>
        </div>

        <x-adminlte-button type="submit" class="mt-2" label="Guardar" theme="primary" icon="fas fa-save" />
    </form>
@endsection

@push('css')
<link
  rel="stylesheet"
  href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
  integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
  crossorigin=""
/>
<style>
/* Botón de control "Mi ubicación" con estilo Leaflet */
.leaflet-control-locate {
    background: #fff;
    border: 1px solid #ccc;
    border-radius: 4px;
    overflow: hidden;
}
.leaflet-control-locate button {
    display: block;
    width: 34px;
    height: 34px;
    line-height: 34px;
    text-align: center;
    background: #fff;
    border: none;
    cursor: pointer;
}
.leaflet-control-locate button:hover {
    background: #f0f0f0;
}
</style>
@endpush

@push('js')
<script
  src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
  integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
  crossorigin=""
></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
  const mapEl = document.getElementById('map');
  if (!mapEl) return;

  const latInput = document.querySelector('[name="latitude"]');
  const lngInput = document.querySelector('[name="longitude"]');

  const DEFAULT_CENTER = [-25.2865, -57.647]; // Asunción aprox (ajustá si querés)
  const map = L.map('map').setView(DEFAULT_CENTER, 15);

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19, attribution: '&copy; OpenStreetMap'
  }).addTo(map);

  let marker = null, accuracyCircle = null;

  function setLatLng(latlng, accuracyMeters = null) {
    if (marker) {
      marker.setLatLng(latlng);
    } else {
      marker = L.marker(latlng, { draggable: true }).addTo(map);
      marker.on('dragend', e => {
        const p = e.target.getLatLng();
        latInput.value = p.lat.toFixed(7);
        lngInput.value = p.lng.toFixed(7);
        if (accuracyCircle) accuracyCircle.setLatLng(p);
      });
    }
    latInput.value = latlng.lat.toFixed(7);
    lngInput.value = latlng.lng.toFixed(7);

    if (accuracyMeters != null) {
      if (accuracyCircle) {
        accuracyCircle.setLatLng(latlng).setRadius(accuracyMeters);
      } else {
        accuracyCircle = L.circle(latlng, { radius: accuracyMeters, color: '#3388ff', fillOpacity: 0.1 }).addTo(map);
      }
    }
  }

  map.on('click', e => setLatLng(e.latlng));

  // ----- Control "📍 Mi ubicación" -----
  const LocateControl = L.Control.extend({
    options: { position: 'topleft' },
    onAdd: function() {
      const container = L.DomUtil.create('div', 'leaflet-control-locate');
      const btn = L.DomUtil.create('button', '', container);
      btn.type = 'button';
      btn.title = 'Usar mi ubicación';
      btn.textContent = '📍';

      L.DomEvent.on(btn, 'click', (ev) => {
        L.DomEvent.stopPropagation(ev);
        L.DomEvent.preventDefault(ev);

        if (!navigator.geolocation) {
          alert('Geolocalización no soportada por el navegador.');
          return;
        }

        navigator.geolocation.getCurrentPosition((pos) => {
          const { latitude, longitude, accuracy } = pos.coords;
          const ll = L.latLng(latitude, longitude);
          setLatLng(ll, accuracy || null);
          map.setView(ll, 18);
        }, (err) => {
          alert('No se pudo obtener ubicación: ' + err.message);
        }, { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 });
      });

      return container;
    }
  });
  map.addControl(new LocateControl());
});
</script>
@endpush
