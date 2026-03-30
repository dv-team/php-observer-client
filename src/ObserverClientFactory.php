<?php

namespace Observer\Client;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;

/**
 * Creates {@see ObserverClient} instances from shared PSR-17 and PSR-18 dependencies.
 *
 * Example:
 * <code>
 * $factory = new ObserverClientFactory(
 *     requestFactory: $requestFactory,
 *     uriFactory: $uriFactory,
 *     client: $httpClient
 * );
 * $client = $factory->createClient(
 *     endpoint: 'https://observer.example/ping',
 *     groupKey: 'billing'
 * );
 * </code>
 */
class ObserverClientFactory {
	/**
	 * Stores the reusable HTTP dependencies that every created client will share.
	 *
	 * Example:
	 * <code>
	 * $factory = new ObserverClientFactory(
	 *     requestFactory: $requestFactory,
	 *     uriFactory: $uriFactory,
	 *     client: $httpClient
	 * );
	 * </code>
	 */
	public function __construct(
		private readonly RequestFactoryInterface $requestFactory,
		private readonly UriFactoryInterface $uriFactory,
		private readonly ClientInterface $client,
	) {}

	/**
	 * Creates a client bound to one observer endpoint and one default group key.
	 *
	 * Example:
	 * <code>
	 * $client = $factory->createClient(
	 *     endpoint: 'https://observer.example/ping',
	 *     groupKey: 'imports'
	 * );
	 * </code>
	 *
	 * @param string $endpoint Full URL of the observer ping endpoint.
	 * @param string $groupKey Default group key used by requests created through the client.
	 * @return ObserverClient Configured observer client instance.
	 */
	public function createClient(string $endpoint, string $groupKey): ObserverClient {
		return new ObserverClient(
			requestFactory: $this->requestFactory,
			uriFactory: $this->uriFactory,
			client: $this->client,
			endpoint: $endpoint,
			groupKey: $groupKey
		);
	}
}
