<?php namespace App\Service;

use App\Entity\Service;
use App\Service\Exception\BadRequestException;
use App\Service\Exception\ServiceClientRequestException;
use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\HttpFoundation\Response;

class ServiceClient
{
    const REQUEST_METHOD_GET = 'get';
    const REQUEST_METHOD_POST = 'post';
    const REQUEST_METHOD_PUT = 'put';
    const REQUEST_METHOD_DELETE = 'delete';

    private $guzzle;
    private $logger;

    public function __construct(
        Guzzle $guzzle,
        Logger $logger
    ) {
        $this->guzzle = $guzzle;
        $this->logger = $logger;
    }

    /**
     * @throws BadRequestException
     * @throws ServiceClientRequestException
     */
    public function request(Service $service, string $route, string $method, $formParams = []): array
    {
        try {
            $url = "https://{$service->domain}/adminApi{$route}";
            $options = [
                'timeout' => 5,
                'connect_timeout' => 5,
                'headers' => [
                    'Authorization' => "Bearer $service->secret",
                ],
            ];
            if (count($formParams) > 0) {
                $options['form_params'] = ['form' => $formParams];
            }
            $request = $this->guzzle->request($method, $url, $options);
            $content = $request->getBody()->getContents();
            $result = json_decode($content, true);

            return $result;
        } catch (GuzzleException|RequestException $e) {
            $response = $e->getResponse();
            $statusCode = null === $response ? null : $response->getStatusCode();

            if (Response::HTTP_BAD_REQUEST === $statusCode) {
                $content = $response->getBody()->getContents();
                $result = json_decode($content, true);
                throw new BadRequestException($result['errors'], $e);
            } else {
                $this->logger->crit("RequestException: {$e->getMessage()}", ['url' => $url]);
                throw new ServiceClientRequestException($e);
            }
        }

    }
}
