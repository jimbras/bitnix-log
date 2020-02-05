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

use PHPUnit\Framework\TestCase;

/**
 * @version 0.1.0
 */
class ElapsedTimeTest extends TestCase {

    public function testConstructorAssignsDecimalsCorrectly() {
        $elapsed = new ElapsedTime();
        $value = $elapsed->value();
        $this->assertTrue((bool) \preg_match('~\.\d{3}$~', (string) $value));

        $elapsed = new ElapsedTime(5);
        $value = $elapsed->value();
        $this->assertTrue((bool) \preg_match('~\.\d{5}$~', (string) $value));
    }

    public function testJsonSupport() {
        $this->assertIsFloat(
            \json_decode(\json_encode(new ElapsedTime()))
        );
    }

    public function testToString() {
        $this->assertIsString((string) new ElapsedTime());
    }

}
