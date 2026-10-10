<?php

use App\Services\ImageUploadService;
use CodeIgniter\HTTP\Files\UploadedFile;
use CodeIgniter\Test\CIUnitTestCase;

/** @internal */
final class ImageUploadServiceTest extends CIUnitTestCase
{
    public function testGenuinePngIsPreparedWithSafeUniqueFilename(): void
    {
        $temporary = tempnam(sys_get_temp_dir(), 'groove-image-');
        $image = imagecreatetruecolor(1200, 600);
        imagefill($image, 0, 0, imagecolorallocate($image, 98, 46, 59));
        imagepng($image, $temporary);
        imagedestroy($image);

        $upload = $this->createMock(UploadedFile::class);
        $upload->method('isValid')->willReturn(true);
        $upload->method('hasMoved')->willReturn(false);
        $upload->method('getMimeType')->willReturn('image/png');
        $upload->method('getTempName')->willReturn($temporary);
        $service = new ImageUploadService();
        $filename = $service->store($upload, 'test-products');
        $path = FCPATH . 'uploads/test-products/' . $filename;

        $this->assertMatchesRegularExpression('/^[a-f0-9]{32}\.png$/', $filename);
        $this->assertFileExists($path);
        [$width, $height] = getimagesize($path);
        $this->assertSame(800, $width);
        $this->assertSame(800, $height);

        $service->delete($filename, 'test-products');
        @rmdir(FCPATH . 'uploads/test-products');
    }

    public function testGenuineJpegIsPreparedAndUnsupportedMimeIsRejected(): void
    {
        $temporary = tempnam(sys_get_temp_dir(), 'groove-jpeg-');
        $image = imagecreatetruecolor(640, 900);
        imagefill($image, 0, 0, imagecolorallocate($image, 37, 35, 38));
        imagejpeg($image, $temporary, 90);
        imagedestroy($image);

        $jpeg = $this->createMock(UploadedFile::class);
        $jpeg->method('isValid')->willReturn(true);
        $jpeg->method('hasMoved')->willReturn(false);
        $jpeg->method('getMimeType')->willReturn('image/jpeg');
        $jpeg->method('getTempName')->willReturn($temporary);
        $service = new ImageUploadService();
        $filename = $service->store($jpeg, 'test-avatars');
        $path = FCPATH . 'uploads/test-avatars/' . $filename;

        $this->assertMatchesRegularExpression('/^[a-f0-9]{32}\.jpg$/', $filename);
        $this->assertFileExists($path);
        [$width, $height] = getimagesize($path);
        $this->assertSame([800, 800], [$width, $height]);

        $unsupported = $this->createMock(UploadedFile::class);
        $unsupported->method('isValid')->willReturn(true);
        $unsupported->method('hasMoved')->willReturn(false);
        $unsupported->method('getMimeType')->willReturn('image/gif');
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Only genuine JPG and PNG images are accepted.');

        try {
            $service->store($unsupported, 'test-avatars');
        } finally {
            $service->delete($filename, 'test-avatars');
            @unlink($temporary);
            @rmdir(FCPATH . 'uploads/test-avatars');
        }
    }
}
