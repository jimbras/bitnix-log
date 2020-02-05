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

use DateTimeImmutable as DateTime,
    DateTimeZone,
    Bitnix\Log\Flogger\Record,
    Bitnix\Log\Flogger\Timestamp,
    Bitnix\Log\Flogger\Util\Json,
    PHPUnit\Framework\TestCase;

/**
 * @version 0.1.0
 */
class LinePackerTest extends TestCase {

    public function testDefaultFormat() {
        $packer = new LinePacker();

        $time = new Timestamp(new DateTimeZone('UTC'));
        $channel = 'foo';
        $tag = 'app.error';
        $payload = ['zig' => 'zag'];
        $metadata = ['zoid' => 'berg'];

        $record = new Record($time, $channel, $tag, $payload, $metadata);
        $expected = \sprintf(
            '%s | %s | %s | %s | %s',
                $time->format(DateTime::ATOM),
                $channel,
                $tag,
                Json::encode($metadata),
                Json::encode($payload)
        );

        $this->assertEquals($expected, $packer->pack($record));
    }

    public function testTimeFormat() {
        $packer = new LinePacker('[{timestamp:d/m/Y H:i:s}-30] [{timestamp}+30]');

        $time = new Timestamp(new DateTimeZone('UTC'));
        $record = new Record($time, 'foo', 'bar', []);

        $expected = \sprintf(
            '[%-30s] [%30s]',
                $time->format('d/m/Y H:i:s'),
                $time->format(DateTime::ATOM)
        );

        $this->assertEquals($expected, $packer->pack($record));
    }

    public function testChannelFormat() {
        $packer = new LinePacker('[{channel}-30] [{channel}30]');

        $time = new Timestamp(new DateTimeZone('UTC'));
        $record = new Record($time, 'foo', 'bar', []);

        $expected = \sprintf('[%-30s] [%30s]', 'foo', 'foo');

        $this->assertEquals($expected, $packer->pack($record));
    }

    public function testTagFormat() {
        $packer = new LinePacker('[{tag}-30] [{tag}+30]');

        $time = new Timestamp(new DateTimeZone('UTC'));
        $record = new Record($time, 'foo', 'bar', []);

        $expected = \sprintf('[%-30s] [%30s]', 'bar', 'bar');

        $this->assertEquals($expected, $packer->pack($record));
    }

    public function testContextFormat() {
        $packer = new LinePacker('[zoid: {context:zoid}+10]');

        $time = new Timestamp(new DateTimeZone('UTC'));
        $record = new Record($time, 'foo', 'bar', [], ['zoid' => 'berg']);

        $expected = \sprintf('[zoid: %10s]', 'berg');
        $this->assertEquals($expected, $packer->pack($record));

        $packer = new LinePacker('[zoid: {context:foo}-10]');
        $expected = \sprintf('[zoid: %-10s]', '');
        $this->assertEquals($expected, $packer->pack($record));
    }

    public function testPayloadFormat() {
        $packer = new LinePacker('[zoid: {payload:zoid}10]');

        $time = new Timestamp(new DateTimeZone('UTC'));
        $record = new Record($time, 'foo', 'bar', ['zoid' => 'berg']);

        $expected = \sprintf('[zoid: %10s]', 'berg');
        $this->assertEquals($expected, $packer->pack($record));

        $packer = new LinePacker('[zoid: {payload:foo}-10]');
        $expected = \sprintf('[zoid: %-10s]', '');
        $this->assertEquals($expected, $packer->pack($record));
    }

    public function testFormatWithSpecialChars() {
         // % is used internally by sprintf
        $packer = new LinePacker('\{{channel}-10} % \{{tag}10} \{tag}');
        $time = new Timestamp(new DateTimeZone('UTC'));
        $record = new Record($time, 'foo', 'bar', []);

        $expected = '{foo       } % {       bar} {tag}';
        $this->assertEquals($expected, $packer->pack($record));
    }

    public function testToString() {
        $this->assertIsString((string) new LinePacker());
    }
}
