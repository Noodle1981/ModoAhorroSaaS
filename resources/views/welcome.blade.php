<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Modo Ahorro - Optimizá el Consumo Energético</title>
        <meta name="description" content="Diagnóstico energético profesional para hogares, comercios y empresas. Reducí costos y mejorá la eficiencia energética.">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet" />

        <!-- Styles / Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <style>
            body {
                font-family: 'Inter', sans-serif;
            }
            
            .gradient-green {
                background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            }
            
            .gradient-green-light {
                background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            }
            
            .hero-pattern {
                background-image: 
                    radial-gradient(circle at 25px 25px, rgba(16, 185, 129, 0.05) 2%, transparent 0%),
                    radial-gradient(circle at 75px 75px, rgba(16, 185, 129, 0.05) 2%, transparent 0%);
                background-size: 100px 100px;
            }
        </style>
    </head>
    <body class="antialiased bg-white">
        <!-- Navigation -->
        <nav class="fixed top-0 left-0 right-0 z-50 bg-white/90 backdrop-blur-md border-b border-gray-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <div class="flex items-center space-x-3">
                        <img src="{{ asset('img/logoMA.png') }}" alt="Modo Ahorro" class="h-10 w-auto">
                        <span class="text-xl font-bold text-gray-900">Modo Ahorro</span>
                    </div>
                    
                    <div class="hidden md:flex items-center space-x-8">
                        <a href="#plataforma" class="text-gray-600 hover:text-green-600 transition">Plataforma</a>
                        <a href="#funcionalidades" class="text-gray-600 hover:text-green-600 transition">Funcionalidades</a>
                        <a href="#planes" class="text-gray-600 hover:text-green-600 transition">Planes</a>
                        <a href="#nosotros" class="text-gray-600 hover:text-green-600 transition">Nosotros</a>
                        
                        @auth
                            <a href="{{ url('/dashboard') }}" class="px-4 py-2 gradient-green text-white rounded-lg hover:opacity-90 transition">
                                Dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="text-gray-600 hover:text-green-600 transition">Ingresar</a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="px-4 py-2 gradient-green text-white rounded-lg hover:opacity-90 transition">
                                    Registrarse
                                </a>
                            @endif
                        @endauth
                    </div>
                    
                    <!-- Mobile menu button -->
                    <div class="md:hidden">
                        <button id="mobile-menu-btn" class="text-gray-600 hover:text-green-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Mobile menu -->
            <div id="mobile-menu" class="hidden md:hidden border-t border-gray-100">
                <div class="px-4 py-3 space-y-3">
                    <a href="#plataforma" class="block text-gray-600 hover:text-green-600">Plataforma</a>
                    <a href="#funcionalidades" class="block text-gray-600 hover:text-green-600">Funcionalidades</a>
                    <a href="#planes" class="block text-gray-600 hover:text-green-600">Planes</a>
                    <a href="#nosotros" class="block text-gray-600 hover:text-green-600">Nosotros</a>
                    @auth
                        <a href="{{ url('/dashboard') }}" class="block px-4 py-2 gradient-green text-white rounded-lg text-center">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="block text-gray-600 hover:text-green-600">Ingresar</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="block px-4 py-2 gradient-green text-white rounded-lg text-center">Registrarse</a>
                        @endif
                    @endauth
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <section class="pt-24 pb-16 md:pt-32 md:pb-24 hero-pattern">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid md:grid-cols-2 gap-12 items-center">
                    <div class="space-y-6">
                        <div class="inline-block px-4 py-2 bg-green-100 text-green-800 rounded-full text-sm font-semibold mb-2">
                            🚀 Plataforma en Desarrollo Activo
                        </div>
                        <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-gray-900 leading-tight">
                            Tu <span class="text-green-600">Gestor Energético Digital</span> disponible 24/7
                        </h1>
                        <p class="text-lg text-gray-600">
                            Modo Ahorro es la plataforma SaaS que te permite gestionar, analizar y optimizar el consumo energético de tu hogar o empresa de forma autónoma e inteligente.
                        </p>
                        <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
                            <p class="text-sm text-blue-800">
                                <strong>🎉 Acceso Gratuito de Lanzamiento:</strong> Regístrate ahora y accede gratis con funcionalidades limitadas mientras ayudás a testear la plataforma.
                            </p>
                        </div>
                        <div class="flex flex-col sm:flex-row gap-4">
                            @guest
                                <a href="{{ route('register') }}" class="px-8 py-4 gradient-green text-white rounded-lg hover:opacity-90 transition font-semibold text-center shadow-lg">
                                    Comenzar Gratis
                                </a>
                                <a href="{{ route('login') }}" class="px-8 py-4 bg-gray-100 text-gray-900 rounded-lg hover:bg-gray-200 transition font-semibold text-center">
                                    Iniciar Sesión
                                </a>
                            @else
                                <a href="{{ url('/dashboard') }}" class="px-8 py-4 gradient-green text-white rounded-lg hover:opacity-90 transition font-semibold text-center shadow-lg">
                                    Ir al Dashboard
                                </a>
                            @endguest
                        </div>
                    </div>
                    <div class="relative">
                        <div class="gradient-green-light rounded-2xl p-8 shadow-2xl">
                            <img src="{{ asset('img/logoMA.png') }}" alt="Modo Ahorro"  class="w-full h-auto mx-auto" style="max-width: 300px;">
                            <div class="mt-6 grid grid-cols-3 gap-4 text-center">
                                <div class="bg-white rounded-lg p-4 shadow">
                                    <div class="text-2xl font-bold text-green-600">-30%</div>
                                    <div class="text-sm text-gray-600">Reducción Costos</div>
                                </div>
                                <div class="bg-white rounded-lg p-4 shadow">
                                    <div class="text-2xl font-bold text-green-600">+25%</div>
                                    <div class="text-sm text-gray-600">Eficiencia</div>
                                </div>
                                <div class="bg-white rounded-lg p-4 shadow">
                                    <div class="text-2xl font-bold text-green-600">100%</div>
                                    <div class="text-sm text-gray-600">Sostenible</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Plataforma Section -->
        <section id="plataforma" class="py-16 md:py-24 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                        La Plataforma que Gestiona tu Energía
                    </h2>
                    <p class="text-lg text-gray-600">
                        Todo lo que necesitás para tomar control de tu consumo energético
                    </p>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                    <div class="bg-white rounded-xl p-6 shadow-lg hover:shadow-xl transition">
                        <div class="w-12 h-12 gradient-green rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Análisis Automático</h3>
                        <p class="text-gray-600">Carga tus facturas y la plataforma analiza automáticamente tu consumo, detecta patrones y genera insights accionables.</p>
                    </div>

                    <div class="bg-white rounded-xl p-6 shadow-lg hover:shadow-xl transition">
                        <div class="w-12 h-12 gradient-green rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Recomendaciones Inteligentes</h3>
                        <p class="text-gray-600">Recibe sugerencias personalizadas de ahorro según tus equipos, hábitos y perfil de consumo real.</p>
                    </div>

                    <div class="bg-white rounded-xl p-6 shadow-lg hover:shadow-xl transition">
                        <div class="w-12 h-12 gradient-green rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Gestión de Equipos</h3>
                        <p class="text-gray-600">Registra todos tus equipos, calcula su consumo individual y simula el impacto de cambios o mejoras.</p>
                    </div>

                    <div class="bg-white rounded-xl p-6 shadow-lg hover:shadow-xl transition">
                        <div class="w-12 h-12 gradient-green rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Reportes y Seguimiento</h3>
                        <p class="text-gray-600">Visualiza la evolución de tu consumo, compara períodos y mide el impacto de tus acciones de ahorro.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Funcionalidades Section -->
        <section id="funcionalidades" class="py-16 md:py-24">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                        Funcionalidades Principales
                    </h2>
                    <p class="text-lg text-gray-600">
                        Descubre todo lo que la plataforma está desarrollando para vos
                    </p>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <!-- Digital Twin -->
                    <div class="bg-white rounded-xl p-6 border-2 border-green-500 shadow-lg hover:shadow-xl transition">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 gradient-green rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900">Gemelo Digital</h3>
                        </div>
                        <p class="text-gray-600 mb-3">Crea una réplica virtual de tus espacios (casa/empresa) con todos tus equipos y simula diferentes escenarios de consumo.</p>
                        <span class="inline-block px-3 py-1 bg-green-100 text-green-800 text-xs font-semibold rounded-full">✅ Disponible</span>
                    </div>

                    <!-- Standby Mode -->
                    <div class="bg-white rounded-xl p-6 border-2 border-green-500 shadow-lg hover:shadow-xl transition">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 gradient-green rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900">Modo Standby</h3>
                        </div>
                        <p class="text-gray-600 mb-3">Detecta consumos fantasma y calcula cuánto gastan tus dispositivos en modo reposo. Identifica los "vampiros energéticos".</p>
                        <span class="inline-block px-3 py-1 bg-green-100 text-green-800 text-xs font-semibold rounded-full">✅ Disponible</span>
                    </div>

                    <!-- Recomendaciones -->
                    <div class="bg-white rounded-xl p-6 border-2 border-green-500 shadow-lg hover:shadow-xl transition">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 gradient-green rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900">Recomendaciones</h3>
                        </div>
                        <p class="text-gray-600 mb-3">Hub de sugerencias personalizadas: termotanque solar, paneles solares, modo vacaciones y planes de ahorro adaptados a tu perfil.</p>
                        <span class="inline-block px-3 py-1 bg-green-100 text-green-800 text-xs font-semibold rounded-full">✅ Disponible</span>
                    </div>

                    <!-- Análisis Solar -->
                    <div class="bg-white rounded-xl p-6 border-2 border-green-500 shadow-lg hover:shadow-xl transition">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 gradient-green rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900">Potencial Solar</h3>
                        </div>
                        <p class="text-gray-600 mb-3">Calcula el potencial de instalación solar en tu techo o terreno. Estima inversión, ahorro proyectado y tiempo de retorno.</p>
                        <span class="inline-block px-3 py-1 bg-green-100 text-green-800 text-xs font-semibold rounded-full">✅ Disponible</span>
                    </div>

                    <!-- Mantenimiento -->
                    <div class="bg-white rounded-xl p-6 border-2 border-blue-300 shadow-lg hover:shadow-xl transition">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900">Mantenimiento</h3>
                        </div>
                        <p class="text-gray-600 mb-3">Programa y registra el mantenimiento de tus equipos. Recibe alertas y calcula el impacto en eficiencia y vida útil.</p>
                        <span class="inline-block px-3 py-1 bg-blue-100 text-blue-800 text-xs font-semibold rounded-full">🚧 En desarrollo</span>
                    </div>

                    <!-- Predicción IA -->
                    <div class="bg-white rounded-xl p-6 border-2 border-blue-300 shadow-lg hover:shadow-xl transition">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900">Predicción IA</h3>
                        </div>
                        <p class="text-gray-600 mb-3">Algoritmos de machine learning que predicen tu consumo futuro y detectan anomalías automáticamente en tiempo real.</p>
                        <span class="inline-block px-3 py-1 bg-blue-100 text-blue-800 text-xs font-semibold rounded-full">🚧 En desarrollo</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Planes Section -->
        <section id="planes" class="py-16 md:py-24 bg-gradient-to-br from-green-50 to-blue-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                        Acceso Gratuito de Lanzamiento
                    </h2>
                    <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                        Actualmente estamos ofreciendo <strong class="text-green-600">acceso gratuito</strong> con funcionalidades limitadas. Ayudanos a testear la plataforma mientras la desarrollamos y accede sin costo.
                    </p>
                </div>

                <div class="grid md:grid-cols-2 gap-8 max-w-5xl mx-auto">
                    <!-- Plan Actual (Gratuito) -->
                    <div class="bg-white rounded-2xl shadow-xl border-4 border-green-500 overflow-hidden relative">
                        <div class="absolute top-4 right-4">
                            <span class="bg-green-500 text-white px-4 py-1 rounded-full text-sm font-bold">
                                🎉 ACTUAL
                            </span>
                        </div>
                        <div class="p-8">
                            <h3 class="text-2xl font-bold text-gray-900 mb-2">Acceso Gratuito</h3>
                            <div class="mb-6">
                                <span class="text-5xl font-bold text-green-600">$0</span>
                                <span class="text-gray-600 ml-2">/ mes</span>
                            </div>
                            <p class="text-gray-600 mb-6">
                                Accede ahora gratis mientras ayudás a testear la plataforma durante su desarrollo.
                            </p>

                            <ul class="space-y-4 mb-8">
                                <li class="flex items-start gap-3">
                                    <svg class="w-6 h-6 text-green-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-700">Dashboard de monitoreo básico</span>
                                </li>
                                <li class="flex items-start gap-3">
                                    <svg class="w-6 h-6 text-green-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-700">Carga de facturas y análisis manual</span>
                                </li>
                                <li class="flex items-start gap-3">
                                    <svg class="w-6 h-6 text-green-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-700">Gemelo Digital (hasta 2 entidades)</span>
                                </li>
                                <li class="flex items-start gap-3">
                                    <svg class="w-6 h-6 text-green-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-700">Análisis de Standby básico</span>
                                </li>
                                <li class="flex items-start gap-3">
                                    <svg class="w-6 h-6 text-green-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-700">Recomendaciones generales de ahorro</span>
                                </li>
                                <li class="flex items-start gap-3">
                                    <svg class="w-6 h-6 text-gray-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                    <span class="text-gray-400">Predicción IA (próximamente)</span>
                                </li>
                                <li class="flex items-start gap-3">
                                    <svg class="w-6 h-6 text-gray-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                    <span class="text-gray-400">Soporte prioritario (próximamente)</span>
                                </li>
                            </ul>

                            <a href="{{ route('register') }}" 
                               class="block w-full py-3 px-6 gradient-green text-white text-center font-semibold rounded-lg hover:opacity-90 transition shadow-lg">
                                Comenzar Gratis Ahora
                            </a>
                        </div>
                    </div>

                    <!-- Plan Futuro (Premium) -->
                    <div class="bg-gradient-to-br from-gray-800 to-gray-900 rounded-2xl shadow-xl overflow-hidden relative border-2 border-gray-700">
                        <div class="absolute top-4 right-4">
                            <span class="bg-blue-500 text-white px-4 py-1 rounded-full text-sm font-bold">
                                🚀 PRÓXIMAMENTE
                            </span>
                        </div>
                        <div class="p-8">
                            <h3 class="text-2xl font-bold text-white mb-2">Plan Premium</h3>
                            <div class="mb-6">
                                <span class="text-5xl font-bold text-blue-400">$$$</span>
                                <span class="text-gray-400 ml-2">/ mes</span>
                            </div>
                            <p class="text-gray-300 mb-6">
                                Próximamente: acceso completo a todas las funcionalidades avanzadas de la plataforma.
                            </p>

                            <ul class="space-y-4 mb-8">
                                <li class="flex items-start gap-3">
                                    <svg class="w-6 h-6 text-blue-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-200">Todo lo incluido en Gratuito</span>
                                </li>
                                <li class="flex items-start gap-3">
                                    <svg class="w-6 h-6 text-blue-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-200">Entidades ilimitadas</span>
                                </li>
                                <li class="flex items-start gap-3">
                                    <svg class="w-6 h-6 text-blue-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-200">Análisis automático con IA</span>
                                </li>
                                <li class="flex items-start gap-3">
                                    <svg class="w-6 h-6 text-blue-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-200">Predicción de consumo futuro</span>
                                </li>
                                <li class="flex items-start gap-3">
                                    <svg class="w-6 h-6 text-blue-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-200">Alertas y notificaciones avanzadas</span>
                                </li>
                                <li class="flex items-start gap-3">
                                    <svg class="w-6 h-6 text-blue-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-200">Reportes personalizados exportables</span>
                                </li>
                                <li class="flex items-start gap-3">
                                    <svg class="w-6 h-6 text-blue-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-200">Soporte prioritario</span>
                                </li>
                            </ul>

                            <button disabled
                                    class="block w-full py-3 px-6 bg-gray-700 text-gray-400 text-center font-semibold rounded-lg cursor-not-allowed">
                                Próximamente
                            </button>
                        </div>
                    </div>
                </div>

                <div class="mt-12 text-center">
                    <div class="inline-block bg-blue-50 border-2 border-blue-200 rounded-xl p-6 max-w-3xl">
                        <p class="text-gray-700 mb-2">
                            <strong class="text-blue-600">💡 ¿Por qué es gratis?</strong>
                        </p>
                        <p class="text-gray-600">
                            La plataforma está en <strong>desarrollo activo</strong>. Tu feedback es valioso para nosotros mientras construimos la mejor herramienta de gestión energética. Los early adopters como vos nos ayudan a mejorar el producto antes del lanzamiento oficial.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Quiénes Somos Section -->
        <section id="nosotros" class="py-16 md:py-24 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid lg:grid-cols-2 gap-12 items-center">
                    <div>
                        <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-6">
                            Quiénes Somos
                        </h2>
                        <div class="space-y-4 text-gray-700 leading-relaxed">
                            <p>
                                Somos un <strong class="text-green-600">grupo de jóvenes emprendedores</strong> impulsados por la energía del cambio y la firme convicción de que la tecnología puede democratizar la gestión energética.
                            </p>
                            <p>
                                Con formación en ingeniería y trabajando junto a especialistas en arquitectura y desarrollo de software, creamos <strong class="text-green-600">Modo Ahorro</strong>: una <strong class="text-blue-600">plataforma SaaS</strong> que pone el control de la eficiencia energética en tus manos, sin necesidad de costosas consultorías.
                            </p>
                            <p>
                                Nuestro objetivo es que <strong class="text-green-600">cualquier persona u organización</strong> pueda acceder a herramientas profesionales de análisis energético de forma <strong>autónoma, inteligente y accesible 24/7</strong>.
                            </p>
                            <p>
                                La plataforma está en <strong class="text-blue-600">desarrollo activo</strong>. Cada semana agregamos nuevas funcionalidades y mejoras basadas en el feedback de nuestros usuarios early adopters.
                            </p>
                            <div class="bg-white rounded-xl p-6 border-l-4 border-green-600 shadow-md">
                                <p class="text-gray-700">
                                    Nuestra propuesta ha sido <strong>reconocida y beneficiada</strong> en el marco del <strong class="text-green-600">Programa de Asistencia para la Innovación Productiva</strong> otorgado por la <strong>Agencia Calidad San Juan</strong>, lo que nos brindó el apoyo necesario para convertir esta visión en realidad.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="relative">
                        <div class="gradient-green rounded-2xl p-8 shadow-2xl">
                            <img src="{{ asset('img/logoMA.png') }}" alt="Modo Ahorro Platform" class="w-full h-auto rounded-lg">
                        </div>
                        <div class="absolute -bottom-6 -right-6 bg-white rounded-xl p-6 shadow-xl max-w-xs">
                            <div class="flex items-center gap-3 mb-2">
                                <svg class="w-8 h-8 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                </svg>
                                <h4 class="font-bold text-gray-900">Reconocimiento Oficial</h4>
                            </div>
                            <p class="text-sm text-gray-600">Programa de Innovación Productiva - Agencia Calidad San Juan</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Final Section -->
        <section class="py-16 md:py-24 gradient-green text-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h2 class="text-3xl md:text-4xl font-bold mb-4">
                    ¿Listo para gestionar tu energía de forma inteligente?
                </h2>
                <p class="text-xl mb-8 text-green-50">
                    Registrate gratis y comienza a optimizar tu consumo hoy mismo
                </p>
                
                <div class="flex flex-col sm:flex-row gap-4 justify-center items-center mb-12">
                    @guest
                        <a href="{{ route('register') }}" 
                           class="inline-flex items-center gap-3 px-8 py-4 bg-white text-green-600 rounded-lg hover:bg-green-50 transition font-semibold text-lg shadow-xl">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                            </svg>
                            Crear Cuenta Gratuita
                        </a>
                        <a href="{{ route('login') }}" 
                           class="inline-flex items-center gap-3 px-8 py-4 bg-green-700 text-white rounded-lg hover:bg-green-800 transition font-semibold text-lg shadow-xl border-2 border-white">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                            </svg>
                            Ya tengo cuenta
                        </a>
                    @else
                        <a href="{{ url('/dashboard') }}" 
                           class="inline-flex items-center gap-3 px-8 py-4 bg-white text-green-600 rounded-lg hover:bg-green-50 transition font-semibold text-lg shadow-xl">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                            Ir al Dashboard
                        </a>
                    @endguest
                </div>

                <div class="mb-8">
                    <p class="text-green-50 mb-3">¿Tenés dudas o consultas?</p>
                    <a href="https://wa.me/+54264154533704?text=Hola,%20tengo%20una%20consulta%20sobre%20la%20plataforma%20Modo%20Ahorro" 
                       target="_blank" 
                       class="inline-flex items-center gap-2 text-white hover:text-green-100 transition underline">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.890-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                        </svg>
                        Contactanos por WhatsApp
                    </a>
                </div>

                <div class="flex justify-center gap-6">
                    <a href="https://www.instagram.com/modoahorrox/" target="_blank" class="text-white hover:text-green-100 transition">
                        <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                        </svg>
                    </a>
                    <a href="https://linkedin.com/company/modo-ahorro-xamanen/" target="_blank" class="text-white hover:text-green-100 transition">
                        <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                        </svg>
                    </a>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-gray-900 text-white py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <p class="mb-2">© Copyright {{ date('Y') }} Modo Ahorro - Todos los derechos reservados</p>
                <p class="text-sm text-gray-400">
                    Una empresa de 
                    <a href="https://www.grupoxamanen.com.ar/" target="_blank" class="text-green-400 hover:text-green-300 transition underline">
                        Grupo Xamanen
                    </a>
                </p>
            </div>
        </footer>

        <!-- Mobile menu toggle script -->
        <script>
            document.getElementById('mobile-menu-btn').addEventListener('click', function() {
                const menu = document.getElementById('mobile-menu');
                menu.classList.toggle('hidden');
            });

            // Close mobile menu when clicking on a link
            document.querySelectorAll('#mobile-menu a').forEach(link => {
                link.addEventListener('click', function() {
                    document.getElementById('mobile-menu').classList.add('hidden');
                });
            });

            // Smooth scroll
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });
        </script>
    </body>
</html>
