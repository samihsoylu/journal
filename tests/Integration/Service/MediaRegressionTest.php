<?php

declare(strict_types=1);

namespace Tests\Integration\Service;

use App\Service\Helper\MediaHelper;
use App\Service\MediaService;
use App\Service\ValueObject\Image;
use App\Utility\Encryptor;
use Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException;
use Defuse\Crypto\Key;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class MediaRegressionTest extends TestCase
{
    #[Test]
    public function image_upload_remains_encrypted_and_requires_its_owners_key() : void
    {
        $userId = random_int(1_000_000_000, 2_000_000_000);
        $helper = new MediaHelper();
        $directory = $helper->getUserUploadDir($userId);
        self::assertDirectoryDoesNotExist($directory);
        $temporary = tempnam(sys_get_temp_dir(), 'journal-image-test-');
        $key = Key::createNewRandomKey();
        $binary = 'synthetic image bytes';
        $image = new Image(base64_encode($binary), 'regression.png', 'png');
        $service = new MediaService(new Encryptor(), $helper);

        try {
            self::assertTrue($service->encryptImage($userId, $image, $key, $temporary));
            self::assertFileDoesNotExist($temporary);
            self::assertStringNotContainsString($binary, file_get_contents($directory . '/regression.png'));
            self::assertSame($binary, $service->getDecryptedImage($userId, 'regression.png', $key)->getBinary());
            $this->expectException(WrongKeyOrModifiedCiphertextException::class);
            $service->getDecryptedImage($userId, 'regression.png', Key::createNewRandomKey());
        } finally {
            $helper->removeUserUploadDir($userId);

            if (is_file($temporary)) {
                unlink($temporary);
            }
        }
    }
}
