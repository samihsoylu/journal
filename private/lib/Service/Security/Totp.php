<?php

declare(strict_types=1);

namespace App\Service\Security;

use OTPHP\TOTP as Generator;
use Psr\Clock\ClockInterface;

final readonly class Totp
{
    public function __construct(private ClockInterface $clock) {}

    public function generateSecret() : string
    {
        return Generator::generate($this->clock, 20)->getSecret();
    }

    public function provisioningUri(string $secret, string $username) : string
    {
        $otp = Generator::createFromSecret($secret, $this->clock);
        $otp->setIssuer('Journal');
        $otp->setLabel(str_replace(':', '-', $username));

        return $otp->getProvisioningUri();
    }

    /** @phpstan-impure */
    public function match(string $secret, string $code) : ?int
    {
        if (preg_match('/^[0-9]{6}$/D', $code) !== 1) {
            return null;
        }
        $current = intdiv($this->clock->now()->getTimestamp(), 30);
        $otp = Generator::createFromSecret($secret, $this->clock);

        // Prefer the highest counter if adjacent six-digit codes coincide.
        foreach ([$current + 1, $current, $current - 1] as $counter) {
            if ($counter >= 0 && hash_equals($otp->at($counter * 30), $code)) {
                return $counter;
            }
        }

        return null;
    }
}
