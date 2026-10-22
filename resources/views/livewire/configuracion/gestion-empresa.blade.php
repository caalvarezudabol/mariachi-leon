<div class="space-y-6" x-data="{ activeTab: @entangle('activeTab') }">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Módulo Gestionar Empresa / Agrupación</h1>
            <p class="text-xs text-slate-400">Administración centralizada de datos institucionales, legales, de contacto, marca y financieros.</p>
        </div>
        <button type="submit" form="formEmpresa" wire:loading.attr="disabled" class="px-5 py-2.5 rounded-xl text-sm font-bold bg-gradient-to-r from-gold-500 to-gold-600 text-slate-950 hover:from-gold-400 hover:to-gold-500 shadow-lg shadow-gold-500/20 transition-all flex items-center justify-center gap-2">
            <span wire:loading.remove wire:target="guardar"><i class="fa-solid fa-floppy-disk"></i> Guardar Cambios</span>
            <span wire:loading wire:target="guardar"><i class="fa-solid fa-spinner animate-spin"></i> Guardando...</span>
        </button>
    </div>

    <!-- Flash Messages -->
    @if(session()->has('success'))
        <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-bold flex items-center gap-2 shadow-lg animate-fade-in">
            <i class="fa-solid fa-circle-check text-base"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Global Error Banner -->
    @if ($errors->any())
        <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-xs font-bold space-y-1.5 shadow-lg animate-fade-in">
            <div class="flex items-center gap-2 text-sm font-extrabold text-rose-300">
                <i class="fa-solid fa-circle-exclamation text-base"></i>
                <span>Por favor revise los siguientes campos para poder guardar:</span>
            </div>
            <ul class="list-disc list-inside pl-2 space-y-0.5 text-rose-300 font-normal">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
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
        <button type="button" @click="activeTab = 'general'" :class="activeTab === 'general' ? 'bg-gold-500 text-slate-950 shadow-md' : 'text-slate-400 hover:text-white hover:bg-slate-900'" class="px-4 py-2.5 rounded-t-xl text-xs font-bold transition-all flex items-center gap-2">
            <i class="fa-solid fa-building"></i>
            <span>1. Identificación & Legal</span>
        </button>
        <button type="button" @click="activeTab = 'contacto'" :class="activeTab === 'contacto' ? 'bg-gold-500 text-slate-950 shadow-md' : 'text-slate-400 hover:text-white hover:bg-slate-900'" class="px-4 py-2.5 rounded-t-xl text-xs font-bold transition-all flex items-center gap-2">
            <i class="fa-solid fa-address-book"></i>
            <span>2. Contacto & Ubicación</span>
        </button>
        <button type="button" @click="activeTab = 'marca'" :class="activeTab === 'marca' ? 'bg-gold-500 text-slate-950 shadow-md' : 'text-slate-400 hover:text-white hover:bg-slate-900'" class="px-4 py-2.5 rounded-t-xl text-xs font-bold transition-all flex items-center gap-2">
            <i class="fa-solid fa-icons"></i>
            <span>3. Marca & Redes</span>
        </button>
        <button type="button" @click="activeTab = 'banco'" :class="activeTab === 'banco' ? 'bg-gold-500 text-slate-950 shadow-md' : 'text-slate-400 hover:text-white hover:bg-slate-900'" class="px-4 py-2.5 rounded-t-xl text-xs font-bold transition-all flex items-center gap-2">
            <i class="fa-solid fa-building-columns"></i>
            <span>4. Banco & Moneda</span>
        </button>
        <button type="button" @click="activeTab = 'contratos'" :class="activeTab === 'contratos' ? 'bg-gold-500 text-slate-950 shadow-md' : 'text-slate-400 hover:text-white hover:bg-slate-900'" class="px-4 py-2.5 rounded-t-xl text-xs font-bold transition-all flex items-center gap-2">
            <i class="fa-solid fa-file-contract"></i>
            <span>5. Términos de Contrato</span>
        </button>
    </div>

    <!-- Main Content Form Card -->
    <div class="bg-brand-card p-6 rounded-3xl border border-brand-border shadow-2xl">
        <form id="formEmpresa" wire:submit="guardar" class="space-y-6">

            <!-- TAB 1: IDENTIFICACIÓN Y LEGAL -->
            <div x-show="activeTab === 'general'" x-cloak class="space-y-4">
                <h3 class="text-base font-bold text-white border-b border-brand-border pb-3 flex items-center gap-2">
                    <i class="fa-solid fa-building text-gold-400"></i>
                    <span>Datos Identificativos & Legales</span>
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Nombre Comercial de la Agrupación *</label>
                        <input type="text" wire:model="nombre_comercial" required class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500 font-bold">
                        @error('nombre_comercial') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Razón Social Oficial *</label>
                        <input type="text" wire:model="razon_social" required class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
                        @error('razon_social') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">NIT / NIF / Registro Fiscal</label>
                        <input type="text" wire:model="nit_ruc" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500 font-mono" placeholder="Ej. 1029384756">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Representante Legal / Director Musical *</label>
                        <input type="text" wire:model="representante_legal" required class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500 font-bold text-gold-300">
                        @error('representante_legal') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Slogan / Frase Institucional</label>
                    <input type="text" wire:model="slogan" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500 italic" placeholder="Puntualidad, elegancia y virtuosismo musical en cada presentación">
                </div>
            </div>

            <!-- TAB 2: CONTACTO Y UBICACIÓN -->
            <div x-show="activeTab === 'contacto'" x-cloak class="space-y-4">
                <h3 class="text-base font-bold text-white border-b border-brand-border pb-3 flex items-center gap-2">
                    <i class="fa-solid fa-address-book text-gold-400"></i>
                    <span>Información de Contacto & Ubicación Física</span>
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Teléfono Principal *</label>
                        <input type="text" wire:model="telefono_principal" required class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500 font-mono">
                        @error('telefono_principal') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">WhatsApp Comercial</label>
                        <input type="text" wire:model="whatsapp_comercial" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500 font-mono">
                        @error('whatsapp_comercial') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Correo Electrónico Oficial *</label>
                        <input type="email" wire:model="email_contacto" required class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
                        @error('email_contacto') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Dirección Física de Oficina *</label>
                        <input type="text" wire:model="direccion_fisica" required class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
                        @error('direccion_fisica') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Ciudad / País</label>
                        <input type="text" wire:model="ciudad_pais" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500" placeholder="Ej. Santa Cruz - Bolivia">
                        @error('ciudad_pais') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <!-- TAB 3: MARCA Y REDES SOCIALES -->
            <div x-show="activeTab === 'marca'" x-cloak class="space-y-6">
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
                                    <i class="fa-regular fa-image"></i>
                                </div>
                                <span class="text-[11px] text-slate-500">Sin logotipo</span>
                            @endif

                            <div wire:loading wire:target="logo_file" class="absolute inset-0 bg-slate-950/80 backdrop-blur-xs flex flex-col items-center justify-center text-gold-400 text-xs font-bold gap-2">
                                <i class="fa-solid fa-spinner animate-spin text-xl"></i>
                                <span>Cargando...</span>
                            </div>
                        </div>

                        <!-- Botones de Acción Logo -->
                        <div class="space-y-3 w-full">
                            <p class="text-xs text-slate-400 leading-relaxed">
                                Cargue el logotipo institucional en alta resolución. Se optimizará automáticamente a formato WebP o PNG transparente para su uso en encabezados, PDFs de contratos y recibos oficiales.
                            </p>
                            <p class="text-[11px] text-slate-500 font-mono">Formatos permitidos: JPG, PNG, WEBP. Tamaño máximo: 5 MB.</p>

                            <div class="flex flex-wrap items-center gap-3 pt-2">
                                <label class="cursor-pointer px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-gold-400 text-xs font-bold border border-gold-500/30 hover:border-gold-500/50 transition-all flex items-center gap-2 shadow">
                                    <i class="fa-solid fa-cloud-arrow-up"></i>
                                    <span>Seleccionar Nuevo Logo</span>
                                    <input type="file" wire:model="logo_file" accept="image/png,image/jpeg,image/webp" class="hidden">
                                </label>

                                @if($logo_url || $logo_file)
                                    <button type="button" wire:click="abrirConfirmacionEliminarLogo" class="px-4 py-2.5 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 text-xs font-bold border border-rose-500/20 transition-all flex items-center gap-2">
                                        <i class="fa-solid fa-trash-can"></i>
                                        <span>Quitar Logo</span>
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Confirmación Eliminar Logo -->
                @if($showConfirmDeleteLogo)
                    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm animate-fade-in">
                        <div class="bg-brand-card border border-brand-border rounded-3xl w-full max-w-md p-6 space-y-4 shadow-2xl">
                            <div class="flex items-center gap-3 text-rose-400">
                                <div class="w-10 h-10 rounded-2xl bg-rose-500/10 flex items-center justify-center text-xl shrink-0">
                                    <i class="fa-solid fa-triangle-exclamation"></i>
                                </div>
                                <h4 class="text-base font-bold text-white">¿Quitar el Logotipo Actual?</h4>
                            </div>
                            <p class="text-xs text-slate-300 leading-relaxed">
                                El logotipo será desvinculado de la empresa y de los encabezados de los documentos oficiales (PDFs y recibos). Deberá hacer clic en "Guardar Cambios" para confirmar.
                            </p>
                            <div class="flex items-center justify-end gap-3 pt-2 border-t border-brand-border">
                                <button type="button" wire:click="cancelarEliminarLogo" class="px-4 py-2 rounded-xl text-xs font-bold bg-slate-800 text-slate-300 hover:bg-slate-700">
                                    Cancelar
                                </button>
                                <button type="button" wire:click="eliminarLogo" class="px-4 py-2 rounded-xl text-xs font-bold bg-rose-600 text-white hover:bg-rose-500">
                                    Sí, Quitar Logo
                                </button>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- REDES SOCIALES OFICIALES (BOTONES INDIVIDUALES) -->
                <div class="space-y-4 pt-4 border-t border-brand-border">
                    <div>
                        <h4 class="text-sm font-bold text-white flex items-center gap-2">
                            <i class="fa-solid fa-share-nodes text-gold-400"></i>
                            <span>Canales & Redes Sociales Oficiales</span>
                        </h4>
                        <p class="text-xs text-slate-400 mt-1">
                            Configure las URLs oficiales para cada plataforma. Cada una contará con su propio botón e icono distintivo en el sitio web público.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Instagram -->
                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2 flex items-center gap-2">
                                <i class="fa-brands fa-instagram text-pink-400 text-sm"></i>
                                <span>Instagram Oficial</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-pink-400 text-sm">
                                    <i class="fa-brands fa-instagram"></i>
                                </span>
                                <input type="url" wire:model="redes_instagram" class="w-full pl-10 pr-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-pink-500 font-mono text-xs" placeholder="https://www.instagram.com/mariachileonguanajuato">
                            </div>
                            @error('redes_instagram') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- TikTok -->
                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2 flex items-center gap-2">
                                <i class="fa-brands fa-tiktok text-cyan-400 text-sm"></i>
                                <span>TikTok Oficial</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-cyan-400 text-sm">
                                    <i class="fa-brands fa-tiktok"></i>
                                </span>
                                <input type="url" wire:model="redes_tiktok" class="w-full pl-10 pr-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-cyan-500 font-mono text-xs" placeholder="https://www.tiktok.com/@mariachileonguanajuato">
                            </div>
                            @error('redes_tiktok') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- YouTube -->
                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2 flex items-center gap-2">
                                <i class="fa-brands fa-youtube text-red-500 text-sm"></i>
                                <span>Canal de YouTube</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-red-500 text-sm">
                                    <i class="fa-brands fa-youtube"></i>
                                </span>
                                <input type="url" wire:model="redes_youtube" class="w-full pl-10 pr-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-red-500 font-mono text-xs" placeholder="http://youtube.com/@mariachileonguanajuato">
                            </div>
                            @error('redes_youtube') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Facebook -->
                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2 flex items-center gap-2">
                                <i class="fa-brands fa-facebook-f text-blue-500 text-sm"></i>
                                <span>Página de Facebook</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-blue-500 text-sm">
                                    <i class="fa-brands fa-facebook-f"></i>
                                </span>
                                <input type="url" wire:model="redes_facebook" class="w-full pl-10 pr-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-blue-500 font-mono text-xs" placeholder="https://www.facebook.com/profile.php?id=...">
                            </div>
                            @error('redes_facebook') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Enlace Adicional Linktree (Opcional) -->
                    <div class="pt-2">
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Enlace Auxiliar (Linktree / Web Externa - Opcional)</label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500 text-sm">
                                <i class="fa-solid fa-link"></i>
                            </span>
                            <input type="url" wire:model="redes_linktree" class="w-full pl-10 pr-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500 font-mono text-xs" placeholder="https://linktr.ee/mariachileonguanajuato">
                        </div>
                        @error('redes_linktree') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <!-- TAB 4: BANCO Y MONEDA -->
            <div x-show="activeTab === 'banco'" x-cloak class="space-y-6">
                <h3 class="text-base font-bold text-white border-b border-brand-border pb-3 flex items-center gap-2">
                    <i class="fa-solid fa-building-columns text-gold-400"></i>
                    <span>Parámetros Financieros & Cobranzas QR</span>
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Nombre Oficial de la Moneda *</label>
                        <input type="text" wire:model="moneda_nombre" required class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500" placeholder="Ej. Bolivianos, Pesos">
                        @error('moneda_nombre') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Símbolo Monetario *</label>
                        <input type="text" wire:model="moneda_simbolo" required class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500 font-mono font-bold" placeholder="Ej. Bs., $">
                        @error('moneda_simbolo') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Entidad Bancaria</label>
                        <input type="text" wire:model="banco_nombre" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500" placeholder="Ej. Banco Nacional de Bolivia">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Número de Cuenta Bancaria</label>
                        <input type="text" wire:model="banco_numero_cuenta" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500 font-mono" placeholder="Ej. 1000-2938-4756">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Titular de la Cuenta</label>
                        <input type="text" wire:model="banco_titular" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500" placeholder="Ej. Enrrique Escalera">
                    </div>
                </div>

                <!-- CARGA DE CÓDIGO QR PARA COBROS -->
                <div class="bg-slate-950 p-6 rounded-2xl border border-slate-800 space-y-4">
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider">
                        Código QR Oficial de Cobro Bancario
                    </label>

                    @if(session()->has('info_qr'))
                        <div class="p-3 rounded-xl bg-blue-500/10 border border-blue-500/30 text-blue-400 text-xs font-bold flex items-center gap-2">
                            <i class="fa-solid fa-circle-info"></i>
                            <span>{{ session('info_qr') }}</span>
                        </div>
                    @endif

                    @error('qr_file')
                        <div class="p-3 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-xs font-bold flex items-center gap-2">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                            <span>{{ $message }}</span>
                        </div>
                    @enderror

                    <div class="flex flex-col sm:flex-row items-center gap-6">
                        <div class="relative w-40 h-40 rounded-2xl bg-slate-900 border-2 border-dashed border-slate-700 flex flex-col items-center justify-center p-3 text-center overflow-hidden flex-shrink-0">
                            @if($qr_file)
                                <img src="{{ $qr_file->temporaryUrl() }}" class="max-w-full max-h-32 object-contain rounded-lg">
                                <span class="absolute bottom-1 px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500 text-slate-950 shadow">
                                    Nuevo QR
                                </span>
                            @elseif($banco_qr_url && !$qr_eliminado)
                                <img src="{{ asset($banco_qr_url) }}" class="max-w-full max-h-32 object-contain rounded-lg">
                                <span class="absolute bottom-1 px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                                    QR Activo
                                </span>
                            @else
                                <div class="text-slate-600 text-4xl mb-1">
                                    <i class="fa-solid fa-qrcode"></i>
                                </div>
                                <span class="text-[11px] text-slate-500">Sin QR asignado</span>
                            @endif

                            <div wire:loading wire:target="qr_file" class="absolute inset-0 bg-slate-950/80 backdrop-blur-xs flex flex-col items-center justify-center text-gold-400 text-xs font-bold gap-2">
                                <i class="fa-solid fa-spinner animate-spin text-xl"></i>
                                <span>Cargando...</span>
                            </div>
                        </div>

                        <div class="space-y-3 w-full">
                            <p class="text-xs text-slate-400 leading-relaxed">
                                Suba el código QR generado por su banco para transferencias y pagos instantáneos. Este QR se adjuntará en recibos digitales y órdenes de anticipo.
                            </p>
                            <p class="text-[11px] text-slate-500 font-mono">Formatos: JPG, PNG, WEBP. Máx: 5 MB.</p>

                            <div class="flex flex-wrap items-center gap-3 pt-2">
                                <label class="cursor-pointer px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-gold-400 text-xs font-bold border border-gold-500/30 hover:border-gold-500/50 transition-all flex items-center gap-2 shadow">
                                    <i class="fa-solid fa-qrcode"></i>
                                    <span>Cargar Imagen QR</span>
                                    <input type="file" wire:model="qr_file" accept="image/png,image/jpeg,image/webp" class="hidden">
                                </label>

                                @if($banco_qr_url || $qr_file)
                                    <button type="button" wire:click="abrirConfirmacionEliminarQr" class="px-4 py-2.5 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 text-xs font-bold border border-rose-500/20 transition-all flex items-center gap-2">
                                        <i class="fa-solid fa-trash-can"></i>
                                        <span>Quitar QR</span>
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Confirmación Eliminar QR -->
                @if($showConfirmDeleteQr)
                    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm animate-fade-in">
                        <div class="bg-brand-card border border-brand-border rounded-3xl w-full max-w-md p-6 space-y-4 shadow-2xl">
                            <div class="flex items-center gap-3 text-rose-400">
                                <div class="w-10 h-10 rounded-2xl bg-rose-500/10 flex items-center justify-center text-xl shrink-0">
                                    <i class="fa-solid fa-triangle-exclamation"></i>
                                </div>
                                <h4 class="text-base font-bold text-white">¿Quitar el Código QR Actual?</h4>
                            </div>
                            <p class="text-xs text-slate-300 leading-relaxed">
                                La imagen del código QR será eliminada y no aparecerá en recibos ni órdenes de pago.
                            </p>
                            <div class="flex items-center justify-end gap-3 pt-2 border-t border-brand-border">
                                <button type="button" wire:click="cancelarEliminarQr" class="px-4 py-2 rounded-xl text-xs font-bold bg-slate-800 text-slate-300 hover:bg-slate-700">
                                    Cancelar
                                </button>
                                <button type="button" wire:click="eliminarQr" class="px-4 py-2 rounded-xl text-xs font-bold bg-rose-600 text-white hover:bg-rose-500">
                                    Sí, Quitar QR
                                </button>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- TAB 5: TÉRMINOS Y CONDICIONES DE CONTRATO -->
            <div x-show="activeTab === 'contratos'" x-cloak class="space-y-4">
                <h3 class="text-base font-bold text-white border-b border-brand-border pb-3 flex items-center gap-2">
                    <i class="fa-solid fa-file-contract text-gold-400"></i>
                    <span>Términos & Condiciones Estándar para Contratos PDF</span>
                </h3>

                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Cláusulas & Compromisos del Mariachi León Guanajuato</label>
                    <textarea wire:model="terminos_contrato" rows="6" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500" placeholder="Escriba los términos legales, horas de anticipación, tolerancia de espera y compromisos de la agrupación..."></textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Observaciones Generales de la Empresa</label>
                    <textarea wire:model="observaciones" rows="2" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500" placeholder="Observaciones o notas adicionales de la gestión de la empresa..."></textarea>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="flex items-center justify-end gap-4 border-t border-brand-border pt-4">
                <button type="submit" form="formEmpresa" wire:loading.attr="disabled" class="px-6 py-3 rounded-xl text-sm font-bold bg-gold-500 text-slate-950 hover:bg-gold-400 shadow-lg shadow-gold-500/20 transition-all flex items-center gap-2">
                    <span wire:loading.remove wire:target="guardar"><i class="fa-solid fa-floppy-disk"></i> Guardar Todos los Datos de la Empresa</span>
                    <span wire:loading wire:target="guardar"><i class="fa-solid fa-spinner animate-spin"></i> Guardando...</span>
                </button>
            </div>
        </form>
    </div>
</div>
