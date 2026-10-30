<?php

namespace App\Controllers;

use App\Models\PostModel;
use App\Models\LikeModel;
use App\Models\CommentModel;
use App\Models\ReplyModel;
use App\Models\CommentLikeModel;
use App\Models\ReplyLikeModel;
use App\Models\HashtagModel;

class PostController
{
    public function store()
    {
        requireAuth();

        $userId = $_SESSION['user_id'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $content = clean_input($_POST['content'] ?? '');

            if (empty($content) || strlen(trim($content)) < 1) {
                flash('error', 'El contenido del post debe tener al menos 1 caracteres');
                redirect('/addPost');
            }

            $imageName = null;

            if (isset($_FILES['post_image']) && $_FILES['post_image']['error'] === 0) {
                $imageName = $this->handleImageUpload($_FILES['post_image']);

                if ($imageName === null) {
                    redirect('/addPost');
                }
            }

            $postModel = new PostModel();

            $safeContent = clean_input($content);
            $result = $postModel->createPost($userId, $safeContent, $imageName);

            if ($result) {
                // Extract and link hashtags (from original content before escaping)
                $hashtags = HashtagModel::extractHashtags($content);
                if (!empty($hashtags)) {
                    $hashtagModel = new HashtagModel();
                    $hashtagModel->linkPostHashtags($result, $hashtags);
                }

                flash('success', 'Post publicado correctamente' . ($imageName ? ' con imagen' : ''));
                redirect('/posts');
            } else {
                flash('error', 'Error al publicar el post');
                redirect('/addPost');
            }
        }

        redirect('/addPost');
    }

    private function handleImageUpload($imageFile)
    {
        $allowedMimes = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/gif'  => 'gif',
            'image/webp' => 'webp'
        ];
        $maxSize = 5 * 1024 * 1024;

        if ($imageFile['size'] > $maxSize) {
            flash('error', 'La imagen no puede ser mayor a 5MB');
            return null;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = $finfo ? finfo_file($finfo, $imageFile['tmp_name']) : false;
        if ($finfo) {
            finfo_close($finfo);
        }

        if (!$mime || !isset($allowedMimes[$mime])) {
            flash('error', 'Solo se permiten imagenes JPEG, PNG, GIF y WebP validas');
            return null;
        }

        $uploadDir = __DIR__ . '/../../assets/imagesPosts/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $extension = $allowedMimes[$mime];
        $fileName = bin2hex(random_bytes(16)) . '.' . $extension;
        $filePath = $uploadDir . $fileName;

        if (move_uploaded_file($imageFile['tmp_name'], $filePath)) {
            return $fileName;
        } else {
            flash('error', 'Error al subir la imagen');
            return null;
        }
    }

    public function update($id)
    {
        requireAuth();

        $userId = $_SESSION['user_id'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $content = clean_input($_POST['content'] ?? '');

            if (empty($content) || strlen(trim($content)) < 1) {
                flash('error', 'El contenido del post debe tener al menos 1 caracteres');
                redirect('/editPost/' . $id);
                return;
            }

            $postModel = new PostModel();
            $post = $postModel->getPostById($id);

            if (!$post || $post['user_id'] != $userId) {
                flash('error', 'No tienes permisos para editar este post');
                redirect('/posts');
                return;
            }

            $safeContent = clean_input($content);
            $result = $postModel->updatePost($id, $safeContent, $post['image']);

            if ($result) {
                // Re-sync hashtags: unlink old, link new
                $hashtagModel = new HashtagModel();
                $hashtagModel->unlinkPostHashtags($id);
                $hashtags = HashtagModel::extractHashtags($content);
                if (!empty($hashtags)) {
                    $hashtagModel->linkPostHashtags($id, $hashtags);
                }

                flash('success', 'Post actualizado correctamente');
                redirect('/posts');
            } else {
                flash('error', 'Error al actualizar el post');
                redirect('/editPost/' . $id);
            }
        } else {
            flash('error', 'Método no permitido');
            redirect('/posts');
        }
    }

    public function destroy($id)
    {
        requireAuth();

        $userId = $_SESSION['user_id'];

        $postModel = new PostModel();
        $post = $postModel->getPostById($id);

        if (!$post || $post['user_id'] != $userId) {
            return jsonError('No tienes permisos para eliminar este post', 403);
        }

        $result = $postModel->deletePost($id);

        if ($result) {
            // Clean up hashtag links
            $hashtagModel = new HashtagModel();
            $hashtagModel->unlinkPostHashtags($id);

            // Clean up orphaned image file if present
            if (!empty($post['image'])) {
                $postImagePath = __DIR__ . '/../../assets/imagesPosts/' . $post['image'];
                if (file_exists($postImagePath)) {
                    @unlink($postImagePath);
                }
            }

            return jsonSuccess(null, 'Post eliminado correctamente');
        } else {
            return jsonError('Error al eliminar el post');
        }
    }

