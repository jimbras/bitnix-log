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

namespace Bitnix\Log\Flogger\Filter;

use PHPUnit\Framework\TestCase;

/**
 * @version 0.1.0
 */
class ContainsFilterTest extends TestCase {

    public function testAcceptCaseSensitive() {
        $filter = new ContainsFilter('foo');
        $this->assertTrue($filter->accept('app.foo.bar'));
        $this->assertFalse($filter->accept('app.Foo.bar'));
        $this->assertFalse($filter->accept('zig,zag'));
    }

    public function testAcceptCaseInsensitive() {
        $filter = new ContainsFilter('foo', true, false);
        $this->assertTrue($filter->accept('app.foo.bar'));
        $this->assertTrue($filter->accept('app.Foo.bar'));
        $this->assertFalse($filter->accept('zig,zag'));
    }

    public function testDenyCaseSensitive() {
        $filter = new ContainsFilter('foo', false);
        $this->assertFalse($filter->accept('app.foo.bar'));
        $this->assertTrue($filter->accept('app.Foo.bar'));
        $this->assertTrue($filter->accept('zig,zag'));
    }

    public function testDenyCaseInsensitive() {
        $filter = new ContainsFilter('foo', false, false);
        $this->assertFalse($filter->accept('app.foo.bar'));
        $this->assertFalse($filter->accept('app.Foo.bar'));
        $this->assertTrue($filter->accept('zig,zag'));
    }

    public function testToString() {
        $this->assertIsString((string) new ContainsFilter('foo'));
    }
}
