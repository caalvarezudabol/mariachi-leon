@php
    // Campos de cada pestaña (para marcar con un punto rojo la pestaña que tiene errores)
    $camposPestana = [
        'general' => ['nombre_comercial', 'razon_social', 'nit_ruc', 'representante_legal', 'slogan'],
        'contacto' => ['telefono_principal', 'whatsapp_comercial', 'email_contacto', 'direccion_fisica', 'ciudad_pais'],
        'marca' => [
            'logo_file',
            'redes_instagram',
            'redes_tiktok',
            'redes_youtube',
            'redes_facebook',
            'redes_linktree',
        ],
        'banco' => [
            'moneda_nombre',
            'moneda_simbolo',
            'porcentaje_fondo_comun',
            'banco_nombre',
            'banco_numero_cuenta',
            'banco_titular',
            'qr_file',
        ],
        'contratos' => ['terminos_contrato', 'observaciones'],
    ];
    $pestanas = [
        'general' => ['label' => 'Identificación', 'icon' => 'fa-building'],
        'contacto' => ['label' => 'Contacto', 'icon' => 'fa-address-book'],
        'marca' => ['label' => 'Marca y redes', 'icon' => 'fa-icons'],
        'banco' => ['label' => 'Banco y finanzas', 'icon' => 'fa-building-columns'],
        'contratos' => ['label' => 'Contrato', 'icon' => 'fa-file-contract'],
    ];
    $redes = [
        [
            'redes_instagram',
            'Instagram',
            'fa-instagram',
            'text-pink-500',
            'https://www.instagram.com/mariachileonguanajuato',
        ],
        ['redes_tiktok', 'TikTok', 'fa-tiktok', 'text-cyan-500', 'https://www.tiktok.com/@mariachileonguanajuato'],
        ['redes_youtube', 'YouTube', 'fa-youtube', 'text-red-500', 'https://youtube.com/@mariachileonguanajuato'],
        ['redes_facebook', 'Facebook', 'fa-facebook-f', 'text-blue-500', 'https://www.facebook.com/profile.php?id=...'],
    ];
    $campoCss =
        'w-full h-9 px-3.5 bg-surface-sunken border border-outline rounded-lg text-sm text-ink-primary placeholder:text-ink-muted focus:border-gold-500 focus:outline-none transition-colors';
    $botonArchivo =
        'inline-flex items-center gap-2 h-8 px-3 rounded-lg border border-outline bg-surface-card text-sm font-semibold text-ink-primary hover:border-gold-500 cursor-pointer transition-colors';
@endphp

