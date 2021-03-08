<?php namespace App\Migration;

use Ewll\DBBundle\Migration\MigrationInterface;

class Migration20180619112000 implements MigrationInterface
{
    public function getDescription(): string
    {
        return 'add unique paymentAccount name';
    }

    public function up(): string
    {
        return <<<SQL
ALTER TABLE  `paymentAccount`
    ADD UNIQUE INDEX `name` (`name`);
SQL;
    }

    public function down(): string
    {
        return <<<SQL
ALTER TABLE  `paymentAccount`
    DROP INDEX `name`;
SQL;
    }
}
