# 📘 Guía Paso a Paso para Despliegue Local: UNIRED

Esta guía está diseñada para que cualquier persona o desarrollador pueda clonar, configurar y desplegar **UNIRED** localmente desde cero en su máquina utilizando **XAMPP** o el **servidor integrado de PHP**.

---

## 📋 Requisitos Previos

Asegúrate de contar con lo siguiente instalado en tu equipo:
1. **PHP 8.0 o superior** (incluido en XAMPP).
2. **MySQL / MariaDB** (incluido en XAMPP).
3. **Composer** ([getcomposer.org](https://getcomposer.org/)).
4. **Git** (opcional, para clonar el repositorio).
5. **XAMPP** ([apachefriends.org](https://www.apachefriends.org/)) o cualquier stack LAMP/WAMP/Laragon.

---

## 🚀 Método 1: Despliegue con XAMPP (Recomendado con VirtualHost)

### Paso 1: Ubicación del Proyecto
Descarga o clona el repositorio dentro de tu directorio de trabajo o directamente en la carpeta `htdocs` de XAMPP.
Por ejemplo:
```text
C:\xampp\htdocs\ProyectoFinal
```

---

### Paso 2: Instalar Dependencias de Composer
Abre una terminal (PowerShell, CMD o Git Bash) en la raíz de la carpeta del proyecto y ejecuta:

```bash
composer install
```
> Esto descargará las librerías necesarias en la carpeta `vendor/` (incluyendo `vlucas/phpdotenv` y `tcpdf`).

---

### Paso 3: Configurar Variables de Entorno (`.env`)
En la raíz del proyecto, crea un archivo llamado `.env` (si no existe ya) con tus credenciales locales de base de datos de XAMPP:

```env
DB_HOST=127.0.0.1
DB_NAME=unired_DB
DB_USER=root
DB_PASS=
```
*(Si tu usuario `root` de MySQL tiene contraseña asignada, indícala en `DB_PASS`)*.

---

### Paso 4: Inicializar la Base de Datos

1. Abre el **XAMPP Control Panel** e inicia el módulo **MySQL**.
2. Abre tu terminal en la raíz del proyecto y ejecuta la creación de base de datos e importación:

#### Opción A: Por comandos (rápido)
```bash
# Crear la base de datos e importar esquema con tablas y procedimientos almacenados
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS unired_DB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p unired_DB < database/unired_db.sql
mysql -u root -p unired_DB < database/migrations/2025_05_17_performance_indexes.sql

# Poblar con datos de prueba (usuarios, posts, comentarios) y crear usuario administrador
php database/seed.php
php database/create_admin.php
```

#### Opción B: Por phpMyAdmin
1. Entra a `http://localhost/phpmyadmin`.
2. Crea una base de datos llamada `unired_DB` con cotejamiento `utf8mb4_unicode_ci`.
3. Haz clic en la pestaña **Importar** y selecciona el archivo `database/unired_db.sql`.
4. Importa también `database/migrations/2025_05_17_performance_indexes.sql`.
5. Ejecuta en tu terminal `php database/seed.php` para generar los usuarios y publicaciones de prueba.

---

### Paso 5: Configuración de Apache en XAMPP

Dado que las rutas y redirecciones del proyecto son raíz (como `/login`, `/posts`, `/dashboard`), la mejor manera es configurar un **VirtualHost**:

1. Abre el archivo de configuración de Virtual Hosts de Apache:
   - Ruta habitual: `C:\xampp\apache\conf\extra\httpd-vhosts.conf`
2. Agrega al final del archivo el siguiente bloque (ajusta la ruta según donde tengas tu proyecto):

   ```apache
   <VirtualHost *:80>
       DocumentRoot "C:/xampp/htdocs/ProyectoFinal"
       ServerName proyecto.test
       <Directory "C:/xampp/htdocs/ProyectoFinal">
           Options Indexes FollowSymLinks Includes ExecCGI
           AllowOverride All
           Require all granted
       </Directory>
   </VirtualHost>
   ```

3. Abre el archivo `hosts` de Windows como **Administrador**:
   - Ruta: `C:\Windows\System32\drivers\etc\hosts`
   - Agrega al final la línea:
     ```text
     127.0.0.1    proyecto.test
     ```

4. En el **XAMPP Control Panel**, inicia (o reinicia) el módulo **Apache**.

---

### Paso 6: ¡Listo! Acceder a la Aplicación
Abre tu navegador e ingresa a:
👉 **`http://proyecto.test`** o **`http://proyecto.test/login`**

---

## ⚡ Método 2: Despliegue Rápido sin VirtualHost (Servidor de Desarrollo PHP)

Si no deseas configurar VirtualHosts en Apache, puedes correrlo inmediatamente con el servidor integrado de PHP:

1. Asegúrate de haber completado los **Pasos 2, 3 y 4** (composer, `.env` y base de datos).
2. Asegúrate de que MySQL esté activo en XAMPP.
3. En la terminal dentro de la carpeta del proyecto, ejecuta:
   ```bash
   php -S 127.0.0.1:8000
   ```
4. Abre tu navegador en:
   👉 **`http://127.0.0.1:8000/login`**

---

## 🔑 Credenciales de Acceso

### 👤 Cuenta Administrador (Acceso a Dashboard de Estadísticas y Moderación)
- **Correo:** `admin@gmail.com`
- **Contraseña:** `Admin123?`

### 🎓 Cuentas de Prueba (Alumnos y Docentes con Feed, Amigos y Posts)
- **Contraseña universal para todas las cuentas seed:** `User1234`
- **Ejemplos de correos:**
  - `maria.rivera@alu.uabcs.mx` (Estudiante)
  - `ana.flores@alu.uabcs.mx` (Estudiante)
  - `laura.mendoza@alu.uabcs.mx` (Estudiante)
  - `carlos.vargas@uabcs.mx` (Docente)
  - `jose.gonzalez@uabcs.mx` (Docente)

---

## 🛠️ Preguntas Frecuentes y Solución de Problemas

- **¿Error 404 al navegar entre páginas?**
  Verifica que Apache tenga habilitado el módulo `mod_rewrite` (por defecto viene habilitado en XAMPP) y que `AllowOverride All` esté configurado en el VirtualHost para permitir el archivo `.htaccess`.
- **¿No puedo subir imágenes de perfil o publicaciones?**
  Verifica que las carpetas `assets/imagesProfile` y `assets/imagesPosts` existan y tengan permisos de escritura.
- **¿Error de conexión a la base de datos?**
  Revisa que el archivo `.env` coincida exactamente con el host, puerto y contraseña de tu servidor MySQL.
