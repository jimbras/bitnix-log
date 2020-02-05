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
    PHPUnit\Framework\TestCase;

/**
 * @version 0.1.0
 */
class RecordTest extends TestCase {

    public function testConstructor() {
        $timestamp = new Timestamp(new DateTimeZone('UTC'));
        $channel = 'main';
        $tag = 'app.error';
        $payload = ['foo' => 'bar'];
        $context = ['zig' => 'zag'];

        $record = new Record(
            $timestamp, $channel, $tag, $payload, $context
        );

        $this->assertSame($timestamp, $record->timestamp());
        $this->assertEquals($channel, $record->channel());
        $this->assertEquals($tag, $record->tag());
        $this->assertEquals($payload, $record->payload());
        $this->assertEquals($context, $record->context());
    }

    public function testToString() {
        $record = new Record(
            new Timestamp(new DateTimeZone('UTC')),
            'main',
            'foo',
            ['bar' => 'baz']
        );
        $this->assertIsString((string) $record);
    }

    public function testJsonSupport() {
        $timestamp = new Timestamp(new DateTimeZone('UTC'));
        $channel = 'main';
        $tag = 'app.error';
        $payload = ['foo' => 'bar'];
        $context = ['zig' => 'zag'];

        $record = new Record(
            $timestamp, $channel, $tag, $payload, $context
        );

        $expected = [
            'timestamp' => $timestamp->format(Timestamp::FORMAT),
            'channel'   => $channel,
            'tag'       => $tag,
            'payload'   => $payload,
            'context'   => $context
        ];

        $this->assertEquals(
            $expected,
            \json_decode(\json_encode($record), true)
        );
    }
}
