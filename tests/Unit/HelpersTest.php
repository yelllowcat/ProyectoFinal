<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class HelpersTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION = [];
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
    }

    public function testCleanInputStripsHtmlTagsAndTrimsWhitespace(): void
    {
        $raw = "  <script>alert('xss')</script>Hola Mundo <b>UABCS</b>  ";
        $cleaned = clean_input($raw);

        $this->assertSame("alert('xss')Hola Mundo UABCS", $cleaned);
    }

    public function testSafeOutputEscapesSpecialCharacters(): void
    {
        $raw = '<div class="test">' . "O'Reilly & Co</div>";
        $escaped = safe_output($raw);

        $this->assertSame(
            '&lt;div class=&quot;test&quot;&gt;O&#039;Reilly &amp; Co&lt;/div&gt;',
            $escaped
        );
    }

    public function testAuthSessionHelpersDetectLoggedUserAndRoles(): void
    {
        $this->assertFalse(isLoggedIn());
        $this->assertNull(getCurrentUserId());

        $_SESSION['user_id'] = 10;
        $_SESSION['user_role'] = 'student';

        $this->assertTrue(isLoggedIn());
        $this->assertSame(10, getCurrentUserId());
        $this->assertTrue(isRegularUser());
        $this->assertFalse(isAdmin());
        $this->assertTrue(isOwner(10));
        $this->assertFalse(isOwner(99));

        $_SESSION['user_role'] = 'admin';
        $this->assertTrue(isAdmin());
        $this->assertTrue(canEdit(99));
    }

    public function testFlashMessagesAreStoredAndClearedAfterRead(): void
    {
        $this->assertFalse(hasFlash());

        flash('success', 'Operación completada');
        $this->assertTrue(hasFlash());

        $messages = getFlash();
        $this->assertCount(1, $messages);
        $this->assertSame('success', $messages[0]['type']);
        $this->assertSame('Operación completada', $messages[0]['message']);
        $this->assertFalse(hasFlash());
    }

    public function testValidatePasswordStrengthRejectsWeakPasswords(): void
    {
        $short = validatePasswordStrength('Abc1');
        $this->assertSame('La contraseña debe tener al menos 8 caracteres.', $short);

        $noUpper = validatePasswordStrength('password123');
        $this->assertSame('La contraseña debe contener al menos una letra mayúscula.', $noUpper);

        $noNumber = validatePasswordStrength('PasswordOnly');
        $this->assertSame('La contraseña debe contener al menos un número.', $noNumber);

        $tooLong = validatePasswordStrength(str_repeat('A1', 35));
        $this->assertSame('La contraseña no puede exceder los 64 caracteres.', $tooLong);
    }

    public function testValidatePasswordStrengthAcceptsStrongPasswords(): void
    {
        $this->assertNull(validatePasswordStrength('Admin2026!'));
        $this->assertNull(validatePasswordStrength('Password123'));
        $this->assertNull(validatePasswordStrength('Uabcs_2026_Secure'));
    }

    public function testCsrfTokenGenerationAndValidation(): void
    {
        $this->assertEmpty($_SESSION['csrf_token'] ?? '');
        $token = generateCsrfToken();

        $this->assertNotEmpty($token);
        $this->assertSame(64, strlen($token));
        $this->assertTrue(validateCsrfToken($token));
        $this->assertFalse(validateCsrfToken('invalid_token'));
    }
}
