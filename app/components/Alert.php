<?php
class Alert
{
    public static function render(): string
    {
        $html = '';

        if (isset($_SESSION['error'])) {
            $error = htmlspecialchars($_SESSION['error']);
            $html .= "
            <div class='alert alert-error'>
                {$error}
            </div>
            ";
            unset($_SESSION['error']);
        }

        if (isset($_SESSION['success'])) {
            $success = htmlspecialchars($_SESSION['success']);
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