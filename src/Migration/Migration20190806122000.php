<?php namespace App\Migration;

use Ewll\DBBundle\Migration\MigrationInterface;

class Migration20190806122000 implements MigrationInterface
{
    public function getDescription(): string
    {
        return 'Payment system unitpay';
    }

    public function up(): string
    {
        return <<<SQL
INSERT INTO `paymentSystem` (`id`, `name`, `config`) VALUES (18, 'unitpay', '["email","secretKey","projectPublicKey","projectSecretKey"]');
SQL;
    }

    public function down(): string
    {
        return <<<SQL
DELETE FROM paymentSystem WHERE id = 18;
SQL;
    }
}
