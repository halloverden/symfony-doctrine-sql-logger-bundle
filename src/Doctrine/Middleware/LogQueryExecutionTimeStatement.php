<?php

namespace HalloVerden\DoctrineSqlLoggerBundle\Doctrine\Middleware;

use Doctrine\DBAL\Driver\Middleware\AbstractStatementMiddleware;
use Doctrine\DBAL\Driver\Result;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\ParameterType;
use HalloVerden\DoctrineSqlLoggerBundle\Logger\QueryExecutionTimeLoggerInterface;

final class LogQueryExecutionTimeStatement extends AbstractStatementMiddleware {

  /** @var array<int,mixed>|array<string,mixed> */
  private array $params = [];

  /** @var array<int,int>|array<string,int> */
  private array $types = [];

  /**
   * @internal This statement can be only instantiated by its connection.
   */
  public function __construct(
    Statement                                          $wrappedStatement,
    private readonly QueryExecutionTimeLoggerInterface $logger,
    private readonly string                            $sql
  ) {
    parent::__construct($wrappedStatement);
  }

  /**
   * @inheritDoc
   */
  public function bindValue(int|string $param, mixed $value, ParameterType $type = ParameterType::STRING): void {
    $this->params[$param] = $value;
    $this->types[$param] = $type;

    parent::bindValue($param, $value, $type);
  }

  /**
   * @inheritDoc
   * @throws \Throwable
   */
  public function execute(): Result {
    $event = $this->logger->start($this->sql, $params ?? $this->params, $this->types);

    try {
      return parent::execute();
    } finally {
      $this->logger->stop($event);
    }
  }
}
