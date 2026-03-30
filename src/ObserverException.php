<?php

namespace Observer\Client;

use RuntimeException;

/**
 * Signals that a ping to the observer service failed or returned an invalid response.
 *
 * Example:
 * <code>
 * try {
 *     $client->ping(observerRequest: $request);
 * } catch (ObserverException $exception) {
 *     error_log($exception->getMessage());
 * }
 * </code>
 */
class ObserverException extends RuntimeException {
}
