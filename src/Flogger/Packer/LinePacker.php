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

use DateTimeInterface as DateTime,
    Bitnix\Log\Flogger\Packer,
    Bitnix\Log\Flogger\Record,
    Bitnix\Log\Flogger\Util\Json;

/**
 * @version 0.1.0
 */
final class LinePacker implements Packer {

    private const EMPTY_STRING  = '';
    private const DEFAULT_SPEC  = '%s';
    private const ALIGNED_SPEC  = '%%%ds';
    private const PATTERN_REGEX
        = '~\{(timestamp|channel|tag|context|payload)(:(.[^{]+))?\}((?:\+|\-)?\d+)?~';

    const PATTERN = '{timestamp} | {channel} | {tag} | {context} | {payload}';

    /**
     * @var string
     */
    private string $pattern;

    /**
     * @var array
     */
    private array $handlers = [];

    /**
     * @param string $pattern
     */
    public function __construct(string $pattern = self::PATTERN) {
        $this->parse($pattern);
    }

    /**
     * @param string $pattern
     */
    private function parse(string $pattern) : void {
        $escape = '__' . \bin2hex(\random_bytes(4));

        $this->pattern = \str_replace(
            ['\{', '%'], [$escape, '%%'], $pattern
        );

        \preg_match_all(self::PATTERN_REGEX, $pattern, $matches);

        $found = $matches[0];
        $tags = $matches[1];
        $data = $matches[3];
        $align = $matches[4];

        foreach ($tags as $index => $value) {
            //$parser = 'parse' . \ucfirst($value);
            $this->handlers[] = $this->$value(\trim($data[$index]));
            $this->update($found[$index], \trim($align[$index]));
        }

        $this->pattern = \str_replace($escape, '{', $this->pattern);
    }

    /**
     * @param string $search
     * @param string $align
     */
    private function update(string $search, string $align) : void {
        $tpl = self::EMPTY_STRING === $align
            ? self::DEFAULT_SPEC
            : \sprintf(self::ALIGNED_SPEC, (int) $align);
        $this->pattern = \str_replace($search, $tpl, $this->pattern);
    }

    /**
     * @param string $format
     * @return callable
     */
    private function timestamp(string $format) : callable {

        if (self::EMPTY_STRING === $format) {
            $format = DateTime::ATOM;
        }

        return fn(Record $record) => $record->timestamp()->format($format);
    }

    /**
     * @param string $value
     * @return callable
     */
    private function channel(string $value) : callable {
        return fn(Record $record) => $record->channel();
    }

    /**
     * @param string $value
     * @return callable
     */
    private function tag(string $value) : callable {
        return fn(Record $record) => $record->tag();
    }

    /**
     * @param string $value
     * @return callable
     */
    private function context(string $value) {

        if (self::EMPTY_STRING === $value) {
            return fn(Record $record) => Json::encode($record->context());
        }

        return fn(Record $record) => \trim(
            Json::encode($record->context()[$value] ?? self::EMPTY_STRING), '"'
        );
    }

    /**
     * @param string $value
     * @return callable
     */
    private function payload(string $value) {

        if (self::EMPTY_STRING === $value) {
            return fn(Record $record) => Json::encode($record->payload());
        }

        return fn(Record $record) => \trim(
            Json::encode($record->payload()[$value] ?? self::EMPTY_STRING), '"'
        );
    }

    /**
     * @param Record $record
     * @return string
     * @throws \Throwable
     */
    public function pack(Record $record) : string {
        $data = [];
        foreach ($this->handlers as $fn) {
            $data[] = $fn($record);
        }
        return \vsprintf($this->pattern, $data);
    }

    /**
     * @return string
     */
    public function __toString() : string {
        return self::CLASS;
    }

}
