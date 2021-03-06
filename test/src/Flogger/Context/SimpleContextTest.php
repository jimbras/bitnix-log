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

namespace Bitnix\Log\Flogger\Context;

use Bitnix\Log\Flogger\Context,
    PHPUnit\Framework\TestCase;

/**
 * @version 0.1.0
 */
class SimpleContextTest extends TestCase {

    private Context $context;

    public function setUp() : void {
        $this->context = new SimpleContext([
            'foo' => 'bar',
            'zig' => 'zag'
        ]);
    }

    public function testConstructor() {
        $this->assertEquals('bar', $this->context->value('foo'));
        $this->assertEquals('zag', $this->context->value('zig'));
        $this->assertNull($this->context->value('zoid'));

        $this->assertEquals(
            ['foo' => 'bar', 'zig' => 'zag'],
            $this->context->map()
        );
    }

    public function testModifyContext() {
        $this->context->put('foo', 'baz');
        $this->context->put('zoid', 'berg');

        $this->assertEquals('baz', $this->context->value('foo'));
        $this->assertEquals('zag', $this->context->value('zig'));
        $this->assertEquals('berg', $this->context->value('zoid'));

        $this->assertEquals(
            ['foo' => 'baz', 'zig' => 'zag', 'zoid' => 'berg'],
            $this->context->map()
        );

        $this->context->remove('foo', 'zoid');

        $this->assertNull($this->context->value('foo'));
        $this->assertEquals('zag', $this->context->value('zig'));
        $this->assertNull($this->context->value('zoid'));

        $this->assertEquals(
            ['zig' => 'zag'],
            $this->context->map()
        );

        $this->context->put('null', null);
        $this->assertEquals(
            ['zig' => 'zag', 'null' => null],
            $this->context->map()
        );

        $this->context->remove('null');
        $this->assertEquals(
            ['zig' => 'zag'],
            $this->context->map()
        );
    }

    public function testToString() {
        $this->assertIsString((string) $this->context);
    }
}
