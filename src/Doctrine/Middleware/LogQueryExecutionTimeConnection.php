<?php

namespace HalloVerden\DoctrineSqlLoggerBundle\Doctrine\Middleware;

use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\Driver\Middleware\AbstractConnectionMiddleware;
use Doctrine\DBAL\Driver\Result;
use Doctrine\DBAL\Driver\Statement;
use HalloVerden\DoctrineSqlLoggerBundle\Logger\QueryExecutionTimeLoggerInterface;

final class LogQueryExecutionTimeConnection extends AbstractConnectionMiddleware {

  /**
   * @internal This connection can be only instantiated by its middleware.
   */
  public function __construct(
    Connection                                         $wrappedConnection,
    private readonly QueryExecutionTimeLoggerInterface $logger
  ) {
    parent::__construct($wrappedConnection);
  }

  public function prepare(string $sql): Statement {
    return new LogQueryExecutionTimeStatement(parent::prepare($sql), $this->logger, $sql);
  }

  public function query(string $sql): Result {
    $event = $this->logger->start($sql);

    try {
      return parent::query($sql);
    } finally {
      if (null !== $event) {
        $this->logger->stop($event);
      }
    }
  }

  public function exec(string $sql): int {
    $event = $this->logger->start($sql);

    try {
      return parent::exec($sql);
    } finally {
      if (null !== $event) {
        $this->logger->stop($event);
      }
    }
  }


}
