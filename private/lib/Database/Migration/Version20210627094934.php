<?php

declare(strict_types=1);

namespace App\Database\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

final class Version20210627094934 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE categories ADD sortOrder INT NOT NULL DEFAULT 0;');
    }

    #[Override]
    public function down(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE categories DROP sortOrder');
    }
}
