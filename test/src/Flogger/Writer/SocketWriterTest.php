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
class SocketWriterTest extends TestCase {

    private $socket = null;

    public function tearDown() : void {
        if (\is_resource($this->socket)) {
            \fclose($this->socket);
        }
    }

    public function testConstructorWithInvalidSocketResourceThrowsException() {
        if (!\function_exists('curl_init')) {
            $this->markTestSkipped('This test can wait...');
        }

        $this->expectException(InvalidArgumentException::CLASS);
        $resource = \curl_init();

        try {
            $this->assertIsResource($resource);
            $writer = new SocketWriter($resource);
        } finally {
            \curl_close($resource);
        }
    }

    public function testConstructorWithInvalidSocketParameterThrowsException() {
        $this->expectException(InvalidArgumentException::CLASS);
        new SocketWriter($this);
    }

    public function testInvalidRetryCountThrowsException() {
        $this->expectException(InvalidArgumentException::CLASS);
        $this->socket = \fopen('php://memory', 'ab');
        new SocketWriter($this->socket, null, [
            SocketWriter::WRITE_RETRY_COUNT => -1
        ]);
    }

    public function testInvalidRetrySleepThrowsException() {
        $this->expectException(InvalidArgumentException::CLASS);
        $this->socket = \fopen('php://memory', 'ab');
        new SocketWriter($this->socket, null, [
            SocketWriter::WRITE_RETRY_SLEEP => 0
        ]);
    }

    public function testInvalidRetrySleepFactorThrowsException() {
        $this->expectException(InvalidArgumentException::CLASS);
        $this->socket = \fopen('php://memory', 'ab');
        new SocketWriter($this->socket, null, [
            SocketWriter::WRITE_RETRY_FACTOR => 0
        ]);
    }

    public function testInvalidConnectionTimeoutThrowsException() {
        $this->expectException(InvalidArgumentException::CLASS);
        new SocketWriter('tcp://notused:666', null, [
            SocketWriter::CONNECTION_TIMEOUT => 0
        ]);
    }

    public function testInvalidSocketTimeoutThrowsException() {
        $this->expectException(InvalidArgumentException::CLASS);
        new SocketWriter('tcp://notused:666', null, [
            SocketWriter::SOCKET_TIMEOUT => 0
        ]);
    }

    public function testInvalidPersistentFlagThrowsException() {
        $this->expectException(InvalidArgumentException::CLASS);
        new SocketWriter('tcp://notused:666', null, [
            SocketWriter::PERSISTENT => 'true'
        ]);
    }

    public function testWriteToSocket() {
        $this->socket = \fopen('php://memory', 'wb+');

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

        $writer = new SocketWriter($this->socket, $packer);
        $writer->write($record);

        \rewind($this->socket);
        $this->assertEquals('logged!!!', \stream_get_contents($this->socket));
    }

    public function testSocketFromConnectionString() {
        $socket = $this->connect();
        try {

            $writer = new SocketWriter('tcp://127.0.0.1:1234');
            $this->assertNotNull($writer);
        } finally {
            socket_close($socket);
        }
    }

    public function testPersitentSocketFromConnectionString() {
        $socket = $this->connect();
        try {

            $writer = new SocketWriter('tcp://127.0.0.1:1234', null, [SocketWriter::PERSISTENT => true]);
            $this->assertNotNull($writer);
        } finally {
            socket_close($socket);
            $writer->close();
        }
    }

    public function testConnectionStringError() {
        $this->expectException(RuntimeException::CLASS);
        @new SocketWriter('tcp://127.0.0.1:1234');
    }

    public function testWriteRetrySocketError() {
        $this->expectException(RuntimeException::CLASS);

        $this->socket = \fopen('php://memory', 'wb');
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
            ->will($this->returnCallback(function() {
                \fclose($this->socket);
                return 'logged';
            }));

        $writer = new SocketWriter($this->socket, $packer);

        @$writer->write($record);
    }

    public function testWriteToACloseSocketError() {
        $this->expectException(RuntimeException::CLASS);

        $this->socket = \fopen('php://memory', 'wb');
        $record = new Record(
            new Timestamp(new DateTimeZone('UTC')),
            'foo',
            'bar',
            ['zig' => 'zag']
        );

        $packer = $this->createMock(Packer::CLASS);
        $packer
            ->expects($this->never())
            ->method('pack');

        $writer = new SocketWriter($this->socket, $packer);

        \fclose($this->socket);

        $writer->write($record);
    }

    public function testToString() {
        $this->socket = \fopen('php://memory', 'wb');
        $this->assertIsString((string) new SocketWriter($this->socket));
    }

    private function connect() {
        if (!$socket = socket_create(AF_INET, SOCK_STREAM, 0)) {
            throw new RuntimeException('create failed');
        }

        if (!socket_bind($socket, '127.0.0.1', 1234)) {
            socket_close($socket);
            throw new RuntimeException('connect failed');
        }

        if (!socket_listen($socket, 5)) {
            socket_close($socket);
            throw new RuntimeException('connect failed');
        }

        return $socket;
    }

}
