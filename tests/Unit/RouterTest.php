<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Router;
use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{
    protected function tearDown(): void
    {
        unset($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
    }

    public function testGetRouteDispatchesMatchingCallbackWithParams(): void
    {
        $router = new Router();
        $capturedId = null;

        $router->get('/profile/:id', function (string $id) use (&$capturedId): string {
            $capturedId = $id;
            return "user-{$id}";
        });

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/profile/42/';

        ob_start();
        $router->dispatch();
        $output = ob_get_clean();

        $this->assertSame('42', $capturedId);
        $this->assertSame('user-42', $output);
    }

    public function testDispatchReturns404WhenRouteDoesNotExist(): void
    {
        $router = new Router();
        $router->get('/known', fn(): string => 'ok');

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/unknown-route';

        ob_start();
        $router->dispatch();
        $output = ob_get_clean();

        $this->assertStringContainsString('404', (string) $output);
    }
}
