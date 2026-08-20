<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Módulo Gestionar Empresa / Agrupación</h1>
            <p class="text-xs text-slate-400">Administración centralizada de datos institucionales, legales, de contacto, marca y financieros.</p>
        </div>
        <button wire:click="guardar" class="px-5 py-2.5 rounded-xl text-sm font-bold bg-gradient-to-r from-gold-500 to-gold-600 text-slate-950 hover:from-gold-400 hover:to-gold-500 shadow-lg shadow-gold-500/20 transition-all flex items-center justify-center gap-2">
            <i class="fa-solid fa-floppy-disk"></i>
            <span>Guardar Cambios</span>
        </button>
    </div>

    <!-- Flash Messages -->
    @if(session()->has('success'))
        <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-bold flex items-center gap-2">
            <i class="fa-solid fa-circle-check"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Company Quick Highlights -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-brand-card p-4 rounded-2xl border border-brand-border flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-gold-500/20 text-gold-400 flex items-center justify-center text-xl font-bold">
                🏢
            </div>
            <div>
                <div class="text-[11px] uppercase font-bold text-slate-400">Nombre Comercial</div>
                <div class="text-sm font-bold text-white truncate max-w-[180px]">{{ $nombre_comercial }}</div>
            </div>
        </div>

        <div class="bg-brand-card p-4 rounded-2xl border border-brand-border flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-xl font-bold">
                👤
            </div>
            <div>
                <div class="text-[11px] uppercase font-bold text-slate-400">Representante Legal</div>
                <div class="text-sm font-bold text-white truncate max-w-[180px]">{{ $representante_legal }}</div>
            </div>
        </div>

        <div class="bg-brand-card p-4 rounded-2xl border border-brand-border flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-500/20 text-blue-400 flex items-center justify-center text-xl font-bold">
                📞
            </div>
            <div>
                <div class="text-[11px] uppercase font-bold text-slate-400">Teléfono Comercial</div>
                <div class="text-sm font-bold text-white truncate max-w-[180px]">{{ $telefono_principal }}</div>
            </div>
        </div>

        <div class="bg-brand-card p-4 rounded-2xl border border-brand-border flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-cyan-500/20 text-cyan-400 flex items-center justify-center text-xl font-bold">
                💵
            </div>
            <div>
                <div class="text-[11px] uppercase font-bold text-slate-400">Moneda Oficial</div>
                <div class="text-sm font-bold text-gold-400 font-mono">{{ $moneda_simbolo }} ({{ $moneda_nombre }})</div>
            </div>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div class="flex items-center gap-2 border-b border-brand-border overflow-x-auto pb-1">
        <button wire:click="setTab('general')" class="px-4 py-2.5 rounded-t-xl text-xs font-bold transition-all flex items-center gap-2 {{ $activeTab === 'general' ? 'bg-gold-500 text-slate-950 shadow-md' : 'text-slate-400 hover:text-white hover:bg-slate-900' }}">
            <i class="fa-solid fa-building"></i>
            <span>1. Identificación & Legal</span>
        </button>
        <button wire:click="setTab('contacto')" class="px-4 py-2.5 rounded-t-xl text-xs font-bold transition-all flex items-center gap-2 {{ $activeTab === 'contacto' ? 'bg-gold-500 text-slate-950 shadow-md' : 'text-slate-400 hover:text-white hover:bg-slate-900' }}">
            <i class="fa-solid fa-address-book"></i>
            <span>2. Contacto & Ubicación</span>
        </button>
        <button wire:click="setTab('marca')" class="px-4 py-2.5 rounded-t-xl text-xs font-bold transition-all flex items-center gap-2 {{ $activeTab === 'marca' ? 'bg-gold-500 text-slate-950 shadow-md' : 'text-slate-400 hover:text-white hover:bg-slate-900' }}">
            <i class="fa-solid fa-icons"></i>
            <span>3. Marca & Redes</span>
        </button>
        <button wire:click="setTab('banco')" class="px-4 py-2.5 rounded-t-xl text-xs font-bold transition-all flex items-center gap-2 {{ $activeTab === 'banco' ? 'bg-gold-500 text-slate-950 shadow-md' : 'text-slate-400 hover:text-white hover:bg-slate-900' }}">
            <i class="fa-solid fa-building-columns"></i>
            <span>4. Banco & Moneda</span>
        </button>
        <button wire:click="setTab('contratos')" class="px-4 py-2.5 rounded-t-xl text-xs font-bold transition-all flex items-center gap-2 {{ $activeTab === 'contratos' ? 'bg-gold-500 text-slate-950 shadow-md' : 'text-slate-400 hover:text-white hover:bg-slate-900' }}">
            <i class="fa-solid fa-file-contract"></i>
            <span>5. Términos de Contrato</span>
        </button>
    </div>

    <!-- Main Content Form Card -->
    <div class="bg-brand-card p-6 rounded-3xl border border-brand-border shadow-2xl">
        <form wire:submit.prevent="guardar" class="space-y-6">

            <!-- TAB 1: IDENTIFICACIÓN Y LEGAL -->
            @if($activeTab === 'general')
                <div class="space-y-4">
                    <h3 class="text-base font-bold text-white border-b border-brand-border pb-3 flex items-center gap-2">
                        <i class="fa-solid fa-building text-gold-400"></i>
                        <span>Datos Identificativos & Legales</span>
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Nombre Comercial de la Agrupación</label>
                            <input type="text" wire:model.defer="nombre_comercial" required class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500 font-bold">
                            @error('nombre_comercial') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Razón Social Oficial</label>
                            <input type="text" wire:model.defer="razon_social" required class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
                            @error('razon_social') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">NIT / NIF / Registro Fiscal</label>
                            <input type="text" wire:model.defer="nit_ruc" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500 font-mono" placeholder="Ej. 1029384756">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Representante Legal / Director Musical</label>
                            <input type="text" wire:model.defer="representante_legal" required class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500 font-bold text-gold-300">
                            @error('representante_legal') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Slogan / Frase Institucional</label>
                        <input type="text" wire:model.defer="slogan" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500 italic" placeholder="Puntualidad, elegancia y virtuosismo musical en cada presentación">
                    </div>
                </div>
            @endif

            <!-- TAB 2: CONTACTO Y UBICACIÓN -->
            @if($activeTab === 'contacto')
                <div class="space-y-4">
                    <h3 class="text-base font-bold text-white border-b border-brand-border pb-3 flex items-center gap-2">
                        <i class="fa-solid fa-address-book text-gold-400"></i>
                        <span>Información de Contacto & Ubicación Física</span>
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Teléfono Principal</label>
                            <input type="text" wire:model.defer="telefono_principal" required class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500 font-mono">
                            @error('telefono_principal') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">WhatsApp Comercial</label>
                            <input type="text" wire:model.defer="whatsapp_comercial" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500 font-mono">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Correo Electrónico Oficial</label>
                            <input type="email" wire:model.defer="email_contacto" required class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
                            @error('email_contacto') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Dirección Física de Oficina</label>
                            <input type="text" wire:model.defer="direccion_fisica" required class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
                            @error('direccion_fisica') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Ciudad / País</label>
                            <input type="text" wire:model.defer="ciudad_pais" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500" placeholder="Ej. Santa Cruz - Bolivia">
                        </div>
                    </div>
                </div>
            @endif

            <!-- TAB 3: MARCA Y REDES SOCIALES (NUEVO SISTEMA DE CARGA DE LOGO) -->
            @if($activeTab === 'marca')
                <div class="space-y-6">
                    <h3 class="text-base font-bold text-white border-b border-brand-border pb-3 flex items-center gap-2">
                        <i class="fa-solid fa-icons text-gold-400"></i>
                        <span>Identidad Visual & Redes Sociales</span>
                    </h3>

                    <!-- Información de Estado del Logo -->
                    @if(session()->has('info_logo'))
                        <div class="p-4 rounded-xl bg-blue-500/10 border border-blue-500/30 text-blue-400 text-xs font-bold flex items-center gap-2">
                            <i class="fa-solid fa-circle-info"></i>
                            <span>{{ session('info_logo') }}</span>
                        </div>
                    @endif

                    @error('logo_file')
                        <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-xs font-bold flex items-center gap-2">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                            <span>{{ $message }}</span>
                        </div>
                    @enderror

                    <!-- COMPONENTE DE CARGA DE LOGOTIPO -->
                    <div class="bg-slate-950 p-6 rounded-2xl border border-slate-800 space-y-4">
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider">
                            Logotipo Oficial de la Agrupación
                        </label>

                        <div class="flex flex-col sm:flex-row items-center gap-6">
                            <!-- Recuadro Vista Previa -->
                            <div class="relative w-44 h-44 rounded-2xl bg-slate-900 border-2 border-dashed border-slate-700 flex flex-col items-center justify-center p-3 text-center overflow-hidden flex-shrink-0 group">
                                @if($logo_file)
                                    <img src="{{ $logo_file->temporaryUrl() }}" class="max-w-full max-h-32 object-contain rounded-lg">
                                    <span class="absolute bottom-1 px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500 text-slate-950 shadow">
                                        Vista Previa Nueva
                                    </span>
                                @elseif($logo_url && !$logo_eliminado)
                                    <img src="{{ asset($logo_url) }}" class="max-w-full max-h-32 object-contain rounded-lg">
                                    <span class="absolute bottom-1 px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                                        Logo Actual
                                    </span>
                                @else
                                    <div class="text-slate-600 text-4xl mb-1">
                                        <i class="fa-solid fa-image"></i>
                                    </div>
                                    <span class="text-[11px] text-slate-500 font-semibold">Sin Logotipo</span>
                                @endif

                                <!-- Loading overlay -->
                                <div wire:loading wire:target="logo_file" class="absolute inset-0 bg-slate-950/80 flex flex-col items-center justify-center text-gold-400 text-xs font-bold">
                                    <i class="fa-solid fa-circle-notch fa-spin text-2xl mb-1"></i>
                                    <span>Cargando...</span>
                                </div>
                            </div>

                            <!-- Botones e Instrucciones -->
                            <div class="space-y-3 flex-1 text-center sm:text-left">
                                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-3">
                                    <!-- Botón Cargar/Cambiar File Input -->
                                    <label class="cursor-pointer px-4 py-2.5 rounded-xl text-xs font-bold bg-gold-500 text-slate-950 hover:bg-gold-400 shadow-md transition-all inline-flex items-center gap-2">
                                        <i class="fa-solid fa-folder-open"></i>
                                        <span>{{ ($logo_url || $logo_file) ? 'Cambiar Imagen' : 'Seleccionar Imagen' }}</span>
                                        <input type="file" wire:model="logo_file" accept=".jpg,.jpeg,.png,.webp" class="hidden">
                                    </label>

                                    <!-- Botón Eliminar Logo -->
                                    @if(($logo_url && !$logo_eliminado) || $logo_file)
                                        <button type="button" wire:click="abrirConfirmacionEliminarLogo" class="px-4 py-2.5 rounded-xl text-xs font-bold bg-rose-500/10 text-rose-400 border border-rose-500/20 hover:bg-rose-500/20 transition-all inline-flex items-center gap-2">
                                            <i class="fa-solid fa-trash-can"></i>
                                            <span>Eliminar Logo</span>
                                        </button>
                                    @endif
                                </div>

                                <div class="text-xs text-slate-400 space-y-1">
                                    <p><strong class="text-slate-300">Formatos permitidos:</strong> JPG, JPEG, PNG, WEBP.</p>
                                    <p><strong class="text-slate-300">Tamaño máximo de archivo:</strong> 5 MB.</p>
                                    <p class="text-[11px] text-slate-500 italic">Las imágenes de alta resolución (>2000px) se optimizarán y comprimirán automáticamente sin perder calidad visual para su uso en la web y documentos PDF.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- MODAL CONFIRMACIÓN DE ELIMINACIÓN DE LOGO -->
                    @if($showConfirmDeleteLogo)
                        <div class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
                            <div class="bg-brand-card border border-brand-border p-6 rounded-3xl max-w-md w-full space-y-4 shadow-2xl">
                                <div class="flex items-center gap-3 text-rose-400 text-lg font-bold">
                                    <i class="fa-solid fa-triangle-exclamation text-2xl"></i>
                                    <span>¿Eliminar Logotipo Oficial?</span>
                                </div>
                                <p class="text-xs text-slate-300 leading-relaxed">
                                    ¿Está seguro de que desea eliminar el logotipo oficial de la empresa? Al guardar los cambios, el archivo físico será borrado definitivamente.
                                </p>
                                <div class="flex items-center justify-end gap-3 pt-2">
                                    <button type="button" wire:click="cancelarEliminarLogo" class="px-4 py-2 rounded-xl text-xs font-semibold bg-slate-800 text-slate-300 hover:bg-slate-700">
                                        Cancelar
                                    </button>
                                    <button type="button" wire:click="eliminarLogo" class="px-4 py-2 rounded-xl text-xs font-bold bg-rose-600 text-white hover:bg-rose-500 shadow-md">
                                        Sí, Eliminar Logo
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Campo Redes Sociales (LINKTREE - INTACTO) -->
                    <div class="pt-4 border-t border-brand-border">
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Enlace Oficial Redes Sociales (Linktree)</label>
                        <input type="url" wire:model.defer="redes_linktree" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500 font-mono" placeholder="https://linktr.ee/mariachileonguanajuato">
                        @error('redes_linktree') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>
            @endif

            <!-- TAB 4: DATOS BANCARIOS Y MONEDA -->
            @if($activeTab === 'banco')
                <div class="space-y-4">
                    <h3 class="text-base font-bold text-white border-b border-brand-border pb-3 flex items-center gap-2">
                        <i class="fa-solid fa-building-columns text-gold-400"></i>
                        <span>Datos Bancarios para Transferencias & Configuración de Moneda</span>
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Nombre de Moneda</label>
                            <input type="text" wire:model.defer="moneda_nombre" required class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Símbolo Oficial de Moneda</label>
                            <input type="text" wire:model.defer="moneda_simbolo" required class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500 font-mono font-bold text-gold-400">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Nombre del Banco</label>
                            <input type="text" wire:model.defer="banco_nombre" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500" placeholder="Ej. Banco Nacional de Bolivia">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Número de Cuenta Bancaria</label>
                            <input type="text" wire:model.defer="banco_numero_cuenta" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500 font-mono" placeholder="Ej. 1000-2938-4756">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Titular de la Cuenta</label>
                            <input type="text" wire:model.defer="banco_titular" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500" placeholder="Ej. Enrrique Escalera">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">URL / Ruta de Imagen QR para Pagos</label>
                        <input type="text" wire:model.defer="banco_qr_url" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500 font-mono" placeholder="/assets/images/qr_pago.png">
                    </div>
                </div>
            @endif

            <!-- TAB 5: TÉRMINOS Y CONDICIONES DE CONTRATO -->
            @if($activeTab === 'contratos')
                <div class="space-y-4">
                    <h3 class="text-base font-bold text-white border-b border-brand-border pb-3 flex items-center gap-2">
                        <i class="fa-solid fa-file-contract text-gold-400"></i>
                        <span>Términos & Condiciones Estándar para Contratos PDF</span>
                    </h3>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Cláusulas & Compromisos del Mariachi León Guanajuato</label>
                        <textarea wire:model.defer="terminos_contrato" rows="6" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500" placeholder="Escriba los términos legales, horas de anticipación, tolerancia de espera y compromisos de la agrupación..."></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Observaciones Generales de la Empresa</label>
                        <textarea wire:model.defer="observaciones" rows="2" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500" placeholder="Observaciones o notas adicionales de la gestión de la empresa..."></textarea>
                    </div>
                </div>
            @endif

            <!-- Submit Buttons -->
            <div class="flex items-center justify-end gap-4 border-t border-brand-border pt-4">
                <button type="submit" class="px-6 py-3 rounded-xl text-sm font-bold bg-gold-500 text-slate-950 hover:bg-gold-400 shadow-lg shadow-gold-500/20 transition-all flex items-center gap-2">
                    <i class="fa-solid fa-floppy-disk"></i>
                    <span>Guardar Todos los Datos de la Empresa</span>
                </button>
            </div>
        </form>
    </div>
</div>
