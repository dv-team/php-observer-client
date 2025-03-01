<?php

namespace Observer\Client;

use DateTimeInterface;

class ObserverClientRequest {
	public ?DateTimeInterface $nextPingAt;
	public null|array|object $data = null;
	public int $runtime;
	
	public function __construct(
		public readonly string $groupKey,
		public readonly string $observableKey
	) {}
	
	public function expectNextPingAt(?DateTimeInterface $nextPingAt): self {
		$this->nextPingAt = $nextPingAt;
		return $this;
	}
	
	public function setData(null|array|object $data): self {
		$this->data = $data;
		return $this;
	}
}