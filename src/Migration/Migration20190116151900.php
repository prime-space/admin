<?php namespace App\Migration;

use Ewll\DBBundle\Migration\MigrationInterface;

class Migration20190116151900 implements MigrationInterface
{
    public function getDescription(): string
    {
        return 'Payment system bitcoin';
    }

    public function up(): string
    {
        return <<<SQL
UPDATE `paymentSystem` SET `id` = 10 WHERE id = 8;
SQL;
    }

    public function down(): string
    {
        return <<<SQL
UPDATE `paymentSystem` SET `id` = 8 WHERE id = 10;
SQL;
    }
}
