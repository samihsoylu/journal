<?php declare(strict_types=1);

namespace App\Database\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210307220104 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE categories (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, createdTimestamp INT NOT NULL, lastUpdatedTimestamp INT NOT NULL, userId INT NOT NULL, INDEX IDX_3AF3466864B64DCC (userId), UNIQUE INDEX unique_category_name (userId, name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE entries (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, createdTimestamp INT NOT NULL, lastUpdatedTimestamp INT NOT NULL, categoryId INT NOT NULL, userId INT NOT NULL, INDEX IDX_2DF8B3C59C370B71 (categoryId), INDEX IDX_2DF8B3C564B64DCC (userId), INDEX search_by_userid_categoryid_createdtimestamp (userId, categoryId, createdTimestamp), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(255) NOT NULL, password LONGTEXT NOT NULL, emailAddress VARCHAR(255) NOT NULL, privilegeLevel INT UNSIGNED DEFAULT 0 NOT NULL, encryptionKey LONGTEXT NOT NULL, createdTimestamp INT UNSIGNED NOT NULL, lastUpdatedTimestamp INT UNSIGNED NOT NULL, UNIQUE INDEX unique_username (username), UNIQUE INDEX unique_email (emailAddress), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE categories ADD CONSTRAINT FK_3AF3466864B64DCC FOREIGN KEY (userId) REFERENCES users (id)');
        $this->addSql('ALTER TABLE entries ADD CONSTRAINT FK_2DF8B3C59C370B71 FOREIGN KEY (categoryId) REFERENCES categories (id)');
        $this->addSql('ALTER TABLE entries ADD CONSTRAINT FK_2DF8B3C564B64DCC FOREIGN KEY (userId) REFERENCES users (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE entries DROP FOREIGN KEY FK_2DF8B3C59C370B71');
        $this->addSql('ALTER TABLE categories DROP FOREIGN KEY FK_3AF3466864B64DCC');
        $this->addSql('ALTER TABLE entries DROP FOREIGN KEY FK_2DF8B3C564B64DCC');
        $this->addSql('DROP TABLE categories');
        $this->addSql('DROP TABLE entries');
        $this->addSql('DROP TABLE users');
    }
}
