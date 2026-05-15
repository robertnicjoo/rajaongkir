<?php

namespace Nicxonsolutions\Rajaongkir\Api;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Nicxonsolutions\Rajaongkir\Exceptions\RajaongkirException;

class Client
{
    public function __construct(private readonly array $config = [])
    {
    }

    public function get(string $endpoint, array $query = []): array
    {
        return $this->parse(
            $this->request()->get($this->url($endpoint), $query)->json() ?? []
        );
    }

    public function post(string $endpoint, array $payload = []): array
    {
        return $this->parse(
            $this->request()->asForm()->post($this->url($endpoint), $payload)->json() ?? []
        );
    }

    private function request(): PendingRequest
    {
        $apiKey = $this->config['api_key'] ?? null;

        if (!$apiKey) {
            throw new RajaongkirException('RajaOngkir API key is not configured.');
        }

        return Http::timeout($this->config['timeout'] ?? 10)
            ->acceptJson()
            ->withHeaders(['key' => $apiKey]);
    }

    private function url(string $endpoint): string
    {
        return rtrim($this->config['base_url'] ?? 'https://rajaongkir.komerce.id/api', '/')
            . '/'
            . ltrim($endpoint, '/');
    }

    private function parse(array $response): array
    {
        $code = data_get($response, 'meta.code');

        if ($code !== null && (int) $code !== 200) {
            throw new RajaongkirException(data_get($response, 'meta.message', 'RajaOngkir API request failed.'));
        }

        if (!array_key_exists('data', $response)) {
            return $response;
        }

        return is_array($response['data']) ? $response['data'] : [];
    }
}
