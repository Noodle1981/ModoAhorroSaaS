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
    *   Entrada de tiempo de uso adaptable: el sistema sugiere la unidad (horas o minutos) más apropiada, pero el usuario tiene control total para introducir el valor exacto.
    *   Cálculo opcional de **consumo en standby** (consumo fantasma) para equipos que apliquen.
*   **Motor de Análisis y Recomendaciones (v1):**
    *   Cálculo del perfil de consumo energético anual estimado basado en el inventario.
    *   Lógica defensiva que maneja con elegancia la ausencia de datos (ej: usuarios sin facturas).
    *   Generación de un informe de oportunidades de mejora (reemplazo de equipos, mantenimientos pendientes).
*   **Panel de Gestor:** Sección privada para administradores (`/gestor`) con gestión de clientes y catálogos de la aplicación (planes, tipos de equipos).

## Etapas Futuras

*   **Refinamiento del Motor de Análisis:** Mejorar y ampliar el tipo de recomendaciones generadas (ej: sugerencias por cambio de hábitos, optimización de tarifas).
*   **Mejora de la Interfaz de Usuario (UI/UX):** Rediseñar las vistas principales, especialmente los dashboards y reportes, para presentar la información de forma más clara y atractiva mediante gráficos interactivos y una mejor organización visual.
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