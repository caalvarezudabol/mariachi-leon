# Mariachi León Guanajuato — Sistema de Gestión

Sistema de administración y sitio web institucional del **Mariachi León Guanajuato** (Santa Cruz de la Sierra, Bolivia).
Desarrollado por Iver Guerrero (Tiluchisoft). Todo el sistema, la UI y los textos están en **español**.

> Este archivo es un borrador inicial. Si algo no coincide con el código real, manda el código.
> Claude: al explorar el proyecto, corrige o completa las secciones marcadas con (verificar).

## Stack

- Laravel + Livewire 3 (componentes en `app/Livewire/...`, vistas en `resources/views/livewire/...`)
- Alpine.js (viene con Livewire), Tailwind CSS por **CDN** (no hay build de Node para estilos), Font Awesome, Leaflet, Flatpickr
- MySQL. Sesiones, cola y caché en `database`
- Local: Laragon (`C:\laragon\www\mariachi-leon`, `http://127.0.0.1:8000`)
- Producción: Hostinger, https://mariachileonguanajuato.sitsistema.com
- Repo: caalvarezudabol/mariachi-leon (GitHub)

## Módulos (según rutas y menú lateral)

- **Sitio público** (`web.*`): inicio, nosotros, servicios, galería, contacto. Se administra desde "Sitio Web Institucional" (banners, galería).
- **Empresa**: datos de la empresa (logo, nombre comercial, ciudad) y parámetros.
- **Administración**: usuarios, roles y permisos, auditoría de logs.
- **Gestión Comercial** (prefijo `admin/comercial`): clientes, cotizaciones, eventos, agenda visual, contratos (PDF), pagos y anticipos.
- **Gestión Operativa**: tipos de evento, servicios, paquetes.
- **Activos Fijos**: dashboard, categorías, artículos, ingresos, egresos, asignaciones, devoluciones, bajas, kardex (PPP), inventario PDF, comprobantes.
- Vistos en el menú pero aún sin revisar el código (verificar): Solicitudes Web, Gestión Financiera, Informes & Reportes.

## Flujo de negocio principal

Cliente → Evento → Cotización → Contrato → Pagos (recibos)
y por evento: Participantes (músicos/personal) → distribución económica / comisiones.

- Un **evento** tiene cliente, servicio contratado, fecha, hora, duración, dirección y participantes sugeridos según la plantilla del servicio.
- Un **contrato** guarda monto total, pago inicial, total pagado y saldo pendiente.
- Un **pago** genera recibo con número y código de verificación (QR).
- Códigos correlativos: `EVT-00001`, `COT-00001`, contratos y recibos con numeración propia.

## Seguridad y documentos

- Rutas admin bajo `auth` + middleware `session.timeout`.
- Los IDs en URLs de PDFs se ofuscan con `App\Services\Security\DocumentToken::encodeId($id, 'cliente'|'contrato'|'evento')`.
- Contratos y pagos tienen `codigo_verificacion` (se genera en el evento `creating` del modelo). Las rutas `/verificar/...` son públicas (QR anti-fraude).
- Sesión: `SESSION_LIFETIME` en `.env`. El layout tiene un temporizador JS de inactividad que lee `config('session.lifetime')`, así que solo se cambia en `.env`.

## Convenciones de UI (respetar siempre)

