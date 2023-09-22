<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Domain\Migration\Doctrine;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230923213906 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE Category (id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', name VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, position INT NOT NULL, createdAt DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updatedAt DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE Entry (id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', category_id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', user_id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', title VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, createdAt DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updatedAt DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_EAE0B27412469DE2 (category_id), INDEX IDX_EAE0B274A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE Template (id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', category_id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', user_id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', title VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, createdAt DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updatedAt DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_6E167DD512469DE2 (category_id), INDEX IDX_6E167DD5A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE User (id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', username VARCHAR(255) NOT NULL, password LONGTEXT NOT NULL, role VARCHAR(255) NOT NULL, encryptionKey LONGTEXT NOT NULL, preferredTimezone VARCHAR(255) DEFAULT NULL, createdAt DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updatedAt DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE Entry ADD CONSTRAINT FK_EAE0B27412469DE2 FOREIGN KEY (category_id) REFERENCES Category (id)');
        $this->addSql('ALTER TABLE Entry ADD CONSTRAINT FK_EAE0B274A76ED395 FOREIGN KEY (user_id) REFERENCES User (id)');
        $this->addSql('ALTER TABLE Template ADD CONSTRAINT FK_6E167DD512469DE2 FOREIGN KEY (category_id) REFERENCES Category (id)');
        $this->addSql('ALTER TABLE Template ADD CONSTRAINT FK_6E167DD5A76ED395 FOREIGN KEY (user_id) REFERENCES User (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE Entry DROP FOREIGN KEY FK_EAE0B27412469DE2');
        $this->addSql('ALTER TABLE Entry DROP FOREIGN KEY FK_EAE0B274A76ED395');
        $this->addSql('ALTER TABLE Template DROP FOREIGN KEY FK_6E167DD512469DE2');
        $this->addSql('ALTER TABLE Template DROP FOREIGN KEY FK_6E167DD5A76ED395');
        $this->addSql('DROP TABLE Category');
        $this->addSql('DROP TABLE Entry');
        $this->addSql('DROP TABLE Template');
        $this->addSql('DROP TABLE User');
    }
}
