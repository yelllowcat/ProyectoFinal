# UNIRED - Red Social Universitaria

Una plataforma de red social moderna diseñada para conectar a estudiantes universitarios, permitiéndoles compartir publicaciones, interactuar con amigos y gestionar sus perfiles.

## 📋 Credenciales de Admin 
- Correo: admin@gmail.com
- Contraseña: Admin123?

## 📋 URL de la Aplicación
- https://darksalmon-jellyfish-884197.hostingersite.com/

## 📋 Características

### Gestión de Usuarios
- ✅ Registro e inicio de sesión seguros
- ✅ Perfiles personalizables con foto de perfil
- ✅ Edición de información personal (nombre completo, biografía, carrera)
- ✅ Sistema de eliminación de cuenta

### Sistema de Publicaciones
- ✅ Crear publicaciones con texto e imágenes
- ✅ Editar y eliminar publicaciones propias
- ✅ Subida de imágenes (máx. 5MB, formatos: JPEG, PNG, GIF)
- ✅ Visualización de imágenes en modal de pantalla completa
- ✅ Sistema de "Me gusta" en publicaciones

### Sistema de Comentarios
- ✅ Agregar comentarios a publicaciones
- ✅ Eliminar comentarios propios
- ✅ Visualización paginada de comentarios (carga de 3 en 3)
- ✅ Enlaces a perfiles en comentarios
- ✅ Menú de opciones solo para propietarios de comentarios

### Sistema de Amigos
- ✅ Enviar solicitudes de amistad
- ✅ Aceptar/rechazar solicitudes
- ✅ Ver lista de amigos
- ✅ Eliminar amigos
- ✅ Búsqueda de usuarios
- ✅ Perfiles públicos

### Interfaz de Usuario
- ✅ Diseño responsive (desktop y móvil)
- ✅ Sidebar colapsable
- ✅ Modales de confirmación para acciones críticas
- ✅ Animaciones y transiciones suaves
- ✅ Tipografía Roboto de Google Fonts
- ✅ Tema moderno con colores vibrantes

## 🛠️ Tecnologías Utilizadas

### Backend
- **PHP 7.4+** - Lenguaje de programación del servidor
- **MySQL** - Base de datos relacional
- **PDO** - Capa de abstracción de base de datos
- **Composer** - Gestor de dependencias
- **vlucas/phpdotenv** - Gestión de variables de entorno
- **TCPDF** - Generación de PDFs

### Frontend
- **HTML5** - Estructura
- **CSS3** - Estilos y diseño
- **JavaScript (Vanilla)** - Interactividad
- **Google Fonts (Roboto)** - Tipografía

### Arquitectura
- **MVC** - Patrón de diseño Modelo-Vista-Controlador
- **PSR-4** - Autoloading de clases
- **RESTful API** - Para operaciones AJAX

## 📁 Estructura del Proyecto

```
ProyectoFinal/
├── app/
│   ├── Components/      # Componentes reutilizables (Post, etc.)
│   ├── Controllers/     # Controladores (Auth, Post, Friend, Profile, User)
│   └── Models/          # Modelos de datos (User, Post, Comment, Like, Friend)
├── assets/
│   ├── images/          # Imágenes del sistema
│   ├── imagesProfile/   # Fotos de perfil de usuarios
│   ├── imagesPosts/     # Imágenes de publicaciones
│   └── styles/          # Archivos CSS
├── config/              # Configuración de la aplicación
├── helpers/             # Funciones auxiliares
├── js/                  # Scripts JavaScript
│   ├── main.js          # Lógica principal de la aplicación
│   └── friends.js       # Lógica del sistema de amigos
├── routes/              # Definición de rutas
├── views/               # Vistas PHP
├── .htaccess            # Configuración de Apache
├── composer.json        # Dependencias PHP
└── index.php            # Punto de entrada

```

## 🚀 Instalación y Configuración

