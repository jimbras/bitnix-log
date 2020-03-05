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

use DateTimeZone,
    Throwable,
    Bitnix\Log\Logger,
    Bitnix\Log\Flogger\Util\Error,
    Bitnix\Log\Flogger\Util\Json,
    Psr\Log\AbstractLogger;

/**
 * @version 0.1.0
 */
final class Channel extends AbstractLogger implements Logger {

    /**
     * @var DateTimeZone
     */
    private DateTimeZone $timezone;

    /**
     * @var Writer
     */
    private Writer $writer;

    /**
     * @var Context
     */
    private Context $context;

    /**
     * @var null|Filter
     */
    private ?Filter $filter;

    /**
     * @var string
     */
    private string $name;

    /**
     * @var callable
     */
    private $onerror;

    /**
     * @param DateTimeZone $timezone
     * @param Writer $writer
     * @param Context $context
     * @param string $name
     * @param null|Filter $filter
     * @param null|callable $onerror
     */
    public function __construct(
        DateTimeZone $timezone,
        Writer $writer,
        Context $context,
        string $name,
        Filter $filter = null,
        callable $onerror = null) {

        $this->timezone = $timezone;
        $this->writer = $writer;
        $this->context = $context;
        $this->name = $name;
        $this->filter = $filter;
        $this->onerror = $onerror ?: fn($record, $x) => \error_log(Json::encode(
            $this->record('log.error', [
                'tag'     => $record->tag(),
                'payload' => $record->payload(),
                'error'   => new Error($x)
            ])
        ));
    }

    /**
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @codeCoverageIgnore
     */
    public function log($level, $message, array $context = []) {
        $tag = (string) $level;

        if (!$this->filter || $this->filter->accept($tag)) {

            $record = $this->record($tag, $this->context($message, $context));

            try {
                $this->writer->write($record);
            } catch (Throwable $x) {
                ($this->onerror)($record, $x);
            }
        }
    }

    /**
     * @param string $message
     * @param array $context
     * @return array
     */
    private function context(string $message, array $context) : array {
        if (false !== \strpos($message, '{')) {
            $replace = [];

            foreach ($context as $key => $value) {
                $tag = '{' . $key . '}';
                if (false !== \strpos($message, $tag)) {
                    $replace[$tag] = \trim(Json::encode($value), '"');
                    unset($context[$key]);
                }
            }

            $message = \strtr($message, $replace);
        }

        $context['message'] = $message;
        return $context;
    }

    /**
     * @param string $tag
     * @param array $payload
     */
    public function post(string $tag, array $payload) : void {
        if (!$this->filter || $this->filter->accept($tag)) {
            $record = $this->record($tag, $payload);
            try {
                $this->writer->write($record);
            } catch (Throwable $x) {
                ($this->onerror)($record, $x);
            }
        }
    }

    /**
     * @param string $tag
     * @param array $payload
     * @return Record
     */
    private function record(string $tag, array $payload) : Record {
        return new Record(
            new Timestamp($this->timezone),
            $this->name,
            $tag,
            $payload,
            $this->context->map()
        );
    }

    /**
     * @return string
     */
    public function __toString() : string {
        return \sprintf(
            '%s (%s)',
                self::CLASS,
                $this->name
        );
    }
}
