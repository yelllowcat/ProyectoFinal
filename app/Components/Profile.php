<?php

namespace App\Components;

class Profile
{
    private $viewState;
    private $userName;
    private $userBio;
    private $postCount;
    private $likesCount;
    private $userId;
    private $profilePicture;

    public function __construct($viewState = 'own', $userName = 'Usuario', $userBio = '', $postCount = 0, $likesCount = 0, $userId = null, $profilePicture = null)
    {
        $this->viewState = $viewState;
        $this->userName = htmlspecialchars($userName);
        $this->userBio = $userBio ? htmlspecialchars($userBio) : $this->getDefaultBio();
        $this->postCount = $postCount;
        $this->likesCount = $likesCount;
        $this->userId = $userId;
        $this->profilePicture = $profilePicture ?: '/assets/imagesProfile/default_avatar.png';
    }

    private function getDefaultBio()
    {
        if ($this->viewState === 'own') {
            return 'No tienes descripción de tu perfil, puedes agregar una en "Editar perfil"';
        } else {
            return 'Este usuario no tiene descripción de perfil.';
        }
    }

    private function getActionButtons()
    {
        if ($this->viewState === 'own') {
            return "
                <div class='profile-actions'>
                    <a href='/editProfile' class='btn btn-primary'>
                        Editar perfil
                    </a>
                </div>";
        } else {
            return "
                <div class='profile-actions'>
                    <button class='btn btn-secondary' onclick='followUser({$this->userId})' style='background: #6c757d;'>
                        <img src='/assets/images/follow.png' alt='Seguir' width='16'>
                        Seguir
                    </button>
                </div>";
        }
    }

    public function render()
    {
        $actionButtons = $this->getActionButtons();

        return "
<div class='profile-section'>
    <div class='profile-photo'>
        <img src='{$this->profilePicture}' alt='Foto de perfil de {$this->userName}' class='profile-avatar'>
    </div>
    <h2 class='profile-name'>{$this->userName}</h2>
    <p class='profile-bio'>{$this->userBio}</p>

    <div class='stats-container'>
        <div class='stat'>
            <div class='stat-number'>" . number_format($this->postCount) . "</div>
            <div class='stat-label'>Publicaciones</div>
        </div>
        <div class='stat'>
            <div class='stat-number'>" . number_format($this->likesCount) . "</div>
            <div class='stat-label'>Me gusta</div>
        </div>
        <div class='stat'>
            <div class='stat-number'>0</div>
            <div class='stat-label'>Seguidores</div>
        </div>
    </div>

    {$actionButtons}
</div>";
    }
}