### Requisitos Previos
- PHP 7.4 o superior
- MySQL 5.7 o superior
- Apache con mod_rewrite habilitado
- Composer

### Pasos de Instalación

1. **Clonar el repositorio**
   ```bash
   git clone <repository-url>
   cd ProyectoFinal
   ```

2. **Instalar dependencias**
   ```bash
   composer install
   ```

3. **Configurar variables de entorno**
   - Crear un archivo `.env` en la raíz del proyecto
   - Configurar las credenciales de la base de datos:
   ```env
   DB_HOST=localhost
   DB_NAME=nombre_base_datos
   DB_USER=usuario
   DB_PASS=contraseña
   ```

4. **Importar la base de datos**
   - Importar el esquema de la base de datos (incluye stored procedures)
   - Asegurarse de que existan los procedimientos almacenados necesarios

5. **Configurar Apache**
   - Apuntar el DocumentRoot a la carpeta del proyecto
   - Asegurarse de que mod_rewrite esté habilitado
   - El archivo `.htaccess` ya está configurado

6. **Configurar permisos**
   ```bash
   chmod -R 755 assets/imagesProfile
   chmod -R 755 assets/imagesPosts
   ```

7. **Acceder a la aplicación**
   - Abrir el navegador y navegar a tu dominio/localhost configurado

## 🔐 Seguridad

- ✅ Autenticación basada en sesiones
- ✅ Sanitización de entradas de usuario
- ✅ Escape de salida HTML para prevenir XSS
- ✅ Preparación de consultas SQL para prevenir inyección SQL
- ✅ Validación de tipos de archivo en subidas
- ✅ Límites de tamaño de archivo
- ✅ Verificación de ownership para operaciones de eliminación

## 📱 Características Responsive

La aplicación está optimizada para diferentes tamaños de pantalla:
- **Desktop**: Sidebar fija con diseño de 2 columnas
- **Tablet**: Sidebar colapsable
- **Móvil**: Sidebar tipo drawer con overlay

## 🎨 Tema y Diseño

- **Paleta de colores**: Azul turquesa (#4db8c4) como color principal
- **Tipografía**: Roboto para una apariencia moderna y legible
- **UI/UX**: Diseño minimalista con enfoque en usabilidad
- **Iconografía**: Iconos personalizados para acciones comunes

## 🗄️ Base de Datos

### Tablas Principales
- `users` - Información de usuarios
- `posts` - Publicaciones
- `comments` - Comentarios en publicaciones
- `likes` - Me gusta en publicaciones
- `friends` - Relaciones de amistad
- `friend_requests` - Solicitudes de amistad pendientes

### Stored Procedures
El proyecto utiliza stored procedures para operaciones complejas:
- `sp_get_comments_by_post` - Obtener comentarios de una publicación
- Y otros procedimientos para gestión de amigos, posts, etc.

## 🔄 Flujo de Trabajo

### Crear una Publicación
1. Usuario hace clic en "Agregar Post"
2. Completa el formulario (texto + imagen opcional)
3. Sistema valida y guarda en base de datos
4. Publicación aparece en el feed

### Sistema de Comentarios
1. Usuario hace clic en "Comentarios" en una publicación
2. Se cargan los comentarios (primeros 3)
3. Usuario puede agregar nuevo comentario
4. Solo el autor puede eliminar su comentario

### Sistema de Amigos
1. Usuario busca otros usuarios
2. Envía solicitud de amistad
3. El receptor acepta/rechaza
4. Una vez aceptada, ambos son amigos

## 👥 Contribuidores

Proyecto Final - Red Social Universitaria

## 📝 Licencia

Este proyecto es de código cerrado y está destinado únicamente para fines educativos.

## 📞 Soporte

Para reportar problemas o sugerencias, por favor contacta al equipo de desarrollo.

---

**Versión**: 1.0.0  
**Última actualización**: Diciembre 2025