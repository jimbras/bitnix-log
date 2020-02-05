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

use JsonSerializable;

/**
 * @version 0.1.0
 */
final class Record implements JsonSerializable {

    /**
     * @var Timestamp
     */
    private Timestamp $timestamp;

    /**
     * @var string
     */
    private string $channel;

    /**
     * @var string
     */
    private string $tag;

    /**
     * @var array
     */
    private array $context;

    /**
     * @var array
     */
    private array $payload;

    /**
     * @param Timestamp $timestamp
     * @param string $channel
     * @param string $tag
     * @param array $payload
     * @param array $context
     */
    public function __construct(
        Timestamp $timestamp,
        string $channel,
        string $tag,
        array $payload,
        array $context = []
    ) {
        $this->timestamp = $timestamp;
        $this->channel = $channel;
        $this->tag = $tag;
        $this->payload = $payload;
        $this->context = $context;
    }

    /**
     * @return Timestamp
     */
    public function timestamp() : Timestamp {
        return $this->timestamp;
    }

    /**
     * @return string
     */
    public function channel() : string {
        return $this->channel;
    }

    /**
     * @return string
     */
    public function tag() : string {
        return $this->tag;
    }

    /**
     * @return array
     */
    public function context() : array {
        return $this->context;
    }

    /**
     * @return array
     */
    public function payload() : array {
        return $this->payload;
    }

    /**
     * @return array
     */
    public function jsonSerialize() : array {
        return [
            'timestamp' => $this->timestamp,
            'channel'   => $this->channel,
            'tag'       => $this->tag,
            'context'   => $this->context,
            'payload'   => $this->payload
        ];
    }

    /**
     * @return string
     */
    public function __toString() : string {
        return \sprintf(
            '%s (%s,%s,%s)',
                self::CLASS,
                $this->timestamp->format(Timestamp::FORMAT),
                $this->channel,
                $this->tag
        );
    }
}
