<?php namespace App\Migration;

use Ewll\DBBundle\Migration\MigrationInterface;

class Migration20190312143600 implements MigrationInterface
{
    public function getDescription(): string
    {
        return 'Payment system advcash';
    }

    public function up(): string
    {
        return <<<SQL
INSERT INTO `paymentSystem` (`id`, `name`, `config`) VALUES (15, 'advcash', '["email","apiName","apiPass","sciName","sciSecret"]');
SQL;
    }

    public function down(): string
    {
        return <<<SQL
DELETE FROM paymentSystem WHERE id = 15;
SQL;
    }
}
