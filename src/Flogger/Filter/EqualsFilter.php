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

use Bitnix\Log\Flogger\Filter;

/**
 * @version 0.1.0
 */
final class EqualsFilter implements Filter {

    /**
     * @var bool
     */
    private bool $accept;

    /**
     * @var string
     */
    private string $value;

    /**
     * @param string $value
     * @param bool $accept
     */
    public function __construct(string $value, bool $accept = true) {
        $this->value = $value;
        $this->accept = $accept;
    }

    /**
     * @param string $value
     * @return bool
     */
    public function accept(string $value) : bool {
        return $value === $this->value ? $this->accept : !$this->accept;
    }

    /**
     * @return string
     */
    public function __toString() : string {
        return self::CLASS;
    }
}
