<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="refresh" content="0; url={{ route('web.home') }}">
    <title>Página Expirada - Mariachi León Guanajuato</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=Playfair+Display:ital,wght@0,600;0,800;1,600&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        window.location.replace("{{ route('web.home') }}");
    </script>
</head>
<body class="bg-[#0b1320] text-slate-200 min-h-screen flex items-center justify-center p-4 font-['Plus_Jakarta_Sans']">
    <div class="max-w-md w-full text-center bg-[#101c2e] border border-slate-800 rounded-3xl p-8 shadow-2xl space-y-5">
        <div class="w-16 h-16 mx-auto rounded-2xl bg-amber-500/10 border border-amber-500/30 flex items-center justify-center text-amber-400 text-2xl shadow-inner">
            <i class="fa-solid fa-hourglass-end"></i>
        </div>
        <div class="space-y-2">
            <h1 class="text-xl font-bold text-white">Sesión o Página Expirada</h1>
            <p class="text-xs text-slate-400 leading-relaxed">
                Por motivos de seguridad o inactividad, la página ha expirado. Redirigiéndote a la página principal...
            </p>
        </div>
        <div class="pt-2">
            <a href="{{ route('web.home') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-slate-950 font-bold text-xs transition-all shadow-lg shadow-amber-500/20">
                <i class="fa-solid fa-house"></i>
                <span>Ir al Inicio Principal</span>
            </a>
        </div>
    </div>
</body>
</html>
