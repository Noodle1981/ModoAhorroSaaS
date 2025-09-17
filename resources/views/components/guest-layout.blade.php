<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Modo Ahorro') }}</title>
    <style>
        body { font-family: sans-serif; background-color: #f9f9f9; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .auth-card { background-color: white; padding: 40px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
    </style>
</head>
<body>
    <div class="auth-card">
        <!-- El contenido de la página de login/register irá aquí -->
        {{ $slot }}
    </div>
</body>
</html>