    public function like($id)
    {
        requireAuth();

        $userId = $_SESSION['user_id'];
        $likeModel = new LikeModel();

        $hasLiked = $likeModel->hasLiked($id, $userId);

        if ($hasLiked) {
            $result = $likeModel->removeLike($id, $userId);
            $action = 'removed';
        } else {
            $result = $likeModel->addLike($id, $userId);
            $action = 'added';
        }

        if ($result) {
            $likeCount = $likeModel->getLikeCount($id);

            return jsonSuccess([
                'likes' => $likeCount,
                'action' => $action
            ], $action === 'added' ? 'Like agregado' : 'Like removido');
        } else {
            return jsonError('Error al procesar el like');
        }
    }

    public function likeComment($id)
    {
        requireAuth();

        $userId = $_SESSION['user_id'];
        $likeModel = new CommentLikeModel();

        $hasLiked = $likeModel->hasLiked($id, $userId);

        if ($hasLiked) {
            $result = $likeModel->removeLike($id, $userId);
            $action = 'removed';
        } else {
            $result = $likeModel->addLike($id, $userId);
            $action = 'added';
        }

        if ($result) {
            $likeCount = $likeModel->getLikeCount($id);

            return jsonSuccess([
                'likes' => $likeCount,
                'action' => $action
            ], $action === 'added' ? 'Like agregado' : 'Like removido');
        } else {
            return jsonError('Error al procesar el like');
        }
    }

    public function likeReply($id)
    {
        requireAuth();

        $userId = $_SESSION['user_id'];
        $likeModel = new ReplyLikeModel();

        $hasLiked = $likeModel->hasLiked($id, $userId);

        if ($hasLiked) {
            $result = $likeModel->removeLike($id, $userId);
            $action = 'removed';
        } else {
            $result = $likeModel->addLike($id, $userId);
            $action = 'added';
        }

        if ($result) {
            $likeCount = $likeModel->getLikeCount($id);

            return jsonSuccess([
                'likes' => $likeCount,
                'action' => $action
            ], $action === 'added' ? 'Like agregado' : 'Like removido');
        } else {
            return jsonError('Error al procesar el like');
        }
    }



    public function addComment($postId)
    {
        requireAuth();

        $userId = $_SESSION['user_id'];
        $commentModel = new CommentModel();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);
            $comment = clean_input($input['comment'] ?? '');

            if (empty($comment)) {
                return jsonError('El comentario no puede estar vacío');
            }

            $safeComment = clean_input($comment);
            $commentId = $commentModel->createComment($postId, $userId, $safeComment);

