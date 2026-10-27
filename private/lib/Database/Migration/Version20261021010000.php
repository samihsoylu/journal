<?php

declare(strict_types=1);

namespace App\Database\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261021010000 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Add optional default entry titles to templates without changing existing data.';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE templates ADD defaultEntryTitlePattern VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE templates DROP defaultEntryTitlePattern');
    }
}
