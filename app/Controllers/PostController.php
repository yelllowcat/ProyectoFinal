<?php

namespace App\Controllers;

use App\Models\PostModel;

class PostController
{
    public function store()
    {
        requireAuth();

        $userId = $_SESSION['user_id'];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $content = clean_input($_POST['content'] ?? '');
            
            if (empty($content)) {
                flash('error', 'El contenido del post no puede estar vacío');
                redirect('/addPost');
            }
            
            $postModel = new PostModel();
            $result = $postModel->createPost($userId, $content, null);
            
            if ($result) {
                flash('success', 'Post publicado correctamente');
                redirect('/posts');
            } else {
                flash('error', 'Error al publicar el post');
                redirect('/addPost');
            }
        }
        
        redirect('/addPost');
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
        return ['success' => true];
    }

    public function unlike($id)
    {
        requireAuth();
        return ['success' => true];
    }

    public function addComment($postId)
    {
        requireAuth();
        $comment = $_POST['comment'] ?? '';
        return ['success' => true, 'message' => 'Comment added'];
    }

    public function deleteComment($id)
    {
        requireAuth();
        return ['success' => true, 'message' => 'Comment deleted'];
    }
}