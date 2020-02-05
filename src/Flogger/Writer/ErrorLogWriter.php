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

namespace Bitnix\Log\Flogger\Writer;

use Bitnix\Log\Flogger\Packer,
    Bitnix\Log\Flogger\Record,
    Bitnix\Log\Flogger\Writer,
    Bitnix\Log\Flogger\Packer\LinePacker;

/**
 * @version 0.1.0
 */
final class ErrorLogWriter implements Writer {

    const SYSTEM = 0;
    const SAPI   = 4;

    private const CHANNELS = [
        self::SYSTEM => 'system',
        self::SAPI   => 'sapi'
    ];

    /**
     * @var Packer
     */
    private Packer $packer;

    /**
     * @var int
     */
    private int $channel;

    /**
     * @param null|Packer $packer
     * @param int $channel
     */
    public function __construct(Packer $packer = null, int $channel = self::SYSTEM) {
        $this->packer = $packer ?: new LinePacker();
        $this->channel = isset(self::CHANNELS[$channel]) ? $channel : self::SYSTEM;
    }

    /**
     * @param Record $record
     */
    public function write(Record $record) : void {
        \error_log($this->packer->pack($record), $this->channel);
    }

    /**
     * @return string
     */
    public function __toString() : string {
        return \sprintf(
            '%s (channel=%s)',
                self::CLASS,
                self::CHANNELS[$this->channel]
        );
    }
}
