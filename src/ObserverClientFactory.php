<?php

namespace Observer\Client;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;

class ObserverClientFactory {
	public function __construct(
		private readonly RequestFactoryInterface $requestFactory,
		private readonly UriFactoryInterface $uriFactory,
		private readonly ClientInterface $client,
	) {}
	
	/**
	 * @param string $endpoint
	 * @return ObserverClient
	 */
	public function createClient(string $endpoint): ObserverClient {
		return new ObserverClient(
			requestFactory: $this->requestFactory,
			uriFactory: $this->uriFactory,
			client: $this->client,
			endpoint: $endpoint
		);
	}
}