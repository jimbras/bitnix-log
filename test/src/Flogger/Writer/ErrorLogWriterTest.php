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
    Bitnix\Log\Flogger\Packer,
    Bitnix\Log\Flogger\Record,
    Bitnix\Log\Flogger\Timestamp,
    PHPUnit\Framework\TestCase;

/**
 * @version 0.1.0
 */
class ErrorLogWriterTest extends TestCase {

    private string $oldlog;
    private const ERROR_LOG = __DIR__ . '/_logs/error.log';

    public function setUp() : void {
        $this->oldlog = \ini_get('error_log');
        \ini_set('error_log', self::ERROR_LOG);
    }

    public function tearDown() : void {
        \ini_set('error_log', $this->oldlog);
        if (\is_file(self::ERROR_LOG)) {
            \unlink(self::ERROR_LOG);
        }
    }

    public function testWriterWritesToLog() {
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

        $writer = new ErrorLogWriter($packer);
        $writer->write($record);
        $this->assertTrue(\is_file(self::ERROR_LOG));
        $this->assertStringEndsWith('logged!!!', \trim(\file_get_contents(self::ERROR_LOG)));
    }

    public function testToString() {
        $this->assertIsString((string) new ErrorLogWriter());
    }

}