- Rediseño en curso (rama `rediseno-ui`): sistema de temas claro/oscuro por variables CSS semánticas, migrando pantalla por pantalla. Colores propios legados (pantallas sin migrar): `gold-*`, `brand-dark`, `brand-card`, `brand-hover`, `brand-border`.
- **Componentes compartidos** en `resources/views/components/ui/`: `button`, `icon-button`, `input`, `select`, `textarea`, `badge`, `modal`, `action-menu` + `action-menu-item`, `card`, `panel`, `table`, `filter-chips` + `filter-chip`, `select-search`. Úsalos en vez de reescribir clases Tailwind a mano.
- **Selector con buscador** (`<x-ui.select-search>`): para listas largas (personas, clientes, etc.) en vez de un `<select>` nativo. Props: `name` (propiedad Livewire a enlazar, usa `@entangle($name).live` internamente), `options` (array/colección de `['id','label','meta','search']` — `search` ya viene en minúsculas y con todos los campos buscables concatenados, armado por quien llama, el componente no conoce nombres de columnas), `placeholder`, `push-event` (nombre de evento `$this->dispatch('evento', id:, label:, meta:, search:)` para insertar y seleccionar en vivo una opción recién creada — p.ej. al registrar una persona nueva desde el mismo formulario) y `on-select` (expresión `$wire->metodo(option.id)` opcional si hace falta algo más que fijar la propiedad). El panel de opciones usa `x-teleport="body"` + posición `fixed` (igual que `action-menu`) para no cortarse dentro de modales con scroll, con buscador autofocus y `max-h-60` de scroll interno.
- **Tokens de tema** (definidos en `components/layouts/app.blade.php`, variables CSS + `tailwind.config`): `surface-page/card/sunken/hover`, `outline`, `ink-primary/secondary/muted`. Cambian de valor entre `:root` (claro) y `.dark` (oscuro) — nunca usar `slate-900`, `white`, `slate-950` fijos en una vista migrada; usar estos tokens para que funcione en ambos temas. Para colores semánticos (verde/rojo/ámbar) usar el patrón `text-emerald-700 dark:text-emerald-400` (oscurecer en claro, aclarar en oscuro) para no perder contraste.
- **Pantallas migradas vs. sin migrar**: cada componente Livewire decide con `->layout('components.layouts.app', ['themed' => true, ...])` si sigue el tema real. Si no pasa `themed => true`, el layout envuelve su contenido en un contenedor `.dark` forzado para que se siga viendo como antes. Migrar una pantalla = agregar `themed => true` en su `render()` + reescribir su vista con los tokens y componentes `ui/`.
- **Separador de texto**: nunca usar "·". Usar guion con espacios " - " (ej. "María Suárez - Banzer 8vo anillo", "75150797 - Banzer 8vo anillo").
- **Panel/Tabla estándar** (ver `gestion-clientes.blade.php` como plantilla de referencia, "Estilo 2"):
  - Todo el bloque (cabecera + filtros + tabla + paginación) va en un solo `<x-ui.panel>` (`rounded-lg border border-outline overflow-hidden`, sin degradados ni sombras de brillo).
  - `<x-slot:header>`: franja izquierda dorada (`border-l-4 border-l-gold-500`), ícono + título (`text-base font-semibold`) + contador en `<x-ui.badge variant="neutral">` (pill, no texto "· N registrados"), oculto en móvil. Botones a la derecha: secundarios con borde, el de acción principal relleno dorado. En móvil, recortar texto de los botones (p.ej. "Nuevo" en vez de "Nuevo cliente").
  - `<x-slot:filters>`: una fila `px-4 py-2 border-b border-outline bg-surface-sunken`. Buscador `flex-1 min-w-0`; a la derecha `<x-ui.filter-chips>` con `<x-ui.filter-chip :active="...">` para reemplazar selects de estado (Todos/Activos/Inactivos con contador). En móvil, buscador a todo el ancho y chips en una segunda fila (`flex-col sm:flex-row`).
  - Tabla: `<x-ui.table>` con slot `header` (fila de `<th>`) y filas `<tr>` en el slot por defecto. Cada `<th>`/`<td>` lleva `border-r border-outline` salvo el último de la fila; cada `<tr>` lleva `border-b border-outline` y `hover:bg-gold-500/5`. Celdas `py-1.5` (filas ~40px), texto `text-sm`, encabezado `text-[13px] font-semibold text-slate-900 dark:text-white` (alto contraste, no usar `ink-*` ahí) sin mayúsculas forzadas, fondo `bg-surface-sunken` (vía el propio componente) con `border-b-2` más oscuro que las líneas internas.
  - La columna "Acciones" (y cualquier columna de solo botones) lleva `w-px whitespace-nowrap` en su `<th>`/`<td>` para que mida justo lo que ocupan los botones; la columna principal (p.ej. "Cliente") se queda sin ancho fijo para absorber el espacio libre, con `truncate flex-1 min-w-0` en su contenido. Botones de acción con `gap-1`.
  - En `<md`: ocultar la tabla (`hidden md:block`) y mostrar filas compactas (`md:hidden divide-y divide-outline`) con avatar de iniciales, nombre + punto de estado, dato secundario truncado abajo, y un único botón `⋮` a la derecha.
  - Pie del panel (`<x-slot:footer>`): "Mostrando X–Y de Z" (`$paginator->firstItem()/lastItem()/total()`) a la izquierda, `$paginator->links()` a la derecha.
  - El menú de acciones por fila (`<x-ui.action-menu>`) usa `x-teleport="body"` con posición `fixed` calculada desde el botón — nunca lo pongas dentro de un contenedor con `overflow-x-auto` sin el teleport, se corta.
