<?php

declare(strict_types=1);

namespace App\Messenger\Middleware;

use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\ReceivedStamp;

/**
 * Logs message handling for monitoring and debugging.
 */
final readonly class LoggingMiddleware implements MiddlewareInterface
{
    public function __construct(
        private LoggerInterface $logger,
    ) {
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $message = $envelope->getMessage();
        $messageClass = $message::class;

        // Check if this is a received message (being processed by worker)
        $isReceived = $envelope->last(ReceivedStamp::class) !== null;

        if ($isReceived) {
            $this->logger->debug('Processing message', [
                'message_class' => $messageClass,
                'message' => $this->serializeMessage($message),
            ]);

            $startTime = microtime(true);
        }

        try {
            $envelope = $stack->next()->handle($envelope, $stack);

            if ($isReceived) {
                $duration = microtime(true) - $startTime;
                $this->logger->info('Message processed successfully', [
                    'message_class' => $messageClass,
                    'duration_ms' => round($duration * 1000, 2),
                ]);
            }

            return $envelope;
        } catch (\Throwable $e) {
            if ($isReceived) {
                $this->logger->error('Message processing failed', [
                    'message_class' => $messageClass,
                    'exception' => $e->getMessage(),
                ]);
            }

            throw $e;
        }
    }

    private function serializeMessage(object $message): array
    {
        // Extract public data from message for logging
        return json_decode(json_encode($message), true) ?? [];
    }
}
