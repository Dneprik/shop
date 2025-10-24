<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251024205452 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE articles (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, price NUMERIC(10, 2) NOT NULL, supplier_email INT DEFAULT NULL, is_deleted TINYINT(1) DEFAULT 1 NOT NULL, created_at VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE customer_articles (id INT AUTO_INCREMENT NOT NULL, customer_id INT NOT NULL, article_id INT NOT NULL, purchased_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_931872479395C3F3 (customer_id), INDEX IDX_931872477294869C (article_id), UNIQUE INDEX ux_customer_article (customer_id, article_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE customer_subscriptions (id INT AUTO_INCREMENT NOT NULL, customer_id INT NOT NULL, subscription_id INT NOT NULL, started_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', status VARCHAR(16) NOT NULL, INDEX IDX_6012FA029A1887DC (subscription_id), UNIQUE INDEX ux_one_subscription_per_customer (customer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE customers (id INT AUTO_INCREMENT NOT NULL, phone VARCHAR(32) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX ux_customer_phone (phone), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE order_items (id INT AUTO_INCREMENT NOT NULL, order_id INT NOT NULL, article_id INT DEFAULT NULL, subscription_id INT DEFAULT NULL, price_at_purchase NUMERIC(10, 2) NOT NULL, INDEX IDX_62809DB08D9F6D38 (order_id), INDEX IDX_62809DB07294869C (article_id), INDEX IDX_62809DB09A1887DC (subscription_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE orders (id INT AUTO_INCREMENT NOT NULL, customer_id INT NOT NULL, order_number VARCHAR(50) NOT NULL, status VARCHAR(32) NOT NULL, total_price NUMERIC(10, 2) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_E52FFDEE9395C3F3 (customer_id), UNIQUE INDEX ux_order_number (order_number), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE subscription_packages (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, price NUMERIC(10, 2) NOT NULL, includes_physical_magazine TINYINT(1) NOT NULL, is_deleted TINYINT(1) DEFAULT 0 NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE customer_articles ADD CONSTRAINT FK_931872479395C3F3 FOREIGN KEY (customer_id) REFERENCES customers (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE customer_articles ADD CONSTRAINT FK_931872477294869C FOREIGN KEY (article_id) REFERENCES articles (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE customer_subscriptions ADD CONSTRAINT FK_6012FA029395C3F3 FOREIGN KEY (customer_id) REFERENCES customers (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE customer_subscriptions ADD CONSTRAINT FK_6012FA029A1887DC FOREIGN KEY (subscription_id) REFERENCES subscription_packages (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE order_items ADD CONSTRAINT FK_62809DB08D9F6D38 FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE order_items ADD CONSTRAINT FK_62809DB07294869C FOREIGN KEY (article_id) REFERENCES articles (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE order_items ADD CONSTRAINT FK_62809DB09A1887DC FOREIGN KEY (subscription_id) REFERENCES subscription_packages (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE orders ADD CONSTRAINT FK_E52FFDEE9395C3F3 FOREIGN KEY (customer_id) REFERENCES customers (id) ON DELETE RESTRICT');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE customer_articles DROP FOREIGN KEY FK_931872479395C3F3');
        $this->addSql('ALTER TABLE customer_articles DROP FOREIGN KEY FK_931872477294869C');
        $this->addSql('ALTER TABLE customer_subscriptions DROP FOREIGN KEY FK_6012FA029395C3F3');
        $this->addSql('ALTER TABLE customer_subscriptions DROP FOREIGN KEY FK_6012FA029A1887DC');
        $this->addSql('ALTER TABLE order_items DROP FOREIGN KEY FK_62809DB08D9F6D38');
        $this->addSql('ALTER TABLE order_items DROP FOREIGN KEY FK_62809DB07294869C');
        $this->addSql('ALTER TABLE order_items DROP FOREIGN KEY FK_62809DB09A1887DC');
        $this->addSql('ALTER TABLE orders DROP FOREIGN KEY FK_E52FFDEE9395C3F3');
        $this->addSql('DROP TABLE articles');
        $this->addSql('DROP TABLE customer_articles');
        $this->addSql('DROP TABLE customer_subscriptions');
        $this->addSql('DROP TABLE customers');
        $this->addSql('DROP TABLE order_items');
        $this->addSql('DROP TABLE orders');
        $this->addSql('DROP TABLE subscription_packages');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
