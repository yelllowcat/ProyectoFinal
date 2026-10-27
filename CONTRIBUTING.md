# Guía de Contribución (`CONTRIBUTING.md`) — UNIRED

¡Gracias por contribuir a **UNIRED**! Este documento establece el flujo de trabajo, los estándares de programación y las verificaciones automáticas que todo desarrollador debe seguir al proponer cambios en el repositorio.

Para consultar la referencia completa de arquitectura, límites de complejidad y seguridad, revisa [`docs/estandares-de-codigo.md`](docs/estandares-de-codigo.md).

---

## 1. Requisitos del Entorno de Desarrollo

- **PHP:** `>= 7.4` (recomendado PHP 8.2+ incluido en XAMPP).
- **MySQL / MariaDB:** `>= 5.7` / `10.4+`.
- **Composer:** `2.x` (si la extensión `zip` no está activa en `php.ini`, puedes invocar `php -d extension=zip` al instalar paquetes).
- **Node.js / npx (opcional):** Para ejecutar ESLint y Prettier sobre `js/` y `assets/styles/`.

Instalación de dependencias (incluyendo herramientas de calidad y pruebas):

```bash
composer install
```

---

## 2. Comandos de Calidad, Formato, Análisis y Pruebas

Antes de enviar un commit o abrir un Pull Request, ejecuta los siguientes scripts definidos en `composer.json`:

| Comando | Herramienta | Propósito |
|---|---|---|
| `composer format` | `phpcbf` (`phpcs.xml`) | Corrige automáticamente el formato PHP según `PSR-12`. |
| `composer lint` | `phpcs` (`phpcs.xml`) | Verifica estilo `PSR-12`, longitud de línea, complejidad ciclomática y anidamiento. |
| `composer analyze` | `phpstan` (`phpstan.neon`) | Ejecuta análisis estático de tipos, llamadas y variables en PHP. |
| `composer test` | `phpunit` (`phpunit.xml`) | Ejecuta la suite de pruebas automatizadas en `tests/`. |
| `composer check` | `phpcs` + `phpstan` + `phpunit` | Ejecuta todas las comprobaciones de calidad en secuencia. |

Para archivos Frontend (`js/` y `assets/styles/`), si cuentas con Node.js:

```bash
npx eslint js/
npx prettier --check "js/**/*.js" "assets/styles/**/*.css"
```

---

## 3. Estándares Esenciales de Código

### Límites de Calidad y Complejidad
- **Líneas por archivo:** 100–200 objetivo (revisar si supera 300).
- **Líneas por función/método:** 5–20 objetivo (revisar si supera 30; evaluar división si supera 50).
- **Longitud de línea:** 80–100 caracteres (umbral máximo de revisión: 120 caracteres).
- **Parámetros por función:** 1–3 (si requiere 4 o más, evalúa agruparlos en un arreglo asociativo u objeto de opciones).
- **Niveles de anidamiento:** 1–2 (máximo 3; utiliza cláusulas de guarda con `return` temprano).
- **Complejidad ciclomática:** Preferiblemente hasta 10.

### Convenciones de Nombres
- **PHP (`PSR-12`):**
  - Clases y Componentes: `PascalCase` (`PostController`, `HashtagModel`, `FriendCard`).
  - Métodos, funciones de dominio y variables: `camelCase` (`getPostsByHashtag`, `$commentCount`).
  - Constantes: `UPPER_SNAKE_CASE` (`DB_HOST`).
  - Booleanos: Prefijos que expresen condición (`isLoggedIn`, `hasLiked`, `canEdit`).
- **JavaScript:**
  - Funciones y variables: `camelCase` (`handleFriendAction`, `currentFilter`).
  - Usa siempre `const` por defecto y `let` solo cuando exista reasignación; nunca uses `var`.
  - Usa comparaciones estrictas (`===` y `!==`).

### Arquitectura MVC y Seguridad
1. **Model (`app/Models/`):** Solo acceso a datos mediante `PDO` y consultas preparadas (`prepare` + `execute`). Nunca uses `$_SESSION`, `echo` ni `header()` en un modelo.
2. **Controller (`app/Controllers/`):** Valida entradas (`clean_input`), verifica autenticación/autorización (`requireAuth`, `requireAdmin`, `requireUser`, `isOwner`, `canEdit`), coordina modelos y retorna vistas o respuestas JSON (`jsonSuccess`, `jsonError`).
3. **View (`views/`) y Components (`app/Components/`):** Solo presentación. Escapa toda salida dinámica con `safe_output()` o `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`.
4. **Secretos:** Jamás subas el archivo `.env`, credenciales ni tokens al repositorio.

---

## 4. Flujo de Trabajo con Git y Control de Versiones

1. **Commits pequeños y atómicos:** Cada commit debe representar un cambio lógico coherente. No mezcles refactorizaciones masivas con nuevas funcionalidades.
2. **Convención de mensajes de commit (Conventional Commits):**
   - `feat: agregar filtro por carrera en búsqueda de usuarios`
   - `fix: validar propietario antes de eliminar respuesta en PostController`
   - `refactor: extraer validación de imagen en UserController`
   - `test: añadir pruebas unitarias para HashtagModel::extractHashtags`
   - `docs: actualizar guía de despliegue y estándares`
   - `chore: configurar phpcs, phpstan y phpunit`
3. **Archivos ignorados (`.gitignore`):** No subas `/vendor/`, `.env`, imágenes subidas por usuarios (`/assets/imagesPosts`, `/assets/imagesProfile`), logs (`*.log`) ni cachés de herramientas (`.phpunit.cache/`, `.phpstan.cache/`).

---

## 5. Lista de Verificación para Pull Requests (Checklist)

Antes de solicitar revisión, confirma cada punto:

- [ ] El código respeta la separación de responsabilidades MVC (`Controllers`, `Models`, `Components`, `views`).
- [ ] Todas las consultas SQL utilizan sentencias preparadas de `PDO` y las salidas HTML están escapadas con `safe_output()`.
- [ ] Se ejecutó `composer format` y `composer lint` sin errores en los archivos modificados.
- [ ] Se ejecutó `composer analyze` (`PHPStan`) sin errores de análisis estático.
- [ ] Se ejecutó `composer test` (`PHPUnit`) y todas las pruebas pasan; se añadieron pruebas para la nueva lógica de negocio.
- [ ] No se incluyeron secretos, `var_dump`, bloques `catch` vacíos ni código comentado muerto.
