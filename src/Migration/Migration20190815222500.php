<?php namespace App\Migration;

use Ewll\DBBundle\Migration\MigrationInterface;

class Migration20190815222500 implements MigrationInterface
{
    public function getDescription(): string
    {
        return 'Payment system enfins';
    }

    public function up(): string
    {
        return <<<SQL
INSERT INTO `paymentSystem` (`id`, `name`, `config`) VALUES (19, 'enfins', '["email","ident","secret"]');
SQL;
    }

    public function down(): string
    {
        return <<<SQL
DELETE FROM paymentSystem WHERE id = 19;
SQL;
    }
}
