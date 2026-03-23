<?php

namespace Observer\Client;

use DateTime;
use JsonException;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;

class ObserverClient {
	public function __construct(
		private readonly RequestFactoryInterface $requestFactory,
		private readonly UriFactoryInterface $uriFactory,
		private readonly ClientInterface $client,
		private readonly string $endpoint,
		private readonly string $groupKey,
	) {}

	public function createRequest(string $observableKey, ?string $groupKey = null): ObserverClientRequest {
		return new ObserverClientRequest(
			groupKey: $groupKey ?? $this->groupKey,
			observableKey: $observableKey
		);
	}

	/**
	 * @template T of mixed|null
	 * @param ObserverClientRequest $observerRequest
	 * @param null|callable(ObserverClientRequest): T $fn
	 * @return ($fn is null ? null : T)
	 * @throws \Psr\Http\Client\ClientExceptionInterface
	 */
	public function ping(ObserverClientRequest $observerRequest, $fn = null) {
		/** @var T $result */
		$result = null;
		if($fn !== null) {
			$timer = microtime(true);
			$result = $fn($observerRequest);
			$timer = microtime(true) - $timer;
			$observerRequest->setRuntime($timer);
		}

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
		$responseRaw = $response->getBody()->getContents();
		try {
			/** @var null|true|array{error?: string} $responseData */
			$responseData = json_decode(json: $responseRaw, associative: true, depth: 512, flags: JSON_THROW_ON_ERROR);
			if($responseData !== true) {
				if(is_array($responseData) && isset($responseData['error'])) {
					throw new ObserverException("Observer: {$responseData['error']}");
				}
				throw new ObserverException('Observer: Something went wrong');
			}
		} catch(JsonException) {
			throw new ObserverException("Something went wrong; HTTP {$response->getStatusCode()}; Response = {$responseRaw}");
		}

		return $result;
	}

	/**
	 * @param string $query
	 * @param string $key
	 * @param int|float|string|array<array-key, mixed>|object $value
	 * @return string
	 */
	private static function setKey(string $query, string $key, int|float|string|array|object $value): string {
		parse_str($query, $params);
		$params[$key] = $value;

		return http_build_query($params);
	}
}