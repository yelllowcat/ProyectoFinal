# Estándares de Programación y Calidad de Código — UNIRED

Este documento constituye la especificación técnica completa de las reglas de programación, arquitectura, seguridad, pruebas y mantenimiento para **UNIRED** (`ProyectoFinal`). Aplica tanto a desarrolladores humanos como a agentes de programación automatizados.

---

## 1. Resumen del Entorno Detectado

| Aspecto | Tecnología / Herramienta en el Proyecto |
|---|---|
| **Lenguaje Principal (Backend)** | PHP `>= 7.4` (compatible con PHP 8.2+), tipado progresivo y autoloading `PSR-4` (`App\` -> `app/`) |
| **Base de Datos y Persistencia** | MySQL / MariaDB mediante `PDO` (`config/database.php`), procedimientos almacenados (`sp_*`), vistas (`v_*`) y consultas preparadas |
| **Frontend** | HTML5 en plantillas PHP (`views/`), CSS3 (`assets/styles/`), JavaScript Vanilla ES2022 (`js/`) |
| **Arquitectura** | MVC ligero con enrutador propio (`App\Router`), Controladores (`app/Controllers/`), Modelos (`app/Models/`), Componentes (`app/Components/`), Vistas (`views/`) y Helpers (`helpers/`) |
| **Gestor de Dependencias** | Composer (`composer.json`, `composer.lock`) |
| **Linter y Formateador PHP** | `squizlabs/php_codesniffer` (`phpcs` y `phpcbf`) configurado en [`phpcs.xml`](../phpcs.xml) |
| **Analizador Estático PHP** | `phpstan/phpstan` configurado en [`phpstan.neon`](../phpstan.neon) |
| **Framework de Pruebas** | `phpunit/phpunit` configurado en [`phpunit.xml`](../phpunit.xml) con pruebas en [`tests/`](../tests/) |
| **Linter y Formateador Frontend** | ESLint ([`eslint.config.mjs`](../eslint.config.mjs)), Prettier ([`.prettierrc.json`](../.prettierrc.json)) y EditorConfig ([`.editorconfig`](../.editorconfig)) |

---

## 2. Límites de Tamaño y Complejidad

Los siguientes valores rigen la calidad estructural del código nuevo y de cualquier función o archivo que sea modificado:

| Elemento | Objetivo | Umbral de revisión | Herramienta / Verificación |
|---|---:|---:|---|
| **Líneas por archivo** | 100–200 | Más de 300 | Automático en JS (`max-lines` en ESLint) / Revisión en PHP |
| **Líneas por función o método** | 5–20 | Más de 30 (evaluar división seria si > 50) | Automático en JS (`max-lines-per-function`) / Revisión en PHP |
| **Longitud de línea** | 80–100 caracteres | Más de 120 | Automático en PHP (`Generic.Files.LineLength` en `phpcs.xml`) y JS/CSS (`Prettier` / `ESLint`) |
| **Parámetros por función** | 1–3 | Más de 4 | Automático en JS (`max-params`) / Revisión en PHP |
| **Niveles de anidamiento** | 1–2 | Más de 3 | Automático en PHP (`Generic.Metrics.NestingLevel`) y JS (`max-depth`) |
| **Complejidad ciclomática** | Hasta 10 | Más de 10 (Error en > 15 en PHP) | Automático en PHP (`Generic.Metrics.CyclomaticComplexity`) y JS (`complexity`) |
| **Variables locales por función** | Las mínimas necesarias | Más de 7–10 | Revisión manual en Pull Request |
| **Profundidad de carpetas** | 3–5 niveles | Más de 5 niveles | Revisión manual de estructura |

### Reglas de Aplicación de Límites
1. **Diferenciación entre error y advertencia:** En `phpcs.xml`, las violaciones de formato `PSR-12`, complejidad ciclomática extrema (`> 15`), anidamiento profundo (`> 5`), sentencias vacías y funciones prohibidas (`eval`, `var_dump`, `dd`) son **errores obligatorios**. El exceso moderado de longitud de línea (`> 100` / `> 120`), complejidad (`> 10`) o anidamiento (`> 3`) emite **advertencias (`WARNING`)** que exigen revisión sin romper la compilación de consultas SQL extensas.
2. **Prohibición de fragmentación artificial:** No dividas una clase o archivo en módulos artificiales que solo reenvíen llamadas únicamente para reducir el conteo de líneas.
3. **Excepciones justificadas:** Quedan exentos de los límites de longitud de archivo:
   - Dependencias y librerías externas (`vendor/`, `js/chart.min.js`).
   - Esquemas, semillas y migraciones de base de datos (`database/unired_db.sql`, `database/seed.php`, `database/migrations/`).
   - Scripts de generación de documentación académica (`docs/generate_documentation.py`, `docs/generate_docx.py`).

---

## 3. Responsabilidad Única, Modularidad y Principios de Diseño

Aplicamos los principios **SRP**, **DRY**, **KISS** y **YAGNI** de forma pragmática:

- **Responsabilidad Única (SRP):** Cada clase, método y script debe tener un único motivo de cambio.
  - Si un método supera **30 líneas**, identifica subtareas (como validación de entrada, subida de imagen o formateo de respuesta) y extráelas en métodos privados con nombres descriptivos (ej. `PostController::handleImageUpload`).
  - Si un método supera **50 líneas**, su división debe evaluarse obligatoriamente antes de añadirle más lógica.
- **No mezclar capas:** Está prohibido combinar en un mismo método consultas SQL (`PDO`), manipulación de cabeceras/sesión HTTP y construcción de marcado HTML.
- **Evitar duplicación (DRY):** Reutiliza los helpers de [`helpers/auth.php`](../helpers/auth.php) (`requireAuth`, `jsonSuccess`, `jsonError`, `clean_input`, `safe_output`, `getProfilePicture`) y [`helpers/files.php`](../helpers/files.php) (`asset`).
- **Simplicidad (KISS y YAGNI):** No introduzcas capas abstractas, interfaces sin múltiples implementaciones ni patrones complejos cuando un método claro en el Modelo o Controlador resuelve el caso de uso.
- **Sin dependencias circulares:** Los Modelos nunca deben importar ni invocar Controladores, Componentes o Vistas.

---

## 4. Arquitectura MVC y Separación de Responsabilidades

UNIRED implementa una arquitectura **MVC** apoyada por Componentes de vista y Helpers:

### 4.1 Model (`app/Models/`)
- **Propósito:** Representar entidades del dominio (`UserModel`, `PostModel`, `CommentModel`, `FriendModel`, `HashtagModel`, `ReportModel`, `SearchModel`, `AdminModel`) y encapsular toda interacción con MySQL mediante `PDO`.
- **Reglas:**
  - Usar siempre consultas preparadas o procedimientos almacenados (`CALL sp_...`).
  - Incluir métodos puros del dominio cuando correspondan al modelo (por ejemplo, `HashtagModel::extractHashtags`).
  - No acceder a `$_POST`, `$_GET`, `$_FILES`, `$_SESSION`, ni emitir salidas (`echo`, `jsonResponse`, `redirect`).

### 4.2 View (`views/`) y Components (`app/Components/`)
- **Propósito:** Renderizar la interfaz gráfica y capturar eventos del usuario.
- **Reglas:**
  - No ejecutar consultas `PDO` directamente en las vistas.
  - Delegar fragmentos repetitivos a `app/Components/` (`Alert`, `Post`, `FriendCard`, `Profile`) o parciales en `views/assets/` (`sidebar.php`, `search_header.php`).
  - Escapar toda variable dinámica con `safe_output()`.

### 4.3 Controller (`app/Controllers/`)
- **Propósito:** Recibir las solicitudes despachadas por `App\Router`, verificar permisos, validar datos de entrada, invocar a los Modelos y devolver la respuesta HTTP o JSON.
- **Reglas:**
  - Verificar siempre autenticación/autorización al inicio de la acción (`requireAuth()`, `requireAdmin()`, `requireUser()`).
  - No escribir SQL dentro del controlador.
  - Mantener acciones concisas delegando operaciones compuestas a métodos auxiliares o modelos.

### 4.4 Helpers (`helpers/`) y Rutas (`routes/web.php`)
- `routes/web.php` registra las rutas en `App\Router`. Para lógica nueva, prefiere apuntar a `[NombreController::class, 'metodo']` en lugar de añadir clausuras anónimas extensas en el archivo de rutas.

---

## 5. Organización de Carpetas

```text
ProyectoFinal/
├── app/
│   ├── Components/      # Componentes UI reutilizables en PHP
│   ├── Controllers/     # Controladores HTTP / API JSON
│   ├── Models/          # Acceso a datos PDO y lógica de dominio
│   └── Router.php       # Enrutador principal HTTP
├── assets/
│   ├── images/          # Iconos y recursos gráficos estáticos del sistema
│   ├── imagesPosts/     # Imágenes subidas en publicaciones (ignorado en Git)
│   ├── imagesProfile/   # Avatares subidos por usuarios (ignorado en Git)
│   └── styles/          # Hojas de estilo CSS3 por módulo
├── config/
│   └── database.php     # Inicialización de Dotenv y conexión PDO
├── database/
│   ├── migrations/      # Scripts SQL incrementales e índices
│   ├── create_admin.php # Script CLI para crear usuario administrador
│   ├── seed.php         # Poblado inicial de datos de prueba
│   └── unired_db.sql    # Esquema base, vistas y procedimientos almacenados
├── docs/
│   └── estandares-de-codigo.md # Este documento
├── helpers/
│   ├── auth.php         # Sesión, autorización, respuestas JSON, sanitización
│   └── files.php        # Versionado de assets estáticos (cache busting)
├── js/                  # Scripts JavaScript del cliente
├── routes/
│   └── web.php          # Definición de rutas web y endpoints AJAX
├── tests/               # Pruebas unitarias automatizadas (PHPUnit)
│   ├── Unit/
│   └── bootstrap.php
├── views/               # Plantillas PHP/HTML5
└── index.php            # Punto de entrada único (Front Controller)
```

---

## 6. Nombres y Convenciones

- **Claridad e intención:** Prohibido usar nombres genéricos como `$data`, `$temp`, `$info`, `$aux` o `funcion1` cuando el concepto de dominio sea conocido (prefiere `$postId`, `$pendingRequests`, `$hashtagNames`).
- **Consistencia por capa:**
  - En código nuevo PHP y JS, utiliza `camelCase` para funciones/métodos y variables (`getPostsByHashtagPaginated`, `handleFriendAction`), `PascalCase` para clases (`PostController`, `HashtagModel`) y `UPPER_SNAKE_CASE` para constantes (`DB_HOST`, `MAX_UPLOAD_SIZE`).
  - No renombres arbitrariamente funciones globales existentes de la API interna (`clean_input`, `safe_output`) que ya son consumidas en todo el proyecto.
- **Booleanos descriptivos:** Usa prefijos `is`, `has`, `can` o `should` (`isLoggedIn()`, `hasLiked()`, `canEdit()`, `hasFlash()`).
- **Colecciones en plural:** Usa nombres plurales para arreglos y listados (`$comments`, `$hashtags`, `$friendCards`).
- **Prohibición de emojis:** Queda estrictamente prohibido el uso de emojis en cualquier parte del código fuente (PHP, JavaScript, CSS, SQL), comentarios de código, mensajes de confirmación de control de versiones (commits) y documentación técnica. Todo el proyecto debe mantener un estilo sobrio, formal y profesional.

---

## 7. Funciones, Parámetros, Variables y Condicionales

1. **Parámetros:** Limita las funciones a un máximo de **3 parámetros**. Cuando una función requiera 4 o más valores relacionados, agrupa los parámetros opcionales o de configuración en un arreglo asociativo documentado o un objeto de transferencia de datos (DTO).
2. **Tipado explícito en código nuevo PHP:** Declara tipos de parámetros y tipos de retorno (`int`, `string`, `bool`, `array`, `void`, `?array`) como ya se aplica en `HashtagModel` y los métodos paginados de `PostModel`.
3. **Inmutabilidad y alcance en JS:** Usa `const` por defecto, `let` cuando se requiera reasignación y nunca `var`. Evita añadir variables globales innecesarias al ámbito `window`.
4. **Constantes en lugar de valores mágicos:** Define constantes con nombre para límites repetidos (por ejemplo, tamaño máximo de imagen `5 * 1024 * 1024`, tamaños de página por defecto `20`, roles permitidos).
5. **Cláusulas de guarda:** Procesa las validaciones y casos de error al inicio de la función y retorna temprano para mantener el flujo principal con **1 a 2 niveles de anidamiento** (máximo 3).
6. **Comparaciones estrictas:** Usa `===` y `!==` tanto en PHP como en JavaScript. Al comparar IDs provenientes de `$_SESSION` o base de datos, convierte explícitamente a `(int)` para poder usar comparación estricta (`(int) $post['user_id'] === (int) $userId`).

---

## 8. Manejo de Errores y Seguridad

### Manejo de Errores
- **Prohibidos los bloques `catch` vacíos:** Todo bloque `catch (PDOException $e)` o `catch (Exception $e)` debe registrar el contexto técnico mediante `error_log("Clase::metodo error: " . $e->getMessage())`.
- **Transacciones seguras:** Cuando una operación modifique múltiples registros relacionados (como `HashtagModel::linkPostHashtags`), envuélvela en `$this->pdo->beginTransaction()`, `$this->pdo->commit()` y ejecuta `$this->pdo->rollBack()` dentro del `catch`.
- **Sin exposición de trazas en respuestas de usuario:** Devuelve mensajes amigables al cliente (`jsonError(...)` o `flash('error', ...)`). Nunca expongas consultas SQL ni rutas internas del servidor al navegador en producción.

### Seguridad
1. **Prevención de Inyección SQL:** Todas las consultas deben usar `PDO::prepare()` con placeholders (`?` o `:nombre`) y `PDO::ATTR_EMULATE_PREPARES => false` (configurado en `config/database.php`).
2. **Prevención de XSS:**
   - Entrada: Limpiar con `clean_input()` (`strip_tags(trim($data))`).
   - Salida: Escapar siempre con `safe_output()` (`htmlspecialchars($data, ENT_QUOTES, 'UTF-8')`).
3. **Contraseñas:** Almacenar únicamente hashes generados con `password_hash($password, PASSWORD_BCRYPT)` y verificar con `password_verify()`.
4. **Control de Acceso y Propiedad (Ownership):**
   - Nunca confíes en que ocultar un botón en la vista protege la acción.
   - Verifica siempre en el Controlador o Modelo que el usuario autenticado sea propietario del recurso (`isOwner($resourceUserId)` o `canEdit($resourceUserId)`) antes de editar o eliminar publicaciones, comentarios, respuestas o perfiles.
5. **Subida de Archivos:**
   - Validar tipo permitido (`image/jpeg`, `image/png`, `image/gif`), tamaño máximo (`<= 5 MB`) y generar un nombre único seguro (`uniqid()`) antes de invocar `move_uploaded_file()`.
6. **Gestión de Secretos:** Todas las credenciales residen exclusivamente en `.env` (excluido en `.gitignore`).

---

## 9. Matriz de Verificación: Automatizada vs. Revisión Manual

No todas las reglas de diseño pueden medirse con un linter estático. Esta tabla especifica qué reglas están automatizadas y cómo deben verificarse manualmente las restantes:

| Regla / Estándar | Estado | Herramienta o Procedimiento de Revisión Manual |
|---|---|---|
| Formato e indentación PHP (`PSR-12`) | **Automatizado** | `composer lint` (`phpcs`) y `composer format` (`phpcbf`) |
| Longitud de línea (`<= 100` obj / `<= 120` máx) | **Automatizado** | `phpcs.xml` (`Generic.Files.LineLength`), `.prettierrc.json` y `eslint.config.mjs` |
| Complejidad ciclomática (`<= 10`) | **Automatizado** | `phpcs.xml` (`Generic.Metrics.CyclomaticComplexity`) y ESLint (`complexity`) |
| Niveles de anidamiento (`<= 3`) | **Automatizado** | `phpcs.xml` (`Generic.Metrics.NestingLevel`) y ESLint (`max-depth`) |
| Bloques `catch` o sentencias vacías | **Automatizado** | `phpcs.xml` (`Generic.CodeAnalysis.EmptyStatement`) y ESLint (`no-empty`) |
| Uso de funciones prohibidas (`eval`, `var_dump`, `dd`) | **Automatizado** | `phpcs.xml` (`Generic.PHP.ForbiddenFunctions`) y ESLint (`no-eval`) |
| Análisis estático de tipos, clases y funciones PHP | **Automatizado** | `composer analyze` (`PHPStan` nivel 2 con `phpstan.neon`) |
| Pruebas unitarias de lógica de negocio y helpers | **Automatizado** | `composer test` (`PHPUnit` con `phpunit.xml`) |
| Líneas por archivo y función en JS (`<= 300` / `<= 30`) | **Automatizado** | ESLint (`max-lines`, `max-lines-per-function`, `max-params`) |
| Líneas por archivo y método en PHP (`<= 300` / `<= 30`) | **Revisión Manual** | Revisar el `git diff` antes de confirmar cambios; si un método modificado supera 30 líneas, extraer subtareas en métodos privados. |
| Separación de responsabilidades MVC y ausencia de SQL en Controladores/Vistas | **Revisión Manual** | Verificar en el diff que ninguna clase en `app/Controllers/` o plantilla en `views/` invoque `getDB()` ni escriba sentencias SQL. |
| Uso de consultas preparadas y escape XSS (`safe_output`) | **Revisión Manual** | Auditar que todo `prepare()` use `?` o `:param` y que toda variable impresa en HTML pase por `safe_output()` o `htmlspecialchars()`. |
| Verificación de permisos y propiedad (`requireAuth`, `isOwner`) | **Revisión Manual** | Confirmar que cada ruta o acción de escritura/borrado compruebe sesión y titularidad del recurso en el servidor. |

---

## 10. Registro de Excepciones Documentadas en el Código Existente

De acuerdo con la política de refactorización incremental (Sección 3.8 y 19), no se reescriben masivamente archivos legados funcionales sin necesidad. Se registran las siguientes excepciones actuales para que sean reducidas gradualmente cuando dichos módulos se modifiquen:

1. **`app/Models/AdminModel.php` (~670 líneas) y `app/Controllers/PostController.php` (~428 líneas):** Superan el umbral de 300 líneas por archivo debido a que concentran múltiples consultas de métricas del dashboard y acciones de comentarios/respuestas/likes respectivamente. **Regla al modificar:** No agregar nuevos métodos extensos; si se amplía la funcionalidad de comentarios o reportes, extraer la lógica nueva a métodos auxiliares pequeños o controladores/modelos específicos.
2. **`js/main.js` (~1,500 líneas) y `js/dashboard.js` (~800 líneas):** Concentran la interactividad del feed y las gráficas de administración. **Regla al modificar:** Cualquier función nueva o editada debe cumplir `<= 30` líneas, usar `const`/`let` y mantener complejidad `<= 10`.
3. **Instanciación de modelos de lectura en vistas existentes (`views/posts.php`, `views/profile.php`, `views/friends.php`, `views/search.php`):** Algunas vistas existentes cargan datos iniciales directamente desde los modelos. **Regla al modificar:** No introducir consultas `PDO` en vistas; cualquier nueva operación de negocio o mutación debe pasar obligatoriamente por un Controlador.
