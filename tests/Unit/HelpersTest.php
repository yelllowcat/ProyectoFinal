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
}
