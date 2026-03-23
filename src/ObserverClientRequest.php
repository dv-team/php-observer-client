<?php

namespace Observer\Client;

use DateTimeInterface;

class ObserverClientRequest {
	public ?DateTimeInterface $nextPingAt = null;
	public null|array|object $data = null;
	public int $runtime = 0;

	public function __construct(
		public readonly string $groupKey,
		public readonly string $observableKey,
	) {}

	public function setRuntime(int $runtime): self {
		$this->runtime = $runtime;

		return $this;
	}

	public function expectNextPingAt(?DateTimeInterface $nextPingAt): self {
		$this->nextPingAt = $nextPingAt;

		return $this;
	}

	public function setData(null|array|object $data): self {
		$this->data = $data;

		return $this;
	}
}