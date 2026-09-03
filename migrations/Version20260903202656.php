<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260903202656 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5362ADF1DA4A75FF ON customer_company (tax_number)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3B1CE6A3551F0F81 ON customer_order (order_number)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D34A04ADF9038C4 ON product (sku)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_5362ADF1DA4A75FF');
        $this->addSql('DROP INDEX UNIQ_3B1CE6A3551F0F81');
        $this->addSql('DROP INDEX UNIQ_D34A04ADF9038C4');
    }
}
