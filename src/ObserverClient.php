<?php

namespace Observer\Client;

use DateTime;
use JsonException;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;

/**
 * Sends heartbeat and schedule metadata to an observer endpoint.
 *
 * Example:
 * <code>
 * $request = $client->createRequest(observableKey: 'nightly-import')
 *     ->setCaption(caption: 'Nightly import')
 *     ->setCron(cron: '0 2 * * *');
 * $client->ping(observerRequest: $request);
 * </code>
 */
class ObserverClient {
	/**
	 * Builds a client for one observer endpoint and one default group key.
	 *
	 * Example:
	 * <code>
	 * $client = new ObserverClient(
	 *     requestFactory: $requestFactory,
	 *     uriFactory: $uriFactory,
	 *     client: $httpClient,
	 *     endpoint: 'https://observer.example/ping',
	 *     groupKey: 'imports'
	 * );
	 * </code>
	 */
	public function __construct(
		private readonly RequestFactoryInterface $requestFactory,
		private readonly UriFactoryInterface $uriFactory,
		private readonly ClientInterface $client,
		private readonly string $endpoint,
		private readonly string $groupKey,
	) {}

	/**
	 * Creates a mutable request object for one observable.
	 *
	 * Example:
	 * <code>
	 * $request = $client->createRequest(observableKey: 'invoice-sync')
	 *     ->setCaption(caption: 'Sync ERP invoices')
	 *     ->setTimeString(timeString: 'every weekday at 07:00');
	 * </code>
	 *
	 * @param string $observableKey Unique identifier of the observable inside the group.
	 * @param null|string $groupKey Optional override for the client's default group key.
	 * @return ObserverClientRequest New request object ready for further customization.
	 */
	public function createRequest(string $observableKey, ?string $groupKey = null): ObserverClientRequest {
		return new ObserverClientRequest(
			groupKey: $groupKey ?? $this->groupKey,
			observableKey: $observableKey
		);
	}

	/**
	 * Sends the request to the observer service and optionally measures a callback runtime.
	 *
	 * Example:
	 * <code>
	 * $client->ping(
	 *     observerRequest: $client->createRequest(observableKey: 'healthcheck')
	 * );
	 * </code>
	 *
	 * Example with runtime measurement:
	 * <code>
	 * $processed = $client->ping(
	 *     observerRequest: $client->createRequest(observableKey: 'sync-users'),
	 *     fn: function (ObserverClientRequest $request): int {
	 *         return 42;
	 *     }
	 * );
	 * </code>
	 *
	 * @template T of mixed|null
	 * @param ObserverClientRequest $observerRequest Request payload to send.
	 * @param null|callable(ObserverClientRequest): T $fn Optional callback whose runtime should be measured.
	 * @return ($fn is null ? null : T) The callback result, if a callback was provided.
	 * @throws ObserverException When the observer rejects the ping or returns malformed JSON.
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

		if($observerRequest->caption !== null) {
			$query = self::setKey($query, 'caption', $observerRequest->caption);
		}

		if($observerRequest->cron !== null) {
			$query = self::setKey($query, 'cron', $observerRequest->cron);
		}

		if($observerRequest->timeString !== null) {
			$query = self::setKey($query, 'time-string', $observerRequest->timeString);
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
	 * Adds or replaces a single key in a raw query string.
	 *
	 * Example:
	 * <code>
	 * self::setKey(
	 *     query: 'groupKey=imports',
	 *     key: 'observableKey',
	 *     value: 'invoice-sync'
	 * );
	 * </code>
	 *
	 * @param string $query Existing raw query string.
	 * @param string $key Query parameter name to add or replace.
	 * @param int|float|string|array<array-key, mixed>|object $value Encodable query parameter value.
	 * @return string Query string containing the provided key and value.
	 */
	private static function setKey(string $query, string $key, int|float|string|array|object $value): string {
		parse_str($query, $params);
		$params[$key] = $value;

		return http_build_query($params);
	}
}
