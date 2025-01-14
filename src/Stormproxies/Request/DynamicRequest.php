<?php

namespace Weijiajia\IpProxyManager\Stormproxies\Request;

use Weijiajia\IpProxyManager\BaseDto;
use Weijiajia\IpProxyManager\Exception\ProxyException;
use Weijiajia\IpProxyManager\ProxyResponse;
use Weijiajia\IpProxyManager\Stormproxies\DTO\DynamicDto;
use Illuminate\Support\Collection;
use Saloon\Enums\Method;
use Weijiajia\IpProxyManager\Request;
use Saloon\Http\Response;

class DynamicRequest extends Request
{
    /**
     * The HTTP method of the request
     */
    protected Method $method = Method::GET;

    public function __construct(DynamicDto $dto)
    {
        parent::__construct($dto);

        if (empty($this->dto->get('app_key'))) {
            throw new \InvalidArgumentException("请配置代理 key");
        }
    }

    /**
     * @param Response $response
     * @return mixed
     * @throws \JsonException|ProxyException
     */
    public function createDtoFromResponse(Response $response): BaseDto
    {
        $data = $response->json();
        if (empty($data['data']['list'])){
            throw new ProxyException($response,$response->body());
        }

         $this->dto->setProxyList((new Collection($response->json()['data']['list'] ?? []))->map(function(string $item){

            [$host, $port] = explode(':', $item);

            return new ProxyResponse(
                host: $host,
                port: $port,
                url: $item,
            );
        }));

        return $this->dto;
    }

    protected function defaultQuery(): array
    {
        return $this->dto->toQueryParameters();
    }


    /**
     * The endpoint for the request
     */
    public function resolveEndpoint(): string
    {
        return '/web_v1/ip/get-ip';
    }
}
