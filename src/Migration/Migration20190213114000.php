<?php namespace App\Migration;

use Ewll\DBBundle\Migration\MigrationInterface;

class Migration20190213114000 implements MigrationInterface
{
    public function getDescription(): string
    {
        return 'Payment system exchanger';
    }

    public function up(): string
    {
        return <<<SQL
INSERT INTO `paymentSystem` (`id`, `name`, `config`) VALUES (13, 'exchanger', '["host","secret"]');
SQL;
    }

    public function down(): string
    {
        return <<<SQL
DELETE FROM paymentSystem WHERE id = 13;
SQL;
    }
}
