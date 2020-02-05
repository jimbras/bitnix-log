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

/**
 * @version 0.1.0
 */
trait ByteFormatter {

    private array $thresholds = [
        ' GB' => 1073741824,    // 1024 * 1024 *1024
        ' MB' => 1048576,       // 1024 * 1024
        ' KB' => 1024
    ];

    private string $bytes = ' B';

    /**
     * @param int $bytes
     * @return string
     */
    private function formatBytes(int $bytes) : string {
        $bytes = \max(0, $bytes);
        foreach ($this->thresholds as $suffix => $value) {
            if ($bytes >= $value) {
                return round($bytes / $value, 3) . $suffix;
            }
        }

        return $bytes . $this->bytes;
    }
}
