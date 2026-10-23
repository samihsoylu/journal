<?php

declare(strict_types=1);

namespace App\Utility;

use RuntimeException;
use Sentry\Event;
use Sentry\ExceptionDataBag;
use Sentry\Frame;
use Sentry\Stacktrace;

final class SentryEventSanitizer
{
    public function __invoke(Event $event) : Event
    {
        // Build an allowlisted event. Dropping bodies alone does not remove
        // secrets from exception messages, breadcrumbs, URLs, locals or extras.
        $safe = Event::createEvent($event->getId());
        $safe->setTimestamp($event->getTimestamp());
        $safe->setLevel($event->getLevel());
        $safe->setRelease($event->getRelease());
        $safe->setEnvironment($event->getEnvironment());
        $safe->setLogger('journal');
        $safe->setMessage('Journal application error');
        $exceptions = [];

        foreach ($event->getExceptions() as $exception) {
            $copy = new ExceptionDataBag(new RuntimeException('Private details omitted'), $this->safeTrace($exception->getStacktrace()));
            $copy->setType($exception->getType());
            $exceptions[] = $copy;
        }
        $safe->setExceptions($exceptions);
        $safe->setStacktrace($this->safeTrace($event->getStacktrace()));

        return $safe;
    }

    private function safeTrace(?Stacktrace $trace) : ?Stacktrace
    {
        if ($trace === null) {
            return null;
        }

        return new Stacktrace(array_map(
            static fn (Frame $frame) => new Frame($frame->getFunctionName(), $frame->getFile(), $frame->getLine()),
            $trace->getFrames(),
        ));
    }
}
