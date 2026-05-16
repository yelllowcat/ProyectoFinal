<?php
/**
 * Seed script — generates dummy data for UNIRED dashboard charts (idempotent).
 * Run: php database/seed.php
 */

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config/database.php';

$pdo = getDB();

echo "Seeding UNIRED database…\n";

// ── Helpers ──────────────────────────────────────────────────────────
function randDate(int $daysBack = 0): string {
    $ts = strtotime("-{$daysBack} days") + random_int(-43200, 86400);
    return date('Y-m-d H:i:s', max($ts, strtotime('-30 days')));
}

function pick(array $arr) {
    return $arr[array_rand($arr)];
}

function seedId(): int {
    return random_int(10000, 99999) . substr((string)time(), -5);
}

// ── 1. Users ─────────────────────────────────────────────────────────
echo "  Cleaning old seed users (role = 'user')…\n";
$pdo->exec("DELETE FROM reply_likes WHERE user_id IN (SELECT user_id FROM users WHERE role = 'user')");
$pdo->exec("DELETE FROM comment_likes WHERE user_id IN (SELECT user_id FROM users WHERE role = 'user')");
$pdo->exec("DELETE FROM likes WHERE user_id IN (SELECT user_id FROM users WHERE role = 'user')");
$pdo->exec("DELETE FROM replies WHERE user_id IN (SELECT user_id FROM users WHERE role = 'user')");
$pdo->exec("DELETE FROM comments WHERE user_id IN (SELECT user_id FROM users WHERE role = 'user')");
$pdo->exec("DELETE FROM posts WHERE user_id IN (SELECT user_id FROM users WHERE role = 'user')");
$pdo->exec("DELETE FROM friend_requests WHERE sender_id IN (SELECT user_id FROM users WHERE role = 'user') OR receiver_id IN (SELECT user_id FROM users WHERE role = 'user')");
$pdo->exec("DELETE FROM friends WHERE user_id1 IN (SELECT user_id FROM users WHERE role = 'user') OR user_id2 IN (SELECT user_id FROM users WHERE role = 'user')");
$pdo->exec("DELETE FROM user_update_log WHERE user_id IN (SELECT user_id FROM users WHERE role = 'user')");
$pdo->exec("DELETE FROM users WHERE role = 'user'");

$existingCount = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role != 'admin'")->fetchColumn();
echo "  Existing non-admin users: $existingCount\n";

$needed = 25 - $existingCount;
$userIds = [];
$stmtU = $pdo->query("SELECT user_id FROM users WHERE role != 'admin'");
while ($row = $stmtU->fetch(PDO::FETCH_ASSOC)) {
    $userIds[] = (int)$row['user_id'];
}

if ($needed > 0) {
    $firstNames = ['Carlos','María','José','Ana','Luis','Laura','Pedro','Sofía','Diego','Valentina',
        'Andrés','Camila','Javier','Isabella','Miguel','Lucía','Alejandro','Valeria','Fernando','Gabriela',
        'Ricardo','Daniela','Roberto','Adriana','Santiago','Elena','Rafael','Natalia','Martín','Carmen'];

    $lastNames  = ['García','Rodríguez','Martínez','López','Hernández','González','Pérez','Sánchez',
        'Ramírez','Torres','Flores','Rivera','Ortiz','Morales','Castro','Reyes','Gómez','Jiménez',
        'Vargas','Mendoza','Silva','Romero','Cruz','Medina','Moreno','Álvarez','Herrera','Aguilar'];

    $avatars = ['avatar1.png','avatar2.png','avatar3.png','avatar4.png','avatar5.png',
        'avatar6.png','avatar7.png','avatar8.png','default_avatar.png'];

    $biographies = [
        'Amante de la tecnología y los videojuegos.', 'Viajera empedernida. 15 países y contando.',
        'Chef amateur. La cocina es mi terapia.', 'Fotógrafo de paisajes urbanos.',
        'Estudiante de ingeniería. Fan del café.', 'Mamá de dos. La vida es mejor en familia.',
        'Deportista. Maratones y CrossFit.', 'Músico de fin de semana. Guitarra y piano.',
        'Lector voraz. Un libro por semana.', 'Diseñadora gráfica freelance.',
        'Programador full-stack. Open source.', 'Bailarina profesional. Salsa y bachata.',
        'Cineasta indie. Cortometrajes.', 'Profesora de yoga. Paz interior.',
        'Emprendedor serial. Startups.', 'Enfermera de vocación. Salud mental.',
        'Streamer de juegos retro. Twitch.', 'Escritora de fantasía y ciencia ficción.',
        'Tatuador. Arte en la piel.', 'Bartender. Coctelería molecular.',
        'Ciclista urbano. Movilidad sostenible.', 'Veterinaria. Rescate animal.',
        'Historiador. Divulgación científica.', 'Psicóloga infantil. Desarrollo humano.',
        'Ingeniero de datos. Big Data y ML.'];

    $insU = $pdo->prepare("INSERT INTO users (full_name, email, password, role, biography, profile_picture, registration_date) VALUES (?, ?, ?, ?, ?, ?, ?)");

    for ($i = 0; $i < $needed; $i++) {
        $firstName = $firstNames[$i % count($firstNames)];
        $lastName  = $lastNames[array_rand($lastNames)];
        $fn = $firstName . ' ' . $lastName;
        $username = strtolower($firstName) . '.' . strtolower($lastName);

        $isTeacher = $i % 2 === 0;
        $role    = $isTeacher ? 'teacher' : 'student';
        $domain  = $isTeacher ? '@uabcs.mx' : '@alu.uabcs.mx';
        $email   = $username . $domain;

        $pass = password_hash('user123', PASSWORD_DEFAULT);
        $insU->execute([$fn, $email, $pass, $role, $biographies[$i % count($biographies)], pick($avatars), randDate(random_int(1, 28))]);
        $userIds[] = (int)$pdo->lastInsertId();
    }
    echo "  +$needed new users created. Total: " . count($userIds) . "\n";
}

