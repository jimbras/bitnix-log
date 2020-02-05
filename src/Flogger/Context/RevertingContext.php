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

use Bitnix\Log\Flogger\Context;

/**
 * @version 0.1.0
 */
final class RevertingContext implements Context {

    /**
     * @var array
     */
    private array $context;

    /**
     * @var array
     */
    private array $defaults;

    /**
     * @param array $defaults
     */
    public function __construct(array $defaults) {
        $this->context = $this->defaults = $defaults;
    }

    /**
     * @return array
     */
    public function map() : array {
        return $this->context;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function value(string $key) {
        return $this->context[$key] ?? null;
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    public function put(string $key, $value) : void {
        $this->context[$key] = $value;
    }

    /**
     * @param string ...$keys
     */
    public function remove(string ...$keys) : void {
        foreach ($keys as $key) {
            if (\array_key_exists($key, $this->context)) {
                if (\array_key_exists($key, $this->defaults)) {
                    $this->context[$key] = $this->defaults[$key];
                } else {
                    unset($this->context[$key]);
                }
            }
        }
    }

    /**
     * @return string
     */
    public function __toString() : string {
        return self::CLASS;
    }
}
