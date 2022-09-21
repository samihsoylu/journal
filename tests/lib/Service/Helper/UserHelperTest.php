<?php declare(strict_types=1);

namespace Tests\Service\Helper;

use App\Database\Repository\UserRepository;
use App\Exception\UserException\NotFoundException;
use App\Service\Helper\UserHelper;
use App\Utility\Registry;
use PHPUnit\Framework\TestCase;

class UserHelperTest extends TestCase
{
    private $userRepository;

    public function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepository::class);

        Registry::set(UserRepository::class, $this->userRepository);
    }

    public function testGetUserByIdNotFoundException(): void
    {
        $this->expectException(NotFoundException::class);

        $this->userRepository
            ->method('getById')
            ->willReturn(null);

        $helper = new UserHelper();
        $helper->getUserById(5);
    }
}
