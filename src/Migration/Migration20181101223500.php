<?php namespace App\Migration;

use Ewll\DBBundle\Migration\MigrationInterface;

class Migration20181101223500 implements MigrationInterface
{
    public function getDescription(): string
    {
        return 'payoutMethod';
    }

    public function up(): string
    {
        return <<<SQL
CREATE TABLE `payoutMethod` (
    `id` TINYINT(3) UNSIGNED NOT NULL AUTO_INCREMENT,
    `paymentSystemId` TINYINT(3) UNSIGNED NOT NULL,
    `name` VARCHAR(64) NOT NULL,
    `balanceKey` VARCHAR(64) NOT NULL,
    PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

INSERT INTO `payoutMethod`
    (`id`, `paymentSystemId`, `name`, `balanceKey`)
VALUES
    (1, 1, 'yandex', '0'),
    (2, 3, 'qiwi', '0'),
    (3, 6, 'webmoney-r', 'wmr');
SQL;
    }

    public function down(): string
    {
        return <<<SQL
DROP TABLE payoutMethod;
SQL;
    }
}