// ── 2. Posts ─────────────────────────────────────────────────────────
$postCount = (int)$pdo->query("SELECT COUNT(*) FROM posts")->fetchColumn();
$neededPosts = max(0, 55 - $postCount);

$postIds = [];
$stmtP = $pdo->query("SELECT post_id FROM posts");
while ($row = $stmtP->fetch(PDO::FETCH_ASSOC)) {
    $postIds[] = (int)$row['post_id'];
}

if ($neededPosts > 0) {
    $postContents = [
        '¡Hoy es un gran día para empezar nuevos proyectos!',
        'Compartiendo este atardecer increíble desde la playa.',
        '¿Alguien más piensa que el café es la mejor bebida del mundo?',
        'Acabo de terminar de leer un libro fascinante. Lo recomiendo.',
        'Nada como una buena caminata para despejar la mente.',
        'Mi nueva receta de pasta quedó espectacular.',
        'Reflexión del día: la perseverancia todo lo alcanza.',
        'Este fin de semana fue increíble con los amigos.',
        'Empezando el lunes con toda la actitud.',
        '¿Qué música están escuchando últimamente?',
        'Hoy aprendí algo nuevo sobre programación.',
        'La naturaleza siempre me sorprende con su belleza.',
        'Comparto mi setup de trabajo remoto.',
        'Feliz cumpleaños a mí. Un año más de aprendizajes.',
        'Recomendación de película: increíble cinematografía.',
        'Domingo de descanso y reflexión.',
        'Acabo de adoptar un perrito. Presento a Toby.',
        'El mejor concierto al que he ido en años.',
        'Gracias a todos por sus buenos deseos.',
        'Nuevo récord personal en el gimnasio hoy.',
        'La vida es mejor cuando haces lo que amas.',
        'Debatiendo: ¿playa o montaña para vacaciones?',
        'Mi rincón favorito de la ciudad al amanecer.',
        'Trabajando en un nuevo proyecto creativo.',
        'Celebrando 5 años de amistad con este grupo.',
        'Hoy cociné para toda la familia. Éxito total.',
        '¿Algún consejo para empezar a meditar?',
        'Increíble exposición de arte contemporáneo.',
        'Día productivo. Checklist completado.',
        'La felicidad está en los pequeños momentos.',
        'Emocionado por el nuevo videojuego que acaba de salir.',
        'Compartiendo mi progreso del jardín.',
        'Hoy es uno de esos días que no quieres que terminen.',
        'Aprendiendo fotografía. Primeras tomas.',
        'Recomendación de libro para el fin de semana.',
        'De vuelta al running después de un mes.',
        'La mejor pizza de la ciudad, sin duda.',
        'Reflexiones nocturnas bajo las estrellas.',
        'Nuevo hobby: cerámica artesanal.',
        'Hoy me desperté agradecido por todo.',
        'Trabajo en equipo, sueño cumplido.',
        'Los pequeños logros también se celebran.',
        'Descubriendo nuevos sabores de helado artesanal.',
        'Momentos que se quedan para siempre.',
        'El poder de una sonrisa no tiene precio.',
        'Avances en mi proyecto personal. Comparto pronto.',
        'Hoy el clima estuvo perfecto para salir.',
        'Cena con amigos. Noches que valen oro.',
        '¿Cuál es su cita motivacional favorita?',
        'Nueva playlist en Spotify. Enlace en bio.',
    ];

    $imagePool = [null, null, null, 'post_img1.jpg', 'post_img2.jpg', null, 'post_img3.jpg', null, null, 'post_img4.jpg'];

    $insP = $pdo->prepare("INSERT INTO posts (user_id, content, image, created_at) VALUES (?, ?, ?, ?)");
    for ($i = 0; $i < $neededPosts; $i++) {
        $insP->execute([pick($userIds), $postContents[$i % count($postContents)], pick($imagePool), randDate(random_int(0, 29))]);
        $postIds[] = (int)$pdo->lastInsertId();
    }
    echo "  +$neededPosts posts created. Total: " . count($postIds) . "\n";
}

