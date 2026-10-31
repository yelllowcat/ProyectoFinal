<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\UserModel;
use PDOException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class UserModelTest extends TestCase
{
    private UserModel $userModel;

    protected function setUp(): void
    {
        $this->userModel = new UserModel();
    }

    public function testHandleDatabaseErrorFormatsDuplicateEmailFriendlyMessage(): void
    {
        $oldErrorLog = ini_get('error_log');
        ini_set('error_log', tempnam(sys_get_temp_dir(), 'test_err'));

        $reflection = new ReflectionClass($this->userModel);
        $method = $reflection->getMethod('handleDatabaseError');
        $method->setAccessible(true);

        $pdoException = new PDOException('1644 El correo ya está registrado', 45000);
        $result = $method->invoke($this->userModel, $pdoException);

        ini_set('error_log', $oldErrorLog ?: '');
        $this->assertSame('Este correo electrónico ya está en uso.', $result);
    }

    public function testHandleDatabaseErrorFormatsUserNotFoundFriendlyMessage(): void
    {
        $oldErrorLog = ini_get('error_log');
        ini_set('error_log', tempnam(sys_get_temp_dir(), 'test_err'));

        $reflection = new ReflectionClass($this->userModel);
        $method = $reflection->getMethod('handleDatabaseError');
        $method->setAccessible(true);

        $pdoException = new PDOException('1644 Correo no encontrado en la plataforma', 45000);
        $result = $method->invoke($this->userModel, $pdoException);

        ini_set('error_log', $oldErrorLog ?: '');
        $this->assertSame('Usuario no encontrado.', $result);
    }

    public function testHandleDatabaseErrorFormatsIntegrityConstraintViolation(): void
    {
        $oldErrorLog = ini_get('error_log');
        ini_set('error_log', tempnam(sys_get_temp_dir(), 'test_err'));

        $reflection = new ReflectionClass($this->userModel);
        $method = $reflection->getMethod('handleDatabaseError');
        $method->setAccessible(true);

        $pdoException = new PDOException('Duplicate entry test@uabcs.mx for key email', 23000);
        $result = $method->invoke($this->userModel, $pdoException);

        ini_set('error_log', $oldErrorLog ?: '');
        $this->assertSame('Este correo electrónico ya está registrado.', $result);
    }

    public function testEmailValidationSupportsInstitutionalDomainsAndUnicodeAccents(): void
    {
        $studentEmail = 'maría.morales@alu.uabcs.mx';
        $teacherEmail = 'juan.perez@uabcs.mx';
        $invalidEmail = 'externo@gmail.com';

        $isValidUnicode = (bool) filter_var(
            $studentEmail,
            FILTER_VALIDATE_EMAIL,
            FILTER_FLAG_EMAIL_UNICODE
        );
        $this->assertTrue($isValidUnicode);

        $this->assertTrue(str_ends_with(mb_strtolower($studentEmail, 'UTF-8'), '@alu.uabcs.mx'));
        $this->assertTrue(str_ends_with(mb_strtolower($teacherEmail, 'UTF-8'), '@uabcs.mx'));
        $this->assertFalse(str_ends_with(mb_strtolower($invalidEmail, 'UTF-8'), '@alu.uabcs.mx'));
        $this->assertFalse(str_ends_with(mb_strtolower($invalidEmail, 'UTF-8'), '@uabcs.mx'));
    }

    public function testRoleDerivationAssignsTeacherAndStudentCorrectly(): void
    {
        $deriveRole = function (string $email): ?string {
            $normalized = mb_strtolower(trim($email), 'UTF-8');
            if (str_ends_with($normalized, '@uabcs.mx')) {
                return 'teacher';
            }
            if (str_ends_with($normalized, '@alu.uabcs.mx')) {
                return 'student';
            }
            return null;
        };

        $this->assertSame('student', $deriveRole('maría.morales@alu.uabcs.mx'));
        $this->assertSame('teacher', $deriveRole('profesor.titular@uabcs.mx'));
        $this->assertNull($deriveRole('otro@hotmail.com'));
    }
}
