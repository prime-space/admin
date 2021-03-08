<?php namespace App\Entity;

use Ewll\DBBundle\Annotation as Db;

class Service
{
    const APP_TYPE_MERCHANT = 'merchant';
    const APP_TYPE_MARKETPLACE = 'marketplace';

    /** @Db\TinyIntType */
    public $id;
    /** @Db\IntType */
    public $responsibleUserId;
    /** @Db\VarcharType(length = 64) */
    public $domain;
    /** @Db\VarcharType(length = 64) */
    public $secret;
    /** @Db\VarcharType(length = 32) */
    public $appType;

    public static function create($domain, $secret): self
    {
        $item = new self();
        $item->domain = $domain;
        $item->secret = $secret;

        return $item;
    }

    public function compileJsConfigView(array $abilities, bool $isGranted): array
    {
        $serviceAbilitiesView = [
            'id' => $this->id,
            'domain' => $this->domain,
            'appType' => $this->appType,
            'abilities' => $abilities,
            'isGranted' => $isGranted,
        ];

        return $serviceAbilitiesView;
    }

    public function compileVueSelectView(): array
    {
        $view = [
            'value' => $this->id,
            'text' => $this->domain,
        ];

        return $view;
    }
}
