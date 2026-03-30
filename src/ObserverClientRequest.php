<?php

namespace Observer\Client;

use DateTimeInterface;

/**
 * Mutable request object used to assemble a single observer ping.
 *
 * Example:
 * <code>
 * $request = (new ObserverClientRequest(groupKey: 'imports', observableKey: 'nightly-job'))
 *     ->setCaption(caption: 'Nightly import')
 *     ->setCron(cron: '0 2 * * *');
 * </code>
 *
 * @phpstan-type TDataType array<string, mixed>|object
 */
class ObserverClientRequest {
	public ?DateTimeInterface $nextPingAt = null;
	/** @var null|TDataType */
	public null|array|object $data = null;
	public ?string $caption = null;
	public ?string $cron = null;
	public ?string $timeString = null;
	public int|float $runtime = 0;

	/**
	 * Creates a request for one group and one observable key.
	 *
	 * Example:
	 * <code>
	 * $request = new ObserverClientRequest(
	 *     groupKey: 'billing',
	 *     observableKey: 'invoice-sync'
	 * );
	 * </code>
	 */
	public function __construct(
		public readonly string $groupKey,
		public readonly string $observableKey,
	) {}

	/**
	 * Sets the runtime in seconds that should be reported to the observer.
	 *
	 * Example:
	 * <code>
	 * $request->setRuntime(runtime: 2.35);
	 * </code>
	 *
	 * @param int|float $runtime Runtime in seconds.
	 * @return $this
	 */
	public function setRuntime(int|float $runtime): self {
		$this->runtime = $runtime;

		return $this;
	}

	/**
	 * Declares when the next ping is expected.
	 *
	 * Example:
	 * <code>
	 * $request->expectNextPingAt(
	 *     nextPingAt: new \DateTimeImmutable(datetime: '+10 minutes')
	 * );
	 * </code>
	 *
	 * @param null|DateTimeInterface $nextPingAt Timestamp of the next expected ping, or null to clear it.
	 * @return $this
	 */
	public function expectNextPingAt(?DateTimeInterface $nextPingAt): self {
		$this->nextPingAt = $nextPingAt;

		return $this;
	}

	/**
	 * Sets a human-readable caption for the observable.
	 *
	 * Example:
	 * <code>
	 * $request->setCaption(caption: 'Import customer master data');
	 * </code>
	 *
	 * @param null|string $caption Display label shown by the observer, or null to clear it.
	 * @return $this
	 */
	public function setCaption(?string $caption): self {
		$this->caption = $caption;

		return $this;
	}

	/**
	 * Sets the cron expression reported to the observer.
	 *
	 * Example:
	 * <code>
	 * $request->setCron(cron: '0 * * * *');
	 * </code>
	 *
	 * @param null|string $cron Cron expression to send, or null to clear it.
	 * @return $this
	 */
	public function setCron(?string $cron): self {
		$this->cron = $cron;

		return $this;
	}

	/**
	 * Sets the human-readable time string reported to the observer.
	 *
	 * Example:
	 * <code>
	 * $request->setTimeString(timeString: 'every weekday at 07:00');
	 * </code>
	 *
	 * @param null|string $timeString Natural-language schedule description, or null to clear it.
	 * @return $this
	 */
	public function setTimeString(?string $timeString): self {
		$this->timeString = $timeString;

		return $this;
	}

	/**
	 * Attaches arbitrary structured data to the ping.
	 *
	 * Example:
	 * <code>
	 * $request->setData(data: ['processed' => 17, 'status' => 'ok']);
	 * </code>
	 *
	 * @param null|TDataType $data Additional payload data to send with the ping.
	 * @return $this
	 */
	public function setData(null|array|object $data): self {
		$this->data = $data;

		return $this;
	}
}
