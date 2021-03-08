<?php namespace App\Migration;

use Ewll\DBBundle\Migration\MigrationInterface;

class Migration20190205195300 implements MigrationInterface
{
    public function getDescription(): string
    {
        return 'Payment system mpay';
    }

    public function up(): string
    {
        return <<<SQL
INSERT INTO `paymentSystem` (`id`, `name`, `config`) VALUES (11, 'mpay', '["username","partnerId","apiKey","projectId","projectKey"]');
SQL;
    }

    public function down(): string
    {
        return <<<SQL
DELETE FROM paymentSystem WHERE id = 11;
SQL;
    }
}
