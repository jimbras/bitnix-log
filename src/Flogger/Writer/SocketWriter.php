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

use InvalidArgumentException,
    RuntimeException,
    Bitnix\Log\Flogger\Packer,
    Bitnix\Log\Flogger\Record,
    Bitnix\Log\Flogger\Writer,
    Bitnix\Log\Flogger\Packer\JsonPacker;

/**
 * @version 0.1.0
 */
final class SocketWriter implements Writer {

    const CONNECTION_TIMEOUT = 'connection_timeout';
    const SOCKET_TIMEOUT     = 'socket_timeout';
    const PERSISTENT         = 'persistent';
    const WRITE_RETRY_COUNT  = 'write_retry_count';
    const WRITE_RETRY_SLEEP  = 'write_retry_sleep';
    const WRITE_RETRY_FACTOR = 'write_retry_factor';

    const DEFAULT_OPTIONS = [
        self::CONNECTION_TIMEOUT => 3,     // seconds
        self::SOCKET_TIMEOUT     => 3,     // seconds
        self::PERSISTENT         => false,
        self::WRITE_RETRY_COUNT  => 3,     // 0 to disable
        self::WRITE_RETRY_SLEEP  => 1000,  // microseconds
        self::WRITE_RETRY_FACTOR => 1,     // every retry increase sleep exponentially by this value
    ];

    /**
     * @var Packer
     */
    private Packer $packer;

    /**
     * @var resource
     */
    private $socket;

    /**
     * @var bool
     */
    private bool $close;

    /**
     * @var string
     */
    private string $uri;

    /**
     * @var int
     */
    private int $retries;

    /**
     * @var int
     */
    private int $sleep;

    /**
     * @var int
     */
    private int $factor;

    /**
     * @param string|resource $socket
     * @param null|Packer $packer
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function __construct($socket, Packer $packer = null, array $options = []) {
        $this->init($socket, $options);
        $this->packer = $packer ?: new JsonPacker();
    }

    public function __destruct() {
        if ($this->close) {
            $this->close();
        }
    }

    public function close() : void {
        if (\is_resource($this->socket)) {
            \fclose($this->socket);
        }
        $this->socket = null;
    }

    /**
     * @param mixed $value
     * @param int $min
     * @param string $message
     * @throws InvalidArgumentException
     */
    private function intval($value, int $min, string $message) : int {

        if (($int = \is_int($value)) && $value >= $min) {
            return $value;
        }

        throw new InvalidArgumentException(\sprintf(
            $message, $int ? $value : \gettype($value)
        ));
    }

    /**
     * @param mixed $flag
     * @return bool
     * @throws InvalidArgumentException
     */
    private function persistent($flag) : bool {
        if (\is_bool($flag)) {
            return $flag;
        }

        throw new InvalidArgumentException(\sprintf(
            'Invalid socket persistent flag, expected bool but got %s', \gettype($flag)
        ));
    }

    /**
     * @param array $opts
     * @throws InvalidArgumentException
     */
    private function retryOptions(array $opts) : void {
        $this->sleep = $this->intval(
            $opts[self::WRITE_RETRY_SLEEP], 1, 'Invalid retry sleep value: %s'
        );

        $this->retries = $this->intval(
            $opts[self::WRITE_RETRY_COUNT], 0, 'Invalid retry count value: %s'
        );

        $this->factor = $this->intval(
            $opts[self::WRITE_RETRY_FACTOR], 1, 'Invalid retry factor value: %s'
        );
    }

    /**
     * @param string $uri
     * @param array $opts
     * @throws InvalidArgumentException
     */
    private function connect(string $uri, array $opts) {
        $cto = $this->intval(
            $opts[self::CONNECTION_TIMEOUT], 1, 'Invalid socket connection timeout: %s'
        );

        $sto = $this->intval(
            $opts[self::SOCKET_TIMEOUT], 1, 'Invalid socket timeout: %s'
        );

        $persistent = $this->persistent($opts[self::PERSISTENT]);

        $flags = \STREAM_CLIENT_CONNECT;

        if ($persistent) {
            $flags |= \STREAM_CLIENT_PERSISTENT;
        }

        if (!$socket = \stream_socket_client($uri, $ec, $em, $cto, $flags)) {
            throw new RuntimeException(\sprintf(
                'Failed connecting to socket "%s": %s',
                    $uri,
                    \error_get_last()['message'] ?? $em ?? 'unknow error'
            ));
        }

        \stream_set_timeout($socket, $sto);

        $this->socket = $socket;
        $this->uri = $uri;
        $this->close = !$persistent;
    }

    /**
     * @param string|resource $socket
     * @param array $opts
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    private function init($socket, array $opts) : void {

        $opts += self::DEFAULT_OPTIONS;

        $this->retryOptions($opts);

        if (\is_string($socket)) {

            $this->connect($socket, $opts);

        } else if (\is_resource($socket)) {

            $type = \get_resource_type($socket);

            if ('stream' !== $type && 'persistent stream' !== $type) {
                throw new InvalidArgumentException(\sprintf(
                    'Unsupported socket resource type: %s', $type
                ));
            }

            $this->socket = $socket;
            $this->uri = \stream_get_meta_data($socket)['uri'] ?? '???';
            $this->close = false;

        } else {
            throw new InvalidArgumentException(\sprintf(
                'Invalid socket parameter, expecting string or resource but got %s',
                    \gettype($socket)
            ));
        }
    }

    /**
     * @return resource
     * @throws RuntimeException
     */
    private function socket() {
        if (\is_resource($this->socket)) {
            return $this->socket;
        }

        throw new RuntimeException(\sprintf(
            'Socket "%s" is closed', $this->uri
        ));
    }

    /**
     * @param Record $record
     * @throws RuntimeException
     */
    public function write(Record $record) : void {
        $socket = $this->socket();

        $retry = $consumed = 0;
        $content = $this->packer->pack($record);
        $size = \strlen($content);

        while ($consumed < $size) {
            $sent = \fwrite($socket, $content);

            if ($sent) {
                $consumed += $sent;
                $content = \substr($content, $sent);
                continue;
            }

            if (!$this->retries || (++$retry > $this->retries)) {
                $this->close();
                throw new RuntimeException(\sprintf(
                    'Error writing to socket "%s"', $this->uri
                ));
            }

            \usleep($this->sleep * pow($this->factor, $retry));
        }
    }

    /**
     * @return string
     */
    public function __toString() : string {
        return \sprintf(
            '%s (%s)',
                self::CLASS,
                $this->uri
        );
    }
}
