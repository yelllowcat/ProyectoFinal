<?php
namespace App\Components;
class Alert
{
    public static function render(): string
    {
        $html = '';

        if (function_exists('hasFlash') && hasFlash()) {
            foreach (getFlash() as $flashItem) {
                $type = htmlspecialchars($flashItem['type'] ?? 'info', ENT_QUOTES, 'UTF-8');
                $msg = htmlspecialchars($flashItem['message'] ?? '', ENT_QUOTES, 'UTF-8');
                $html .= "
            <div class='alert alert-{$type}'>
                {$msg}
            </div>
            ";
            }
        }

        if (isset($_SESSION['error'])) {
            $error = htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8');
            $html .= "
            <div class='alert alert-error'>
                {$error}
            </div>
            ";
            unset($_SESSION['error']);
        }

        if (isset($_SESSION['success'])) {
            $success = htmlspecialchars($_SESSION['success'], ENT_QUOTES, 'UTF-8');
            $html .= "
            <div class='alert alert-success'>
                {$success}
            </div>
            ";
            unset($_SESSION['success']);
        }

        return $html;
    }
}
?>