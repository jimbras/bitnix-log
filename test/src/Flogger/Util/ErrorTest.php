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
    PHPUnit\Framework\TestCase;

/**
 * @version 0.1.0
 */
class ErrorTest extends TestCase {

    public function testInfoReturnsExpectedStructure() {
        $x1 = new Exception('x1');
        $x2 = new Exception('x2', 1, $x1);
        $x3 = new Exception('x3', 2, $x2);

        $error = new Error($x3);
        $info = $error->info();

        $keys = \array_keys($info);
        sort($keys);
        $this->assertEquals(['class', 'code', 'file', 'line', 'message', 'stack', 'trace'], $keys);

        $this->assertDump($x3, $info);

        $this->assertTrue(\is_array($info['trace']) && !empty($info['trace']));
        $this->assertTrue(2 === \count($info['stack']));

        $x2info = $info['stack'][0];
        $keys = array_keys($x2info);
        sort($keys);
        $this->assertEquals(['class', 'code', 'file', 'line', 'message'], $keys);
        $this->assertDump($x2, $x2info);

        $x1info = $info['stack'][1];
        $keys = array_keys($x1info);
        sort($keys);
        $this->assertEquals(['class', 'code', 'file', 'line', 'message'], $keys);
        $this->assertDump($x1, $x1info);
    }

    private function assertDump(Exception $x, array $info) {
        $this->assertEquals(Exception::CLASS, $info['class']);
        $this->assertEquals($x->getMessage(), $info['message']);
        $this->assertSame($x->getCode(), $info['code']);
        $this->assertEquals($x->getFile(), $info['file']);
        $this->assertEquals($x->getLine(), $info['line']);
    }

    public function testJsonSupport() {
        $error = new Error(new Exception('kaput'));
        $this->assertEquals($error->info(), \json_decode(\json_encode($error), true));
    }

    public function testToString() {
        $this->assertIsString((string) new Error(new Exception('kaput')));
    }

}
