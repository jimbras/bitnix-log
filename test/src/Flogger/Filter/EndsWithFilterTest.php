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
class EndsWithFilterTest extends TestCase {

    public function testEndsWithCaseSensitiveAccept() {
        $filter = new EndsWithFilter('foo');
        $this->assertTrue($filter->accept('bar.foo'));
        $this->assertFalse($filter->accept('foo.bar'));
        $this->assertFalse($filter->accept('Bar.Foo'));
    }

    public function testEndsWithCaseInsensitiveAccept() {
        $filter = new EndsWithFilter('foo', true, false);
        $this->assertTrue($filter->accept('bar.foo'));
        $this->assertFalse($filter->accept('foo.bar'));
        $this->assertTrue($filter->accept('Bar.Foo'));
    }

    public function testEndsWithCaseSensitiveDeny() {
        $filter = new EndsWithFilter('foo', false);
        $this->assertTrue($filter->accept('foo.bar'));
        $this->assertFalse($filter->accept('bar.foo'));
        $this->assertTrue($filter->accept('Foo.Bar'));
    }

    public function testEndsWithCaseInsensitiveDeny() {
        $filter = new EndsWithFilter('foo', false, false);
        $this->assertTrue($filter->accept('foo.bar'));
        $this->assertFalse($filter->accept('bar.foo'));
        $this->assertTrue($filter->accept('Foo.Bar'));
    }

    public function testToString() {
        $this->assertIsString((string) new EndsWithFilter('foo'));
    }
}
