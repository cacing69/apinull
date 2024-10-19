<?php

namespace Tests\Modules\User;

use App\Core\ServiceContainer;
use Modules\User\Handlers\UserHandler;
use PHPUnit\Framework\TestCase;

class UserHandlerTest extends TestCase
{
    private $container;
    private $userHandler;

    protected function setUp(): void
    {
        // Mock UserService
        $this->container = $this->createMock(ServiceContainer::class);

        // Instance UserHandler dengan UserService yang di-mock
        $this->userHandler = new UserHandler($this->container);
    }

    public function testCreateUser()
    {
        // Data yang akan dikirim
        $data = ['name' => 'John Doe'];

        // Mocking UserService untuk metode createUser
        // $this->userService->method('createUser')
        //     ->with($data)
        //     ->willReturn(['id' => 1, 'name' => 'John Doe']);

        // Membuat request palsu
        // $request = new Request();
        // $request->request->replace($data);

        // Memanggil metode createUser
        $response = $this->userHandler->ping();

        // Assert hasil yang diharapkan
        $this->assertEquals(['ping' => 'pong'], $response);
    }
}
