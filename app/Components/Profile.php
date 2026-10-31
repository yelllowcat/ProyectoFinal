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
    private ?string $department;
    private ?string $career;
    private ?int $semester;

    public function __construct(
        $viewState = 'own',
        $userName = 'Usuario',
        $userBio = '',
        $postCount = 0,
        $likesCount = 0,
        $friendsCount = 0,
        $userId = null,
        $profilePicture = null,
        $userRole = 'user',
        ?string $department = null,
        ?string $career = null,
        ?int $semester = null
    ) {
        $this->viewState = $viewState;
        $this->userName = htmlspecialchars($userName, ENT_QUOTES, 'UTF-8');
        $this->userBio = $userBio ? htmlspecialchars($userBio, ENT_QUOTES, 'UTF-8') : $this->getDefaultBio();
        $this->postCount = (int) $postCount;
        $this->likesCount = (int) $likesCount;
        $this->friendsCount = (int) $friendsCount;
        $this->userId = $userId;
        $this->profilePicture = $profilePicture;
        $this->userRole = $userRole;
        $this->department = $department;
        $this->career = $career;
        $this->semester = $semester !== null ? (int) $semester : null;
    }

    private function getDefaultBio(): string
    {
        if ($this->viewState === 'own') {
            return 'No tienes descripción de tu perfil aún. Puedes agregar una en "Editar perfil".';
        }
        return 'Este usuario no ha agregado una descripción de perfil.';
    }

    private function getActionButtons(): string
    {
        if ($this->viewState === 'own') {
            return "
                <div class='profile-actions'>
                    <a href='/editProfile' class='btn btn-primary profile-btn-edit'>
                        <svg width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round' aria-hidden='true'>
                            <path d='M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7'></path>
                            <path d='M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z'></path>
                        </svg>
                        <span>Editar perfil</span>
                    </a>
                    <a href='/settings' class='btn btn-profile-secondary'>
                        <svg width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' aria-hidden='true'>
                            <circle cx='12' cy='12' r='3'></circle>
                            <path d='M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z'></path>
                        </svg>
                        <span>Configuración</span>
                    </a>
                </div>";
        } elseif ($this->viewState === 'friend') {
            return "
                <div class='profile-actions'>
                    <span class='friendship-status-pill'>
                        <svg width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round' aria-hidden='true'>
                            <polyline points='20 6 9 17 4 12'></polyline>
                        </svg>
                        <span>Amigos</span>
                    </span>
                    <button type='button' class='btn btn-outline-danger profile-action-btn' data-action='remove' data-user-id='{$this->userId}'>
                        <svg width='15' height='15' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' aria-hidden='true'>
                            <path d='M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2'></path>
                            <circle cx='8.5' cy='7' r='4'></circle>
                            <line x1='18' y1='8' x2='23' y2='13'></line>
                            <line x1='23' y1='8' x2='18' y2='13'></line>
                        </svg>
                        <span>Eliminar amistad</span>
                    </button>
                    <button type='button' class='btn btn-ghost-report' onclick=\"openReportModal('user', {$this->userId})\" title='Reportar usuario'>
                        <svg width='15' height='15' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' aria-hidden='true'>
                            <path d='M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z'></path>
                            <line x1='4' y1='22' x2='4' y2='15'></line>
                        </svg>
                        <span>Reportar</span>
                    </button>
                </div>";
        } elseif ($this->viewState === 'request') {
            return "
                <div class='profile-actions'>
                    <button type='button' class='btn btn-primary profile-action-btn' data-action='accept' data-user-id='{$this->userId}'>
                        <svg width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round' aria-hidden='true'>
                            <polyline points='20 6 9 17 4 12'></polyline>
                        </svg>
                        <span>Aceptar solicitud</span>
                    </button>
                    <button type='button' class='btn btn-outline-danger profile-action-btn' data-action='reject' data-user-id='{$this->userId}'>
                        <svg width='15' height='15' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round' aria-hidden='true'>
                            <line x1='18' y1='6' x2='6' y2='18'></line>
                            <line x1='6' y1='6' x2='18' y2='18'></line>
                        </svg>
                        <span>Rechazar</span>
                    </button>
                </div>";
        } elseif ($this->viewState === 'pending') {
            return "
                <div class='profile-actions'>
                    <button type='button' class='btn btn-profile-secondary profile-action-btn' data-action='reject' data-user-id='{$this->userId}'>
                        <svg width='15' height='15' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' aria-hidden='true'>
                            <line x1='18' y1='6' x2='6' y2='18'></line>
                            <line x1='6' y1='6' x2='18' y2='18'></line>
                        </svg>
                        <span>Cancelar solicitud</span>
                    </button>
                    <button type='button' class='btn btn-ghost-report' onclick=\"openReportModal('user', {$this->userId})\" title='Reportar usuario'>
                        <svg width='15' height='15' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' aria-hidden='true'>
                            <path d='M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z'></path>
                            <line x1='4' y1='22' x2='4' y2='15'></line>
                        </svg>
                        <span>Reportar</span>
                    </button>
                </div>";
        } elseif ($this->viewState === 'stranger') {
            return "
                <div class='profile-actions'>
                    <button type='button' class='btn btn-primary profile-action-btn' data-action='add' data-user-id='{$this->userId}'>
                        <svg width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round' aria-hidden='true'>
                            <path d='M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2'></path>
                            <circle cx='8.5' cy='7' r='4'></circle>
                            <line x1='20' y1='8' x2='20' y2='14'></line>
                            <line x1='23' y1='11' x2='17' y2='11'></line>
                        </svg>
                        <span>Agregar amigo</span>
                    </button>
                    <button type='button' class='btn btn-ghost-report' onclick=\"openReportModal('user', {$this->userId})\" title='Reportar usuario'>
                        <svg width='15' height='15' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' aria-hidden='true'>
                            <path d='M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z'></path>
                            <line x1='4' y1='22' x2='4' y2='15'></line>
                        </svg>
                        <span>Reportar</span>
                    </button>
                </div>";
        }

        return "";
    }

    public function render(): string
    {
        $actionButtons = $this->getActionButtons();

        $academicPills = [];
        if (!empty($this->department)) {
            $safeDept = htmlspecialchars($this->department, ENT_QUOTES, 'UTF-8');
            $academicPills[] = "
                <div class='academic-chip' title='Departamento'>
                    <svg width='15' height='15' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' aria-hidden='true'>
                        <path d='M3 21h18'></path>
                        <path d='M5 21V7l7-4 7 4v14'></path>
                        <path d='M9 10h1'></path>
                        <path d='M9 14h1'></path>
                        <path d='M14 10h1'></path>
                        <path d='M14 14h1'></path>
                    </svg>
                    <span>{$safeDept}</span>
                </div>";
        }
        if (!empty($this->career)) {
            $safeCareer = htmlspecialchars($this->career, ENT_QUOTES, 'UTF-8');
            $academicPills[] = "
                <div class='academic-chip' title='Carrera'>
                    <svg width='15' height='15' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' aria-hidden='true'>
                        <path d='M22 10v6M2 10l10-5 10 5-10 5z'></path>
                        <path d='M6 12v5c3 3 9 3 12 0v-5'></path>
                    </svg>
                    <span>{$safeCareer}</span>
                </div>";
        }
        if (!empty($this->semester) && $this->userRole !== 'teacher') {
            $academicPills[] = "
                <div class='academic-chip' title='Semestre'>
                    <svg width='15' height='15' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' aria-hidden='true'>
                        <circle cx='12' cy='12' r='10'></circle>
                        <polyline points='12 6 12 12 16 14'></polyline>
                    </svg>
                    <span>{$this->semester}&deg; Semestre</span>
                </div>";
        }

        $academicInfoHtml = !empty($academicPills)
            ? "<div class='profile-academic-chips'>" . implode('', $academicPills) . "</div>"
            : "";

        $roleBadge = "";
        if ($this->userRole === 'teacher') {
            $roleBadge = "<span class='role-badge role-teacher'>Profesor</span>";
        } elseif ($this->userRole === 'student') {
            $roleBadge = "<span class='role-badge role-student'>Estudiante</span>";
        }

        $formattedPosts = number_format($this->postCount);
        $formattedLikes = number_format($this->likesCount);
        $formattedFriends = number_format($this->friendsCount);

        return "
<div class='profile-header-card'>
    <div class='profile-cover-banner'></div>
    <div class='profile-body'>
        <div class='profile-avatar-wrapper'>
            <img src='{$this->profilePicture}' alt='Foto de perfil de {$this->userName}' class='profile-avatar-img'>
        </div>

        <div class='profile-identity'>
            <div class='profile-name-row'>
                <h1 class='profile-display-name'>{$this->userName}</h1>
                {$roleBadge}
            </div>
            <p class='profile-biography'>{$this->userBio}</p>
            {$academicInfoHtml}
        </div>

        <div class='profile-stats-chips'>
            <div class='profile-stat-chip' title='Total de publicaciones'>
                <div class='stat-icon-wrap'>
                    <svg width='18' height='18' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round' aria-hidden='true'>
                        <rect x='3' y='3' width='18' height='18' rx='2' ry='2'></rect>
                        <line x1='3' y1='9' x2='21' y2='9'></line>
                        <line x1='9' y1='21' x2='9' y2='9'></line>
                    </svg>
                </div>
                <div class='stat-data'>
                    <span class='stat-number'>{$formattedPosts}</span>
                    <span class='stat-label'>Publicaciones</span>
                </div>
            </div>

            <div class='profile-stat-chip' title='Total de me gusta recibidos'>
                <div class='stat-icon-wrap stat-icon-heart'>
                    <svg width='18' height='18' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round' aria-hidden='true'>
                        <path d='M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z'></path>
                    </svg>
                </div>
                <div class='stat-data'>
                    <span class='stat-number'>{$formattedLikes}</span>
                    <span class='stat-label'>Me gusta</span>
                </div>
            </div>

            <div class='profile-stat-chip' title='Amigos conectados'>
                <div class='stat-icon-wrap stat-icon-friends'>
                    <svg width='18' height='18' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round' aria-hidden='true'>
                        <path d='M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2'></path>
                        <circle cx='9' cy='7' r='4'></circle>
                        <path d='M23 21v-2a4 4 0 0 0-3-3.87'></path>
                        <path d='M16 3.13a4 4 0 0 1 0 7.75'></path>
                    </svg>
                </div>
                <div class='stat-data'>
                    <span class='stat-number'>{$formattedFriends}</span>
                    <span class='stat-label'>Amigos</span>
                </div>
            </div>
        </div>

        {$actionButtons}
    </div>
</div>";
    }
}
