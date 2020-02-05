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

use JsonSerializable;

/**
 * @version 0.1.0
 */
final class MemoryPeakUsage implements JsonSerializable {

    use ByteFormatter;

    /**
     * @return string
     */
    public function value() : string {
        return $this->jsonSerialize();
    }

    /**
     * @return string
     */
    public function jsonSerialize() : string {
        return $this->formatBytes(\memory_get_peak_usage());
    }

    /**
     * @return string
     */
    public function __toString() : string {
        return self::CLASS;
    }
}
