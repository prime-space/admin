<?php namespace App\Migration;

use Ewll\DBBundle\Migration\MigrationInterface;

class Migration20190422170400 implements MigrationInterface
{
    public function getDescription(): string
    {
        return 'Payment system mpay_card';
    }

    public function up(): string
    {
        return <<<SQL
INSERT INTO `paymentSystem` (`id`, `name`, `config`) VALUES (17, 'mpay_card', '["username","partnerId","apiKey","projectId","projectKey"]');
SQL;
    }

    public function down(): string
    {
        return <<<SQL
DELETE FROM paymentSystem WHERE id = 17;
SQL;
    }
}
