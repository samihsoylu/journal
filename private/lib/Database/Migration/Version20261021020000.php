<?php

declare(strict_types=1);

namespace App\Database\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261021020000 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Add optional authenticator settings and one-use recovery hashes; existing accounts remain password-only.';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('CREATE TABLE user_two_factor (userId INT NOT NULL, encryptedSecret TEXT DEFAULT NULL, lastCounter BIGINT DEFAULT NULL, revision CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL, PRIMARY KEY(userId), CONSTRAINT FK_two_factor_user FOREIGN KEY (userId) REFERENCES users (id) ON DELETE CASCADE) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_two_factor_recovery (userId INT NOT NULL, codeHash CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL, revision CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL, PRIMARY KEY(userId, codeHash), CONSTRAINT FK_two_factor_recovery_user FOREIGN KEY (userId) REFERENCES user_two_factor (userId) ON DELETE CASCADE) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('DROP TABLE user_two_factor_recovery');
        $this->addSql('DROP TABLE user_two_factor');
    }
}
