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

namespace Bitnix\Log\Flogger\Packer;

use Bitnix\Log\Flogger\Packer,
    Bitnix\Log\Flogger\Record,
    Bitnix\Log\Flogger\Util\Json;

/**
 * @version 0.1.0
 */
final class FluentdPacker implements Packer {

    /**
     * @param Record $record
     * @return string
     * @throws \Throwable
     */
    public function pack(Record $record) : string {
        return Json::encode([
            $record->tag(),
            $record->timestamp()->getTimestamp(),
            [
                'channel' => $record->channel(),
                'context' => $record->context(),
                'payload' => $record->payload()
            ]
        ]);
    }

    /**
     * @return string
     */
    public function __toString() : string {
        return self::CLASS;
    }

}
