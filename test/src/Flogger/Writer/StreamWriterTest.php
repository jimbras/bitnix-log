<?php declare(strict_types=1);

/**
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as
 *  published by the Free Software Foundation, either version 3 of the
 *  License, or (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program. If not, see <https://www.gnu.org/licenses/agpl-3.0.txt>.
 */

namespace Bitnix\Log\Flogger\Writer;

use DateTimeZone,
    InvalidArgumentException,
    RuntimeException,
    Bitnix\Log\Flogger\Packer,
    Bitnix\Log\Flogger\Record,
    Bitnix\Log\Flogger\Timestamp,
    PHPUnit\Framework\TestCase;

/**
 * @version 0.1.0
 */
class StreamWriterTest extends TestCase {

    public function testWriteWithFilePath() {
        $file = __DIR__ . '/_logs/error.log';

        $this->assertFalse(\is_file($file));

        $record = new Record(
            new Timestamp(new DateTimeZone('UTC')),
            'foo',
            'bar',
            ['zig' => 'zag']
        );

        $packer = $this->createMock(Packer::CLASS);
        $packer
            ->expects($this->once())
            ->method('pack')
            ->with($record)
            ->will($this->returnValue('logged!!!'));

        $writer = new StreamWriter($file, $packer);
        try {
            $writer->write($record);
            $this->assertTrue(\is_file($file));
            $this->assertEquals('logged!!!', \trim(\file_get_contents($file)));
        } finally {
            @\unlink($file);
        }
    }

    public function testWriteWithFileUri() {
        $file = 'file://' . __DIR__ . '/_logs/error.log';

        $this->assertFalse(\is_file($file));

        $record = new Record(
            new Timestamp(new DateTimeZone('UTC')),
            'foo',
            'bar',
            ['zig' => 'zag']
        );

        $packer = $this->createMock(Packer::CLASS);
        $packer
            ->expects($this->once())
            ->method('pack')
            ->with($record)
            ->will($this->returnValue('logged!!!'));

        $writer = new StreamWriter($file, $packer);
        try {
            $writer->write($record);
            $this->assertTrue(\is_file($file));
            $this->assertEquals('logged!!!', \trim(\file_get_contents($file)));
        } finally {
            @\unlink($file);
        }
    }

    public function testWriteWithResource() {
        $file = __DIR__ . '/_logs/error.log';
        $this->assertFalse(\is_file($file));

        $fp = \fopen($file, 'ab');

        $record = new Record(
            new Timestamp(new DateTimeZone('UTC')),
            'foo',
            'bar',
            ['zig' => 'zag']
        );

        $packer = $this->createMock(Packer::CLASS);
        $packer
            ->expects($this->once())
            ->method('pack')
            ->with($record)
            ->will($this->returnValue('logged!!!'));

        $writer = new StreamWriter($fp, $packer);
        try {
            $writer->write($record);
            $this->assertTrue(\is_file($file));
            $this->assertEquals('logged!!!', \trim(\file_get_contents($file)));
        } finally {
            @\fclose($fp);
            @\unlink($file);
        }
    }

    public function testWriteError() {
        $this->expectException(RuntimeException::CLASS);

        $file = __DIR__ . '/_logs/error.log';
        $this->assertFalse(\is_file($file));
        \touch($file);
        $fp = \fopen($file, 'rb');

        $record = new Record(
            new Timestamp(new DateTimeZone('UTC')),
            'foo',
            'bar',
            ['zig' => 'zag']
        );

        $packer = $this->createMock(Packer::CLASS);
        $packer
            ->expects($this->once())
            ->method('pack')
            ->with($record)
            ->will($this->returnValue('logged!!!'));

        $writer = new StreamWriter($fp, $packer);
        try {
            @$writer->write($record);
        } finally {
            @\fclose($fp);
            @\unlink($file);
        }
    }

    public function testWriteErrorOnClosedStream() {
        $this->expectException(RuntimeException::CLASS);

        $file = __DIR__ . '/_logs/error.log';
        $this->assertFalse(\is_file($file));

        $fp = \fopen($file, 'ab');

        $record = new Record(
            new Timestamp(new DateTimeZone('UTC')),
            'foo',
            'bar',
            ['zig' => 'zag']
        );

        $packer = $this->createMock(Packer::CLASS);
        $packer
            ->expects($this->once())
            ->method('pack')
            ->with($record)
            ->will($this->returnValue('logged!!!'));

        $writer = new StreamWriter($fp, $packer);
        try {
            $writer->write($record);
            \fclose($fp);
            $writer->write($record);
            $this->fail('RuntimeException not thrown');
        } finally {
            @\unlink($file);
        }
    }

    public function testWriterDoesNotCloseStreamGivenToTheConstructor() {
        $file = __DIR__ . '/_logs/error.log';
        $fp = \fopen($file, 'ab');

        try {
            $writer = new StreamWriter($fp);
            unset($writer);
            $this->assertTrue(\is_resource($fp));
        } finally {
            @\fclose($fp);
            @\unlink($file);
        }
    }

    public function testConstructorAcceptsOnlyStringOrResourceAsStreams() {
        $this->expectException(InvalidArgumentException::CLASS);
        new StreamWriter($this);
    }

    public function testConstructorOpenStreamError() {
        $this->expectException(RuntimeException::CLASS);
        $file = __DIR__ . '/_logs/error/kaput.log';
        \mkdir(\dirname($file));
        \chmod(\dirname($file), 0000);
        try {
            $writer = @new StreamWriter($file);
        } finally {
            \chmod(\dirname($file), 0755);
            \rmdir(\dirname($file));
        }
    }

    public function testConstructorCreateDirectoryError() {
        $this->expectException(RuntimeException::CLASS);
        $file = __DIR__ . '/_logs/error/kaput.log';

        \chmod(\dirname(\dirname($file)), 0000);
        try {
            $writer = @new StreamWriter($file);
        } finally {
            \chmod(\dirname(\dirname($file)), 0755);
            @\rmdir(\dirname($file));
        }
    }

    public function testConstructorInvalidStreamResourceError() {
        if (!\function_exists('curl_init')) {
            $this->markTestSkipped('This test can wait...');
        }

        $this->expectException(InvalidArgumentException::CLASS);
        $resource = \curl_init();
        try {
            $this->assertIsResource($resource);
            $writer = new StreamWriter($resource);
        } finally {
            \curl_close($resource);
        }
    }

    public function testToString() {
        $this->assertIsString((string) new StreamWriter('php://memory'));
    }

}
