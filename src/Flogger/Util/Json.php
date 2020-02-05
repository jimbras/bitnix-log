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

use JsonException,
    JsonSerializable,
    Throwable;

/**
 * @version 0.1.0
 */
final class Json {

    const DEPTH = 512;
    const FLAGS
        = \JSON_UNESCAPED_SLASHES
        | \JSON_UNESCAPED_UNICODE
        | \JSON_PRESERVE_ZERO_FRACTION
        | \JSON_NUMERIC_CHECK
        | \JSON_THROW_ON_ERROR;

    private const OBJECT                  = 'object (%s)';
    private const RESOURCE                = 'resource (%s)';
    private const UTF8_REGEX              = '~~u';
    private const MALFORMED_UTF8          = '*** MALFORMED UTF-8 ***';
    private const MAX_STACK_DEPTH_REACHED = '*** MAX STACK DEPTH REACHED (%d) ***';
    private const JSON_ENCODE_ERROR       = '*** JSON ENCODE ERROR ***';

    private function __construct() {}

    /**
     * @param mixed $data
     * @param int $flags
     * @param int $depth
     * @param int $level
     * @return mixed
     */
    private static function process($data, int $flags, int $depth, int $level = 0) {
        if (null === $data) {
            return null;
        } else if (\is_bool($data)) {
            return $data ? true : false;
        } else if (\is_int($data)) {
            return $data;
        } else if (\is_float($data)) {
            if (\is_finite($data)) {
                return $data;
            }
            return \var_export($data, true);
        } else if (\is_string($data)) {
            if (false === @\preg_match(self::UTF8_REGEX, $data)) {
                return self::MALFORMED_UTF8;
            }
            return $data;
        } else if (\is_array($data)) {
            if (++$level > $depth) {
                return \sprintf(self::MAX_STACK_DEPTH_REACHED, $depth);
            }

            $buffer = [];
            foreach ($data as $key => $value) {
                $buffer[self::process($key, $flags, $depth, $level)]
                    = self::process($value, $flags, $depth, $level);
            }

            return $buffer;
        } else if (\is_object($data)) {
            if ($data instanceof Throwable) {
                return new Error($data);
            } else if ($data instanceof JsonSerializable) {
                return $data;
            } else {
                return \sprintf(self::OBJECT, \get_class($data));
            }
        } else if (\is_resource($data)) {
            return \sprintf(self::RESOURCE, \get_resource_type($data));
        } else {
            // just in case...
            return \gettype($data);
        }
    }

    /**
     * @param mixed $data
     * @param int $flags
     * @param int $depth
     * @return string
     */
    public static function encode($data, int $flags = self::FLAGS, int $depth = self::DEPTH) : string {
        $flags |= \JSON_THROW_ON_ERROR;
        $depth = \max(1, $depth);

        try {
            return \json_encode($data, $flags, $depth);
        } catch (Throwable $x1) {
            try {
                return \json_encode(self::process($data, $flags, $depth), $flags, $depth);
            } catch (Throwable $x2) {
                return \json_encode([
                    self::JSON_ENCODE_ERROR => new Error($x2)
                ], $flags);
            }
        }
    }
}
