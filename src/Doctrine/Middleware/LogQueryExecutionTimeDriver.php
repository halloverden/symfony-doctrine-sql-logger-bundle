<?php

namespace HalloVerden\DoctrineSqlLoggerBundle\Doctrine\Middleware;

use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\Driver\Middleware\AbstractDriverMiddleware;
use HalloVerden\DoctrineSqlLoggerBundle\Logger\QueryExecutionTimeLoggerInterface;

final class LogQueryExecutionTimeDriver extends AbstractDriverMiddleware {

  /**
   * @internal This driver can be only instantiated by its middleware.
   */
  public function __construct(
    Driver                                             $wrappedDriver,
    private readonly QueryExecutionTimeLoggerInterface $logger
  ) {
    parent::__construct($wrappedDriver);
  }

  /**
   * @inheritDoc
   */
  public function connect(array $params): Connection {
    return new LogQueryExecutionTimeConnection(parent::connect($params), $this->logger);
  }

}
