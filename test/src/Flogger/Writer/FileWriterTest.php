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
    RuntimeException,
    Bitnix\Log\Flogger\Packer,
    Bitnix\Log\Flogger\Record,
    Bitnix\Log\Flogger\Timestamp,
    PHPUnit\Framework\TestCase;

/**
 * @version 0.1.0
 */
class FileWriterTest extends TestCase {

    private const ERROR_LOG = __DIR__ . '/_logs/error.log';

    public function tearDown() : void {
        if (\is_file(self::ERROR_LOG)) {
            \unlink(self::ERROR_LOG);
        }
    }

    public function testWrite() {
        $this->assertFalse(\is_file(self::ERROR_LOG));

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

        $writer = new FileWriter(self::ERROR_LOG, $packer);
        $writer->write($record);
        $this->assertTrue(\is_file(self::ERROR_LOG));
        $this->assertEquals('logged!!!', \trim(\file_get_contents(self::ERROR_LOG)));
    }

    public function testWriteError() {
        $this->expectException(RuntimeException::CLASS);

        \touch(self::ERROR_LOG);
        \chmod(self::ERROR_LOG, 0000);

        $record = new Record(
            new Timestamp(new DateTimeZone('UTC')),
            'foo',
            'bar',
            ['zig' => 'zag']
        );

        $writer = new FileWriter(self::ERROR_LOG);

        try {
            @$writer->write($record);
        } finally {
            \chmod(self::ERROR_LOG, 0666);
        }
    }

    public function testConstructorError() {
        $this->expectException(RuntimeException::CLASS);

        $file = __DIR__ . '/_logs/error/kaput.log';
        \mkdir(\dirname($file));
        \chmod(\dirname(self::ERROR_LOG), 0000);
        try {
            $writer = @new FileWriter($file);
        } finally {
            \chmod(\dirname(self::ERROR_LOG), 0755);
            \rmdir(\dirname($file));
        }
    }

    public function testToString() {
        $this->assertIsString((string) new FileWriter(self::ERROR_LOG));
    }
}
