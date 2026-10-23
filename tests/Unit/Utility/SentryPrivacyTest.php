<?php

declare(strict_types=1);

namespace Tests\Unit\Utility;

use App\Utility\SentryConfiguration;
use App\Utility\SentryEventSanitizer;
use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Sentry\ClientBuilder;
use Sentry\Event;
use Sentry\Serializer\PayloadSerializer;
use Sentry\State\Scope;
use Sentry\Transport\Result;
use Sentry\Transport\ResultStatus;
use Sentry\Transport\TransportInterface;

/** @internal */
#[CoversClass(SentryEventSanitizer::class)]
#[CoversClass(SentryConfiguration::class)]
final class SentryPrivacyTest extends TestCase
{
    #[Test]
    public function the_actual_outgoing_envelope_has_diagnostics_without_private_request_or_exception_values() : void
    {
        $transport = new class () implements TransportInterface {
            public ?Event $event = null;

            public function send(Event $event) : Result
            {
                $this->event = $event;

                return new Result(ResultStatus::success(), $event);
            }

            public function close(?int $timeout = null) : Result
            {
                return new Result(ResultStatus::success());
            }
        };
        $secret = 'SYNTHETIC-PRIVATE-JOURNAL-PASSWORD-EEK-TOKEN';
        $options = SentryConfiguration::options('https://public@example.invalid/1');
        $options['transport'] = $transport;
        // Do not install process-wide handlers in a unit test.
        $options['default_integrations'] = false;
        $client = ClientBuilder::create($options)->getClient();
        $scope = new Scope();
        $scope->setExtra('private-entry', $secret);
        $scope->setContext('private-context', ['content' => $secret]);
        $scope->addEventProcessor(static function (Event $event) use ($secret) : Event {
            // Exercise the outgoing boundary even if a future integration adds data.
            $event->setRequest([
                'url' => 'https://journal.example/entry?form_key=' . $secret,
                'data' => ['entry_content' => $secret, 'newPassword' => $secret],
                'cookies' => ['EEK' => $secret],
                'headers' => ['Authorization' => $secret],
            ]);

            return $event;
        });
        $exception = new LogicException($secret, 0, new RuntimeException($secret));
        $client->captureException($exception, $scope);
        self::assertNotNull($transport->event);
        $payload = new PayloadSerializer($client->getOptions())->serialize($transport->event);
        self::assertStringNotContainsString($secret, $payload);
        self::assertStringContainsString(LogicException::class, $payload);
        self::assertStringContainsString(RuntimeException::class, $payload);
        self::assertSame([], $transport->event->getRequest());
        self::assertSame([], $transport->event->getExtra());
        self::assertSame([], $transport->event->getContexts());
        self::assertSame('never', $client->getOptions()->getMaxRequestBodySize());
        self::assertNotEmpty($transport->event->getExceptions()[0]->getStacktrace()->getFrames());
    }
}
