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

namespace Bitnix\Log\Flogger\Util;

use Exception,
    JsonSerializable,
    PHPUnit\Framework\TestCase;

/**
 * @version 0.1.0
 */
class JsonTest extends TestCase implements JsonSerializable {

    public function testEncodeValidJson() {
        $input = [
            'null'   => null,
            'bool'   => true,
            'int'    => 1,
            'float'  => 2.3,
            'string' => 'foo',
            'map'    => ['foo' => 'bar'],
            'list'   => [1, 2, 3],
            'json'   => $this,
            'object' => new \stdClass()
        ];

        $output = $input;
        $output['json'] = 'test';
        $output['object'] = [];

        $this->assertSame($output, \json_decode(Json::encode($input), true));
    }

    public function testEncodeInvalidJson() {
        $fp = \fopen('php://memory', 'wb');
        $x = new Exception('kaput');
        $error = new Error($x);

        $input = [
            'null'   => null,
            'bool'   => false,
            'int'    => 1,
            'float'  => 2.3,
            '-inf'   => log(0),
            'inf'    => -log(0),
            'nan'    => acos(8),
            'string' => 'foo',
            'map'    => ['foo' => "\xCE"], // ά = \xCE\xAC
            'list'   => [1, 2, 3],
            'json'   => $this,
            'stream' => $fp,
            'object' => new \stdClass(),
            'error'  => $x
        ];

        $output = $input;
        foreach ([
            '-inf'   => '-INF',
            'inf'    => 'INF',
            'nan'    => 'NAN',
            'json'   => 'test',
            'stream' => 'resource (stream)',
            'map'    => ['foo' => '*** MALFORMED UTF-8 ***'],
            'object' => 'object (stdClass)',
            'error'  => $error->info()
        ] as $key => $value) {
            $output[$key] = $value;
        }

        try {
            $result = \json_decode(Json::encode($input), true);
        } finally {
            \fclose($fp);
        }

        $this->assertSame($output, $result);
    }

    public function testJsonEncodeDoubleError() {
        $input = ['foo' => new class() implements JsonSerializable {
            public function jsonSerialize() { return "\xCE"; }
        }];
        $result = \json_decode(Json::encode($input), true);
        $this->assertTrue(isset($result['*** JSON ENCODE ERROR ***']));
        $this->assertIsArray($result['*** JSON ENCODE ERROR ***']);
    }

    public function testJsonEncodeStackOverflowError() {
        $input = [[[[4]]]];
        $this->assertEquals(
            [[['*** MAX STACK DEPTH REACHED (3) ***']]],
            \json_decode(Json::encode($input, Json::FLAGS, 3), true)
        );
    }

    public function jsonSerialize() : string {
        return 'test';
    }
}
