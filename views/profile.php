<?php
namespace App\views;
use App\Components\Post;
use App\Components\Profile;
$userId = getCurrentUserId();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UNIRED - Perfil de Usuario</title>
    <link rel="stylesheet" href="/assets/styles/styles.css">
</head>

<body>
    <?php
    $currentPage = 'profile';
    require_once 'assets/sidebar.php';
    ?>
    <div class="main-content">
        <div class="content-wrapper">
            <?php $profile = new Profile();
            echo $profile->render();
            ?>

            <?php
            $posts = [
                [
                    'id' => 1,
                    'author' => 'Manuel Orozco',
                    'date' => '18/03/2025',
                    'image' => 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800&h=350&fit=crop',
                    'image_alt' => 'Paisaje montañoso',
                    'text' => 'Blandit habitasse eleifend himenaeos maecenas risus dui congue torquent, felis curae eros cubilia justo iaculis ornare, inceptos est arcu odio mus diam rhoncus. Orci tortor semper parturient nascetur venenatis porta cum nisi suscipit sagittis.',
                    'likes' => 200,
                    'comments_count' => 20,
                    'comments' => [
                        [
                            'author' => 'Gabriel Hernández',
                            'text' => 'Eres publicista o dentista',
                            'time' => 'Hace 2 hrs',
                            'date' => '5 de diciembre'
                        ],
                        [
                            'author' => 'Manuel Orozco',
                            'text' => 'Soy publicista o tecnicista',
                            'time' => 'Hace 2 hrs',
                            'date' => '5 de diciembre'
                        ]
                    ]
                ],
                [
                    'id' => 2,
                    'author' => 'Juanito Alimaña',
                    'date' => '10/03/2025',
                    'image' => 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800&h=350&fit=crop',
                    'image_alt' => 'Gran Cañón',
                    'text' => 'Blandit habitasse eleifend himenaeos maecenas risus dui congue torquent, felis curae eros cubilia justo iaculis ornare, inceptos est arcu odio mus diam rhoncus. Orci tortor semper parturient nascetur venenatis porta cum nisi suscipit sagittis.',
                    'likes' => 391,
                    'comments_count' => 12,
                    'comments' => [
                        [
                            'author' => 'Gabriel Hernández',
                            'text' => 'Eres publicista o dentista',
                            'time' => 'Hace 2 hrs',
                            'date' => '5 de diciembre'
                        ],
                        [
                            'author' => 'Manuel Orozco',
                            'text' => 'Soy publicista o tecnicista',
                            'time' => 'Hace 2 hrs',
                            'date' => '5 de diciembre'
                        ],
                        [
                            'author' => 'Gabriel Hernández',
                            'text' => 'Eres publicista o dentista',
                            'time' => 'Hace 2 hrs',
                            'date' => '5 de diciembre'
                        ],
                        [
                            'author' => 'Manuel Orozco',
                            'text' => 'Soy publicista o tecnicista',
                            'time' => 'Hace 2 hrs',
                            'date' => '5 de diciembre'
                        ],
                        [
                            'author' => 'Gabriel Hernández',
                            'text' => 'Eres publicista o dentista',
                            'time' => 'Hace 2 hrs',
                            'date' => '5 de diciembre'
                        ],
                        [
                            'author' => 'Manuel Orozco',
                            'text' => 'Soy publicista o tecnicista',
                            'time' => 'Hace 2 hrs',
                            'date' => '5 de diciembre'
                        ]
                    ]
                ],
                [
                    'id' => 3,
                    'author' => 'María González',
                    'date' => '05/03/2025',
                    'image' => 'https://images.unsplash.com/photo-1469474968028-56623f02e42e?w=800&h=350&fit=crop',
                    'image_alt' => 'Naturaleza',
                    'text' => 'Blandit habitasse eleifend himenaeos maecenas risus dui congue torquent, felis curae eros cubilia justo iaculis ornare, inceptos est arcu odio mus diam rhoncus. Orci tortor semper parturient nascetur venenatis porta cum nisi suscipit sagittis.',
                    'likes' => 95,
                    'comments_count' => 12,
                    'comments' => [
                        [
                            'author' => 'Gabriel Hernández',
                            'text' => 'Eres publicista o dentista',
                            'time' => 'Hace 2 hrs',
                            'date' => '5 de diciembre'
                        ],
                        [
                            'author' => 'Manuel Orozco',
                            'text' => 'Soy publicista o tecnicista',
                            'time' => 'Hace 2 hrs',
                            'date' => '5 de diciembre'
                        ]
                    ]
                ]
            ];

            foreach ($posts as $postData) {
                $post = new Post($postData);
                echo $post->render();
            }
            ?>

        </div>
        <dialog id="confirm-delete-modal" class="confirm-dialog" aria-labelledby="confirm-delete-title">
            <div class="confirm-box">
                <div class="confirm-head">
                    <h3 id="confirm-delete-title">Confirmar eliminación</h3>
                    <p class="confirm-subtitle">¿Estás seguro/a de que deseas eliminar a <span
                            class="friend-name">[Nombre del amigo]</span>?</p>
                </div>

                <div class="confirm-sep"></div>

                <form method="dialog" class="confirm-actions">
                    <button value="confirm" class="confirm-delete">Eliminar</button>
                    <button value="cancel" class="confirm-cancel">Cancelar</button>
                </form>
            </div>
        </dialog>
        <script src="../js/main.js"></script>
</body>

</html>