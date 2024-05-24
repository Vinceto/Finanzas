# Proyecto Transacciones

Este proyecto es una aplicación de Symfony para gestionar transacciones financieras.

## Requisitos

- PHP >= 7.4
- Composer
- PostgreSQL

## Instalación

1. Clona el repositorio:

    ```bash
    git clone https://github.com/tu_usuario/transacciones.git
    ```

2. Accede al directorio del proyecto:

    ```bash
    cd transacciones
    ```

3. Instala las dependencias con Composer:

    ```bash
    composer install
    ```

4. Copia el archivo `.env`:

    ```bash
    cp .env.example .env
    ```

5. Configura tu base de datos en el archivo `.env`:

    ```dotenv
    # Configura la conexión a la base de datos
    DATABASE_URL=mysql://usuario:contraseña@127.0.0.1:3306/transacciones
    ```

6. Crea la base de datos y ejecuta las migraciones:

    ```bash
    php bin/console doctrine:database:create
    php bin/console doctrine:migrations:migrate
    ```

7. Inicia el servidor local:

    ```bash
    symfony server:start
    ```

## Uso

La aplicación ofrece una interfaz de usuario y una API para gestionar transacciones financieras.

### Interfaz de Usuario

Accede a la aplicación en tu navegador web:

### API

La API ofrece endpoints para listar, crear, editar y eliminar transacciones. Puedes encontrar la documentación de la API en:

## Contribución

Si deseas contribuir a este proyecto, sigue estos pasos:

1. Haz un fork del repositorio.
2. Crea una nueva rama (`git checkout -b feature/nueva-caracteristica`).
3. Realiza tus cambios y commitea (`git commit -am 'Añade nueva característica'`).
4. Haz push a la rama (`git push origin feature/nueva-caracteristica`).
5. Abre un pull request.

## Licencia

Este proyecto está bajo la Licencia MIT. Consulta el archivo [LICENSE](LICENSE) para más detalles.

## Configuración utilizada

- PHP 7.4.26 (cli)
- Symfony CLI version 5.8.17
- psql (PostgreSQL) 16.2

## Editor de Texto

- Visual Studio Code

## Extensiones de VS Code

- Twig v1.0.2
- Live Server v5.7.9
