<div class="min-h-[80vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 bg-slate-900/90 p-8 rounded-3xl border border-slate-800 backdrop-blur-xl shadow-2xl">
        <div class="text-center">
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-gold-600 to-gold-400 mx-auto flex items-center justify-center shadow-lg shadow-gold-500/20 mb-4">
                <i class="fa-solid fa-lock text-slate-950 text-2xl"></i>
            </div>
            <h2 class="font-serif font-bold text-2xl text-white">Acceso al Sistema</h2>
            <p class="mt-2 text-sm text-slate-400">Mariachi León Guanajuato</p>
        </div>

        <form wire:submit.prevent="login" class="mt-8 space-y-6">
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Correo Electrónico</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-500">
                            <i class="fa-solid fa-envelope"></i>
                        </span>
                        <input type="email" wire:model.defer="email" required placeholder="admin@mariachileon.com" class="w-full pl-10 pr-4 py-3 bg-slate-950 border border-slate-800 rounded-xl text-white placeholder-slate-600 focus:outline-none focus:border-gold-500 focus:ring-1 focus:ring-gold-500 transition-all text-sm">
                    </div>
                    @error('email') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Contraseña</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-500">
                            <i class="fa-solid fa-key"></i>
                        </span>
                        <input type="password" wire:model.defer="password" required placeholder="••••••••" class="w-full pl-10 pr-4 py-3 bg-slate-950 border border-slate-800 rounded-xl text-white placeholder-slate-600 focus:outline-none focus:border-gold-500 focus:ring-1 focus:ring-gold-500 transition-all text-sm">
                    </div>
                    @error('password') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" wire:model="remember" class="w-4 h-4 rounded bg-slate-950 border-slate-800 text-gold-500 focus:ring-gold-500">
                        <span class="text-xs text-slate-400">Recordar sesión</span>
                    </label>
                </div>
            </div>

            <button type="submit" wire:loading.attr="disabled" class="w-full py-3.5 px-4 rounded-xl text-sm font-bold bg-gradient-to-r from-gold-500 to-gold-600 text-slate-950 hover:from-gold-400 hover:to-gold-500 shadow-lg shadow-gold-500/20 transition-all flex items-center justify-center gap-2">
                <span wire:loading.remove>Ingresar al Sistema</span>
                <span wire:loading><i class="fa-solid fa-spinner animate-spin"></i> Verificando...</span>
            </button>
        </form>

        <div class="mt-6 pt-6 border-t border-slate-800/80 text-center">
            <p class="text-xs text-slate-500">Credenciales por defecto: <code class="text-gold-400">admin@mariachileon.com</code> / <code class="text-gold-400">admin12345</code></p>
        </div>
    </div>
</div>
