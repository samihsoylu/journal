<?php

declare(strict_types=1);

namespace App\Database\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210609111114 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE templates (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, createdTimestamp INT NOT NULL, lastUpdatedTimestamp INT NOT NULL, userId INT NOT NULL, categoryId INT NOT NULL, INDEX IDX_6F287D8E64B64DCC (userId), INDEX IDX_6F287D8E9C370B71 (categoryId), UNIQUE INDEX unique_template_title (userId, title), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE templates ADD CONSTRAINT FK_6F287D8E64B64DCC FOREIGN KEY (userId) REFERENCES users (id)');
        $this->addSql('ALTER TABLE templates ADD CONSTRAINT FK_6F287D8E9C370B71 FOREIGN KEY (categoryId) REFERENCES categories (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE templates');
    }
}
