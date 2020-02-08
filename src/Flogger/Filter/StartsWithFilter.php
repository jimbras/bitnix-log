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
final class StartsWithFilter implements Filter {

    /**
     * @var bool
     */
    private bool $accept;

    /**
     * @var string
     */
    private string $needle;

    /**
     * @var int
     */
    private int $size;

    /**
     * @var bool
     */
    private bool $caseless;

    /**
     * @param string $needle
     * @param bool $accept
     * @param bool $case
     */
    public function __construct(string $needle, bool $accept = true, bool $case = true) {
        $this->needle = $needle;
        $this->accept = $accept;
        $this->size = \strlen($needle);
        $this->caseless = !$case;
    }

    /**
     * @param string $haystack
     * @return bool
     */
    public function accept(string $haystack) : bool {
        return 0 === \substr_compare($haystack, $this->needle, 0, $this->size, $this->caseless)
            ? $this->accept : !$this->accept;
    }

    /**
     * @return string
     */
    public function __toString() : string {
        return self::CLASS;
    }
}
