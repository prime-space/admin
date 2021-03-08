<?php namespace App\Migration;

use Ewll\DBBundle\Migration\MigrationInterface;

class Migration20180619121000 implements MigrationInterface
{
    public function getDescription(): string
    {
        return 'add secret to service';
    }

    public function up(): string
    {
        return <<<SQL
ALTER TABLE  `service`
    ADD COLUMN `secret` VARCHAR (64) DEFAULT '' NOT NULL AFTER `domain`;
SQL;
    }

    public function down(): string
    {
        return <<<SQL
ALTER TABLE  `service`
    DROP COLUMN `secret`;
SQL;
    }
}
