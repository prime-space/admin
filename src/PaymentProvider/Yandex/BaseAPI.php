<?php namespace App\PaymentProvider\Yandex;

class BaseAPI
{
    public static function sendRequest($url, $options = array(), $access_token = null)
    {
        if (strpos($url, "https") === false) {
            $full_url= Config::$MONEY_URL . $url;
        } else {
            $full_url = $url;
        }

        $curl = curl_init($full_url);
        if ($access_token !== null) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                "Authorization: Bearer " . $access_token
            ));
        }
        curl_setopt($curl, CURLOPT_USERAGENT, 'Yandex.Money.SDK/PHP');
        curl_setopt($curl, CURLOPT_POST, 1);
        $query = http_build_query($options);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $query);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        //curl_setopt($curl, CURLOPT_VERBOSE, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
        $body = curl_exec($curl);

        $result = new \StdClass();
        $result->status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $result->body = $body;
        curl_close($curl);

        return self::processResult($result);
    }
    protected static function processResult($result)
    {
        switch ($result->status_code) {
            case 400:
                throw new FormatError;
                break;
            case 401:
                throw new TokenError;
                break;
            case 403:
                throw new ScopeError;
                break;
            default:
                if ($result->status_code >= 500) {
                    throw new ServerError($result->status_code);
                } else {
                    return json_decode($result->body);
                }
        }
    }
}
