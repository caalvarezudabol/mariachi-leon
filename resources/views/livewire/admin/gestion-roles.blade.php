<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Roles y Permisos</h1>
            <p class="text-xs text-slate-400">Configuración de perfiles de usuario y matriz de permisos de acceso.</p>
        </div>
        <button wire:click="abrirModal" class="px-5 py-2.5 rounded-xl text-sm font-bold bg-gradient-to-r from-gold-500 to-gold-600 text-slate-950 hover:from-gold-400 hover:to-gold-500 shadow-lg shadow-gold-500/20 transition-all flex items-center justify-center gap-2">
            <i class="fa-solid fa-plus"></i>
            <span>Nuevo Rol</span>
        </button>
    </div>

    <!-- Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @foreach($roles as $role)
            <div class="p-6 rounded-2xl bg-brand-card border border-brand-border space-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-purple-500/10 text-purple-400 flex items-center justify-center text-lg font-bold">
                            <i class="fa-solid fa-user-shield"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-base text-white">{{ $role->name }}</h3>
                            <p class="text-xs text-slate-400">{{ $role->permissions->count() }} permisos asignados</p>
                        </div>
                    </div>
                    <button wire:click="editar({{ $role->id }})" class="p-2 text-slate-400 hover:text-gold-400 hover:bg-gold-500/10 rounded-lg transition-colors">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </button>
                </div>

                <div class="flex flex-wrap gap-2 pt-2 border-t border-brand-border/60">
                    @forelse($role->permissions as $p)
                        <span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-950 text-slate-300 border border-slate-800">
                            {{ $p->name }}
                        </span>
                    @empty
                        <span class="text-xs text-slate-500">Sin permisos específicos.</span>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>

    <!-- Modal Form -->
    @if($modalOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">
            <div class="w-full max-w-2xl bg-slate-900 border border-brand-border rounded-3xl p-6 space-y-6 shadow-2xl max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between border-b border-brand-border pb-4">
                    <h3 class="font-bold text-lg text-white">{{ $isEdit ? 'Editar Rol' : 'Nuevo Rol' }}</h3>
                    <button wire:click="$set('modalOpen', false)" class="text-slate-400 hover:text-white">
                        <i class="fa-solid fa-xmark text-xl"></i>
                    </button>
                </div>

                <form wire:submit.prevent="guardar" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Nombre del Rol</label>
                        <input type="text" wire:model.defer="name" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500" placeholder="Ej. Contador, Músico">
                        @error('name') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-3">Matriz de Permisos</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-4 bg-slate-950 rounded-2xl border border-slate-800 max-h-60 overflow-y-auto">
                            @foreach($permissions as $perm)
                                <label class="flex items-center gap-2 p-2 rounded-xl hover:bg-slate-900 cursor-pointer">
                                    <input type="checkbox" wire:model="selectedPermissions" value="{{ $perm->name }}" class="w-4 h-4 rounded bg-slate-900 border-slate-700 text-gold-500">
                                    <span class="text-xs text-slate-300 font-medium">{{ $perm->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-brand-border">
                        <button type="button" wire:click="$set('modalOpen', false)" class="px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-400 hover:text-white">Cancelar</button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl text-sm font-bold bg-gold-500 text-slate-950 hover:bg-gold-400 shadow-lg shadow-gold-500/20">Guardar Rol</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
