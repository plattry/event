<?php

declare(strict_types=1);

namespace Plattry\Event\Network;

use InvalidArgumentException;
use Plattry\Event\Dispatch\DispatcherAwareTrait;
use Plattry\Event\Notification\LooperAwareTrait;
use RuntimeException;

/**
 * A server instance.
 */
class Server
{
    use ProtocolAwareTrait;
    use DispatcherAwareTrait;
    use LooperAwareTrait;

    /**
     * The tcp protocol.
     */
    protected const TRANSPORT_TCP = "tcp";

    /**
     * The udp protocol.
     */
    protected const TRANSPORT_UDP = "udp";

    /**
     * The socket context.
     * @var resource
     */
    protected mixed $context;

    /**
     * The file describer.
     * @var resource
     */
    protected mixed $fd;

    /**
     * The constructor.
     */
    public function __construct()
    {
        $this->context = stream_context_create(['socket' => ['so_reuseport' => 1]]);
    }

    /**
     * Check $address, and return a valid address or false.
     * @param string $address
     * @return bool|string
     */
    protected static function parseAddress(string $address): bool|string
    {
        $address = strtolower(trim($address));

        // Get transport protocol.
        $transportPos = strpos($address, "://");
        if ($transportPos === false) {
            $transport = self::TRANSPORT_TCP;
            $transportPos = 0;
        } else {
            $transport = substr($address, 0, $transportPos);
            if ($transport !== self::TRANSPORT_TCP && $transport !== self::TRANSPORT_UDP) {
                return false;
            }

            $transportPos += 3;
        }

        // Get ip address.
        $ipPos = strpos($address, ":", $transportPos);
        if ($ipPos === false) {
            $ip = "0.0.0.0";
            $ipPos = 0;
        } else {
            $ip = substr($address, $transportPos, $ipPos - $transportPos);
            if (preg_match("/^((25[0-5]|2[0-4]\d|[01]?\d\d?)\.){3}(25[0-5]|2[0-4]\d|[01]?\d\d?)$/", $ip) !== 1) {
                return false;
            }

            $ipPos += 1;
        }

        // Get port.
        $port = substr($address, $ipPos);
        if (is_numeric($port) === false) {
            return false;
        }

        return sprintf("%s://%s:%d", $transport, $ip, $port);
    }

    /**
     * Create a socket.
     * @param string $address
     * @param mixed $context
     * @return mixed|resource
     */
    protected static function createSocket(string $address, mixed $context): mixed
    {
        $flags = str_starts_with($address, self::TRANSPORT_UDP) ?
            STREAM_SERVER_BIND : STREAM_SERVER_BIND | STREAM_SERVER_LISTEN;

        ($fd = stream_socket_server($address, $errno, $msg, $flags, $context)) === false &&
        throw new RuntimeException("Listen `$address` error, code: $errno, msg: $msg.");

        stream_set_blocking($fd, false);

        return $fd;
    }

    /**
     * Establish tcp connection and transmit data.
     */
    public function acceptTcp(): void
    {
        $fd = stream_socket_accept($this->fd, 0, $remote_address);
        if ($fd === false) {
            return;
        }

        $connection = new Tcp($fd, $remote_address);
        $connection->setProtocol($this->protocol);
        $connection->setDispatcher($this->dispatcher);
        $connection->setLooper($this->looper);
        $connection->accept();
    }

    public function acceptUdp(): void
    {
        $buffer = stream_socket_recvfrom($this->fd, Udp::$reader_buffer_size, 0, $remote_address);
        if ($buffer === false || $remote_address === "") {
            return;
        }

        $connection = new Udp($this->fd, $remote_address);
        $connection->setProtocol($this->protocol);
        $connection->setDispatcher($this->dispatcher);
        $connection->accept($buffer);
    }

    /**
     * Set options and params for socket context.
     * @param array $options
     * @param array $params
     */
    public function setContext(array $options, array $params = []): void
    {
        $options = array_merge(stream_context_get_options($this->context), $options);
        stream_context_set_option($this->context, $options);

        $params = array_merge(stream_context_get_params($this->context), $params);
        stream_context_set_params($this->context, $params);
    }

    /**
     * Start listening.
     * @param string $address The valid format such as "tcp://127.0.0.1:9000", "127.0.0.1:9000", ":9000".
     * @return void
     */
    public function listen(string $address): void
    {
        $this->protocol === null &&
        throw new RuntimeException("Protocol is not specified in server.");

        $this->dispatcher === null &&
        throw new RuntimeException("Dispatcher is not specified in server.");

        $this->looper === null &&
        throw new RuntimeException("Looper is not specified in server.");

        ($address = static::parseAddress($address)) === false &&
        throw new InvalidArgumentException("Invalid address format `$address`");

        $this->fd = static::createSocket($address, $this->context);

        if (str_starts_with($address, self::TRANSPORT_UDP)) {
            $this->looper->reader()->add($this->fd, [$this, "acceptUdp"]);
        } else {
            $this->looper->reader()->add($this->fd, [$this, "acceptTcp"]);
        }

        printf("start listening %s\n", $address);
    }

    /**
     * Stop listening.
     * @return void
     */
    public function shutdown(): void
    {
        $this->looper->reader()->remove($this->fd);

        fclose($this->fd);
        $this->fd = null;
    }
}
