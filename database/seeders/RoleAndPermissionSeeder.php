<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use App\Models\Configuracion;
use App\Models\TipoEvento;
use App\Models\Servicio;
use App\Models\Paquete;
use App\Models\GaleriaItem;
use App\Models\Banner;
use App\Models\MusicoPersonal;
use Illuminate\Support\Facades\Hash;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Permisos
        $permissions = [
            'administrar-usuarios',
            'administrar-roles',
            'ver-auditoria',
            'gestionar-configuracion',
            'gestionar-tipos-evento',
            'gestionar-servicios',
            'gestionar-paquetes',
            'gestionar-banners',
            'gestionar-galeria',
            'gestionar-clientes',
            'gestionar-eventos',
            'gestionar-cotizaciones',
            'gestionar-contratos',
            'gestionar-pagos',
            'gestionar-musicos',
            'gestionar-gastos',
            'gestionar-liquidaciones',
            'gestionar-fondo-comun',
            'gestionar-anticipos',
            'ver-reportes',
            // Permisos Activos Fijos
            'ver-activos',
            'crear-activos',
            'editar-activos',
            'desactivar-activos',
            'registrar-ingresos-activos',
            'registrar-egresos-activos',
            'asignar-activos',
            'registrar-devoluciones-activos',
            'registrar-bajas-activos',
            'consultar-kardex-activos',
            'realizar-ajustes-activos',
            'generar-comprobantes-activos',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        // Músico/Personal de Prueba Centralizado (Idempotente)
        MusicoPersonal::firstOrCreate(
            ['nombre_completo' => 'Juan Pérez'],
            [
                'nombre' => 'Juan',
                'apellido' => 'Pérez',
                'tipo' => 'Músico',
                'telefono' => '70000000',
                'estado' => 'Activo',
                'observaciones' => 'Registro generado automáticamente para pruebas del Módulo de Activos Fijos.',
            ]
        );

        // Crear Roles
        $adminRole = Role::findOrCreate('Administrador', 'web');
        $adminRole->givePermissionTo(Permission::all());

        $comercialRole = Role::findOrCreate('Encargado Comercial', 'web');
        $comercialRole->givePermissionTo([
            'gestionar-clientes',
            'gestionar-eventos',
            'gestionar-cotizaciones',
            'gestionar-contratos',
            'gestionar-pagos',
            'ver-reportes',
        ]);

        $contadorRole = Role::findOrCreate('Contador', 'web');
        $contadorRole->givePermissionTo([
            'gestionar-pagos',
            'gestionar-gastos',
            'gestionar-liquidaciones',
            'gestionar-fondo-comun',
            'gestionar-anticipos',
            'ver-reportes',
        ]);

        $musicoRole = Role::findOrCreate('Músico', 'web');

        // Crear Usuario Administrador por defecto
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@mariachileon.com'],
            [
                'name' => 'Ing. Carlos Andrés Álvarez',
                'telefono' => '70000000',
                'activo' => true,
                'password' => Hash::make('admin12345'),
            ]
        );
        $adminUser->assignRole($adminRole);

        // Crear Configuración Inicial (Moneda oficial Bs. Bolivianos y Redes Linktree)
        $configs = [
            ['clave' => 'nombre_agrupacion', 'valor' => 'Mariachi León Guanajuato', 'grupo' => 'general', 'descripcion' => 'Nombre oficial de la agrupación'],
            ['clave' => 'moneda_simbolo', 'valor' => 'Bs.', 'grupo' => 'general', 'descripcion' => 'Símbolo oficial de moneda del sistema'],
            ['clave' => 'moneda_nombre', 'valor' => 'Bolivianos', 'grupo' => 'general', 'descripcion' => 'Nombre de la moneda oficial'],
            ['clave' => 'redes_linktree', 'valor' => 'https://linktr.ee/mariachileonguanajuato', 'grupo' => 'general', 'descripcion' => 'Enlace directo a Redes Sociales (Linktree)'],
            ['clave' => 'telefono_contacto', 'valor' => '+591 700 00000', 'grupo' => 'general', 'descripcion' => 'Teléfono principal de contacto'],
            ['clave' => 'email_contacto', 'valor' => 'contacto@mariachileonguanajuato.com', 'grupo' => 'general', 'descripcion' => 'Correo de contacto comercial'],
            ['clave' => 'direccion_oficina', 'valor' => 'León, Guanajuato, México', 'grupo' => 'general', 'descripcion' => 'Dirección fiscal/comercial'],
            ['clave' => 'porcentaje_fondo_comun', 'valor' => '10', 'grupo' => 'financiero', 'descripcion' => 'Porcentaje por defecto retenido para el fondo común (%)'],
            ['clave' => 'terminos_contrato', 'valor' => 'El Mariachi León Guanajuato se compromete a brindar puntualidad, profesionalismo y presentación impecable.', 'grupo' => 'contrato', 'descripcion' => 'Términos y condiciones por defecto en contratos'],
        ];

        foreach ($configs as $config) {
            Configuracion::firstOrCreate(['clave' => $config['clave']], $config);
        }

        // Crear Banners del Banner de Inicio
        $banners = [
            [
                'titulo' => 'El Mejor Mariachi de Gala',
                'subtitulo' => 'Puntualidad, elegancia y virtuosismo musical en León Guanajuato y Santa Cruz Bolivia',
                'imagen_url' => '/assets/images/banner_grupo_amarillo.jpg',
                'boton_texto' => 'Cotizar Tu Evento',
                'boton_link' => '#cotizar',
                'orden' => 1,
                'activo' => true,
            ],
            [
                'titulo' => 'Bodas & Recepciones de Ensueño',
                'subtitulo' => 'Acompañamiento musical único e inolvidable para el día más especial de tu vida',
                'imagen_url' => '/assets/images/banner_escenario_vivo.jpg',
                'boton_texto' => 'Reservar Ahora',
                'boton_link' => '#cotizar',
                'orden' => 2,
                'activo' => true,
            ],
            [
                'titulo' => 'Serenatas Románticas en Vivo',
                'subtitulo' => 'Lleva la emoción de la música mexicana directamente a tu ser querido',
                'imagen_url' => 'https://images.unsplash.com/photo-1465847899084-d164df4dedc6?q=80&w=1920&auto=format&fit=crop',
                'boton_texto' => 'Ver Paquetes',
                'boton_link' => '/servicios',
                'orden' => 3,
                'activo' => true,
            ],
            [
                'titulo' => 'Eventos & XV Años Inolvidables',
                'subtitulo' => 'Shows alegres y bailables con trajes de charro de gran gala',
                'imagen_url' => 'https://images.unsplash.com/photo-1514525253161-7a46d19cd819?q=80&w=1920&auto=format&fit=crop',
                'boton_texto' => 'Cotización Rápida',
                'boton_link' => '#cotizar',
                'orden' => 4,
                'activo' => true,
            ],
        ];

        foreach ($banners as $b) {
            Banner::firstOrCreate(['titulo' => $b['titulo']], $b);
        }

        // Crear Tipos de Eventos por defecto (en Bolivianos Bs.)
        $tipos = [
            ['nombre' => 'Serenata', 'descripcion' => 'Presentación de 7 a 9 canciones (aprox. 45 min a 1 hora)', 'precio_sugerido' => 1200.00],
            ['nombre' => 'Boda', 'descripcion' => 'Acompañamiento en ceremonia y/o recepción de bodas', 'precio_sugerido' => 2500.00],
            ['nombre' => 'XV Años', 'descripcion' => 'Celebración tradicional de 15 años', 'precio_sugerido' => 2200.00],
            ['nombre' => 'Cumpleaños', 'descripcion' => 'Show bailable y alegre para fiestas de cumpleaños', 'precio_sugerido' => 1500.00],
            ['nombre' => 'Evento Corporativo', 'descripcion' => 'Eventos de empresas, inauguraciones y galas', 'precio_sugerido' => 3500.00],
        ];

        foreach ($tipos as $tipo) {
            TipoEvento::firstOrCreate(['nombre' => $tipo['nombre']], $tipo);
        }

        // Crear Servicios por defecto (en Bolivianos Bs.)
        $servicios = [
            ['nombre' => 'Show Estándar (1 Hora)', 'descripcion' => '1 hora de música en vivo con repertorio variado', 'precio_base' => 1200.00, 'duracion_minutos' => 60],
            ['nombre' => 'Show Ampliado (2 Horas)', 'descripcion' => '2 horas continuas con interacción y complacencias', 'precio_base' => 2200.00, 'duracion_minutos' => 120],
            ['nombre' => 'Acompañamiento Misa', 'descripcion' => 'Música sacra mariachi para ceremonias religiosas', 'precio_base' => 1000.00, 'duracion_minutos' => 45],
        ];

        foreach ($servicios as $servicio) {
            Servicio::firstOrCreate(['nombre' => $servicio['nombre']], $servicio);
        }

        // Crear Galerías Iniciales (Fotos y Videos)
        $galerias = [
            [
                'titulo' => 'Presentación Oficial en Vivo (Facebook Video)',
                'descripcion' => 'Muestra de actuación del Mariachi León Guanajuato en vivo. ¡Puedes darle Me Gusta e interactuar directamente!',
                'tipo' => 'facebook',
                'imagen_url' => '/assets/images/banner_escenario_vivo.jpg',
                'video_url' => null,
                'facebook_url' => 'https://www.facebook.com/facebook/videos/10153231379946729/',
                'categoria' => 'Corporativos',
                'fecha_evento' => '2026-08-05',
                'destacado' => true,
            ],
            [
                'titulo' => 'Recepción de Boda de Gala',
                'descripcion' => 'Presentación romántica en hacienda con repertorio exclusivo de mariachi.',
                'tipo' => 'foto',
                'imagen_url' => '/assets/images/banner_grupo_amarillo.jpg',
                'video_url' => null,
                'facebook_url' => null,
                'categoria' => 'Bodas',
                'fecha_evento' => '2026-08-01',
                'destacado' => true,
            ],
            [
                'titulo' => 'Serenata Nocturna Tradicional',
                'descripcion' => 'Emotiva serenata bajo la luna con canciones clásicas.',
                'tipo' => 'foto',
                'imagen_url' => '/assets/images/banner_escenario_vivo.jpg',
                'video_url' => null,
                'facebook_url' => null,
                'categoria' => 'Serenatas',
                'fecha_evento' => '2026-07-25',
                'destacado' => true,
            ],
            [
                'titulo' => 'Video Show en Vivo XV Años',
                'descripcion' => 'Presentación musical bailable y alegre en fiesta de quinceañera.',
                'tipo' => 'video',
                'imagen_url' => 'https://images.unsplash.com/photo-1514525253161-7a46d19cd819?q=80&w=800&auto=format&fit=crop',
                'video_url' => 'https://www.youtube.com/embed/dQw4w9WgXcQ',
                'categoria' => 'XV Años',
                'fecha_evento' => '2026-07-15',
                'destacado' => true,
            ],
            [
                'titulo' => 'Gala Corporativa de Aniversario',
                'descripcion' => 'Acompañamiento a evento de gala empresarial.',
                'tipo' => 'foto',
                'imagen_url' => 'https://images.unsplash.com/photo-1501386761578-eac5c94b800a?q=80&w=800&auto=format&fit=crop',
                'video_url' => null,
                'categoria' => 'Corporativos',
                'fecha_evento' => '2026-06-30',
                'destacado' => false,
            ],
        ];

        foreach ($galerias as $g) {
            GaleriaItem::firstOrCreate(['titulo' => $g['titulo']], $g);
        }
    }
}
