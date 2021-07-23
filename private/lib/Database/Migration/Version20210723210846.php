<?php declare(strict_types=1);

namespace App\Database\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210723210846 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DROP INDEX search_by_userid_categoryid_createdtimestamp ON entries');
        $this->addSql('CREATE INDEX SearchBy_UserId_CategoryId_CreatedTimestamp_Title ON entries (userId, categoryId, createdTimestamp, title)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX SearchBy_UserId_CategoryId_CreatedTimestamp_Title ON entries');
        $this->addSql('CREATE INDEX search_by_userid_categoryid_createdtimestamp ON entries (userId, categoryId, createdTimestamp)');
    }
}
