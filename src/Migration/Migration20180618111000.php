<?php namespace App\Migration;

use Ewll\DBBundle\Migration\MigrationInterface;

class Migration20180618111000 implements MigrationInterface
{
    public function getDescription(): string
    {
        return 'Rename tables';
    }

    public function up(): string
    {
        return <<<SQL
RENAME TABLE  `payment_account` TO  `paymentAccount`;
RENAME TABLE  `payment_system` TO  `paymentSystem`;
RENAME TABLE  `user_session` TO  `userSession`;
SQL;
    }

    public function down(): string
    {
        return <<<SQL
RENAME TABLE  `paymentAccount` TO  `payment_account`;
RENAME TABLE  `paymentSystem` TO  `payment_system`;
RENAME TABLE  `userSession` TO  `user_session`;
SQL;
    }
}