<div class="space-y-3" x-data="{ activeTab: @entangle('activeTab') }">

    @if (session()->has('success'))
        <div
            class="flex items-center gap-3 p-3 rounded-lg border border-emerald-500/30 bg-emerald-500/10 text-sm text-ink-primary">
            <i class="fa-solid fa-circle-check text-lg text-emerald-600 dark:text-emerald-400"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if ($errors->any())
        <div
            class="flex items-start gap-3 p-3 rounded-lg border-l-[3px] border-rose-500 bg-rose-500/10 text-sm text-ink-primary">
            <i class="fa-solid fa-circle-exclamation text-lg text-rose-600 dark:text-rose-400 mt-0.5"></i>
            <div class="min-w-0">
                <p class="font-semibold">Revisa los siguientes campos para poder guardar:</p>
                <ul class="mt-1 list-disc list-inside text-xs text-ink-secondary space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <form wire:submit="guardar">
        <x-ui.panel>
            {{-- Cabecera --}}
            <x-slot:header>
                <div class="flex items-center gap-2.5 min-w-0">
                    <i class="fa-solid fa-building text-lg text-gold-600 dark:text-gold-400"></i>
                    <h1 class="text-base font-semibold text-ink-primary truncate">Empresa</h1>
                </div>
                <x-ui.button size="sm" type="submit" wire:loading.attr="disabled" wire:target="guardar">
                    <i class="fa-solid fa-floppy-disk" wire:loading.remove wire:target="guardar"></i>
                    <i class="fa-solid fa-spinner fa-spin" wire:loading wire:target="guardar"></i>
                    <span>Guardar</span>
                </x-ui.button>
            </x-slot:header>

            {{-- Pestañas --}}
            <x-slot:filters>
                <div class="flex items-center gap-1 overflow-x-auto">
                    @foreach ($pestanas as $clave => $p)
                        @php $conError = collect($camposPestana[$clave])->contains(fn ($c) => $errors->has($c)); @endphp
                        <button type="button" @click="activeTab = '{{ $clave }}'"
                            :class="activeTab === '{{ $clave }}' ?
                                'bg-gold-500/15 text-gold-700 dark:text-gold-400 font-semibold' :
                                'text-ink-secondary hover:text-ink-primary hover:bg-surface-hover'"
                            class="relative inline-flex items-center gap-1.5 h-8 px-3 rounded-lg text-sm whitespace-nowrap transition-colors">
                            <i class="fa-solid {{ $p['icon'] }} text-xs"></i>
                            <span>{{ $p['label'] }}</span>
                            @if ($conError)
                                <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                            @endif
                        </button>
                    @endforeach
                </div>
            </x-slot:filters>

            <div class="px-5 py-5">

                {{-- IDENTIFICACIÓN --}}
                <div x-show="activeTab === 'general'" x-cloak class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-ui.input label="Nombre comercial" required name="nombre_comercial"
                        wire:model="nombre_comercial" />
                    <x-ui.input label="Razón social" required name="razon_social" wire:model="razon_social" />
                    <x-ui.input label="NIT / NIF / Registro fiscal" name="nit_ruc" wire:model="nit_ruc"
                        placeholder="Ej. 1029384756" />
                    <x-ui.input label="Representante legal / Director musical" required name="representante_legal"
                        wire:model="representante_legal" />
                    <div class="sm:col-span-2">
                        <x-ui.input label="Eslogan" name="slogan" wire:model="slogan"
                            placeholder="Puntualidad, elegancia y virtuosismo musical en cada presentación" />
                    </div>
                </div>

                {{-- CONTACTO --}}
                <div x-show="activeTab === 'contacto'" x-cloak class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <x-ui.input label="Teléfono principal" required name="telefono_principal"
                        wire:model="telefono_principal" />
                    <x-ui.input label="WhatsApp comercial" name="whatsapp_comercial" wire:model="whatsapp_comercial" />
                    <x-ui.input type="email" label="Correo oficial" required name="email_contacto"
                        wire:model="email_contacto" />
                    <div class="sm:col-span-2">
                        <x-ui.input label="Dirección de la oficina" required name="direccion_fisica"
                            wire:model="direccion_fisica" />
                    </div>
                    <x-ui.input label="Ciudad / País" name="ciudad_pais" wire:model="ciudad_pais"
                        placeholder="Ej. Santa Cruz - Bolivia" />
                </div>

                {{-- MARCA Y REDES --}}
                <div x-show="activeTab === 'marca'" x-cloak class="space-y-5">
                    @if (session()->has('info_logo'))
                        <div
                            class="flex items-center gap-2.5 p-3 rounded-lg border border-sky-500/30 bg-sky-500/10 text-sm text-ink-primary">
                            <i class="fa-solid fa-circle-info text-sky-600 dark:text-sky-400"></i>
                            <span>{{ session('info_logo') }}</span>
                        </div>
                    @endif

                    <div>
                        <span class="block text-sm font-semibold text-ink-primary mb-2">Logotipo oficial:</span>
                        <div class="flex flex-col sm:flex-row items-start gap-5">
                            <div
                                class="relative w-36 h-36 rounded-lg bg-surface-sunken border border-dashed border-outline flex flex-col items-center justify-center p-2 text-center overflow-hidden shrink-0">
                                @if ($logo_file)
                                    <img src="{{ $logo_file->temporaryUrl() }}"
                                        class="max-w-full max-h-24 object-contain">
                                    <span
                                        class="absolute bottom-1 px-1.5 py-0.5 rounded text-[10px] font-semibold bg-amber-500 text-slate-900">Vista
                                        previa nueva</span>
                                @elseif ($logo_url && !$logo_eliminado)
                                    <img src="{{ asset($logo_url) }}" class="max-w-full max-h-24 object-contain">
                                    <span
                                        class="absolute bottom-1 px-1.5 py-0.5 rounded text-[10px] font-semibold bg-emerald-500/15 text-emerald-700 dark:text-emerald-400 border border-emerald-500/30">Logo
                                        actual</span>
                                @else
                                    <i class="fa-regular fa-image text-3xl text-ink-muted mb-1"></i>
                                    <span class="text-xs text-ink-muted">Sin logotipo</span>
                                @endif

                                <div wire:loading wire:target="logo_file"
                                    class="absolute inset-0 bg-surface-card/80 flex flex-col items-center justify-center text-gold-700 dark:text-gold-400 text-xs font-semibold gap-1.5">
                                    <i class="fa-solid fa-spinner fa-spin text-lg"></i>
                                    <span>Cargando...</span>
                                </div>
                            </div>

                            <div class="space-y-2.5 min-w-0">
                                <p class="text-sm text-ink-secondary">
                                    Se optimiza automáticamente para el sitio, los contratos y los recibos en PDF.
                                    Formatos JPG, PNG o WEBP, hasta 5 MB.
                                </p>
                                @error('logo_file')
                                    <p class="text-xs text-rose-500 font-semibold">{{ $message }}</p>
                                @enderror
                                <div class="flex flex-wrap items-center gap-2">
                                    <label class="{{ $botonArchivo }}">
                                        <i class="fa-solid fa-cloud-arrow-up"></i>
                                        <span>Seleccionar logo</span>
                                        <input type="file" wire:model="logo_file"
                                            accept="image/png,image/jpeg,image/webp" class="hidden">
                                    </label>
                                    @if ($logo_url || $logo_file)
                                        <x-ui.button type="button" size="sm" variant="danger"
                                            wire:click="abrirConfirmacionEliminarLogo">
                                            <i class="fa-solid fa-trash-can"></i>
                                            <span>Quitar logo</span>
                                        </x-ui.button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-outline space-y-3">
                        <div>
                            <h2 class="text-sm font-semibold text-ink-primary">Redes sociales</h2>
                            <p class="text-xs text-ink-secondary">Cada red tendrá su botón en el sitio web público.</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach ($redes as [$campo, $nombre, $icono, $color, $ejemplo])
                                <div>
                                    <label
                                        class="block text-sm font-semibold text-ink-primary mb-1">{{ $nombre }}:</label>
                                    <div class="relative">
                                        <i
                                            class="fa-brands {{ $icono }} {{ $color }} absolute left-3 top-1/2 -translate-y-1/2 text-sm"></i>
                                        <input type="url" wire:model="{{ $campo }}"
                                            placeholder="{{ $ejemplo }}" class="{{ $campoCss }} pl-9">
                                    </div>
                                    @error($campo)
                                        <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span>
                                    @enderror
                                </div>
                            @endforeach
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-ink-primary mb-1">Enlace auxiliar (Linktree
                                o web externa):</label>
                            <div class="relative">
                                <i
                                    class="fa-solid fa-link text-ink-muted absolute left-3 top-1/2 -translate-y-1/2 text-sm"></i>
                                <input type="url" wire:model="redes_linktree"
                                    placeholder="https://linktr.ee/mariachileonguanajuato"
                                    class="{{ $campoCss }} pl-9">
                            </div>
                            @error('redes_linktree')
                                <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- BANCO Y MONEDA --}}
                <div x-show="activeTab === 'banco'" x-cloak class="space-y-5">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <x-ui.input label="Moneda oficial" required name="moneda_nombre" wire:model="moneda_nombre"
                            placeholder="Ej. Bolivianos" />
                        <x-ui.input label="Símbolo" required name="moneda_simbolo" wire:model="moneda_simbolo"
                            placeholder="Ej. Bs." />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-ui.input label="Fondo común sugerido (%)" required type="number" step="0.01"
                                min="0" max="100" name="porcentaje_fondo_comun"
                                wire:model="porcentaje_fondo_comun" />
                            <p class="mt-1 text-xs text-ink-muted">Solo es una sugerencia (ej. 5). Al pagar a una
                                persona se puede cambiar o dejar en 0.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <x-ui.input label="Entidad bancaria" name="banco_nombre" wire:model="banco_nombre"
                            placeholder="Ej. Banco Nacional de Bolivia" />
                        <x-ui.input label="Número de cuenta" name="banco_numero_cuenta"
                            wire:model="banco_numero_cuenta" placeholder="Ej. 1000-2938-4756" />
                        <x-ui.input label="Titular de la cuenta" name="banco_titular" wire:model="banco_titular" />
                    </div>

                    <div class="pt-4 border-t border-outline">
                        <span class="block text-sm font-semibold text-ink-primary mb-2">Código QR de cobro:</span>

                        @if (session()->has('info_qr'))
                            <div
                                class="mb-3 flex items-center gap-2.5 p-3 rounded-lg border border-sky-500/30 bg-sky-500/10 text-sm text-ink-primary">
                                <i class="fa-solid fa-circle-info text-sky-600 dark:text-sky-400"></i>
                                <span>{{ session('info_qr') }}</span>
                            </div>
                        @endif

                        <div class="flex flex-col sm:flex-row items-start gap-5">
                            <div
                                class="relative w-36 h-36 rounded-lg bg-surface-sunken border border-dashed border-outline flex flex-col items-center justify-center p-2 text-center overflow-hidden shrink-0">
                                @if ($qr_file)
                                    <img src="{{ $qr_file->temporaryUrl() }}"
                                        class="max-w-full max-h-24 object-contain">
                                    <span
                                        class="absolute bottom-1 px-1.5 py-0.5 rounded text-[10px] font-semibold bg-amber-500 text-slate-900">Nuevo
                                        QR</span>
                                @elseif ($banco_qr_url && !$qr_eliminado)
                                    <img src="{{ asset($banco_qr_url) }}" class="max-w-full max-h-24 object-contain">
                                    <span
                                        class="absolute bottom-1 px-1.5 py-0.5 rounded text-[10px] font-semibold bg-emerald-500/15 text-emerald-700 dark:text-emerald-400 border border-emerald-500/30">QR
                                        activo</span>
                                @else
                                    <i class="fa-solid fa-qrcode text-3xl text-ink-muted mb-1"></i>
                                    <span class="text-xs text-ink-muted">Sin QR asignado</span>
                                @endif

                                <div wire:loading wire:target="qr_file"
                                    class="absolute inset-0 bg-surface-card/80 flex flex-col items-center justify-center text-gold-700 dark:text-gold-400 text-xs font-semibold gap-1.5">
                                    <i class="fa-solid fa-spinner fa-spin text-lg"></i>
                                    <span>Cargando...</span>
                                </div>
                            </div>

                            <div class="space-y-2.5 min-w-0">
                                <p class="text-sm text-ink-secondary">
                                    El QR de tu banco se adjunta en los recibos digitales y órdenes de anticipo.
                                    Formatos JPG, PNG o WEBP, hasta 5 MB.
                                </p>
                                @error('qr_file')
                                    <p class="text-xs text-rose-500 font-semibold">{{ $message }}</p>
                                @enderror
                                <div class="flex flex-wrap items-center gap-2">
                                    <label class="{{ $botonArchivo }}">
                                        <i class="fa-solid fa-qrcode"></i>
                                        <span>Cargar imagen QR</span>
                                        <input type="file" wire:model="qr_file"
                                            accept="image/png,image/jpeg,image/webp" class="hidden">
                                    </label>
                                    @if ($banco_qr_url || $qr_file)
                                        <x-ui.button type="button" size="sm" variant="danger"
                                            wire:click="abrirConfirmacionEliminarQr">
                                            <i class="fa-solid fa-trash-can"></i>
                                            <span>Quitar QR</span>
                                        </x-ui.button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- CONTRATO --}}
                <div x-show="activeTab === 'contratos'" x-cloak class="space-y-4">
                    <x-ui.textarea label="Términos y condiciones de los contratos (PDF)" name="terminos_contrato"
                        rows="1" class="!min-h-[180px]" wire:model="terminos_contrato"
                        placeholder="Horas de anticipación, tolerancia de espera, compromisos de la agrupación..." />
                    <x-ui.textarea label="Observaciones generales" name="observaciones" rows="1"
                        class="!min-h-[72px]" wire:model="observaciones" />
                </div>
            </div>
        </x-ui.panel>
    </form>

    {{-- Confirmación: quitar logo --}}
    @if ($showConfirmDeleteLogo)
        <x-ui.modal icon="fa-solid fa-triangle-exclamation" title="Quitar el logotipo" maxWidth="max-w-md"
            onClose="cancelarEliminarLogo">
            <div class="px-6 py-5">
                <p class="text-sm text-ink-primary">
                    El logotipo se desvincula de la empresa y de los encabezados de los documentos oficiales (PDF y
                    recibos).
                    Para confirmar, después presiona <strong>Guardar</strong>.
                </p>
            </div>
            <x-slot:footer>
                <x-ui.button type="button" variant="secondary" wire:click="cancelarEliminarLogo">
                    <i class="fa-solid fa-xmark"></i>
                    <span>Cancelar</span>
                </x-ui.button>
                <x-ui.button type="button" variant="danger" wire:click="eliminarLogo">
                    <i class="fa-solid fa-trash-can"></i>
                    <span>Quitar logo</span>
                </x-ui.button>
            </x-slot:footer>
        </x-ui.modal>
    @endif

    {{-- Confirmación: quitar QR --}}
    @if ($showConfirmDeleteQr)
        <x-ui.modal icon="fa-solid fa-triangle-exclamation" title="Quitar el código QR" maxWidth="max-w-md"
            onClose="cancelarEliminarQr">
            <div class="px-6 py-5">
                <p class="text-sm text-ink-primary">
                    La imagen del QR se elimina y no aparecerá en recibos ni órdenes de pago.
                    Para confirmar, después presiona <strong>Guardar</strong>.
                </p>
            </div>
            <x-slot:footer>
                <x-ui.button type="button" variant="secondary" wire:click="cancelarEliminarQr">
                    <i class="fa-solid fa-xmark"></i>
                    <span>Cancelar</span>
                </x-ui.button>
                <x-ui.button type="button" variant="danger" wire:click="eliminarQr">
                    <i class="fa-solid fa-trash-can"></i>
                    <span>Quitar QR</span>
                </x-ui.button>
            </x-slot:footer>
        </x-ui.modal>
    @endif
</div>