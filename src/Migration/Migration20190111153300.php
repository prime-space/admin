<?php namespace App\Migration;

use Ewll\DBBundle\Migration\MigrationInterface;

class Migration20190111153300 implements MigrationInterface
{
    public function getDescription(): string
    {
        return 'Payment system bitcoin';
    }

    public function up(): string
    {
        return <<<SQL
INSERT INTO `paymentSystem` (`id`, `name`, `config`) VALUES (8, 'bitcoin', '["ip","user","pass"]');
SQL;
    }

    public function down(): string
    {
        return <<<SQL
DELETE FROM paymentSystem WHERE id = 8;
SQL;
    }
}