- **Modales**: overlay `fixed inset-0 z-50 ... p-0 sm:p-4`, contenedor `flex flex-col max-h-screen sm:max-h-[90vh]`, header y footer con `shrink-0`, cuerpo con `overflow-y-auto flex-1`. Usa `<x-ui.modal>`. Pantalla completa en móvil.
  - `<x-ui.modal>` acepta `:title`/`icon` simples, o un slot `<x-slot:header>` cuando el encabezado necesita algo más rico (ícono + título + badge + subtítulo, como la Plantilla Económica de Servicios) — no dupliques esa estructura a mano en cada vista.
  - Un modal que se abre desde otro modal (p.ej. "Registrar nueva persona" dentro de la Plantilla) pasa `:nested="true"` a `<x-ui.modal>` para que use `z-[60]` en vez de `z-50`.
  - Un modal con `<form wire:submit.prevent="...">` que necesita volver al tope al fallar la validación (`x-on:evento-error-validacion.window="document.getElementById('X')?.scrollTo(...)"`) pasa `bodyId="X"` a `<x-ui.modal>` para que el `id` quede en el contenedor con scroll real (no en un `<div>` interno que no es el que hace scroll). El `<form>` envuelve a `<x-ui.modal>` desde AFUERA (el botón `type="submit"` del `<x-slot:footer>` sigue funcionando porque busca el `<form>` ancestro más cercano, sin importar cuántos `<div>` haya en medio).
  - **Contraste dentro de modales ("gris sobre gris")**: el cuerpo del modal es `bg-surface-card`; los campos (`x-ui.input/select/textarea/select-search`) son `bg-surface-sunken` — esa pareja ya contrasta bien. El problema aparece cuando se anida una CAJA (subformulario, callout, vista previa, resumen) con `bg-surface-sunken` y DENTRO van campos también `bg-surface-sunken`: quedan iguales. Regla: las cajas/contenedores que envuelven campos usan `bg-surface-card` (con su propio `border`), nunca `bg-surface-sunken`; `bg-surface-sunken` se reserva para los campos mismos y para franjas que NO contienen otros campos (encabezado de tabla, barra de filtros). Etiquetas siempre `text-ink-primary` (nunca `ink-secondary`/`ink-muted`, se ven débiles). Texto auxiliar y pies de modal en `text-ink-secondary` como mínimo (no `ink-muted`). Avisos informativos (tipo "¿cómo funciona?") van como callout con borde izquierdo de color (`border-l-[3px] border-amber-500 dark:border-sky-500`, fondo `bg-amber-500/10 dark:bg-sky-500/10`) y texto `text-ink-primary`, no como texto plano gris.
  - La misma regla aplica a `<x-slot:filters>` de `<x-ui.panel>`: el contenedor de la barra de filtros es `bg-surface-card` (no `bg-surface-sunken`), para que el buscador y los demás campos dentro sí contrasten.
