@php
$iconos = [
    'Productos' => [
        'ri ri-price-tag-3-line'     => 'Etiqueta',
        'ri ri-shopping-bag-line'    => 'Bolsa',
        'ri ri-shopping-cart-line'   => 'Carrito',
        'ri ri-store-2-line'         => 'Tienda',
        'ri ri-box-3-line'           => 'Caja',
        'ri ri-archive-line'         => 'Archivo',
        'ri ri-stack-line'           => 'Pila',
        'ri ri-gift-line'            => 'Regalo',
        'ri ri-barcode-line'         => 'Código barras',
        'ri ri-scan-line'            => 'Escáner',
    ],
    'Médico / Salud' => [
        'ri ri-medicine-bottle-line' => 'Medicina',
        'ri ri-capsule-line'         => 'Cápsula',
        'ri ri-heart-pulse-line'     => 'Pulso',
        'ri ri-stethoscope-line'     => 'Estetoscopio',
        'ri ri-first-aid-kit-line'   => 'Botiquín',
        'ri ri-microscope-line'      => 'Microscopio',
        'ri ri-test-tube-line'       => 'Tubo ensayo',
        'ri ri-syringe-line'         => 'Jeringa',
        'ri ri-thermometer-line'     => 'Termómetro',
        'ri ri-eye-line'             => 'Ojo',
    ],
    'Tecnología' => [
        'ri ri-computer-line'        => 'Computadora',
        'ri ri-smartphone-line'      => 'Smartphone',
        'ri ri-tablet-line'          => 'Tablet',
        'ri ri-printer-line'         => 'Impresora',
        'ri ri-keyboard-line'        => 'Teclado',
        'ri ri-mouse-line'           => 'Mouse',
        'ri ri-router-line'          => 'Router',
        'ri ri-hard-drive-line'      => 'Disco duro',
        'ri ri-camera-line'          => 'Cámara',
        'ri ri-headphone-line'       => 'Audífonos',
    ],
    'Oficina' => [
        'ri ri-file-text-line'       => 'Documento',
        'ri ri-folder-line'          => 'Carpeta',
        'ri ri-pen-nib-line'         => 'Pluma',
        'ri ri-scissors-line'        => 'Tijeras',
        'ri ri-clipboard-line'       => 'Portapapeles',
        'ri ri-book-line'            => 'Libro',
        'ri ri-bookmark-line'        => 'Marcador',
        'ri ri-calendar-line'        => 'Calendario',
        'ri ri-mail-line'            => 'Correo',
        'ri ri-briefcase-line'       => 'Maletín',
    ],
    'Hogar / General' => [
        'ri ri-home-line'            => 'Hogar',
        'ri ri-tools-line'           => 'Herramientas',
        'ri ri-hammer-line'          => 'Martillo',
        'ri ri-flashlight-line'      => 'Linterna',
        'ri ri-plug-line'            => 'Enchufe',
        'ri ri-lightbulb-line'       => 'Bombilla',
        'ri ri-drop-line'            => 'Gota',
        'ri ri-leaf-line'            => 'Hoja',
        'ri ri-recycle-line'         => 'Reciclaje',
        'ri ri-truck-line'           => 'Camión',
    ],
];
@endphp

<div class="form-group mb-3">
    <label>Ícono</label>

    {{-- Botón que abre/cierra el selector --}}
    <div class="d-flex align-items-center gap-2 mb-2">
        <button type="button"
                class="btn btn-outline-secondary btn-sm"
                onclick="document.getElementById('iconPicker').classList.toggle('d-none')">
            @if($icono)
                <i class="{{ $icono }} me-1"></i>
            @endif
            {{ $icono ?: 'Seleccionar ícono' }}
            <i class="ri ri ri-arrow-down-s-line ms-1"></i>
        </button>
        @if($icono)
            <button type="button"
                    class="btn btn-outline-danger btn-sm"
                    wire:click="$set('icono', '')">
                <i class="ri ri ri-close-line"></i>
            </button>
        @endif
    </div>

    {{-- Panel selector --}}
    <div id="iconPicker" class="d-none border rounded p-3 bg-light" style="max-height:320px;overflow-y:auto;">

        {{-- Búsqueda --}}
        <input type="text"
               id="iconSearch"
               class="form-control form-control-sm mb-3"
               placeholder="Buscar ícono..."
               oninput="filterIcons(this.value)">

        @foreach($iconos as $grupo => $lista)
            <div class="icon-group mb-3" data-group="{{ strtolower($grupo) }}">
                <p class="text-muted small fw-bold mb-2 text-uppercase">{{ $grupo }}</p>
                <div class="d-flex flex-wrap gap-2">
                    @foreach($lista as $clase => $etiqueta)
                        <button type="button"
                                title="{{ $etiqueta }}"
                                data-icon-label="{{ strtolower($etiqueta) }}"
                                data-icon-class="{{ $clase }}"
                                wire:click="$set('icono', '{{ $clase }}')"
                                onclick="document.getElementById('iconPicker').classList.add('d-none')"
                                class="btn btn-sm icon-option {{ $icono === $clase ? 'btn-primary' : 'btn-outline-secondary' }}"
                                style="width:42px;height:42px;padding:0;display:flex;align-items:center;justify-content:center;">
                            <i class="{{ $clase }}" style="font-size:1.2rem;"></i>
                        </button>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    @error('icono') <span class="text-danger small">{{ $message }}</span> @enderror
</div>

@once
<script>
function filterIcons(query) {
    const q = query.toLowerCase().trim();
    document.querySelectorAll('#iconPicker .icon-option').forEach(btn => {
        const label = btn.dataset.iconLabel ?? '';
        const cls   = btn.dataset.iconClass ?? '';
        btn.closest('.d-flex') && (btn.style.display = (!q || label.includes(q) || cls.includes(q)) ? '' : 'none');
    });
    document.querySelectorAll('#iconPicker .icon-group').forEach(group => {
        const visible = [...group.querySelectorAll('.icon-option')].some(b => b.style.display !== 'none');
        group.style.display = visible ? '' : 'none';
    });
}
</script>
@endonce
