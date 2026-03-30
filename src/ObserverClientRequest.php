<?php

namespace Observer\Client;

use DateTimeInterface;
use InvalidArgumentException;

/**
 * @phpstan-type TDataType array<string, mixed>|object
 * @phpstan-type TScheduleType 'cron'|'time-string'|'minutes'
 * @phpstan-type TSchedule array{string, TScheduleType}
 */
class ObserverClientRequest {
	public ?DateTimeInterface $nextPingAt = null;
	/** @var null|TDataType */
	public null|array|object $data = null;
	public ?string $caption = null;
	/** @var null|TSchedule */
	public ?array $schedule = null;
	public int|float $runtime = 0;

	public function __construct(
		public readonly string $groupKey,
		public readonly string $observableKey,
	) {}

	public function setRuntime(int|float $runtime): self {
		$this->runtime = $runtime;

		return $this;
	}

	public function expectNextPingAt(?DateTimeInterface $nextPingAt): self {
		$this->nextPingAt = $nextPingAt;

		return $this;
	}

	public function setCaption(?string $caption): self {
		$this->caption = $caption;

		return $this;
	}

	/**
	 * @param null|TScheduleType $scheduleType
	 */
	public function setSchedule(?string $schedule, ?string $scheduleType = null): self {
		if(($schedule === null) !== ($scheduleType === null)) {
			throw new InvalidArgumentException('Schedule and schedule type must be set together');
		}

		if($schedule === null) {
			$this->schedule = null;

			return $this;
		}

		if($scheduleType === null) {
			throw new InvalidArgumentException('Schedule and schedule type must be set together');
		}

		if(!in_array($scheduleType, ['cron', 'time-string', 'minutes'], true)) {
			throw new InvalidArgumentException("Invalid schedule type '{$scheduleType}'");
		}

		$this->schedule = [$schedule, $scheduleType];

		return $this;
	}

	/**
	 * @param null|TDataType $data
	 * @return $this
	 */
	public function setData(null|array|object $data): self {
		$this->data = $data;

		return $this;
	}
}
