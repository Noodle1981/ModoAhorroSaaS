# Modo Ahorro

Modo Ahorro es una aplicación web diseñada para ayudar a los usuarios a comprender y optimizar su consumo de energía. Permite a los usuarios registrar sus propiedades, inventariar sus equipos eléctricos, analizar sus facturas y recibir recomendaciones personalizadas para reducir sus costos energéticos y su huella de carbono.

## Contexto del Proyecto

El objetivo principal de este proyecto es ofrecer una herramienta intuitiva que centralice toda la información energética de un usuario. Desde el detalle de sus contratos de suministro hasta el consumo de cada electrodoméstico, Modo Ahorro busca empoderar al usuario con datos claros y accionables para tomar decisiones informadas sobre su uso de la energía.

La aplicación también cuenta con un panel de administración para "Gestores", diseñado para que personal de la empresa pueda supervisar a los clientes y gestionar los catálogos de la aplicación.

## Funcionalidades Implementadas

Actualmente, el proyecto se encuentra en una fase de desarrollo avanzada y cuenta con las siguientes funcionalidades:

*   **Autenticación de Usuarios:** Sistema completo de registro, inicio y cierre de sesión.
*   **Gestión de Perfil:** Los usuarios pueden editar su información personal.
*   **Dashboard Principal:** Una vista general del estado energético del usuario.
*   **Gestión de Entidades:** CRUD completo para que los usuarios registren sus propiedades (casas, apartamentos, etc.).
*   **Gestión de Suministros:** CRUD para los puntos de suministro energético asociados a una entidad.
*   **Gestión de Contratos y Facturas:** Permite registrar los contratos con las compañías eléctricas y las facturas correspondientes.
*   **Inventario de Equipos:** CRUD para que los usuarios añadan los equipos eléctricos de cada una de sus entidades, especificando su consumo.
*   **Panel de Gestor:** Una sección privada para administradores (`/gestor`) que permite:
    *   Visualizar y gestionar clientes.
    *   Administrar los planes de suscripción.
    *   Gestionar el catálogo de tipos de equipos genéricos.

## Etapas Futuras

*   **Lógica de Recomendaciones y Mantenimientos:** Implementar el motor de inteligencia que analizará los datos del usuario para generar recomendaciones de ahorro personalizadas y planes de mantenimiento preventivo para sus equipos.
*   **Visualización de Datos:** Crear una interfaz de usuario atractiva y moderna, con gráficos y estadísticas que faciliten la comprensión de los datos de consumo energético.

## Tecnologías Utilizadas

*   **Backend:** Laravel 12
*   **Frontend:** Vite con Tailwind CSS
*   **Base de Datos:** SQLite (configurado por defecto para desarrollo local)
*   **Versión de PHP:** 8.2 o superior

## Puesta en Marcha (Instalación)

Para clonar y ejecutar este proyecto en un entorno de desarrollo local, sigue estos pasos:

1.  **Clonar el repositorio:**
    ```bash
    git clone <URL_DEL_REPOSITORIO>
    cd modoahorro
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
    Esto creará la estructura de la base de datos y la llenará con los datos de prueba iniciales.
    ```bash
    php artisan migrate --seed
    ```

7.  **Iniciar los servicios de desarrollo:**
    Este comando iniciará el servidor de PHP, el compilador de Vite, la cola de trabajos y el visor de logs, todo al mismo tiempo.
    ```bash
    composer run dev
    ```

    Una vez ejecutado, la aplicación estará disponible en `http://127.0.0.1:8000`.

## Ejecución de Pruebas

Para ejecutar el conjunto de pruebas automatizadas y asegurar que todo funciona correctamente, utiliza el siguiente comando:

```bash
php artisan test
```
