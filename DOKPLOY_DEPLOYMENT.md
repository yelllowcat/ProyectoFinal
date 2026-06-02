# Guía de Despliegue en Dokploy

Esta guía explica cómo desplegar la aplicación **UNIRED** en tu instancia de **Dokploy**.

---

## Paso 1: Configurar la Base de Datos MySQL en Dokploy

1. En la consola de **Dokploy**, ve a tu **Proyecto** (o crea uno nuevo).
2. Haz clic en **Crear Servicio** y selecciona **Database** (Base de datos).
3. Selecciona **MySQL** e ingresa la configuración deseada:
   - **Nombre de la BD**: `unired_db` (o el nombre de tu preferencia).
   - **Usuario**: `unired_user` (o el de tu preferencia).
   - **Contraseña**: Ingresa una contraseña segura.
4. Una vez creada la base de datos:
   - Toma nota del **Host** (normalmente el nombre del servicio generado por Dokploy, ej. `mysql-xxxx`).
   - Toma nota del **Puerto** (normalmente `3306`).
   - Toma nota de las credenciales de conexión.

---

## Paso 2: Crear y Configurar la Aplicación en Dokploy

1. En tu Proyecto, haz clic en **Crear Servicio** y selecciona **Application** (Aplicación).
2. Configura los detalles básicos de la aplicación (nombre, puerto expuesto al exterior, etc.).
3. En **Provider** (Proveedor), vincula tu repositorio Git de esta aplicación.
4. En **Build Configuration** (Configuración de Construcción):
   - Cambia el tipo de construcción a **Dockerfile**.
   - Deja la ruta del Dockerfile por defecto (`/Dockerfile` o `./Dockerfile`).

---

## Paso 3: Configurar las Variables de Entorno (Environment Variables)

En la pestaña **Environment** (o Variables de Entorno) de la aplicación en Dokploy, añade las siguientes variables:

| Variable | Valor | Descripción |
|----------|-------|-------------|
| `DB_HOST` | *(Host de tu servicio MySQL en Dokploy)* | Ej. `mysql-xxxx` |
| `DB_NAME` | *(Nombre de la base de datos)* | Ej. `unired_db` |
| `DB_USER` | *(Usuario de la base de datos)* | Ej. `unired_user` |
| `DB_PASS` | *(Contraseña de la base de datos)* | La contraseña configurada en el Paso 1 |
| `SEED_DB` | `true` | **(Opcional)** Ponlo en `true` la primera vez para sembrar datos de prueba automáticamente. |

---

## Paso 4: Configurar los Volúmenes Persistentes (Crucial para Imágenes)

Como los contenedores Docker son efímeros, las imágenes subidas por los usuarios se perderían en cada despliegue si no se configuran volúmenes persistentes.

En la pestaña **Volumes** (Volúmenes) de tu Aplicación en Dokploy, crea los siguientes volúmenes:

1. **Volumen para Fotos de Perfil**:
   - **Path en el Contenedor**: `/var/www/html/assets/imagesProfile`
   - **Nombre del volumen**: `unired-profiles`

2. **Volumen para Imágenes de Publicaciones**:
   - **Path en el Contenedor**: `/var/www/html/assets/imagesPosts`
   - **Nombre del volumen**: `unired-posts`

---

## Paso 5: Desplegar

1. En Dokploy, haz clic en **Deploy** (Desplegar).
2. Dokploy construirá la imagen usando el `Dockerfile`, instalará las dependencias de Composer, y ejecutará el script `docker-entrypoint.sh`.
3. El entrypoint esperará a que MySQL esté en línea, inicializará la estructura de tablas (`unired_db.sql`), aplicará los índices de optimización, sembrará los datos (si `SEED_DB=true`), y finalmente iniciará Apache.
4. Una vez completado, ¡podrás acceder a UNIRED en el dominio asignado por Dokploy!
