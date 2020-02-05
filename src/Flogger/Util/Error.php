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

namespace Bitnix\Log\Flogger\Util;

use JsonSerializable,
    Throwable;

/**
 * @version 0.1.0
 */
final class Error implements JsonSerializable {

    /**
     * @var Throwable
     */
    private $error = null;

    /**
     * @var array
     */
    private $dump = null;

    /**
     * @param Throwable $error
     */
    public function __construct(Throwable $error) {
        $this->error = $error;
    }

    /**
     * @param Throwable $t
     * @return array
     */
    private function dump(Throwable $t) : array {
        return [
            'class'   => \get_class($t),
            'message' => $t->getMessage(),
            'code'    => $t->getCode(),
            'file'    => $t->getFile(),
            'line'    => $t->getLine()
        ];
    }

    /**
     * @return array
     */
    public function jsonSerialize() : array {
        if (null === $this->dump) {
            $x = $p = $this->error;
            $this->dump = $this->dump($x);

            $stack = [];
            while ($x = $x->getPrevious()) {
                $p = $x;
                $stack[] = $this->dump($x);
            }

            $this->dump['trace'] = \explode(\PHP_EOL, $p->getTraceAsString());
            $this->dump['stack'] = $stack;
        }
        return $this->dump;
    }

    /**
     * @return array
     */
    public function info() : array {
        return $this->jsonSerialize();
    }

    /**
     * @return string
     */
    public function __toString() : string {
        return \sprintf(
            '%s (%s)',
                self::CLASS,
                \get_class($this->error)
        );
    }
}
