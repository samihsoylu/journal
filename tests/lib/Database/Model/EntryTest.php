<?php declare(strict_types=1);

namespace Tests\Database\Model;

use App\Database\Model\AbstractModel;
use App\Database\Model\Category;
use App\Database\Model\Entry;
use App\Database\Model\ModelInterface;
use App\Database\Model\User;
use PHPUnit\Framework\TestCase;

class EntryTest extends TestCase
{
    public function testThatWeCanGetFields(): void
    {
        $content = "# Meeting Jenny\nIs a comedic drama that... \n[Link](http://domain.com)";
        //$expectedContentAsMarkup = file_get_contents(__DIR__ . '/EntryTest/GetContentAsMarkup.txt');

        $entry = new Entry();
        $entry->setTitle('Meeting Jenni')
            ->setContent($content)
            ->setReferencedUser(new User())
            ->setReferencedCategory(new Category());

        // Checks if the user model extends AbstractModel and implements ModelInterface
        $this->assertInstanceOf(ModelInterface::class, $entry);
        $this->assertInstanceOf(AbstractModel::class, $entry);

        // Checks if set values equal the values we retrieve
        $this->assertEquals('Meeting Jenni', $entry->getTitle());
        $this->assertEquals($content, $entry->getContent());
        //$this->assertEquals($expectedContentAsMarkup, $entry->getContentAsMarkup());

        $this->assertInstanceOf(User::class, $entry->getReferencedUser());
        $this->assertInstanceOf(Category::class, $entry->getReferencedCategory());
    }
}
