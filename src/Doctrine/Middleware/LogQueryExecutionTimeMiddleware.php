<?php

namespace HalloVerden\DoctrineSqlLoggerBundle\Doctrine\Middleware;

use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Driver\Middleware;
use HalloVerden\DoctrineSqlLoggerBundle\Logger\QueryExecutionTimeLoggerInterface;

final readonly class LogQueryExecutionTimeMiddleware implements Middleware {

  /**
   * LogQueryExecutionTimeMiddleware constructor.
   */
  public function __construct(
    private QueryExecutionTimeLoggerInterface $logger
  ) {
  }

  public function wrap(Driver $driver): Driver {
    return new LogQueryExecutionTimeDriverMiddleware($driver, $this->logger);
  }

}
