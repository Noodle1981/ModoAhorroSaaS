# Modo Ahorro

Modo Ahorro es una aplicación web diseñada para ayudar a los usuarios a comprender y optimizar su consumo de energía. Permite a los usuarios registrar sus propiedades, inventariar sus equipos eléctricos, analizar sus facturas y recibir recomendaciones personalizadas para reducir sus costos energéticos y su huella de carbono.

## Contexto del Proyecto

El objetivo principal de este proyecto es ofrecer una herramienta intuitiva que centralice toda la información energética de un usuario. Desde el detalle de sus contratos de suministro hasta el consumo de cada electrodoméstico, Modo Ahorro busca empoderar al usuario con datos claros y accionables para tomar decisiones informadas sobre su uso de la energía.

La aplicación también cuenta con un panel de administración para "Gestores", diseñado para que personal de la empresa pueda supervisar a los clientes y gestionar los catálogos de la aplicación.

## Funcionalidades Implementadas

*   **Autenticación de Usuarios:** Sistema completo de registro, inicio y cierre de sesión.
*   **Gestión de Perfil:** Los usuarios pueden editar su información personal.
*   **Gestión de Entidades y Suministros:** CRUD completo para propiedades y sus puntos de suministro.
*   **Gestión de Contratos y Facturas:** Permite registrar contratos y facturas de compañías eléctricas.
*   **Inventario de Equipos Dinámico:**
    *   Formulario de creación de equipos con lógica condicional avanzada.
    *   Menús desplegables en cascada (Categoría -> Tipo de Equipo).
    *   Campos que aparecen o se ocultan según las propiedades del equipo (ej: la ubicación no se pide para equipos portátiles).
    *   Entrada de tiempo de uso adaptable y cálculo opcional de **consumo en standby**.
*   **Sistema de Historial y Snapshots de Uso:**
    *   La aplicación ya no es "amnésica". Se ha implementado una arquitectura que guarda un "snapshot" (una foto) del uso del inventario para cada período de facturación.
    *   **Nuevo Flujo:** Tras cargar una factura, el usuario es redirigido a una pantalla para confirmar los patrones de uso de sus equipos para ese período específico.
    *   **Nuevos Componentes:** Este sistema se apoya en una nueva tabla `equipment_usage_snapshots`, el controlador `UsageSnapshotController` y rutas dedicadas.
    *   **Análisis de Período Activo:** La página de detalle de la entidad ahora muestra un dashboard que compara con precisión el consumo real de la última factura contra el consumo estimado del inventario para ese mismo período.
*   **Motor de Análisis y Recomendaciones (v1.5):**
    *   Cálculo del perfil de consumo energético anual estimado basado en el inventario.
    *   Generación de un informe de oportunidades de mejora con 3 estados por equipo: "Equipo Deficiente" (con recomendación), "Equipo Eficiente" y "Equipo sin Comparativa".
    *   **Log de Faltantes:** Creación de un log (`mejoras_faltantes.log`) que registra automáticamente los equipos que no se pudieron comparar por falta de alternativas en el catálogo, facilitando la mejora continua del motor.
*   **Panel de Gestor:** Sección privada para administradores (`/gestor`) con gestión de clientes y catálogos de la aplicación.
*   **Flujo de Gestión de Facturas Mejorado:**
    *   **Guía al Usuario:** La interfaz ahora guía activamente al usuario para que complete los datos necesarios. Si una entidad no tiene un punto de suministro o un contrato, los enlaces para "añadir factura" se transforman inteligentemente para llevar al usuario al formulario de creación de suministros o contratos, evitando así callejones sin salida.
    *   **Centro de Gestión de Suministros:** La página de detalles de un suministro se ha rediseñado para ser un centro de operaciones de facturación. Ahora incluye un botón para añadir facturas directamente al contrato activo y un historial completo de todas las facturas cargadas para ese suministro.

## Etapas Futuras

*   **Dashboard Histórico:** Aprovechar el nuevo sistema de snapshots para construir un dashboard con gráficos que muestren la evolución del consumo real vs. el estimado a lo largo del tiempo.
*   **Refinamiento del Motor de Análisis:** Ampliar el tipo de recomendaciones (ej: sugerencias por cambio de hábitos, optimización de tarifas).
*   **Notificaciones y Alertas:** Implementar un sistema que notifique a los usuarios sobre mantenimientos próximos o nuevas oportunidades de ahorro detectadas.

## Tecnologías Utilizadas

*   **Backend:** Laravel 11
*   **Frontend:** Vite (con una implementación básica de Blade y JavaScript, pendiente de rediseño con un framework como Vue/React o mejora con Tailwind CSS).
*   **Base de Datos:** SQLite (configurado por defecto para desarrollo local).
*   **Versión de PHP:** 8.2 o superior

## Puesta en Marcha (Instalación)

Para clonar y ejecutar este proyecto en un entorno de desarrollo local, sigue estos pasos:

1.  **Clonar el repositorio:**
    ```bash
    git clone <URL_DEL_REPOSITORIO>
    cd ModoAhorroSaaS
    ```

2.  **Instalar dependencias de PHP:**
    ```bash
    composer install
    ```

3.  **Instalar dependencias de JavaScript:**
    ```bash
    npm install
    ```

4.  **Configurar el entorno:**
    Copia el archivo de ejemplo para las variables de entorno y genera la clave de la aplicación.
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

5.  **Crear la base de datos:**
    El proyecto está configurado para usar SQLite. Simplemente crea el archivo de la base de datos.
    ```bash
    touch database/database.sqlite
    ```

6.  **Ejecutar las migraciones y los seeders:**
    Esto creará la estructura de la base de datos y la llenará con los datos de catálogo iniciales.
    ```bash
    php artisan migrate:fresh --seed
    ```

7.  **Compilar los assets y arrancar el servidor:**
    ```bash
    npm run dev
    php artisan serve
    ```

    Una vez ejecutado, la aplicación estará disponible en `http://127.0.0.1:8000`.

## Ejecución de Pruebas

Para ejecutar el conjunto de pruebas automatizadas y asegurar que todo funciona correctamente, utiliza el siguiente comando:

```bash
php artisan test
```
