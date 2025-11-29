<?php

declare(strict_types=1);

namespace App\Database\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

final class Version20230209195153 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE users ADD timezone VARCHAR(255) DEFAULT NULL');
    }

    #[Override]
    public function down(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE users DROP timezone');
    }
}
