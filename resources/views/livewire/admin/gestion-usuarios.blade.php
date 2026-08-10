<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Gestión de Usuarios</h1>
            <p class="text-xs text-slate-400">Administra los usuarios del sistema, sus datos y asignación de roles.</p>
        </div>
        <button wire:click="abrirModal" class="px-5 py-2.5 rounded-xl text-sm font-bold bg-gradient-to-r from-gold-500 to-gold-600 text-slate-950 hover:from-gold-400 hover:to-gold-500 shadow-lg shadow-gold-500/20 transition-all flex items-center justify-center gap-2">
            <i class="fa-solid fa-user-plus"></i>
            <span>Nuevo Usuario</span>
        </button>
    </div>

    <!-- Search & Filter Bar -->
    <div class="p-4 rounded-2xl bg-brand-card border border-brand-border flex items-center gap-4">
        <div class="relative flex-1">
            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-500">
                <i class="fa-solid fa-magnifying-glass"></i>
            </span>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar por nombre o correo..." class="w-full pl-10 pr-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:border-gold-500 text-sm">
        </div>
    </div>

    <!-- Table -->
    <div class="rounded-2xl bg-brand-card border border-brand-border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="bg-slate-950/80 border-b border-brand-border text-xs uppercase font-bold text-slate-400">
                    <tr>
                        <th class="px-6 py-4">Usuario</th>
                        <th class="px-6 py-4">Teléfono</th>
                        <th class="px-6 py-4">Rol Asignado</th>
                        <th class="px-6 py-4">Estado</th>
                        <th class="px-6 py-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-brand-border/60">
                    @forelse($usuarios as $user)
                        <tr class="hover:bg-brand-hover/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-gold-500/10 border border-gold-500/20 text-gold-400 flex items-center justify-center font-bold">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="font-semibold text-white">{{ $user->name }}</div>
                                        <div class="text-xs text-slate-400">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-slate-300">{{ $user->telefono ?? 'N/A' }}</td>
                            <td class="px-6 py-4">
                                @foreach($user->roles as $r)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-purple-500/10 text-purple-300 border border-purple-500/20">
                                        {{ $r->name }}
                                    </span>
                                @endforeach
                            </td>
                            <td class="px-6 py-4">
                                <button wire:click="cambiarEstado({{ $user->id }})" class="cursor-pointer">
                                    @if($user->activo)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Activo
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-500/10 text-rose-400 border border-rose-500/20">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span> Inactivo
                                        </span>
                                    @endif
                                </button>
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <button wire:click="editar({{ $user->id }})" class="p-2 text-slate-400 hover:text-gold-400 hover:bg-gold-500/10 rounded-lg transition-colors">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                @if(Auth::id() !== $user->id)
                                    <button wire:click="eliminar({{ $user->id }})" onclick="return confirm('¿Está seguro de eliminar este usuario?')" class="p-2 text-slate-400 hover:text-rose-400 hover:bg-rose-500/10 rounded-lg transition-colors">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-slate-500">No se encontraron usuarios.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-brand-border">
            {{ $usuarios->links() }}
        </div>
    </div>

    <!-- Modal Form -->
    @if($modalOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">
            <div class="w-full max-w-lg bg-slate-900 border border-brand-border rounded-3xl p-6 space-y-6 shadow-2xl">
                <div class="flex items-center justify-between border-b border-brand-border pb-4">
                    <h3 class="font-bold text-lg text-white">{{ $isEdit ? 'Editar Usuario' : 'Nuevo Usuario' }}</h3>
                    <button wire:click="$set('modalOpen', false)" class="text-slate-400 hover:text-white">
                        <i class="fa-solid fa-xmark text-xl"></i>
                    </button>
                </div>

                <form wire:submit.prevent="guardar" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Nombre Completo</label>
                        <input type="text" wire:model.defer="name" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
                        @error('name') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Correo Electrónico</label>
                        <input type="email" wire:model.defer="email" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
                        @error('email') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Teléfono</label>
                            <input type="text" wire:model.defer="telefono" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
                            @error('telefono') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Rol</label>
                            <select wire:model.defer="role" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
                                <option value="">Seleccione un rol...</option>
                                @foreach($roles as $r)
                                    <option value="{{ $r->name }}">{{ $r->name }}</option>
                                @endforeach
                            </select>
                            @error('role') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">
                            Contraseña {{ $isEdit ? '(Dejar en blanco para conservar la actual)' : '' }}
                        </label>
                        <input type="password" wire:model.defer="password" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
                        @error('password') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex items-center gap-2 pt-2">
                        <input type="checkbox" wire:model="activo" id="user_activo" class="w-4 h-4 rounded bg-slate-950 text-gold-500">
                        <label for="user_activo" class="text-sm font-semibold text-slate-300">Usuario Activo</label>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-brand-border">
                        <button type="button" wire:click="$set('modalOpen', false)" class="px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-400 hover:text-white">Cancelar</button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl text-sm font-bold bg-gold-500 text-slate-950 hover:bg-gold-400 shadow-lg shadow-gold-500/20">Guardar Usuario</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
