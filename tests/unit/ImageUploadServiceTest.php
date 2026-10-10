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
}
