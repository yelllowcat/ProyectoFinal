CREATE DATABASE IF NOT EXISTS unired_DB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE unired_DB;

-- TABLA users
CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    biography TEXT,
    profile_picture VARCHAR(255) DEFAULT 'default_avatar.png',
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    registration_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    role VARCHAR(20) DEFAULT 'user',
    active BOOLEAN DEFAULT TRUE,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- TABLA posts
CREATE TABLE IF NOT EXISTS posts (
    post_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    image VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- TABLA comments
CREATE TABLE IF NOT EXISTS comments (
    comment_id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (post_id) REFERENCES posts(post_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- TABLA hidden_comments
CREATE TABLE IF NOT EXISTS hidden_comments (
    hidden_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    comment_id INT NOT NULL,
    hidden_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (comment_id) REFERENCES comments(comment_id) ON DELETE CASCADE,
    UNIQUE (user_id, comment_id)
) ENGINE=InnoDB;

-- TABLA likes
CREATE TABLE IF NOT EXISTS likes (
    like_id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    user_id INT NOT NULL,
    liked_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(post_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    UNIQUE (post_id, user_id)
) ENGINE=InnoDB;

-- Tabla comment_likes
CREATE TABLE comment_likes (
    like_id     INT AUTO_INCREMENT PRIMARY KEY,
    comment_id  INT NOT NULL,
    user_id     INT NOT NULL,
    liked_at    DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (comment_id) REFERENCES comments(comment_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)    REFERENCES users(user_id) ON DELETE CASCADE,
    UNIQUE (comment_id, user_id)
) ENGINE=InnoDB;

-- Tabla replies
CREATE TABLE replies (
    reply_id    INT AUTO_INCREMENT PRIMARY KEY,
    comment_id  INT NOT NULL,                    -- parent comment
    user_id     INT NOT NULL,
    content     TEXT NOT NULL,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    active      BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (comment_id) REFERENCES comments(comment_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)    REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Tabla reply_likes
CREATE TABLE reply_likes (
    like_id     INT AUTO_INCREMENT PRIMARY KEY,
    reply_id    INT NOT NULL,
    user_id     INT NOT NULL,
    liked_at    DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reply_id) REFERENCES replies(reply_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)  REFERENCES users(user_id) ON DELETE CASCADE,
    UNIQUE (reply_id, user_id)
) ENGINE=InnoDB;

-- TABLA friend_requests
CREATE TABLE IF NOT EXISTS friend_requests (
    request_id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    status VARCHAR(20) DEFAULT 'pending',
    request_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    response_date DATETIME,
    FOREIGN KEY (sender_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- TABLA friends (parejas de amistad)
CREATE TABLE IF NOT EXISTS friends (
    friendship_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id1 INT NOT NULL,
    user_id2 INT NOT NULL,
    friendship_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id1) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id2) REFERENCES users(user_id) ON DELETE CASCADE,
    UNIQUE (user_id1, user_id2)
) ENGINE=InnoDB;

-- TABLA user_update_log
CREATE TABLE IF NOT EXISTS user_update_log (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    old_full_name VARCHAR(100),
    new_full_name VARCHAR(100),
    old_biography TEXT,
    new_biography TEXT,
    change_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
) ENGINE=InnoDB;

-- TABLA reports
CREATE TABLE IF NOT EXISTS reports (
    report_id INT AUTO_INCREMENT PRIMARY KEY,
    reporter_id INT NOT NULL,
    reported_user_id INT,
    post_id INT,
    comment_id INT,
    reply_id INT,
    reason TEXT NOT NULL,
    status VARCHAR(20) DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    resolved_at DATETIME,
    FOREIGN KEY (reporter_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (reported_user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (post_id) REFERENCES posts(post_id) ON DELETE CASCADE,
    FOREIGN KEY (comment_id) REFERENCES comments(comment_id) ON DELETE CASCADE,
    FOREIGN KEY (reply_id) REFERENCES replies(reply_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Comprobaciones básicas (puedes descomentar si deseas ver resultados)
-- SELECT * FROM users;
-- SELECT * FROM posts;
-- SELECT * FROM comments;
-- SELECT * FROM likes;
-- SELECT * FROM hidden_comments;
-- SELECT * FROM friend_requests;
-- SELECT * FROM friends;
-- SELECT * FROM user_update_log;

-- ---------------------------------------------------
-- Procedimientos almacenados (2 ejemplos)
DELIMITER $$
CREATE PROCEDURE sp_register_user(
    IN p_full_name VARCHAR(100),
    IN p_email VARCHAR(100),
    IN p_password VARCHAR(255),
    IN p_role VARCHAR(20)
)
BEGIN
    IF EXISTS (SELECT 1 FROM users WHERE email = p_email) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El correo ya está registrado';
    ELSE
        INSERT INTO users (full_name, email, password, role)
        VALUES (p_full_name, p_email, p_password, p_role);
    END IF;
END$$
DELIMITER ;

DELIMITER $$
CREATE PROCEDURE sp_login_user(
    IN p_email VARCHAR(100)
)
BEGIN
    DECLARE user_exists INT;
    SELECT COUNT(*) INTO user_exists FROM users WHERE email = p_email;
    IF user_exists = 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Correo no encontrado';
    ELSE
        SELECT user_id, full_name, email, password, role, registration_date, active FROM users WHERE email = p_email;
    END IF;
END$$
DELIMITER ;

-- -------------------store procedure of likes-------------------------------

-- Procedimiento para agregar un like
DELIMITER $$
CREATE PROCEDURE sp_add_like(
    IN p_post_id INT,
    IN p_user_id INT
)
BEGIN
    INSERT IGNORE INTO likes (post_id, user_id, liked_at) 
    VALUES (p_post_id, p_user_id, NOW());
    
    SELECT ROW_COUNT() AS affected_rows;
END$$
DELIMITER ;

-- Procedimiento para eliminar un like
DELIMITER $$
CREATE PROCEDURE sp_remove_like(
    IN p_post_id INT,
    IN p_user_id INT
)
BEGIN
    DELETE FROM likes 
    WHERE post_id = p_post_id AND user_id = p_user_id;
    
    SELECT ROW_COUNT() AS affected_rows;
END$$
DELIMITER ;

-- Procedimiento para obtener el conteo de likes de un post
DELIMITER $$
CREATE PROCEDURE sp_get_like_count(
    IN p_post_id INT
)
BEGIN
    SELECT COUNT(*) as like_count 
    FROM likes 
    WHERE post_id = p_post_id;
END$$
DELIMITER ;

-- Procedimiento para verificar si un usuario ya dio like a un post
DELIMITER $$
CREATE PROCEDURE sp_has_liked(
    IN p_post_id INT,
    IN p_user_id INT
)
BEGIN
    SELECT EXISTS(
        SELECT 1 FROM likes 
        WHERE post_id = p_post_id AND user_id = p_user_id
    ) as has_liked;
END$$
DELIMITER ;

-- Procedimiento para obtener todos los likes de un usuario
DELIMITER $$
CREATE PROCEDURE sp_get_user_likes(
    IN p_user_id INT
)
BEGIN
    SELECT l.*, p.content as post_content
    FROM likes l
    JOIN posts p ON l.post_id = p.post_id
    WHERE l.user_id = p_user_id
    ORDER BY l.liked_at DESC;
END$$
DELIMITER ;

-- Procedimiento para obtener los usuarios que dieron like a un post
DELIMITER $$
CREATE PROCEDURE sp_get_post_likers(
    IN p_post_id INT
)
BEGIN
    SELECT u.user_id, u.full_name, u.profile_picture, l.liked_at
    FROM likes l
    JOIN users u ON l.user_id = u.user_id
    WHERE l.post_id = p_post_id
    ORDER BY l.liked_at DESC;
END$$
DELIMITER ;

-- ---------------------------store procedure of comment likes-------------------------------

DELIMITER $$
CREATE PROCEDURE sp_add_comment_like(
    IN p_comment_id INT,
    IN p_user_id INT
)
BEGIN
    INSERT IGNORE INTO comment_likes (comment_id, user_id, liked_at)
    VALUES (p_comment_id, p_user_id, NOW());
    SELECT ROW_COUNT() AS affected_rows;
END$$
DELIMITER ;

DELIMITER $$
CREATE PROCEDURE sp_remove_comment_like(
    IN p_comment_id INT,
    IN p_user_id INT
)
BEGIN
    DELETE FROM comment_likes
    WHERE comment_id = p_comment_id AND user_id = p_user_id;
    SELECT ROW_COUNT() AS affected_rows;
END$$
DELIMITER ;

DELIMITER $$
CREATE PROCEDURE sp_get_comment_like_count(
    IN p_comment_id INT
)
BEGIN
    SELECT COUNT(*) as like_count
    FROM comment_likes
    WHERE comment_id = p_comment_id;
END$$
DELIMITER ;

DELIMITER $$
CREATE PROCEDURE sp_has_comment_liked(
    IN p_comment_id INT,
    IN p_user_id INT
)
BEGIN
    SELECT EXISTS(
        SELECT 1 FROM comment_likes
        WHERE comment_id = p_comment_id AND user_id = p_user_id
    ) as has_liked;
END$$
DELIMITER ;

-- ---------------------------store procedure of reply likes-------------------------------

DELIMITER $$
CREATE PROCEDURE sp_add_reply_like(
    IN p_reply_id INT,
    IN p_user_id INT
)
BEGIN
    INSERT IGNORE INTO reply_likes (reply_id, user_id, liked_at)
    VALUES (p_reply_id, p_user_id, NOW());
    SELECT ROW_COUNT() AS affected_rows;
END$$
DELIMITER ;

DELIMITER $$
CREATE PROCEDURE sp_remove_reply_like(
    IN p_reply_id INT,
    IN p_user_id INT
)
BEGIN
    DELETE FROM reply_likes
    WHERE reply_id = p_reply_id AND user_id = p_user_id;
    SELECT ROW_COUNT() AS affected_rows;
END$$
DELIMITER ;

DELIMITER $$
CREATE PROCEDURE sp_get_reply_like_count(
    IN p_reply_id INT
)
BEGIN
    SELECT COUNT(*) as like_count
    FROM reply_likes
    WHERE reply_id = p_reply_id;
END$$
DELIMITER ;

DELIMITER $$
CREATE PROCEDURE sp_has_reply_liked(
    IN p_reply_id INT,
    IN p_user_id INT
)
BEGIN
    SELECT EXISTS(
        SELECT 1 FROM reply_likes
        WHERE reply_id = p_reply_id AND user_id = p_user_id
    ) as has_liked;
END$$
DELIMITER ;

-- ---------------------------store procedure of comments-------------------------------

-- Procedimiento para crear un comentario
DELIMITER $$
CREATE PROCEDURE sp_create_comment(
    IN p_post_id INT,
    IN p_user_id INT,
    IN p_content TEXT
)
BEGIN
    INSERT INTO comments (post_id, user_id, content, created_at) 
    VALUES (p_post_id, p_user_id, p_content, NOW());
    
    SELECT LAST_INSERT_ID() as comment_id;
END$$
DELIMITER ;

-- Procedimiento para obtener comentarios de un post
DELIMITER $$
CREATE PROCEDURE sp_get_comments_by_post(
    IN p_post_id INT
)
BEGIN
    SELECT c.*, u.full_name, u.profile_picture,
        (SELECT COUNT(*) FROM replies r WHERE r.comment_id = c.comment_id AND r.active = 1) as reply_count
    FROM comments c 
    JOIN users u ON c.user_id = u.user_id 
    WHERE c.post_id = p_post_id AND c.active = 1 
    ORDER BY c.created_at ASC;
END$$
DELIMITER ;

-- Procedimiento para eliminar (soft delete) un comentario
DELIMITER $$
CREATE PROCEDURE sp_delete_comment(
    IN p_comment_id INT,
    IN p_user_id INT
)
BEGIN
    UPDATE comments 
    SET active = 0 
    WHERE comment_id = p_comment_id AND user_id = p_user_id;
    
    SELECT ROW_COUNT() AS affected_rows;
END$$
DELIMITER ;

-- Procedimiento para obtener el conteo de comentarios de un post
DELIMITER $$
CREATE PROCEDURE sp_get_comment_count(
    IN p_post_id INT
)
BEGIN
    SELECT COUNT(*) as comment_count 
    FROM comments 
    WHERE post_id = p_post_id AND active = 1;
END$$
DELIMITER ;

-- Procedimiento para obtener un comentario específico
DELIMITER $$
CREATE PROCEDURE sp_get_comment_by_id(
    IN p_comment_id INT
)
BEGIN
    SELECT c.*, u.full_name, u.profile_picture 
    FROM comments c 
    JOIN users u ON c.user_id = u.user_id 
    WHERE c.comment_id = p_comment_id AND c.active = 1;
END$$
DELIMITER ;

-- ---------------------------store procedure of replies-------------------------------

-- Procedimiento para crear una reply
DELIMITER $$
CREATE PROCEDURE sp_create_reply(
    IN p_comment_id INT,
    IN p_user_id INT,
    IN p_content TEXT
)
BEGIN
    INSERT INTO replies (comment_id, user_id, content, created_at)
    VALUES (p_comment_id, p_user_id, p_content, NOW());

    SELECT LAST_INSERT_ID() as reply_id;
END$$
DELIMITER ;

-- Procedimiento para obtener replies de un comentario
DELIMITER $$
CREATE PROCEDURE sp_get_replies_by_comment(
    IN p_comment_id INT
)
BEGIN
    SELECT r.*, u.full_name, u.profile_picture
    FROM replies r
    JOIN users u ON r.user_id = u.user_id
    WHERE r.comment_id = p_comment_id AND r.active = 1
    ORDER BY r.created_at ASC;
END$$
DELIMITER ;

-- Procedimiento para eliminar (soft delete) una reply
DELIMITER $$
CREATE PROCEDURE sp_delete_reply(
    IN p_reply_id INT,
    IN p_user_id INT
)
BEGIN
    UPDATE replies
    SET active = 0
    WHERE reply_id = p_reply_id AND user_id = p_user_id;

    SELECT ROW_COUNT() AS affected_rows;
END$$
DELIMITER ;

-- Procedimiento para obtener el conteo de replies de un comentario
DELIMITER $$
CREATE PROCEDURE sp_get_reply_count(
    IN p_comment_id INT
)
BEGIN
    SELECT COUNT(*) as reply_count
    FROM replies
    WHERE comment_id = p_comment_id AND active = 1;
END$$
DELIMITER ;

-- Procedimiento para obtener una reply específica
DELIMITER $$
CREATE PROCEDURE sp_get_reply_by_id(
    IN p_reply_id INT
)
BEGIN
    SELECT r.*, u.full_name, u.profile_picture
    FROM replies r
    JOIN users u ON r.user_id = u.user_id
    WHERE r.reply_id = p_reply_id AND r.active = 1;
END$$
DELIMITER ;

-- --------------------------------store procedure of posts-----------------------------------------------

-- Procedimiento para crear post (con o sin imagen)
DELIMITER $$
CREATE PROCEDURE sp_create_post(
    IN p_user_id INT,
    IN p_content TEXT,
    IN p_image VARCHAR(255)
)
BEGIN
    INSERT INTO posts (user_id, content, image, created_at, updated_at) 
    VALUES (p_user_id, p_content, p_image, NOW(), NOW());
    
    SELECT LAST_INSERT_ID() as post_id;
END$$
DELIMITER ;

-- --------------------------------store procedure of reports-----------------------------------------------

DELIMITER $$
CREATE PROCEDURE sp_create_report(
    IN p_reporter_id INT,
    IN p_reported_user_id INT,
    IN p_post_id INT,
    IN p_comment_id INT,
    IN p_reply_id INT,
    IN p_reason TEXT
)
BEGIN
    INSERT INTO reports (reporter_id, reported_user_id, post_id, comment_id, reply_id, reason, created_at)
    VALUES (p_reporter_id, p_reported_user_id, p_post_id, p_comment_id, p_reply_id, p_reason, NOW());
    
    SELECT LAST_INSERT_ID() as report_id;
END$$
DELIMITER ;

DELIMITER $$
CREATE PROCEDURE sp_get_reports()
BEGIN
    SELECT r.*, 
           u.full_name AS reporter_name, 
           ru.full_name AS reported_user_name,
           p.content AS post_content,
           p.user_id AS post_user_id,
           c.content AS comment_content,
           c.post_id AS comment_post_id,
           re.content AS reply_content,
           rc.post_id AS reply_post_id
    FROM reports r
    JOIN users u ON r.reporter_id = u.user_id
    LEFT JOIN users ru ON r.reported_user_id = ru.user_id
    LEFT JOIN posts p ON r.post_id = p.post_id
    LEFT JOIN comments c ON r.comment_id = c.comment_id
    LEFT JOIN replies re ON r.reply_id = re.reply_id
    LEFT JOIN comments rc ON re.comment_id = rc.comment_id
    ORDER BY r.created_at DESC;
END$$
DELIMITER ;

DELIMITER $$
CREATE PROCEDURE sp_resolve_report(
    IN p_report_id INT,
    IN p_status VARCHAR(20)
)
BEGIN
    UPDATE reports 
    SET status = p_status, resolved_at = NOW()
    WHERE report_id = p_report_id;
    
    SELECT ROW_COUNT() as affected_rows;
END$$
DELIMITER ;

DELIMITER $$
CREATE PROCEDURE sp_delete_report(
    IN p_report_id INT
)
BEGIN
    DELETE FROM reports WHERE report_id = p_report_id;
    SELECT ROW_COUNT() as affected_rows;
END$$
DELIMITER ;

-- ---------------------------------------------------
-- Trigger: registrar historial antes de update en users
DELIMITER $$
CREATE TRIGGER trg_user_update_log
BEFORE UPDATE ON users
FOR EACH ROW
BEGIN
    INSERT INTO user_update_log (
        user_id,
        old_full_name, new_full_name,
        old_biography, new_biography
    ) VALUES (
        OLD.user_id,
        OLD.full_name, NEW.full_name,
        OLD.biography, NEW.biography
    );
END$$
DELIMITER ;

-- ---------------------------------------------------
-- Vista: posts con información
CREATE OR REPLACE VIEW v_posts_stats AS
SELECT 
    p.post_id,
    p.user_id,
    p.content,
    p.image,
    p.created_at,
    p.updated_at,
    u.full_name AS author_name,
    u.profile_picture AS author_picture,
    u.email AS author_email,
    IFNULL(l.likes_count, 0) AS likes_count,
    IFNULL(c.comments_count, 0) AS comments_count
FROM posts p
INNER JOIN users u ON p.user_id = u.user_id
LEFT JOIN (
    SELECT post_id, COUNT(*) AS likes_count
    FROM likes
    GROUP BY post_id
) l ON p.post_id = l.post_id
LEFT JOIN (
    SELECT post_id, COUNT(*) AS comments_count
    FROM comments
    WHERE active = 1
    GROUP BY post_id
) c ON p.post_id = c.post_id
WHERE p.active = 1
ORDER BY p.created_at DESC;

DELIMITER $$
CREATE PROCEDURE sp_admin_delete_post(
    IN p_post_id INT
)
BEGIN
    UPDATE posts 
    SET active = 0 
    WHERE post_id = p_post_id;
    
    SELECT ROW_COUNT() AS affected_rows;
END$$
DELIMITER ;

DELIMITER $$
CREATE PROCEDURE sp_admin_delete_comment(
    IN p_comment_id INT
)
BEGIN
    UPDATE comments 
    SET active = 0 
    WHERE comment_id = p_comment_id;
    
    SELECT ROW_COUNT() AS affected_rows;
END$$
DELIMITER ;

DELIMITER $$
CREATE PROCEDURE sp_admin_delete_reply(
    IN p_reply_id INT
)
BEGIN
    UPDATE replies 
    SET active = 0 
    WHERE reply_id = p_reply_id;
    
    SELECT ROW_COUNT() AS affected_rows;
END$$
DELIMITER ;

DELIMITER $$
CREATE PROCEDURE sp_admin_suspend_user(
    IN p_user_id INT
)
BEGIN
    UPDATE users 
    SET active = 0 
    WHERE user_id = p_user_id;
    
    SELECT ROW_COUNT() AS affected_rows;
END$$
DELIMITER ;

-- ---------------------------------------------------
-- TABLA hashtags
CREATE TABLE IF NOT EXISTS hashtags (
    hashtag_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_name (name)
) ENGINE=InnoDB;

-- TABLA post_hashtags (relación muchos-a-muchos)
CREATE TABLE IF NOT EXISTS post_hashtags (
    post_id INT NOT NULL,
    hashtag_id INT NOT NULL,
    PRIMARY KEY (post_id, hashtag_id),
    FOREIGN KEY (post_id) REFERENCES posts(post_id) ON DELETE CASCADE,
    FOREIGN KEY (hashtag_id) REFERENCES hashtags(hashtag_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Procedimiento: obtener o crear hashtag
DELIMITER $$
CREATE PROCEDURE sp_get_or_create_hashtag(
    IN p_name VARCHAR(50)
)
BEGIN
    DECLARE v_hashtag_id INT;
    SET p_name = LOWER(TRIM(p_name));
    
    SELECT hashtag_id INTO v_hashtag_id FROM hashtags WHERE name = p_name;
    
    IF v_hashtag_id IS NULL THEN
        INSERT INTO hashtags (name) VALUES (p_name);
        SET v_hashtag_id = LAST_INSERT_ID();
    END IF;
    
    SELECT v_hashtag_id AS hashtag_id;
END$$
DELIMITER ;

-- Procedimiento: vincular post con hashtag
DELIMITER $$
CREATE PROCEDURE sp_link_post_hashtag(
    IN p_post_id INT,
    IN p_hashtag_id INT
)
BEGIN
    INSERT IGNORE INTO post_hashtags (post_id, hashtag_id) VALUES (p_post_id, p_hashtag_id);
    SELECT ROW_COUNT() AS affected_rows;
END$$
DELIMITER ;

-- Procedimiento: desvincular todos los hashtags de un post
DELIMITER $$
CREATE PROCEDURE sp_unlink_post_hashtags(
    IN p_post_id INT
)
BEGIN
    DELETE FROM post_hashtags WHERE post_id = p_post_id;
END$$
DELIMITER ;

-- Procedimiento: obtener posts por hashtag
DELIMITER $$
CREATE PROCEDURE sp_get_posts_by_hashtag(
    IN p_name VARCHAR(50)
)
BEGIN
    SELECT p.*, u.full_name AS author_name, u.profile_picture AS author_picture, u.email AS author_email,
           IFNULL(l.likes_count, 0) AS likes_count,
           IFNULL(c.comments_count, 0) AS comments_count
    FROM posts p
    INNER JOIN users u ON p.user_id = u.user_id
    INNER JOIN post_hashtags ph ON p.post_id = ph.post_id
    INNER JOIN hashtags h ON ph.hashtag_id = h.hashtag_id
    LEFT JOIN (
        SELECT post_id, COUNT(*) AS likes_count FROM likes GROUP BY post_id
    ) l ON p.post_id = l.post_id
    LEFT JOIN (
        SELECT post_id, COUNT(*) AS comments_count FROM comments WHERE active = 1 GROUP BY post_id
    ) c ON p.post_id = c.post_id
    WHERE h.name = LOWER(p_name)
      AND p.active = 1
    ORDER BY p.created_at DESC;
END$$
DELIMITER ;

-- Procedimiento: obtener hashtags en tendencia
DELIMITER $$
CREATE PROCEDURE sp_get_trending_hashtags(
    IN p_limit INT
)
BEGIN
    SELECT h.name, COUNT(DISTINCT ph.post_id) AS post_count
    FROM hashtags h
    JOIN post_hashtags ph ON h.hashtag_id = ph.hashtag_id
    JOIN posts p ON ph.post_id = p.post_id AND p.active = 1
    GROUP BY h.hashtag_id, h.name
    ORDER BY post_count DESC, h.name ASC
    LIMIT p_limit;
END$$
DELIMITER ;

-- Procedimiento: obtener hashtags de un post
DELIMITER $$
CREATE PROCEDURE sp_get_hashtags_for_post(
    IN p_post_id INT
)
BEGIN
    SELECT h.name
    FROM hashtags h
    JOIN post_hashtags ph ON h.hashtag_id = ph.hashtag_id
    WHERE ph.post_id = p_post_id
    ORDER BY h.name ASC;
END$$
DELIMITER ;
