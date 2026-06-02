<?php

namespace App\Components;

class Profile
{
    private $viewState;
    private $userName;
    private $userBio;
    private $postCount;
    private $likesCount;
    private $friendsCount;
    private $userId;
    private $profilePicture;
    private $userRole;

    public function __construct($viewState = 'own', $userName = 'Usuario', $userBio = '', $postCount = 0, $likesCount = 0, $friendsCount = 0, $userId = null, $profilePicture = null, $userRole = 'user')
    {
        $this->viewState = $viewState;
        $this->userName = htmlspecialchars($userName);
        $this->userBio = $userBio ? htmlspecialchars($userBio) : $this->getDefaultBio();
        $this->postCount = $postCount;
        $this->likesCount = $likesCount;
        $this->friendsCount = $friendsCount;
        $this->userId = $userId;
        $this->profilePicture = $profilePicture;
        $this->userRole = $userRole;
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
        } else if ($this->viewState === 'friend') {
            return "
                <div class='profile-actions'>
                    <button class='btn btn-remove profile-action-btn' data-action='remove' data-user-id='{$this->userId}'>
                        Eliminar amistad
                    </button>
                    <button class='btn btn-remove profile-action-btn' style='margin-left: 10px;' onclick=\"openReportModal('user', {$this->userId})\">
                        Reportar usuario
                    </button>
                </div>";
        } else if ($this->viewState === 'request') {
            return "
                <div class='profile-actions' style='display: flex; gap: 10px; justify-content: space-evenly;'>
                    <button class='btn btn-primary profile-action-btn' data-action='accept' data-user-id='{$this->userId}'>
                        Aceptar solicitud
                    </button>
                    <button class='btn btn-remove profile-action-btn' data-action='reject' data-user-id='{$this->userId}'>
                        Rechazar solicitud
                    </button>
                </div>";
        } else if ($this->viewState === 'pending') {
            return "
                <div class='profile-actions'>
                    <button class='btn btn-primary profile-action-btn' data-action='reject' data-user-id='{$this->userId}'>
                        Cancelar solicitud
                    </button>
                    <button class='btn btn-remove profile-action-btn' style='margin-left: 10px;' onclick=\"openReportModal('user', {$this->userId})\">
                        Reportar usuario
                    </button>
                </div>";
        } else if ($this->viewState === 'stranger') {
            return "
                <div class='profile-actions'>
                    <button class='btn btn-primary profile-action-btn' data-action='add' data-user-id='{$this->userId}'>
                        Agregar amigo
                    </button>
                    <button class='btn btn-remove profile-action-btn' style='margin-left: 10px;' onclick=\"openReportModal('user', {$this->userId})\">
                        Reportar usuario
                    </button>
                </div>";
        }
        
        return "";
    }

    public function render()
    {
        $actionButtons = $this->getActionButtons();

        $roleBadge = "";
        if ($this->userRole === 'teacher') {
            $roleBadge = "<span class='role-badge role-teacher'>Profesor</span>";
        } elseif ($this->userRole === 'student') {
            $roleBadge = "<span class='role-badge role-student'>Estudiante</span>";
        }

        return "
<div class='profile-section'>
    <div class='profile-photo'>
        <img src='{$this->profilePicture}' alt='Foto de perfil de {$this->userName}' class='profile-avatar'>
    </div>
    <h2 class='profile-name' style='display:flex; justify-content:center; align-items:center;'>{$this->userName} {$roleBadge}</h2>
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
            <div class='stat-number'>" . number_format($this->friendsCount) . "</div>
            <div class='stat-label'>Amigos</div>
        </div>
    </div>

    {$actionButtons}
</div>";
    }
}