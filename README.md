# Modo Ahorro

Modo Ahorro es una aplicación web diseñada para ayudar a los usuarios a comprender y optimizar su consumo de energía. Permite a los usuarios registrar sus propiedades, inventariar sus equipos eléctricos, analizar sus facturas y recibir recomendaciones personalizadas para reducir sus costos energéticos y su huella de carbono.

## Contexto del Proyecto

El objetivo principal de este proyecto es ofrecer una herramienta intuitiva que centralice toda la información energética de una empresa o usuario individual. Desde el detalle de sus contratos de suministro hasta el consumo de cada electrodoméstico, Modo Ahorro busca empoderar a las empresas y usuarios con datos claros y accionables para tomar decisiones informadas sobre su uso de la energía. Cada usuario gestiona su propia empresa y sus datos energéticos asociados.

## Funcionalidades Implementadas

*   **Autenticación de Usuarios:** Sistema completo de registro, inicio y cierre de sesión.
    *   **Registro Mejorado:** Durante el registro, cada nuevo usuario crea automáticamente una empresa asociada y selecciona su provincia y localidad, sentando las bases para una gestión energética personalizada.
*   **Gestión de Perfil:** Los usuarios pueden editar su información personal.
*   **Gestión de Entidades y Suministros:** CRUD completo para propiedades y sus puntos de suministro.
    *   **Visualización Mejorada de Entidades:** La página de detalles de la entidad (`/entities/{id}`) ha sido significativamente mejorada visualmente y ahora muestra claramente las propiedades de la entidad y permite la gestión completa (CRUD) de sus equipos asociados.
    *   **Manejo de Detalles de Entidad:** Se ha mejorado la presentación de los detalles adicionales de la entidad, especialmente para estructuras de datos complejas como las habitaciones, mostrándolas de forma legible.
*   **Gestión de Contratos y Facturas:** Permite registrar contratos y facturas de compañías eléctricas.
    *   **Edición de Facturas:** La opción para editar facturas ya está disponible en la página de detalles del contrato, permitiendo corregir errores en los datos cargados.
*   **Inventario de Equipos Dinámico:**
    *   Formulario de creación de equipos con lógica condicional avanzada.
    *   Menús desplegables en cascada (Categoría -> Tipo de Equipo).
    *   Campos que aparecen o se ocultan según las propiedades del equipo (ej: la ubicación no se pide para equipos portátiles).
    *   **Entrada de Uso Flexible:** Ahora permite introducir el tiempo de uso en **horas** (con decimales) o en **minutos** (para usos menores a una hora) mediante un selector interactivo.
    *   **Opción "No se usó":** Se ha añadido una casilla para indicar si un equipo no fue utilizado durante el período, simplificando la entrada de datos.
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

*   **Flujo de Gestión de Facturas Mejorado:**
    *   **Guía al Usuario:** La interfaz ahora guía activamente al usuario para que complete los datos necesarios. Si una entidad no tiene un punto de suministro o un contrato, los enlaces para "añadir factura" se transforman inteligentemente para llevar al usuario al formulario de creación de suministros o contratos, evitando así callejones sin salida.
    *   **Centro de Gestión de Suministros:** La página de detalles de un suministro se ha rediseñado para ser un centro de operaciones de facturación. Ahora incluye un botón para añadir facturas directamente al contrato activo y un historial completo de todas las facturas cargadas para ese suministro.

## Correcciones y Mejoras Recientes

*   **Resolución de Errores de Carga de Facturas:** Se corrigió un error que impedía la carga de facturas debido a un método `create` faltante en `UsageSnapshotController`.
*   **Uso Correcto de Carbon:** Se solucionó el error `Class "App\Http\Controllers\Carbon\Carbon" not found` en `EntityController` mediante la importación adecuada de la clase `Carbon`.
*   **Relación de Equipos de Entidad:** Se corrigió el error `BadMethodCallException: Call to undefined method App\Models\Entity::entityEquipment()` en `InventoryAnalysisService` ajustando el nombre de la relación a `entityEquipments`.
*   **Mejoras Visuales Generales:** Se aplicaron estilos de Tailwind CSS más detallados y consistentes en varias secciones, especialmente en la página de detalles de la entidad, para una apariencia más atractiva y alineada con el diseño del proyecto.

## Etapas Futuras

*   **Dashboard Histórico:** Aprovechar el nuevo sistema de snapshots para construir un dashboard con gráficos que muestren la evolución del consumo real vs. el estimado a lo largo del tiempo.
*   **Refinamiento del Motor de Análisis:** Ampliar el tipo de recomendaciones (ej: sugerencias por cambio de hábitos, optimización de tarifas).
*   **Notificaciones y Alertas:** Implementar un sistema que notifique a los usuarios sobre mantenimientos próximos o nuevas oportunidades de ahorro detectadas.

## Tecnologías Utilizadas

*   **Backend:** Laravel 11
*   **Frontend:** Vite (con una implementación robusta de Blade, JavaScript y **Tailwind CSS** para el estilizado).
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