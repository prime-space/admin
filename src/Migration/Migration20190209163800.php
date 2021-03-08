<?php namespace App\Migration;

use Ewll\DBBundle\Migration\MigrationInterface;

class Migration20190209163800 implements MigrationInterface
{
    public function getDescription(): string
    {
        return 'Payment system free-kassa';
    }

    public function up(): string
    {
        return <<<SQL
INSERT INTO `paymentSystem` (`id`, `name`, `config`) VALUES (12, 'freekassa', '["merchantId","secret","secret2"]');
SQL;
    }

    public function down(): string
    {
        return <<<SQL
DELETE FROM paymentSystem WHERE id = 12;
SQL;
    }
}
