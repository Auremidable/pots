<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210216101240 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE module (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, picture VARCHAR(255) NOT NULL, active TINYINT(1) DEFAULT \'0\' NOT NULL, title VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE guest (id INT AUTO_INCREMENT NOT NULL, event_id INT NOT NULL, user_id INT NOT NULL, status TINYINT(1) NOT NULL, INDEX IDX_ACB79A3571F7E88B (event_id), INDEX IDX_ACB79A35A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE event_module (id INT AUTO_INCREMENT NOT NULL, id_event_id INT NOT NULL, id_module_id INT NOT NULL, data LONGTEXT NOT NULL, INDEX IDX_3EBD517A212C041E (id_event_id), INDEX IDX_3EBD517A2FF709B6 (id_module_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE contact (id INT AUTO_INCREMENT NOT NULL, user1_id INT DEFAULT NULL, user2_id INT DEFAULT NULL, status INT NOT NULL, INDEX IDX_4C62E63856AE248B (user1_id), INDEX IDX_4C62E638441B8B65 (user2_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE guest ADD CONSTRAINT FK_ACB79A3571F7E88B FOREIGN KEY (event_id) REFERENCES event (id)');
        $this->addSql('ALTER TABLE guest ADD CONSTRAINT FK_ACB79A35A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE event_module ADD CONSTRAINT FK_3EBD517A212C041E FOREIGN KEY (id_event_id) REFERENCES event (id)');
        $this->addSql('ALTER TABLE event_module ADD CONSTRAINT FK_3EBD517A2FF709B6 FOREIGN KEY (id_module_id) REFERENCES module (id)');
        $this->addSql('ALTER TABLE contact ADD CONSTRAINT FK_4C62E63856AE248B FOREIGN KEY (user1_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE contact ADD CONSTRAINT FK_4C62E638441B8B65 FOREIGN KEY (user2_id) REFERENCES user (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON user (email)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE event_module DROP FOREIGN KEY FK_3EBD517A2FF709B6');
        $this->addSql('DROP TABLE contact');
        $this->addSql('DROP TABLE event_module');
        $this->addSql('DROP TABLE guest');
        $this->addSql('DROP TABLE module');
        $this->addSql('DROP INDEX UNIQ_8D93D649E7927C74 ON user');
    }
}
