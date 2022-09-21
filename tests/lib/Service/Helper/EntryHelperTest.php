<?php declare(strict_types=1);

namespace Tests\Service\Helper;

use App\Database\Model\Entry;
use App\Database\Model\User;
use App\Database\Repository\EntryRepository;
use App\Exception\UserException\NotFoundException;
use App\Service\Helper\EntryHelper;
use App\Utility\Registry;
use PHPUnit\Framework\TestCase;

class EntryHelperTest extends TestCase
{
    private $entryRepository;

    public function setUp(): void
    {
        $this->entryRepository = $this->createMock(EntryRepository::class);

        Registry::set(EntryRepository::class, $this->entryRepository);
    }

    public function testGetEntryForUserNotFoundException(): void
    {
        $this->expectException(NotFoundException::class);

        $this->entryRepository
            ->method('getById')
            ->willReturn(null);

        $helper = new EntryHelper();
        $helper->getEntryForUser(5, 5);
    }

    public function testGetEntryForUserNotOwnedByUserException(): void
    {
        $this->expectException(NotFoundException::class);

        $mockUser = $this->createMock(User::class);
        $mockUser->method('getId')->willReturn(6);

        $mockEntry = $this->createMock(Entry::class);
        $mockEntry->method('getReferencedUser')->willReturn($mockUser);

        $this->entryRepository
            ->method('getById')
            ->willReturn($mockEntry);

        $helper = new EntryHelper();
        $helper->getEntryForUser(5, 5);
    }
}
