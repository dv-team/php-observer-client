<?php

namespace Observer\Client;

use DateTime;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;

class ObserverClient {
	public function __construct(
		private readonly RequestFactoryInterface $requestFactory,
		private readonly UriFactoryInterface $uriFactory,
		private readonly ClientInterface $client,
		private readonly string $endpoint
	) {}
	
	public function createRequest(string $groupKey, string $observableKey): ObserverClientRequest {
		return new ObserverClientRequest(
			groupKey: $groupKey,
			observableKey: $observableKey
		);
	}
	
	public function ping(ObserverClientRequest $observerRequest): void {
		$uri = $this->uriFactory->createUri($this->endpoint);
		$query = $uri->getQuery();
		$query = self::setKey($query, 'groupKey', $observerRequest->groupKey);
		$query = self::setKey($query, 'observableKey', $observerRequest->observableKey);
		
		if($observerRequest->nextPingAt !== null && $observerRequest->nextPingAt > new DateTime) {
			$query = self::setKey($query, 'nextPingAt', $observerRequest->nextPingAt->format('c'));
		}
		
		if($observerRequest->runtime > 0) {
			$query = self::setKey($query, 'runtime', $observerRequest->runtime);
		}
		
		if($observerRequest->data !== null) {
			$query = self::setKey($query, 'data', $observerRequest->data);
		}
		
		$uri = $uri->withQuery($query);
		
		$request = $this->requestFactory->createRequest('GET', $uri);
		$response = $this->client->sendRequest($request);
		echo $response->getBody()->getContents();
	}
	
	private static function setKey(string $query, string $key, string|array $value): string {
		parse_str($query, $params);
		$params[$key] = $value;
		return http_build_query($params);
	}
}