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

use RuntimeException,
    Bitnix\Log\Flogger\Packer,
    Bitnix\Log\Flogger\Record,
    Bitnix\Log\Flogger\Writer,
    Bitnix\Log\Flogger\Packer\JsonPacker;

/**
 * @version 0.1.0
 */
final class FileWriter implements Writer {

    private const FLAGS = LOCK_EX | FILE_APPEND;

    /**
     * @var Packer
     */
    private Packer $packer;

    /**
     * @var string
     */
    private string $file;

    /**
     * @param string $file
     * @param null|Packer $packer
     * @throws RuntimeException
     */
    public function __construct(string $file, Packer $packer = null) {
        $dir = \dirname($file);

        if (!\is_dir($dir) && !\mkdir($dir, 0755, true)) {
            throw new RuntimeException(\sprintf(
                'Failed to create log directory "%s"', $dir
            ));
        }

        $this->file = $file;
        $this->packer = $packer ?: new JsonPacker();
    }

    /**
     * @param Record $record
     * @throws RuntimeException
     */
    public function write(Record $record) : void {
        $entry = $this->packer->pack($record) . \PHP_EOL;
        if (false === \file_put_contents($this->file, $entry, self::FLAGS)) {
            throw new RuntimeException(\sprintf(
                'Failed to write log to file "%s": %s',
                    $this->file,
                    \error_get_last()['message'] ?? ''
            ));
        }
    }

    /**
     * @return string
     */
    public function __toString() : string {
        return \sprintf(
            '%s (%s)',
                self::CLASS,
                $this->file
        );
    }
}
