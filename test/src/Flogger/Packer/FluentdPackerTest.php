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

namespace Bitnix\Log\Flogger\Packer;

use DateTimeZone,
    Bitnix\Log\Flogger\Record,
    Bitnix\Log\Flogger\Timestamp,
    Bitnix\Log\Flogger\Util\Json,
    PHPUnit\Framework\TestCase;

/**
 * @version 0.1.0
 */
class FluentdPackerTest extends TestCase {

    public function testPack() {
        $packer = new FluentdPacker();

        $time = new Timestamp(new DateTimeZone('UTC'));
        $channel = self::CLASS;
        $tag = 'app.error';
        $payload = ['zig' => 'zag'];
        $context = ['zoid' => 'berg'];

        $expected = [
            $tag,
            $time->getTimestamp(),
            [
                'channel' => $channel,
                'context' => $context,
                'payload' => $payload
            ]
        ];

        $record = new Record($time, $channel, $tag, $payload, $context);

        $this->assertEquals(
            $expected,
            \json_decode($packer->pack($record), true)
        );
    }

    public function testToString() {
        $this->assertIsString((string) new FluentdPacker());
    }
}
