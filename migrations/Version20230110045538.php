<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230110045538 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Criada a tabela GERENTE e incluído relacionamento 1:1 com a entidade AGENCIA';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE gerente (id INT AUTO_INCREMENT NOT NULL, nome VARCHAR(255) NOT NULL, cpf VARCHAR(15) NOT NULL, matricula VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE agencia ADD gerente_id INT NOT NULL');
        $this->addSql('ALTER TABLE agencia ADD CONSTRAINT FK_EB6C2B995AEA750D FOREIGN KEY (gerente_id) REFERENCES gerente (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_EB6C2B995AEA750D ON agencia (gerente_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE agencia DROP FOREIGN KEY FK_EB6C2B995AEA750D');
        $this->addSql('DROP TABLE gerente');
        $this->addSql('DROP INDEX UNIQ_EB6C2B995AEA750D ON agencia');
        $this->addSql('ALTER TABLE agencia DROP gerente_id');
    }
}
