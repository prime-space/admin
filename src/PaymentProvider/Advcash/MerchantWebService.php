<?php namespace App\PaymentProvider\Advcash;

use Exception;
use SoapClient;

class MerchantWebService extends SoapClient
{
    private static $classmap = [
        "getBalances" => GetBalancesMethod::class,
        "getBalancesResponse" => GetBalancesResponse::class,
    ];

    /**
     * Constructor using wsdl location and options array
     * @param string $wsdl WSDL location for this service
     * @param array $options Options for the SoapClient
     */
    public function __construct($wsdl = "https://wallet.advcash.com/wsm/merchantWebService?wsdl", $options = [])
    {
        foreach (self::$classmap as $wsdlClassName => $phpClassName) {
            if (!isset($options['classmap'][$wsdlClassName])) {
                $options['classmap'][$wsdlClassName] = $phpClassName;
            }
        }
        $options['location'] = 'https://wallet.advcash.com/wsm/merchantWebService';
        libxml_disable_entity_loader(false);
        parent::__construct($wsdl, $options);
    }

    public function getAuthenticationToken($securityWord)
    {
        $gmt = gmdate('Ymd:H');
        $token = hash("sha256", $securityWord . ':' . $gmt);
        return $token;
    }

    /**
     * Service Call: getBalances
     * Parameter options:
     * (getBalances) parameters
     * @param mixed,... See function description for parameter options
     * @return getBalancesResponse
     * @throws Exception invalid function signature message
     */
    public function getBalances($mixed = null) {
        $validParameters = [
            '('.GetBalancesMethod::class.')',
        ];
        $args = func_get_args();
        $this->_checkArguments($args, $validParameters);
        return $this->__soapCall('getBalances', $args);
    }

    /**
     * Checks if an argument list matches against a valid argument type list
     * @param array $arguments The argument list to check
     * @param array $validParameters A list of valid argument types
     * @return boolean true if arguments match against validParameters
     * @throws Exception invalid function signature message
     */
    public function _checkArguments($arguments, $validParameters) {
        $variables = "";
        foreach ($arguments as $arg) {
            $type = gettype($arg);
            if ($type == "object") {
                $type = get_class($arg);
            }
            $variables .= "(".$type.")";
        }
        if (!in_array($variables, $validParameters)) {
            throw new Exception("Invalid parameter types: ".str_replace(")(", ", ", $variables));
        }
        return true;
    }
}
