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

            $safeContent = safe_output($content);
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
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];
        $maxSize = 5 * 1024 * 1024;

        if (!in_array($imageFile['type'], $allowedTypes)) {
            flash('error', 'Solo se permiten imágenes JPEG, PNG y GIF');
            return null;
        }

        if ($imageFile['size'] > $maxSize) {
            flash('error', 'La imagen no puede ser mayor a 5MB');
            return null;
        }

        $uploadDir = __DIR__ . '/../../assets/imagesPosts/';

        $extension = pathinfo($imageFile['name'], PATHINFO_EXTENSION);
        $fileName = microtime(true) . '_' . uniqid() . '.' . $extension;
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

            $safeContent = safe_output($content);
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

    public function unlike($id)
    {
        requireAuth();
        return ['success' => true];
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

            $safeComment = safe_output($comment);
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

            $safeReply = safe_output($reply);
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
}