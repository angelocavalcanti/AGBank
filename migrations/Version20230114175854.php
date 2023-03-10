<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230114175854 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Criada entidade TipoConta e relacionamento N:1 entre CONTA e ela';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE tipo_conta (id INT AUTO_INCREMENT NOT NULL, tipo VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE conta ADD tipo_id INT NOT NULL');
        $this->addSql('ALTER TABLE conta ADD CONSTRAINT FK_485A16C3A9276E6C FOREIGN KEY (tipo_id) REFERENCES tipo_conta (id)');
        $this->addSql('CREATE INDEX IDX_485A16C3A9276E6C ON conta (tipo_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE conta DROP FOREIGN KEY FK_485A16C3A9276E6C');
        $this->addSql('DROP TABLE tipo_conta');
        $this->addSql('DROP INDEX IDX_485A16C3A9276E6C ON conta');
        $this->addSql('ALTER TABLE conta DROP tipo_id');
    }
}
