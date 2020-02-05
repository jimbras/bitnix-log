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

namespace Bitnix\Log\Flogger;

use DateTimeImmutable,
    DateTimeZone,
    JsonSerializable;

/**
 * @version 0.1.0
 */
final class Timestamp extends DateTimeImmutable implements JsonSerializable {

    private const NOW    = 'now';
    const FORMAT = 'Y-m-d\TH:i:s.uP';

    /**
     * @param DateTimeZone $timezone
     */
    public function __construct(DateTimeZone $timezone) {
        parent::__construct(self::NOW, $timezone);
    }

    /**
     * @return string
     */
    public function jsonSerialize() : string {
        return $this->format(self::FORMAT);
    }

    /**
     * @return string
     */
    public function __toString() : string {
        return \sprintf(
            '%s (%s)',
                self::CLASS,
                $this->format(self::FORMAT)
        );
    }

}
