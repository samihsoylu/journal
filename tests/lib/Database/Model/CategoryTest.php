<?php

declare(strict_types=1);

namespace Tests\Database\Model;

use App\Database\Model\AbstractModel;
use App\Database\Model\Category;
use App\Database\Model\ModelInterface;
use App\Database\Model\User;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class CategoryTest extends TestCase
{
    public function testThatWeCanGetFields() : void
    {
        $expectedCategoryName = 'Dreams';
        $expectedCategoryDescription = 'Recording dream experiences allow you to start analyzing what your dreams mean';

        $category = new Category();
        $category->setName($expectedCategoryName);
        $category->setDescription($expectedCategoryDescription);
        $category->setReferencedUser(new User());

        // Checks if the category model extends AbstractModel and implements ModelInterface
        self::assertInstanceOf(ModelInterface::class, $category);
        self::assertInstanceOf(AbstractModel::class, $category);

        // Checks if set values equal the values we retrieve
        self::assertSame($expectedCategoryName, $category->getName());
        self::assertSame($expectedCategoryDescription, $category->getDescription());

        self::assertInstanceOf(User::class, $category->getReferencedUser());
    }
}
