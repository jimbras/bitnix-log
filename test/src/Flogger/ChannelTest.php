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

namespace Bitnix\Log\Flogger;

use DateTimeZone,
    Exception,
    PHPUnit\Framework\TestCase;

/**
 * @version 0.1.0
 */
class ChannelTest extends TestCase {

    private const ERROR_LOG = __DIR__ . '/_error.log';
    private Writer $writer;
    private Context $context;
    private string $oldlog;

    public function setUp() : void {
        $this->oldlog = \ini_get('error_log');
        \ini_set('error_log', self::ERROR_LOG);

        $this->writer = $this->createMock(Writer::CLASS);
        $this->context = $this->createMock(Context::CLASS);
    }

    public function tearDown() : void {
        \ini_set('error_log', $this->oldlog);
        if (\is_file(self::ERROR_LOG)) {
            \unlink(self::ERROR_LOG);
        }
    }

    public function testLog() {
        $record = null;
        $this->writer
            ->expects($this->once())
            ->method('write')
            ->will($this->returnCallback(function($log) use (&$record) {
                $record = $log;
            }));

        $this->context
            ->expects($this->once())
            ->method('map')
            ->will($this->returnValue([]));

        $channel = $this->channel();
        $channel->log('alert', 'foo = {foo}', ['foo' => 'bar', 'zig' => 'zag']);
        $this->assertInstanceOf(Record::CLASS, $record);
        $this->assertEquals('alert', $record->tag());
        $this->assertEquals(['zig' => 'zag', 'message' => 'foo = bar'], $record->payload());
    }

    public function testWrite() {
        $record = null;
        $this->writer
            ->expects($this->once())
            ->method('write')
            ->will($this->returnCallback(function($log) use (&$record) {
                $record = $log;
            }));

        $this->context
            ->expects($this->once())
            ->method('map')
            ->will($this->returnValue(['zig' => 'zag']));

        $channel = $this->channel();
        $channel->post('foo', ['bar' => 'baz']);
        $this->assertInstanceOf(Record::CLASS, $record);

        $this->assertEquals('main', $record->channel());
        $this->assertInstanceOf(Timestamp::CLASS, $record->timestamp());
        $this->assertEquals('foo', $record->tag());
        $this->assertEquals(['bar' => 'baz'], $record->payload());
        $this->assertEquals(['zig' => 'zag'], $record->context());
    }

    public function testWriteFilterAccept() {
        $record = null;
        $this->writer
            ->expects($this->once())
            ->method('write')
            ->will($this->returnCallback(function($log) use (&$record) {
                $record = $log;
            }));

        $this->context
            ->expects($this->once())
            ->method('map')
            ->will($this->returnValue(['zig' => 'zag']));

        $filter = $this->createMock(Filter::CLASS);
        $filter
            ->expects($this->once())
            ->method('accept')
            ->with('foo')
            ->will($this->returnValue(true));

        $channel = $this->channel($filter);
        $channel->post('foo', ['bar' => 'baz']);
        $this->assertInstanceOf(Record::CLASS, $record);

        $this->assertEquals('main', $record->channel());
        $this->assertInstanceOf(Timestamp::CLASS, $record->timestamp());
        $this->assertEquals('foo', $record->tag());
        $this->assertEquals(['bar' => 'baz'], $record->payload());
        $this->assertEquals(['zig' => 'zag'], $record->context());
    }

    public function testWriteFilterDeny() {
        $record = null;
        $this->writer
            ->expects($this->never())
            ->method('write');

        $this->context
            ->expects($this->never())
            ->method('map');

        $filter = $this->createMock(Filter::CLASS);
        $filter
            ->expects($this->once())
            ->method('accept')
            ->with('foo')
            ->will($this->returnValue(false));

        $channel = $this->channel($filter);
        $channel->post('foo', ['bar' => 'baz']);
        $this->assertNull($record);
    }

    public function testDefaultErrorHandler() {

        $x = new Exception('kaput');

        $this->writer
            ->expects($this->once())
            ->method('write')
            ->will($this->throwException($x));

        $this->context
            ->expects($this->exactly(2))
            ->method('map')
            ->will($this->returnValue(['zig' => 'zag']));

        $this->assertFalse(\is_file(self::ERROR_LOG));
        $channel = $this->channel();
        $channel->post('foo', ['bar' => 'baz']);
        $this->assertTrue(\is_file(self::ERROR_LOG));
        $this->assertStringContainsString('log.error', \file_get_contents(self::ERROR_LOG));
    }

    public function tesCustomErrorHandler() {

        $x = new Exception('kaput');

        $this->writer
            ->expects($this->once())
            ->method('write')
            ->will($this->throwException($x));

        $this->context
            ->expects($this->exactly(2))
            ->method('map')
            ->will($this->returnValue(['zig' => 'zag']));

        $this->assertFalse(\is_file(self::ERROR_LOG));
        $channel = $this->channel(null, fn($r, $x) => \file_put_contents(
            self::ERROR_LOG, $record->tag() . '::' . get_class($x)
        ));
        $channel->post('foo', ['bar' => 'baz']);
        $this->assertTrue(\is_file(self::ERROR_LOG));
        $log = \file_get_contents(self::ERROR_LOG);
        $this->assertStringContainsString('foo', $log);
        $this->assertStringContainsString('::Exception', $log);
    }

    public function testToString() {
        $this->assertIsString((string) $this->channel());
    }

    private function channel(Filter $filter = null, callable $onerror = null) : Channel {
        return new Channel(
            new DateTimeZone('UTC'),
            $this->writer,
            $this->context,
            'main',
            $filter,
            $onerror
        );
    }

}
