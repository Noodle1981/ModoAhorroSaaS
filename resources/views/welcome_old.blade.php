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
                        <a href="#beneficios" class="text-gray-600 hover:text-green-600 transition">Beneficios</a>
                        <a href="#diagnostico" class="text-gray-600 hover:text-green-600 transition">Diagnóstico</a>
                        <a href="#nosotros" class="text-gray-600 hover:text-green-600 transition">Nosotros</a>
                        <a href="#contacto" class="text-gray-600 hover:text-green-600 transition">Contacto</a>
                        
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
                    <a href="#beneficios" class="block text-gray-600 hover:text-green-600">Beneficios</a>
                    <a href="#diagnostico" class="block text-gray-600 hover:text-green-600">Diagnóstico</a>
                    <a href="#nosotros" class="block text-gray-600 hover:text-green-600">Nosotros</a>
                    <a href="#contacto" class="block text-gray-600 hover:text-green-600">Contacto</a>
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
                        <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-gray-900 leading-tight">
                            Optimizá el consumo energético de tu 
                            <span class="text-green-600">hogar, comercio o empresa</span>
                        </h1>
                        <p class="text-lg text-gray-600">
                            Nuestro enfoque innovador te permitirá elegir la mejor opción que se adapte a tus necesidades y presupuesto para una transición energética eficiente.
                        </p>
                        <div class="flex flex-col sm:flex-row gap-4">
                            <a href="#contacto" class="px-8 py-4 gradient-green text-white rounded-lg hover:opacity-90 transition font-semibold text-center">
                                Solicitar Diagnóstico
                            </a>
                            @guest
                                <a href="{{ route('register') }}" class="px-8 py-4 bg-gray-100 text-gray-900 rounded-lg hover:bg-gray-200 transition font-semibold text-center">
                                    Crear Cuenta Gratis
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

        <!-- Beneficios Section -->
        <section id="beneficios" class="py-16 md:py-24 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                        Conocé los beneficios de un diagnóstico energético
                    </h2>
                    <p class="text-lg text-gray-600">
                        Transformá tu consumo energético en una ventaja competitiva
                    </p>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                    <div class="bg-white rounded-xl p-6 shadow-lg hover:shadow-xl transition">
                        <div class="w-12 h-12 gradient-green rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Reducción de costos inmediatos</h3>
                        <p class="text-gray-600">Identifica y elimina gastos innecesarios en tu consumo eléctrico. Ahorra dinero desde el primer mes.</p>
                    </div>

                    <div class="bg-white rounded-xl p-6 shadow-lg hover:shadow-xl transition">
                        <div class="w-12 h-12 gradient-green rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Optimización del consumo</h3>
                        <p class="text-gray-600">Detecta desperdicios energéticos y hace que cada watt cuente. Mejora la eficiencia sin sacrificar comodidad.</p>
                    </div>

                    <div class="bg-white rounded-xl p-6 shadow-lg hover:shadow-xl transition">
                        <div class="w-12 h-12 gradient-green rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Mayor vida útil de equipos</h3>
                        <p class="text-gray-600">Evita sobrecargos y desgastes prematuros en tus dispositivos. Un uso eficiente alarga su durabilidad.</p>
                    </div>

                    <div class="bg-white rounded-xl p-6 shadow-lg hover:shadow-xl transition">
                        <div class="w-12 h-12 gradient-green rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Sostenibilidad</h3>
                        <p class="text-gray-600">Reduce tu impacto ambiental optimizando el uso de tu energía. Contribuye a un futuro más limpio.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Diagnóstico Process Section -->
        <section id="diagnostico" class="py-16 md:py-24">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                        MODO AHORRO DIAGNÓSTICO
                    </h2>
                    <p class="text-lg text-gray-600">
                        Conocé el alcance de un diagnóstico energético, nuestro equipo de ingeniería se encargará de los siguientes pasos
                    </p>
                </div>

                <div class="relative">
                    <!-- Timeline line -->
                    <div class="hidden lg:block absolute left-1/2 transform -translate-x-1/2 h-full w-1 bg-green-200"></div>

                    <div class="space-y-12">
                        <!-- Step 1 -->
                        <div class="relative grid lg:grid-cols-2 gap-8 items-center">
                            <div class="lg:text-right">
                                <div class="inline-block bg-gradient-green-light rounded-xl p-8 shadow-lg">
                                    <div class="flex lg:flex-row-reverse items-start gap-4">
                                        <div class="flex-shrink-0 w-12 h-12 gradient-green rounded-full flex items-center justify-center text-white font-bold text-xl">
                                            1
                                        </div>
                                        <div>
                                            <h3 class="text-2xl font-bold text-gray-900 mb-2">Relevamiento Energético</h3>
                                            <p class="text-gray-700">
                                                Organizamos los datos históricos de consumo de la empresa y clasificamos la energía utilizada para identificar áreas clave.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="hidden lg:block"></div>
                        </div>

                        <!-- Step 2 -->
                        <div class="relative grid lg:grid-cols-2 gap-8 items-center">
                            <div class="hidden lg:block"></div>
                            <div>
                                <div class="inline-block bg-gradient-green-light rounded-xl p-8 shadow-lg">
                                    <div class="flex items-start gap-4">
                                        <div class="flex-shrink-0 w-12 h-12 gradient-green rounded-full flex items-center justify-center text-white font-bold text-xl">
                                            2
                                        </div>
                                        <div>
                                            <h3 class="text-2xl font-bold text-gray-900 mb-2">Evaluación de Infraestructura</h3>
                                            <p class="text-gray-700">
                                                Realizamos una evaluación de las infraestructuras físicas, detectando ineficiencias en los sistemas eléctricos, iluminación, calefacción, ventilación y refrigeración.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 3 -->
                        <div class="relative grid lg:grid-cols-2 gap-8 items-center">
                            <div class="lg:text-right">
                                <div class="inline-block bg-gradient-green-light rounded-xl p-8 shadow-lg">
                                    <div class="flex lg:flex-row-reverse items-start gap-4">
                                        <div class="flex-shrink-0 w-12 h-12 gradient-green rounded-full flex items-center justify-center text-white font-bold text-xl">
                                            3
                                        </div>
                                        <div>
                                            <h3 class="text-2xl font-bold text-gray-900 mb-2">Sugerencias y Mejoras</h3>
                                            <p class="text-gray-700">
                                                Generamos recomendaciones específicas para cada energía utilizada por la empresa y plasmamos un reporte de ahorro estimado.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="hidden lg:block"></div>
                        </div>

                        <!-- Step 4 -->
                        <div class="relative grid lg:grid-cols-2 gap-8 items-center">
                            <div class="hidden lg:block"></div>
                            <div>
                                <div class="inline-block bg-gradient-green-light rounded-xl p-8 shadow-lg">
                                    <div class="flex items-start gap-4">
                                        <div class="flex-shrink-0 w-12 h-12 gradient-green rounded-full flex items-center justify-center text-white font-bold text-xl">
                                            4
                                        </div>
                                        <div>
                                            <h3 class="text-2xl font-bold text-gray-900 mb-2">Informe Técnico</h3>
                                            <p class="text-gray-700">
                                                Estos informes están diseñados para facilitar la toma de decisiones informadas en todos los niveles de la organización y hacer un seguimiento de las mejoras.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
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
                                Somos un <strong class="text-green-600">grupo de jóvenes emprendedores</strong> impulsados por la energía del cambio y la firme convicción de que podemos marcar la diferencia.
                            </p>
                            <p>
                                Con una sólida formación, y trabajando en conjunto con especialistas en ingeniería y arquitectura, hemos creado <strong class="text-green-600">Modo Ahorro</strong>, una empresa profundamente comprometida con la sostenibilidad y dedicada a optimizar el consumo energético de hogares, comercios y empresas.
                            </p>
                            <p>
                                En un mundo donde la eficiencia energética se ha vuelto indispensable, nuestro propósito es ayudar a nuestros clientes a <strong class="text-green-600">reducir costos y minimizar su huella de carbono</strong>.
                            </p>
                            <div class="bg-white rounded-xl p-6 border-l-4 border-green-600 shadow-md">
                                <p class="text-gray-700">
                                    Nuestra propuesta ha sido <strong>reconocida y beneficiada</strong> en el marco del <strong class="text-green-600">Programa de Asistencia para la Innovación Productiva</strong> otorgado por la <strong>Agencia Calidad San Juan</strong>, lo que nos ha brindado el apoyo necesario para convertir nuestro proyecto en realidad.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="relative">
                        <div class="gradient-green rounded-2xl p-8 shadow-2xl">
                            <img src="{{ asset('img/logoMA.png') }}" alt="Modo Ahorro Team" class="w-full h-auto rounded-lg">
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

        <!-- Contacto Section -->
        <section id="contacto" class="py-16 md:py-24 gradient-green text-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h2 class="text-3xl md:text-4xl font-bold mb-4">
                    ¿Listo para optimizar tu consumo energético?
                </h2>
                <p class="text-xl mb-8 text-green-50">
                    Contáctese rápidamente por WhatsApp
                </p>
                <a href="https://wa.me/+54264154533704?text=Hola,%20deseo%20realizar%20una%20consulta%20sobre%20Modo%20Ahorro" 
                   target="_blank" 
                   class="inline-flex items-center gap-3 px-8 py-4 bg-white text-green-600 rounded-lg hover:bg-green-50 transition font-semibold text-lg shadow-xl">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.890-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                    </svg>
                    Enviar consulta a WhatsApp
                </a>

                <div class="mt-12 flex justify-center gap-6">
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
                <p>© Copyright {{ date('Y') }} Modo Ahorro - Todos los derechos reservados</p>
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