- **Etiquetas de formulario**: `text-sm font-semibold text-ink-primary` (no `text-xs`/`ink-secondary`, se ve muy débil), seguidas de dos puntos: "Nombre Completo:". El asterisco rojo va DESPUÉS de los dos puntos: "Nombre Completo: *" (ya integrado en `<x-ui.input>`, `<x-ui.select>`, `<x-ui.textarea>` — el `label` no debe incluir el ":").
- **Campos obligatorios**: el asterisco va en rojo y negrita: `<span class="text-rose-500 font-black text-base leading-none">*</span>` (ya integrado en `<x-ui.input required>`, etc.).
- Errores de validación: `<span class="text-xs text-rose-400 mt-1">` (ya integrado en los componentes `ui/` vía la prop `name`).
- **Textarea**: siempre `rows="1"` y auto-expandible (ya integrado en `<x-ui.textarea>` vía Alpine `resize()` en `x-init`/`@input`); no uses `rows="2"` ni más para forzar altura, se expande sola.
- **Botones de modal**: "Cancelar" con ícono `fa-xmark`, el botón de guardar con ícono `fa-floppy-disk` y texto corto ("Guardar", no "Guardar Cliente"/"Actualizar Cliente"). El botón de guardar lleva `wire:loading.attr="disabled" wire:target="<método>"` y alterna el ícono por un spinner (`fa-spinner fa-spin`) con `wire:loading`/`wire:loading.remove` scoped al mismo `wire:target`.
- Un modal que se abre desde otro usa `z-[60]` (ver nota de `<x-ui.modal :nested="true">` arriba).
- **Chips por valor dinámico** (p.ej. "Tipo" en Servicios, no es un enum fijo): en vez de escribir cada `<x-ui.filter-chip>` a mano, calcula en `render()` un `pluck('total', 'columna')` agrupado (respetando el buscador, no el propio filtro) y recórrelo con `@foreach` dentro de `<x-ui.filter-chips>`. El "Todos" se agrega aparte con el conteo total.
- **Menú lateral persistido** (`components/layouts/app.blade.php`): el `<aside>` está envuelto en `@persist('sidebar') ... @endpersist` para no perder scroll/estado al navegar. Por eso:
  - Todos los enlaces internos del sidebar llevan `wire:navigate` (excepto "Ver Sitio Web", que abre en pestaña nueva).
  - El resaltado del ítem activo NO puede depender de `request()->routeIs(...)` (el sidebar no se vuelve a renderizar): se usa `wire:current="!bg-gold-500 !text-slate-950 !font-semibold"` en cada `<a>` (con `!` para que gane siempre sobre el color base, sin importar el orden de las clases de Tailwind).
  - Cada grupo colapsable (`x-data`) trae un array `paths` con las rutas relativas del grupo (generadas con `route('x', [], false)`, nunca `@json(...)` dentro de un atributo HTML con comillas — usa `@js(...)`, que es seguro para atributos) y un método `checkOpen()` que abre el grupo si la URL actual coincide; se llama en `x-init="checkOpen()"` y en `x-on:livewire:navigated.window="checkOpen()"` (NUNCA `@livewire:navigated...`: la `@` choca con la directiva Blade `@livewire(...)`).
  - El temporizador de inactividad limpia su `setInterval` en `livewire:navigating` para no acumular timers al navegar con `wire:navigate`.
  - **Títulos de sección** (Empresa, Administración, Gestión Comercial, etc.): ícono Font Awesome (`fa-solid`, `text-base`, color `text-gold-600 dark:text-gold-400`) + texto `text-sm font-bold text-ink-primary` — sin `uppercase`/`tracking-wide` (se escribe "Gestión Comercial", no "GESTIÓN COMERCIAL"). Chevron a la derecha `text-xs text-ink-secondary`. Botón `py-2`. Los ítems hijos se distinguen por ser más chicos (`text-sm font-medium`, ícono sin `text-base`) y van con sangría: el `<div x-show="open">` que los envuelve lleva `ml-2 pl-2 border-l border-outline`.

## Cómo trabajar conmigo

- Responde en español, directo y práctico. Nada de sobreingeniería.
- Primero diagnostica y entiende el código; no cambies "por hacer".
- Cuando pida un archivo completo, entrégalo completo y listo para reemplazar.
- Antes de crear una ruta, un campo o una migración, revisa que no exista ya.
- Producción corre en Hostinger: avisa si un cambio requiere `php artisan migrate`, `config:clear` o `view:clear`.

## Pendientes conocidos

- Contratos/pagos antiguos pueden tener `codigo_verificacion` vacío y romper la ruta `admin.contratos.pdf` (verificar en producción).
- Revisar que existan las rutas `admin.clientes.excel` y `admin.eventos.comisiones.pdf`, usadas en las vistas (verificar).
