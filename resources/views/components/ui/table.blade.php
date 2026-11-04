{{-- Tabla estándar tipo rejilla. El header y las filas deben traer su propio border-r
     en cada <th>/<td> salvo el último de la fila. Ver CLAUDE.md "Panel/Tabla estándar". --}}
<div class="overflow-x-auto">
    <table class="w-full border-collapse text-left text-sm">
        <thead>
            <tr class="bg-surface-sunken text-slate-900 dark:text-white text-[13px] font-semibold border-b-2 border-slate-400 dark:border-slate-600">
                {{ $header }}
            </tr>
        </thead>
        <tbody>
            {{ $slot }}
        </tbody>
    </table>
</div>
