# Modo Ahorro

Modo Ahorro es una aplicación web diseñada para ayudar a los usuarios a comprender y optimizar su consumo de energía. Permite a los usuarios registrar sus propiedades (entidades), inventariar sus equipos eléctricos, analizar sus facturas y recibir recomendaciones personalizadas para reducir sus costos energéticos y su huella de carbono.

## Filosofía de Navegación

La aplicación se estructura en torno a dos niveles de dashboards para una experiencia de usuario clara y eficiente:

1.  **Dashboard General (`/dashboard`):** Actúa como la página de inicio y un centro de mando global. Ofrece un resumen de todas las entidades gestionadas por el usuario, mostrando métricas clave y proporcionando acceso directo a cada una de ellas.
2.  **Dashboard de Entidad (`/entities/{id}`):** Es el panel de control detallado para una entidad específica (ej: un hogar o una oficina). Aquí, el usuario puede analizar el consumo, gestionar el inventario de equipos, administrar suministros y facturas, y ver informes específicos de esa entidad.

## Funcionalidades Implementadas

*   **Autenticación de Usuarios:** Sistema completo de registro, inicio y cierre de sesión.
*   **Gestión de Perfil:** Los usuarios pueden editar su información personal.
*   **Navegación por Dashboards:**
    *   **Dashboard General:** Vista de pájaro de todas las propiedades del usuario.
    *   **Dashboard de Entidad:** Análisis y gestión detallada por propiedad.
    *   **Redirección Inteligente:** Los usuarios con una sola entidad son llevados directamente a su dashboard de entidad para un acceso más rápido.
*   **Gestión de Entidades y Suministros:** CRUD completo para propiedades y sus puntos de suministro.
*   **Gestión de Contratos y Facturas:** Permite registrar contratos y facturas de compañías eléctricas.
*   **Inventario de Equipos Dinámico:**
    *   Formulario de creación de equipos con lógica condicional avanzada.
    *   Menús desplegables en cascada (Categoría -> Tipo de Equipo).
    *   Campos que se adaptan a las propiedades del equipo (ej: ubicación para equipos fijos).
    *   Cálculo opcional de **consumo en standby**.
*   **Análisis de Período Activo:** El dashboard de la entidad compara el consumo real (de la factura) con el consumo estimado (del inventario) para el último período de facturación, identificando el porcentaje de consumo explicado.
*   **Flujo de Usuario Guiado:** La interfaz guía activamente al usuario para que complete los datos necesarios. Si faltan datos (como un suministro o un contrato), los botones se adaptan para llevar al usuario al formulario correcto, evitando callejones sin salida.

## Modelo de Dominio Principal

La lógica de la aplicación gira en torno a los siguientes conceptos:

*   **Usuario (User):** El titular de la cuenta.
*   **Compañía (Company):** Una entidad que agrupa a un usuario y sus propiedades. Permite la futura expansión a modelos B2B.
*   **Entidad (Entity):** Una propiedad que se quiere analizar (ej: "Casa de Campo", "Oficina Central").
*   **Suministro (Supply):** Un punto de suministro eléctrico dentro de una Entidad, identificado por su CUPS.
*   **Contrato (Contract):** Un contrato con una comercializadora eléctrica, asociado a un Suministro.
*   **Factura (Invoice):** Una factura de un período específico, asociada a un Contrato.
*   **Equipo de Entidad (EntityEquipment):** Un equipo o electrodoméstico individual que pertenece a una Entidad.

## Nota sobre el Rol "Gestor"

El código fuente contiene rutas, controladores y vistas para un panel de "Gestor" (ubicado en el prefijo de ruta `/gestor`). **Esta funcionalidad está actualmente comentada y desactivada del flujo principal de la aplicación.**

Se tomó la decisión de centrar el desarrollo en la experiencia del usuario final. El código del Gestor se ha mantenido por si se decide retomar en el futuro, pero es posible que sea eliminado en próximas versiones para simplificar la base del código.

## Tecnologías Utilizadas

*   **Backend:** Laravel 11
*   **Frontend:** Vite con Blade y JavaScript vanilla.
*   **Base de Datos:** SQLite (para desarrollo).
*   **Versión de PHP:** 8.2 o superior.

## Instalación y Puesta en Marcha

Para clonar y ejecutar este proyecto en un entorno de desarrollo local, sigue estos pasos:

1.  **Clonar el repositorio:**
    ```bash
    git clone <URL_DEL_REPOSITORIO>
    cd modoahorro
    ```

2.  **Instalar dependencias:**
    ```bash
    composer install
    npm install
    ```

3.  **Configurar el entorno:**
    Copia el archivo `.env.example` y genera la clave de la aplicación.
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

4.  **Crear la base de datos:**
    El proyecto usa SQLite por defecto. Simplemente crea el archivo.
    ```bash
    touch database/database.sqlite
    ```

5.  **Ejecutar migraciones y seeders:**
    Esto creará la estructura de la base de datos y la llenará con datos de catálogo iniciales.
    ```bash
    php artisan migrate:fresh --seed
    ```

6.  **Compilar assets y arrancar el servidor de desarrollo:**
    ```bash
    npm run dev & php artisan serve
    ```

La aplicación estará disponible en `http://127.0.0.1:8000`.

## Ejecución de Pruebas

Para ejecutar el conjunto de pruebas automatizadas, utiliza el siguiente comando:

```bash
php artisan test
```