// ── 3. Comments ──────────────────────────────────────────────────────
$commentCount = (int)$pdo->query("SELECT COUNT(*) FROM comments")->fetchColumn();
$neededComments = max(0, 150 - $commentCount);
$commentIds = [];
$stmtC = $pdo->query("SELECT comment_id FROM comments");
while ($row = $stmtC->fetch(PDO::FETCH_ASSOC)) {
    $commentIds[] = (int)$row['comment_id'];
}

if ($neededComments > 0) {
    $commentsTexts = [
        '¡Qué buen post! Totalmente de acuerdo.',
        'Me encanta esto, gracias por compartir.',
        'Justo lo que necesitaba leer hoy.',
        'Increíble foto. ¿Dónde fue tomada?',
        'Jajaja, me identifico completamente.',
        'Qué bonito mensaje. Saludos.',
        '100% de acuerdo contigo.',
        'Esto es muy inspirador.',
        '¿Puedes compartir más detalles?',
        'Guardado para recordarlo.',
        'Tienes toda la razón.',
        'Qué envidia de la buena.',
        'Felicidades, te lo mereces.',
        'Sigue así, vas por buen camino.',
        'No podría estar más de acuerdo.',
        'Gracias por la recomendación.',
        'Espectacular, sin palabras.',
        'Me pasó algo muy similar.',
        'Qué recuerdos me trae esto.',
        'Definitivamente voy a probarlo.',
    ];

    $insC = $pdo->prepare("INSERT INTO comments (post_id, user_id, content, created_at) VALUES (?, ?, ?, ?)");
    for ($i = 0; $i < $neededComments; $i++) {
        $insC->execute([pick($postIds), pick($userIds), pick($commentsTexts), randDate(random_int(0, 28))]);
        $commentIds[] = (int)$pdo->lastInsertId();
    }
    echo "  +$neededComments comments created. Total: " . count($commentIds) . "\n";
}

// ── 4. Replies ───────────────────────────────────────────────────────
$replyCount = (int)$pdo->query("SELECT COUNT(*) FROM replies")->fetchColumn();
$neededReplies = max(0, 80 - $replyCount);
$replyIds = [];
$stmtR = $pdo->query("SELECT reply_id FROM replies");
while ($row = $stmtR->fetch(PDO::FETCH_ASSOC)) {
    $replyIds[] = (int)$row['reply_id'];
}

if ($neededReplies > 0) {
    $replyTexts = [
        'Sí, totalmente.', 'Opino lo mismo.',
        'Buena pregunta, también quiero saber.',
        'Me uno a la conversación.',
        'Qué interesante punto de vista.',
        'Exacto, justo iba a decir eso.',
        'Te respondo por DM.', 'Jajaja, buenísimo.',
        'Aquí siguiendo la conversación.',
        'Concuerdo plenamente.',
    ];

    $insRp = $pdo->prepare("INSERT INTO replies (comment_id, user_id, content, created_at) VALUES (?, ?, ?, ?)");
    for ($i = 0; $i < $neededReplies; $i++) {
        $insRp->execute([pick($commentIds), pick($userIds), pick($replyTexts), randDate(random_int(0, 26))]);
        $replyIds[] = (int)$pdo->lastInsertId();
    }
    echo "  +$neededReplies replies created. Total: " . count($replyIds) . "\n";
}

