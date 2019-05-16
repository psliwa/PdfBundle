<?php

namespace Ps\PdfBundle\Tests\Templating;

use PHPUnit\Framework\TestCase;
use Ps\PdfBundle\Templating\ImageLocator;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\HttpKernel\Kernel;

class ImageLocatorTest extends TestCase
{
    private $kernel;

    protected function setup(): void
    {
        $this->kernel = $this->getMockBuilder(Kernel::class)
                             ->setMethods(['getBundle', 'registerBundles', 'registerContainerConfiguration', 'getRootDir'])
                             ->disableOriginalConstructor()
                             ->getMock();
    }

    /**
     * @test
     * @dataProvider dataProvider
     */
    public function getImagePathSuccessfullyWhenBundleExists($bundleName, $imageName): void
    {
        $bundle = $this->getMockBuilder(Bundle::class)
                       ->setMethods(['getPath'])
                       ->disableOriginalConstructor()
                       ->getMock();

        $bundlePath = 'some/bundle/path';

        $imageLogicalName = sprintf('%s:%s', $bundleName, $imageName);
        $expectedImagePath = $bundlePath.'/Resources/public/images/'.$imageName;

        $this->kernel->expects($this->once())
                     ->method('getBundle')
                     ->with($bundleName)
                     ->will($this->returnValue($bundle));

        $bundle->expects($this->once())
               ->method('getPath')
               ->will($this->returnValue($bundlePath));

        $locator = new ImageLocator($this->kernel);

        $this->assertEquals($expectedImagePath, $locator->getImagePath($imageLogicalName));
    }

    public function dataProvider(): array
    {
        return [
            ['SomeBundle', 'some-image.jpg'],
            ['SomeBundle', 'dir/some:image.jpg'],
        ];
    }

    /**
     * @test
     */
    public function throwExceptionIfBundleDoesNotExist(): void
    {
        $this->kernel->expects($this->once())
                     ->method('getBundle')
                     ->will($this->throwException(new \InvalidArgumentException()));

        $this->expectException(\InvalidArgumentException::class);

        $locator = new ImageLocator($this->kernel);

        $locator->getImagePath('unexistedBundle:someImage.jpg');
    }

    /**
     * @test
     */
    public function getImagePathFromGlobalResourcesWhenBundleNameIsEmpty()
    {
        $r = new \ReflectionObject($this->kernel);
        $rootDir = \dirname($r->getFileName());
        $imageName = 'some/image/name.jpg';
        $prefixes = ['', ':', '::'];

        $this->kernel->expects($this->atMost(1))
                     ->method('getRootDir')
                     ->will($this->returnValue($rootDir));

        $this->kernel->expects($this->never())
                     ->method('getBundle');

        $expectedPath = $rootDir.'/Resources/public/images/'.$imageName;

        $locator = new ImageLocator($this->kernel);

        foreach ($prefixes as $prefix) {
            $this->assertEquals($expectedPath, $locator->getImagePath($prefix.$imageName));
        }
    }
}
