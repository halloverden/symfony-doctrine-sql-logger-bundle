<?php

namespace HalloVerden\DoctrineSqlLoggerBundle\Logger;

use HalloVerden\DoctrineSqlLoggerBundle\Context\QueryExecutionTimeContext;
use HalloVerden\DoctrineSqlLoggerBundle\Event\QueryExecutionTimeEvent;

interface QueryExecutionTimeLoggerInterface {
  public function start(string $sql, array $params = [], array $types = []): QueryExecutionTimeEvent;
  public function stop(QueryExecutionTimeEvent $event): void;
  public function getDefaultThreshold(): int;

  /**
   * @param QueryExecutionTimeContext ...$contexts
   *
   * @return void
   */
  public function addContext(QueryExecutionTimeContext ...$contexts): void;
}
