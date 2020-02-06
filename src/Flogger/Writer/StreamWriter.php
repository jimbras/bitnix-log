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
final class StreamWriter implements Writer {

    /**
     * @var Packer
     */
    private Packer $packer;

    /**
     * @var string
     */
    private string $uri;

    /**
     * @var resource
     */
    private $stream;

    /**
     * @var bool
     */
    private bool $close;

    /**
     * @param string|resource $stream
     * @param null|Packer $packer
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function __construct($stream, Packer $packer = null) {
        $this->init($stream);
        $this->packer = $packer ?: new JsonPacker();
    }

    public function __destruct() {
        if ($this->close) {
            $this->close();
        }
    }

    private function close() : void {
        if (\is_resource($this->stream)) {
            \fclose($this->stream);
        }
        $this->stream = null;
    }

    /**
     * @param string|resource $stream
     * @param null|Packer $packer
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    private function init($stream) : void {
        if (\is_string($stream)) {

            $this->mkdir($stream);

            if (!$fp = \fopen($stream, 'ab')) {
                throw new RuntimeException(\sprintf(
                    'Failed to open log stream "%s"', $stream
                ));
            }

            $this->close = true;
            $this->stream = $fp;
            $this->uri = $stream;

        } else if (\is_resource($stream)) {

            if ('stream' !== ($type = \get_resource_type($stream))) {
                throw new InvalidArgumentException(\sprintf(
                    'Invalid resource type... stream required but got %s', $type
                ));
            }

            $this->close = false;
            $this->stream = $stream;
            $this->uri = \stream_get_meta_data($stream)['uri'] ?? '???';

        } else {
            throw new InvalidArgumentException(\sprintf(
                'Invalid stream parameter... resource or string required but got %s',
                    \gettype($stream)
            ));
        }
    }

    /**
     * @param string $uri
     * @throws RuntimeException
     */
    private function mkdir(string $uri) : void {

        if (false === \strpos($uri, '://')) {
            $dir = \dirname($uri);
        } else if (0 === ($pos = \strpos($uri, 'file://'))) {
            $dir = \dirname(\substr($uri, 7));
        } else {
            return;
        }

        if (!\is_dir($dir) && !\mkdir($dir, 0755, true)) {
            throw new RuntimeException(\sprintf(
                'Failed to create log directory "%s"', $dir
            ));
        }
    }

    /**
     * @return resource
     * @throws RuntimeException
     */
    private function stream() {
        if (\is_resource($this->stream)) {
            return $this->stream;
        }

        throw new RuntimeException(\sprintf(
            'Stream "%s" is closed', $this->uri
        ));
    }

    /**
     * @param Record $record
     * @throws RuntimeException
     */
    public function write(Record $record) : void {
        if (false === \fwrite($this->stream(), $this->packer->pack($record) . \PHP_EOL)) {
            $this->close();
            throw new RuntimeException(\sprintf(
                'Failed to write log record to stream "%s": %s',
                    $this->uri,
                    \error_get_last()['message'] ?? 'unknown error'
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
                $this->uri
        );
    }
}