            if ($commentId) {
                $newComment = $commentModel->getCommentById($commentId);
                $comments = $commentModel->getCommentsByPost($postId);
                $commentCount = $commentModel->getCommentCount($postId);

                return jsonSuccess([
                    'comment' => $newComment,
                    'comments' => $comments,
                    'comment_count' => $commentCount
                ], 'Comentario agregado');
            } else {
                return jsonError('Error al agregar el comentario');
            }
        }

        return jsonError('Método no permitido');
    }

    public function deleteComment($id)
    {
        requireAuth();

        $userId = $_SESSION['user_id'];
        $commentModel = new CommentModel();

        $comment = $commentModel->getCommentById($id);

        if (!$comment) {
            return jsonError('Comentario no encontrado');
        }

        $postId = $comment['post_id'];

        $result = $commentModel->deleteComment($id, $userId);

        if ($result) {
            $updatedCommentCount = $commentModel->getCommentCount($postId);

            return jsonSuccess([
                'comment_count' => $updatedCommentCount,
                'post_id' => $postId
            ], 'Comentario eliminado');
        } else {
            return jsonError('Error al eliminar el comentario o no tienes permisos');
        }
    }

    public function getComments($postId)
    {
        requireAuth();

        $commentModel = new CommentModel();
        $comments = $commentModel->getCommentsByPost($postId);
        $commentCount = $commentModel->getCommentCount($postId);

        return jsonSuccess([
            'comments' => $comments,
            'comment_count' => $commentCount
        ]);
    }

    public function addReply($commentId)
    {
        requireAuth();

        $userId = $_SESSION['user_id'];
        $replyModel = new ReplyModel();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);
            $reply = clean_input($input['reply'] ?? '');

            if (empty($reply)) {
                return jsonError('La respuesta no puede estar vacía');
            }

            $safeReply = clean_input($reply);
            $replyId = $replyModel->createReply($commentId, $userId, $safeReply);

            if ($replyId) {
                $newReply = $replyModel->getReplyById($replyId);
                $replies = $replyModel->getRepliesByComment($commentId);
                $replyCount = $replyModel->getReplyCount($commentId);

                $commentModel = new CommentModel();
                $comment = $commentModel->getCommentById($commentId);
                $postId = $comment['post_id'] ?? null;
                $postCommentCount = $postId ? $commentModel->getCommentCount($postId) : 0;

                return jsonSuccess([
                    'reply' => $newReply,
                    'replies' => $replies,
                    'reply_count' => $replyCount,
                    'post_comment_count' => $postCommentCount
                ], 'Respuesta agregada');
            } else {
                return jsonError('Error al agregar la respuesta');
            }
        }

        return jsonError('Método no permitido');
    }

    public function deleteReply($id)
    {
        requireAuth();

        $userId = $_SESSION['user_id'];
        $replyModel = new ReplyModel();

        $reply = $replyModel->getReplyById($id);

        if (!$reply) {
            return jsonError('Respuesta no encontrada');
        }

        $commentId = $reply['comment_id'];

        $result = $replyModel->deleteReply($id, $userId);

        if ($result) {
            $updatedReplyCount = $replyModel->getReplyCount($commentId);

            $commentModel = new CommentModel();
            $comment = $commentModel->getCommentById($commentId);
            $postId = $comment['post_id'] ?? null;
            $postCommentCount = $postId ? $commentModel->getCommentCount($postId) : 0;

            return jsonSuccess([
                'reply_count' => $updatedReplyCount,
                'comment_id' => $commentId,
                'post_comment_count' => $postCommentCount
            ], 'Respuesta eliminada');
        } else {
            return jsonError('Error al eliminar la respuesta o no tienes permisos');
        }
    }

    public function getReplies($commentId)
    {
        requireAuth();

        $replyModel = new ReplyModel();
        $replies = $replyModel->getRepliesByComment($commentId);
        $replyCount = $replyModel->getReplyCount($commentId);

        return jsonSuccess([
            'replies' => $replies,
            'reply_count' => $replyCount
        ]);
    }

    public function updateComment($id)
    {
        requireAuth();

        $userId = $_SESSION['user_id'];
        $commentModel = new CommentModel();

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            $input = $_POST;
        }

        $comment = clean_input($input['comment'] ?? ($input['content'] ?? ''));

        if (empty($comment)) {
            return jsonError('El comentario no puede estar vacio');
        }

        if (strlen($comment) > 500) {
            return jsonError('El comentario no puede tener mas de 500 caracteres');
        }

        $existing = $commentModel->getCommentById($id);
        if (!$existing) {
            return jsonError('Comentario no encontrado', 404);
        }

        if ($existing['user_id'] != $userId && !isAdmin()) {
            return jsonError('No tienes permisos para editar este comentario', 403);
        }

        $ok = $commentModel->updateComment((int) $id, (int) $existing['user_id'], $comment);
        if ($ok) {
            return jsonSuccess([
                'comment_id' => (int) $id,
                'content' => $comment,
                'is_edited' => true,
                'updated_at' => date('Y-m-d H:i:s')
            ], 'Comentario actualizado correctamente');
        } else {
            return jsonError('Error al actualizar el comentario');
        }
    }

    public function updateReply($id)
    {
        requireAuth();

        $userId = $_SESSION['user_id'];
        $replyModel = new ReplyModel();

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            $input = $_POST;
        }

        $reply = clean_input($input['reply'] ?? ($input['content'] ?? ''));

        if (empty($reply)) {
            return jsonError('La respuesta no puede estar vacia');
        }

        if (strlen($reply) > 500) {
            return jsonError('La respuesta no puede tener mas de 500 caracteres');
        }

        $existing = $replyModel->getReplyById($id);
        if (!$existing) {
            return jsonError('Respuesta no encontrada', 404);
        }

        if ($existing['user_id'] != $userId && !isAdmin()) {
            return jsonError('No tienes permisos para editar esta respuesta', 403);
        }

        $ok = $replyModel->updateReply((int) $id, (int) $existing['user_id'], $reply);
        if ($ok) {
            return jsonSuccess([
                'reply_id' => (int) $id,
                'content' => $reply,
                'is_edited' => true,
                'updated_at' => date('Y-m-d H:i:s')
            ], 'Respuesta actualizada correctamente');
        } else {
            return jsonError('Error al actualizar la respuesta');
        }
    }
}
