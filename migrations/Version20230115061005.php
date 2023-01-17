<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230115061005 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Criada entity TRANSACAO e relacionamento N:1 entre ela e CONTA';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE transacao (id INT AUTO_INCREMENT NOT NULL, destinatario_id INT NOT NULL, remetente VARCHAR(255) DEFAULT NULL, data DATETIME NOT NULL, valor DOUBLE PRECISION NOT NULL, descricao VARCHAR(255) NOT NULL, INDEX IDX_6C9E60CEB564FBC1 (destinatario_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE transacao ADD CONSTRAINT FK_6C9E60CEB564FBC1 FOREIGN KEY (destinatario_id) REFERENCES conta (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE transacao DROP FOREIGN KEY FK_6C9E60CEB564FBC1');
        $this->addSql('DROP TABLE transacao');
    }
}
