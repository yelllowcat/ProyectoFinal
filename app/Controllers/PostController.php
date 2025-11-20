<?php

namespace App\Controllers;

use App\Models\PostModel;
use App\Models\LikeModel;
use App\Models\CommentModel;

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
            $result = $postModel->createPost($userId, $content, $imageName);

            if ($result) {
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

            if (empty($content)) {
                return jsonError('El contenido del post no puede estar vacío');
            }

            $postModel = new PostModel();
            $post = $postModel->getPostById($id);

            if (!$post || $post['user_id'] != $userId) {
                return jsonError('No tienes permisos para editar este post', 403);
            }

            $result = $postModel->updatePost($id, $content, $post['image']);

            if ($result) {
                return jsonSuccess(null, 'Post actualizado correctamente');
            } else {
                return jsonError('Error al actualizar el post');
            }
        }

        return jsonError('Método no permitido');
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

            $commentId = $commentModel->createComment($postId, $userId, $comment);

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

        $result = $commentModel->deleteComment($id, $userId);

        if ($result) {
            return jsonSuccess(null, 'Comentario eliminado');
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
}