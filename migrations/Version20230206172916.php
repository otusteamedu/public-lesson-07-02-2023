<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230206172916 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE "user" (id BIGSERIAL NOT NULL, first_name VARCHAR(100) NOT NULL, middle_name VARCHAR(100) NOT NULL, last_name VARCHAR(100) NOT NULL, age SMALLINT NOT NULL, user_type VARCHAR(10) NOT NULL, user_status VARCHAR(10) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN "user".user_type IS \'(DC2Type:user_type)\'');
        $this->addSql('COMMENT ON COLUMN "user".user_status IS \'(DC2Type:user_status)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE "user"');
    }
}