// ── 5. Post Likes ────────────────────────────────────────────────────
$likeCount = (int)$pdo->query("SELECT COUNT(*) FROM likes")->fetchColumn();
$neededLikes = max(0, 300 - $likeCount);

if ($neededLikes > 0) {
    $insLk = $pdo->prepare("INSERT IGNORE INTO likes (post_id, user_id, liked_at) VALUES (?, ?, ?)");
    $added = 0;
    for ($i = 0; $i < $neededLikes * 3; $i++) {
        $insLk->execute([pick($postIds), pick($userIds), randDate(random_int(0, 28))]);
        if ($insLk->rowCount() > 0) {
            $added++;
            if ($added >= $neededLikes) break;
        }
    }
    echo "  +$added post likes created.\n";
}

// ── 6. Comment Likes ─────────────────────────────────────────────────
$cLikeCount = (int)$pdo->query("SELECT COUNT(*) FROM comment_likes")->fetchColumn();
$neededCLikes = max(0, 100 - $cLikeCount);

if ($neededCLikes > 0) {
    $insCLk = $pdo->prepare("INSERT IGNORE INTO comment_likes (comment_id, user_id, liked_at) VALUES (?, ?, ?)");
    $added = 0;
    for ($i = 0; $i < $neededCLikes * 3; $i++) {
        $insCLk->execute([pick($commentIds), pick($userIds), randDate(random_int(0, 26))]);
        if ($insCLk->rowCount() > 0) {
            $added++;
            if ($added >= $neededCLikes) break;
        }
    }
    echo "  +$added comment likes created.\n";
}

// ── 7. Reply Likes ───────────────────────────────────────────────────
$rLikeCount = (int)$pdo->query("SELECT COUNT(*) FROM reply_likes")->fetchColumn();
$neededRLikes = max(0, 60 - $rLikeCount);

if ($neededRLikes > 0) {
    $insRLk = $pdo->prepare("INSERT IGNORE INTO reply_likes (reply_id, user_id, liked_at) VALUES (?, ?, ?)");
    $added = 0;
    for ($i = 0; $i < $neededRLikes * 3; $i++) {
        $insRLk->execute([pick($replyIds), pick($userIds), randDate(random_int(0, 24))]);
        if ($insRLk->rowCount() > 0) {
            $added++;
            if ($added >= $neededRLikes) break;
        }
    }
    echo "  +$added reply likes created.\n";
}

// ── 8. Friendships ───────────────────────────────────────────────────
$fCount = (int)$pdo->query("SELECT COUNT(*) FROM friends")->fetchColumn();
$neededFriends = max(0, 40 - $fCount);

if ($neededFriends > 0) {
    $reqStmt = $pdo->prepare("INSERT IGNORE INTO friend_requests (sender_id, receiver_id, status, request_date, response_date) VALUES (?, ?, 'accepted', ?, ?)");
    $fStmt = $pdo->prepare("INSERT IGNORE INTO friends (user_id1, user_id2, friendship_date) VALUES (?, ?, ?)");

    $existing = [];
    $ef = $pdo->query("SELECT user_id1, user_id2 FROM friends");
    while ($row = $ef->fetch(PDO::FETCH_ASSOC)) {
        $existing[min($row['user_id1'], $row['user_id2']) . '-' . max($row['user_id1'], $row['user_id2'])] = true;
    }

    $added = 0;
    for ($i = 0; $i < $neededFriends * 5; $i++) {
        $s = pick($userIds);
        $r = pick($userIds);
        if ($s === $r) continue;
        $key = min($s, $r) . '-' . max($s, $r);
        if (isset($existing[$key])) continue;
        $existing[$key] = true;

        $reqDate = randDate(random_int(5, 28));
        $respDate = date('Y-m-d H:i:s', strtotime($reqDate) + random_int(3600, 86400));
        $reqStmt->execute([$s, $r, $reqDate, $respDate]);
        $fStmt->execute([min($s, $r), max($s, $r), randDate(random_int(0, 27))]);
        $added++;
        if ($added >= $neededFriends) break;
    }
    echo "  +$added friendships created.\n";
}

echo "\nDone.\n";
