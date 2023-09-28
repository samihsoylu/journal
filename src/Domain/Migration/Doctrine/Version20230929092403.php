<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Domain\Migration\Doctrine;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230929092403 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE Category (id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', userId CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', name VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, position INT NOT NULL, createdAt DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updatedAt DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_FF3A7B9764B64DCC (userId), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE Entry (id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', userId CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', categoryId CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', title VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, createdAt DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updatedAt DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_EAE0B27464B64DCC (userId), INDEX IDX_EAE0B2749C370B71 (categoryId), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE Template (id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', userId CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', categoryId CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', title VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, createdAt DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updatedAt DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_6E167DD564B64DCC (userId), INDEX IDX_6E167DD59C370B71 (categoryId), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE User (id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', username VARCHAR(255) NOT NULL, password LONGTEXT NOT NULL, emailAddress VARCHAR(255) NOT NULL, role VARCHAR(255) NOT NULL, protectedKey LONGTEXT NOT NULL, preferredTimezone VARCHAR(255) DEFAULT NULL, createdAt DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updatedAt DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE Category ADD CONSTRAINT FK_FF3A7B9764B64DCC FOREIGN KEY (userId) REFERENCES User (id)');
        $this->addSql('ALTER TABLE Entry ADD CONSTRAINT FK_EAE0B27464B64DCC FOREIGN KEY (userId) REFERENCES User (id)');
        $this->addSql('ALTER TABLE Entry ADD CONSTRAINT FK_EAE0B2749C370B71 FOREIGN KEY (categoryId) REFERENCES Category (id)');
        $this->addSql('ALTER TABLE Template ADD CONSTRAINT FK_6E167DD564B64DCC FOREIGN KEY (userId) REFERENCES User (id)');
        $this->addSql('ALTER TABLE Template ADD CONSTRAINT FK_6E167DD59C370B71 FOREIGN KEY (categoryId) REFERENCES Category (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE Category DROP FOREIGN KEY FK_FF3A7B9764B64DCC');
        $this->addSql('ALTER TABLE Entry DROP FOREIGN KEY FK_EAE0B27464B64DCC');
        $this->addSql('ALTER TABLE Entry DROP FOREIGN KEY FK_EAE0B2749C370B71');
        $this->addSql('ALTER TABLE Template DROP FOREIGN KEY FK_6E167DD564B64DCC');
        $this->addSql('ALTER TABLE Template DROP FOREIGN KEY FK_6E167DD59C370B71');
        $this->addSql('DROP TABLE Category');
        $this->addSql('DROP TABLE Entry');
        $this->addSql('DROP TABLE Template');
        $this->addSql('DROP TABLE User');
    }
}
