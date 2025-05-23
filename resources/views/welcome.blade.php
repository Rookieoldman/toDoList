<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Mi Lista de Tareas</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

        <!-- Carga los assets (JS y CSS) compilados por Vite -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

    </head>
    <body class="font-sans antialiased dark:bg-black dark:text-white/50">
        <div id="app">
            {{-- Aquí es donde Vue montará la aplicación --}}
            {{-- Y aquí usamos nuestro componente registrado globalmente --}}
            <task-list></task-list>
        </div>
    </body>
</html>