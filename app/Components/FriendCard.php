<?php

namespace App\Components;

class FriendCard
{
    private string $id;
    private string $name;

    private string $joinDate;
    private string $status;

    private ?string $avatarUrl;
    private ?string $requestId;

    private ?string $suggestionId;
    private ?string $email;


    public function __construct(string $id, string $name, string $joinDate, string $status = 'friend', ?string $avatarUrl = null, ?string $requestId = null, ?string $email = null)
    {
        $this->id = $id;
        $this->name = htmlspecialchars($name);
        $this->joinDate = htmlspecialchars($joinDate);
        $this->status = htmlspecialchars($status);
        $this->avatarUrl = $avatarUrl;
        $this->requestId = $requestId;
        $this->email = $email ? htmlspecialchars($email) : null;
    }

    public function render(): string
    {
        $buttons = $this->getButtons();
        $avatarStyle = $this->avatarUrl
            ? "background-image: url('" . htmlspecialchars($this->avatarUrl, ENT_QUOTES) . "');"
            : '';

        return "
        <div class='friend-card' data-status='{$this->status}'>
            <a href='/profile/{$this->id}'>
            <div class='friend-avatar' style=\"{$avatarStyle}\"></div>
            <h3 class='friend-name'>{$this->name}</h3>
            <p class='friend-date'>Se unió el: {$this->joinDate}</p>
            </a>
            <div class='friend-actions'>
                {$buttons}
            </div>
        </div>
        ";
    }

    private function getButtons(): string
    {
        switch ($this->status) {
            case 'request':
                return "
                    <button data-request-id='{$this->requestId}' data-action='accept' class='btn btn-primary btn-accept btn-action'>Aceptar</button>
                    <button data-request-id='{$this->requestId}' data-action='deny' class='btn btn-deny btn-action'>Eliminar</button>
                ";
            case 'suggestion':
                return "
                    <button data-suggestion-id='{$this->id}' data-action='add' class='btn btn-primary btn-add btn-action'>Agregar</button>
                    <button data-suggestion-id='{$this->id}' data-action='deny' class='btn btn-deny btn-action'>Eliminar</button>
                ";
            default:
                return "<a href='/profile/{$this->id}'><button data-user-id='{$this->id}' data-action='view' class='btn btn-view-profile btn-action'>Ver perfil</button></a>";
        }
    }
}
