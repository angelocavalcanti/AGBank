<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230113060258 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Criado relacionamento 1:N entre AGENCIA e CONTA';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE conta (id INT AUTO_INCREMENT NOT NULL, agencia_id INT NOT NULL, numero VARCHAR(15) NOT NULL, saldo DOUBLE PRECISION NOT NULL, data_abertura DATETIME NOT NULL, INDEX IDX_485A16C3A6F796BE (agencia_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE conta ADD CONSTRAINT FK_485A16C3A6F796BE FOREIGN KEY (agencia_id) REFERENCES agencia (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE conta DROP FOREIGN KEY FK_485A16C3A6F796BE');
        $this->addSql('DROP TABLE conta');
    }
}
