# Proyecto Transacciones

Este proyecto es una aplicación de Symfony para gestionar transacciones financieras.

## Requisitos

- PHP >= 7.4
- Composer
- PostgreSQL 16.2
- Symfony CLI version 5.8.17

# Instalación desde Windows
1. Clona el repositorio:

    ```bash
    git clone git@github.com:Vinceto/finanzas.git
    ```

2. Accede al directorio del proyecto:

    ```bash
    cd finanzas
    ```

3. Descargar PHP 7.4.26:

    ```bash
    https://phpdev.toolsforresearch.com/php-7.4.33-nts-Win32-vs16-x64.zip
    ```

4. Descomprime y crea la variable de entorno en Path con esta ruta y dentro de 7.4 descomprime el zip:

    ```bash
    C:\php\7.4
    ```

5. Probar si funciona:

    ```bash
    php -v
    ```
# te retornara un mensaje
PHP 7.4.33 (cli) (built: May 22 2024 13:42:34) ( NTS Visual C++ 2019 x64 )

6. Descargar y ejecutar .exe composer:

    ```bash
    https://getcomposer.org/download/
    ```

7. Probar si funciona:

    ```bash
    composer -v
    ```
# te retornara un mensaje
   ______
  / ____/___  ____ ___  ____  ____  ________  _____
 / /   / __ \/ __ `__ \/ __ \/ __ \/ ___/ _ \/ ___/
/ /___/ /_/ / / / / / / /_/ / /_/ (__  )  __/ /    
\____/\____/_/ /_/ /_/ .___/\____/____/\___/_/     
                    /_/
Composer version 2.7.6 2024-05-04 23:03:15

8. Instalar las dependencias de Composer:

    ```bash
    composer install
    ```

9. Descargar e instalar postgresql:

    ```bash
    https://www.enterprisedb.com/downloads/postgres-postgresql-downloads
    ```

10. Probar instalacion:

    ```bash
    psql -v
    ```

11. Entrar con usuario postgres si desea usar la consola:

    ```bash
    psql -U postgres
    ```
12. Descargar e instalar symfony:

    ```bash
    https://symfony.com/download
    ```

13. Cambiar la Política de Ejecución de powershell:

    ```bash
    Set-ExecutionPolicy RemoteSigned -Scope CurrentUser
    ```

14. Instalar scoop:

    ```bash
    iex (new-object net.webclient).downloadstring('https://get.scoop.sh')
    ```

15. Instalar cliente symfony:

    ```bash
    scoop install symfony-cli
    ```

16. Probar instalacion:

    ```bash
    symfony -v
    ```

17. Ver ubicacion del php.ini y habilitar la extension ;extension=pdo_pgsql quitando el ';' :

    ```bash
    php --ini
    ```

18. Crear base de datos:

    ```bash
    php bin/console doctrine:database:create
    ```

19. Crear la migracion para crear las tablas y sus atributos:

    ```bash
    php bin/console doctrine:migrations:migrate
    ```

20. Ahora se puede ejecutar el servidor de la app y probarlo con:

    ```bash
    symfony server:start
    ```

## podras acceder desde local: http://127.0.0.1:8000/transactions

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

## Editor de Texto

- Visual Studio Code

## Extensiones de VS Code

- Twig v1.0.2
- Live Server v5.7.9
