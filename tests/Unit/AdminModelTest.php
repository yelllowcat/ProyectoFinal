<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\AdminModel;
use PDO;
use PHPUnit\Framework\TestCase;

class AdminModelTest extends TestCase
{
    private PDO $db;
    private AdminModel $adminModel;

    protected function setUp(): void
    {
        $this->db = new PDO('sqlite::memory:', null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        $this->db->sqliteCreateFunction('now', fn() => date('Y-m-d H:i:s'));

        $this->db->exec("
            CREATE TABLE users (
                user_id INTEGER PRIMARY KEY AUTOINCREMENT,
                full_name TEXT NOT NULL,
                email TEXT NOT NULL,
                role TEXT NOT NULL DEFAULT 'student',
                department TEXT,
                career TEXT,
                semester INTEGER,
                profile_picture TEXT,
                active INTEGER NOT NULL DEFAULT 1,
                registration_date TEXT NOT NULL,
                updated_at TEXT
            );

            CREATE TABLE posts (
                post_id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                content TEXT NOT NULL,
                active INTEGER NOT NULL DEFAULT 1
            );

            CREATE TABLE friends (
                user_id1 INTEGER NOT NULL,
                user_id2 INTEGER NOT NULL,
                status TEXT NOT NULL DEFAULT 'accepted'
            );
        ");

        $this->db->exec("
            INSERT INTO users (
                user_id, full_name, email, role, department,
                career, semester, active, registration_date
            ) VALUES
            (1, 'Admin Supremo', 'admin@uabcs.mx', 'admin',
             'Sistemas', 'ISC', 8, 1, '2026-01-01 10:00:00'),
            (2, 'María Morales', 'maría.morales@alu.uabcs.mx', 'student',
             'Sistemas', 'Ingeniería en Software', 6, 1, '2026-01-02 10:00:00'),
            (3, 'Dr. José Ramírez', 'j.ramirez@uabcs.mx', 'teacher',
             'Ciencias de la Tierra', 'Biología Marina', NULL, 1, '2026-01-03 10:00:00'),
            (4, 'Pedro Suspendido', 'pedro@alu.uabcs.mx', 'student',
             'Derecho', 'Derecho', 2, 0, '2026-01-04 10:00:00');

            INSERT INTO posts (user_id, content) VALUES
            (2, 'Hola mundo desde UABCS'),
            (2, 'Segundo post de María');

            INSERT INTO friends (user_id1, user_id2, status) VALUES
            (2, 3, 'accepted');
        ");

        global $pdo;
        $pdo = $this->db;
        $this->adminModel = new AdminModel();
    }

    public function testGetAllUsersForManagementExcludesAdmins(): void
    {
        $users = $this->adminModel->getAllUsersForManagement();
        $this->assertCount(3, $users);

        $roles = array_column($users, 'role');
        $this->assertNotContains('admin', $roles);
    }

    public function testGetAllUsersForManagementFiltersBySearch(): void
    {
        $searchName = $this->adminModel->getAllUsersForManagement('María');
        $this->assertCount(1, $searchName);
        $this->assertSame('María Morales', $searchName[0]['full_name']);
        $this->assertSame(2, (int) $searchName[0]['posts_count']);
        $this->assertSame(1, (int) $searchName[0]['friends_count']);

        $searchCareer = $this->adminModel->getAllUsersForManagement('Biología');
        $this->assertCount(1, $searchCareer);
        $this->assertSame('Dr. José Ramírez', $searchCareer[0]['full_name']);
    }

    public function testGetAllUsersForManagementFiltersByStatus(): void
    {
        $activeUsers = $this->adminModel->getAllUsersForManagement('', 'active');
        $this->assertCount(2, $activeUsers);
        foreach ($activeUsers as $u) {
            $this->assertSame(1, (int) $u['active']);
        }

        $suspendedUsers = $this->adminModel->getAllUsersForManagement('', 'suspended');
        $this->assertCount(1, $suspendedUsers);
        $this->assertSame('Pedro Suspendido', $suspendedUsers[0]['full_name']);
        $this->assertSame(0, (int) $suspendedUsers[0]['active']);
    }

    public function testGetAllUsersForManagementFiltersByRole(): void
    {
        $students = $this->adminModel->getAllUsersForManagement('', 'all', 'student');
        $this->assertCount(2, $students);

        $teachers = $this->adminModel->getAllUsersForManagement('', 'all', 'teacher');
        $this->assertCount(1, $teachers);
        $this->assertSame('Dr. José Ramírez', $teachers[0]['full_name']);
    }

    public function testSetUserActiveTogglesStatusAndProtectsAdmin(): void
    {
        // Suspend user 2
        $suspended = $this->adminModel->setUserActive(2, 0);
        $this->assertTrue($suspended);

        $user2 = $this->db->query("SELECT active FROM users WHERE user_id = 2")->fetch();
        $this->assertSame(0, (int) $user2['active']);

        // Reactivate user 4 (previously suspended)
        $reactivated = $this->adminModel->setUserActive(4, 1);
        $this->assertTrue($reactivated);

        $user4 = $this->db->query("SELECT active FROM users WHERE user_id = 4")->fetch();
        $this->assertSame(1, (int) $user4['active']);

        // Attempting to suspend admin user 1 must not modify the row
        $adminAttempt = $this->adminModel->setUserActive(1, 0);
        $user1 = $this->db->query("SELECT active FROM users WHERE user_id = 1")->fetch();
        $this->assertSame(1, (int) $user1['active']);
    }
}
