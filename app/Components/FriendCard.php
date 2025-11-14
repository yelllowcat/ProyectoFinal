<?php

namespace App\Components;

class FriendCard
{
    private string $id;
    private string $name;
    private string $joinDate;
    private string $status;
    private ?string $avatarUrl;

    public function __construct(string $id, string $name, string $joinDate, string $status = 'friend', ?string $avatarUrl = null)
    {
        $this->id = $id;
        $this->name = htmlspecialchars($name);
        $this->joinDate = htmlspecialchars($joinDate);
        $this->status = htmlspecialchars($status);
        $this->avatarUrl = $avatarUrl;
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
                    <button class='btn btn-primary btn-accept'>Aceptar</button>
                    <button class='btn btn-deny'>Eliminar</button>
                ";
            case 'send':
                return "
                    <button class='btn btn-primary btn-add'>Agregar</button>
                    <button class='btn btn-deny'>Eliminar</button>
                ";
            default:
                return "<button class='btn btn-view-profile'>Ver perfil</button>";
        }
    }
}
