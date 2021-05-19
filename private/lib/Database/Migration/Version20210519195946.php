<?php

declare(strict_types=1);

namespace App\Database\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210519195946 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE widgets (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, enabled TINYINT(1) NOT NULL, createdTimestamp INT NOT NULL, lastUpdatedTimestamp INT NOT NULL, userId INT NOT NULL, INDEX IDX_9D58E4C164B64DCC (userId), UNIQUE INDEX unique_widget_name (userId, name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE widgets ADD CONSTRAINT FK_9D58E4C164B64DCC FOREIGN KEY (userId) REFERENCES users (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE widgets');
    }
}
