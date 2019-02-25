<?php

declare(strict_types=1);

namespace Redstraw\Hooch\Builder\Connection\Driver;

use Psr\Log\LoggerInterface;
use Redstraw\Hooch\Query\Common\Statement\HasOnFilter;
use Redstraw\Hooch\Query\Common\Statement\HasSelect;
use Redstraw\Hooch\Query\Common\Statement\HasUpdate;
use Redstraw\Hooch\Query\Common\Driver\HasCache;
use Redstraw\Hooch\Query\Common\Driver\HasDriver;
use Redstraw\Hooch\Query\Common\Driver\HasFetch;
use Redstraw\Hooch\Query\Common\Driver\HasFetchAll;
use Redstraw\Hooch\Query\Common\HasQuery;
use Redstraw\Hooch\Query\Common\Operator\Comparison;
use Redstraw\Hooch\Query\Common\Operator\Logical;
use Redstraw\Hooch\Query\Common\Statement\HasFilter;
use Redstraw\Hooch\Query\Connection\Driver\DriverInterface;
use Redstraw\Hooch\Query\Common\Operator\Operator;
use Redstraw\Hooch\Query\Sql\Query;
use Redstraw\Hooch\Query\Sql\Sql;

/**
 * Class MysqliDriver
 * @package Redstraw\Hooch\Builder\Connection\Driver
 */
class MysqliDriver implements DriverInterface
{
    use HasDriver;
    use HasQuery;
    use HasCache;
    use HasFetch;
    use HasFetchAll;
    use HasFilter;
    use HasOnFilter;
    use HasSelect;
    use HasUpdate;

    /**
     * @var array
     */
    private static $instances = [];

    /**
     * @var \mysqli
     */
    private $mysqli;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * MysqliDriver constructor.
     * @param \mysqli $mysqli
     * @param Query $query
     * @param LoggerInterface $logger
     */
    public function __construct(\mysqli $mysqli, Query $query, LoggerInterface $logger)
    {
        $this->mysqli = $mysqli;
        $this->driverName = self::DRIVER_MYSQL;
        $this->query = $query;
        $this->logger = $logger;
    }

    /**
     * @return Operator
     */
    public function operator(): Operator
    {
        if(!isset(self::$instances[Operator::class])){
            self::$instances[Operator::class] = new Operator(
                new Comparison(
                    new \Redstraw\Hooch\Query\Common\Operator\Comparison\Param(new Sql()),
                    new \Redstraw\Hooch\Query\Common\Operator\Comparison\SubQuery(new Sql()),
                    new \Redstraw\Hooch\Query\Common\Operator\Comparison\Field(new Sql(), $this->query->accent())
                ),
                new Logical(
                    new \Redstraw\Hooch\Query\Common\Operator\Logical\Param(new Sql()),
                    new \Redstraw\Hooch\Query\Common\Operator\Logical\SubQuery(new Sql()),
                    new \Redstraw\Hooch\Query\Common\Operator\Logical\Field(new Sql(), $this->query->accent())
                )
            );
        }

        return self::$instances[Operator::class];
    }

    /**
     * @return LoggerInterface
     */
    public function logger(): LoggerInterface
    {
        return $this->logger;
    }

    /**
     * @param Sql $sql
     * @param string $method
     * @return array|mixed|null
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function execute(Sql $sql, string $method)
    {
        $fromCache = false;
        $cacheKey = md5(serialize($sql));
        $time = microtime(true);

        if (!empty($this->cache) && !empty($this->cache->has($cacheKey))) {
            $fromCache = true;
            $result = json_decode((string)$this->cache->get($cacheKey));
        } else {
            $query = $this->bindParameters($this->mysqli->prepare($sql->string()), $sql);

            if (!$query->execute()) {
                $this->logger->error("Mysqli error code: " . $this->mysqli->connect_errno, [
                    'query'     => $sql->string(),
                    'message'   => $this->mysqli->error
                ]);

                return false;
            }

            $result = $this->getResult($query, $method, $cacheKey);
        }

        $this->query->reset();

        $this->logger->info("Successfully executed query", [
            'query'             => $sql->string(),
            'parameters'        => $sql->parameters(),
            'driver'            => $this->driverName(),
            'execution_time'    => round(microtime(true) - $time, 4) . "s",
            'from_cache'        => $fromCache
        ]);

        return $result;
    }

    /**
     * @param \mysqli_stmt $stmt
     * @param Sql $sql
     * @return \mysqli_stmt
     */
    private function bindParameters(\mysqli_stmt $stmt, Sql $sql): \mysqli_stmt
    {
        $parameters = [0 => ''];
        foreach ($sql->parameters() as $parameter) {
            switch (gettype($parameter)) {
                case 'integer':
                    $parameters[0] .= 'i';
                    break;

                case 'double':
                    $parameters[0] .= 'd';
                    break;

                case 'string':
                    $parameters[0] .= 's';
                    break;

                default:
                    $parameters[0] .= 'b';
                    break;
            }

            $parameters[] = &$parameter;
        }

        call_user_func_array([$stmt, 'bind_param'], $parameters);

        return $stmt;
    }

    /**
     * @param \mysqli_stmt $stmt
     * @param string $method
     * @param string $cacheKey
     * @return array|mixed|null
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    private function getResult(\mysqli_stmt $stmt, string $method, string $cacheKey)
    {
        $result = [];
        switch ($method) {
            case DriverInterface::FETCH:
                $result = $stmt->get_result()->fetch_assoc();
                break;

            case DriverInterface::FETCH_ALL:
                $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                break;
        }

        if (!empty($this->cache)) {
            $this->cache->set($cacheKey, json_encode($result), $this->ttl);
        }

        return $result;
    }
}
