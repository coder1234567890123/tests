<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190122054317 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE users (id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', email VARCHAR(255) NOT NULL, password VARCHAR(60) NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, enabled TINYINT(1) NOT NULL, roles JSON NOT NULL COMMENT \'(DC2Type:json_array)\', token VARCHAR(255) DEFAULT NULL, token_requested DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_1483A5E9E7927C74 (email), UNIQUE INDEX UNIQ_1483A5E95F37A13B (token), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE countries (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(2) NOT NULL, name VARCHAR(100) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE subject_profiles (id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', subject_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', platform VARCHAR(255) NOT NULL, first_name VARCHAR(255) DEFAULT NULL, last_name VARCHAR(255) DEFAULT NULL, email_address VARCHAR(255) DEFAULT NULL, phone VARCHAR(255) DEFAULT NULL, link VARCHAR(255) NOT NULL, INDEX IDX_B34DC55823EDC87 (subject_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE subjects (id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', created_by_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', identification VARCHAR(255) NOT NULL, first_name VARCHAR(255) NOT NULL, middle_name VARCHAR(255) DEFAULT NULL, last_name VARCHAR(255) NOT NULL, maiden_name VARCHAR(255) DEFAULT NULL, nickname VARCHAR(255) DEFAULT NULL, handles JSON NOT NULL, date_of_birth DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', primary_email VARCHAR(255) NOT NULL, secondary_email VARCHAR(255) DEFAULT NULL, primary_mobile VARCHAR(255) NOT NULL, secondary_mobile VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_AB259917B03A8386 (created_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE subject_profiles ADD CONSTRAINT FK_B34DC55823EDC87 FOREIGN KEY (subject_id) REFERENCES subjects (id)');
        $this->addSql('ALTER TABLE subjects ADD CONSTRAINT FK_AB259917B03A8386 FOREIGN KEY (created_by_id) REFERENCES users (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE subjects DROP FOREIGN KEY FK_AB259917B03A8386');
        $this->addSql('ALTER TABLE subject_profiles DROP FOREIGN KEY FK_B34DC55823EDC87');
        $this->addSql('DROP TABLE users');
        $this->addSql('DROP TABLE countries');
        $this->addSql('DROP TABLE subject_profiles');
        $this->addSql('DROP TABLE subjects');
    }
}
