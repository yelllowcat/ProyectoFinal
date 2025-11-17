<?php

namespace App\Components;

class Profile
{
    private $viewState;
    private $userName;
    private $userBio;
    private $friendsCount;
    private $likesCount;

    public function __construct($viewState = 'own', $userName = 'Manuel Orozco', $userBio = '', $friendsCount = 2100, $likesCount = 187)
    {
        $this->viewState = $viewState;
        $this->userName = $userName;
        $this->userBio = $userBio ?: 'No tienes decripcion de tu perfil, puedes agregar una en "Editar perfil"';
        $this->friendsCount = $friendsCount;
        $this->likesCount = $likesCount;
    }

    private function getActionButtons()
    {
        switch ($this->viewState) {
            case 'own':
                return "
                    <div class='action-buttons'>
                        <a href='/editProfile/'>
                            <button class='btn btn-primary'>Editar perfil</button>
                        </a>
                    </div>";

            case 'friend':
                return "
                    <div class='action-buttons'>
                        <button class='btn btn-delete' onclick='deleteFriend()'>Eliminar amigo</button>
                    </div>";

            case 'stranger':
                return "
                    <div class='action-buttons'>
                        <button class='btn btn-secondary' onclick='sendFriendRequest()'>Enviar solicitud</button>
                    </div>";

            case 'pending':
                return "
                    <div class='action-buttons'>
                        <button class='btn btn-secondary' onclick='acceptRequest()'>Aceptar solicitud</button>
                        <button class='btn btn-deny' onclick='declineRequest()'>Rechazar solicitud</button>
                    </div>";

            default:
                return "";
        }
    }

    public function render()
    {
        $actionButtons = $this->getActionButtons();

        return "
<div class='profile-section'>
    <div class='profile-photo'></div>
    <h2 class='profile-name'>{$this->userName}</h2>
    <p class='profile-bio'>
        {$this->userBio}
    </p>

    <div class='stats-container'>
        <div class='stat'>
            <div class='stat-number'>" . number_format($this->friendsCount) . "</div>
            <div class='stat-label'>Amigos</div>
        </div>
        <div class='stat'>
            <div class='stat-number'>{$this->likesCount}</div>
            <div class='stat-label'>Me gusta</div>
        </div>
    </div>

    {$actionButtons}
</div>";
    }
}