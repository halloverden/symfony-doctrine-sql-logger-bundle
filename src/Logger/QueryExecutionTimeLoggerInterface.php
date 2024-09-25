<?php

namespace HalloVerden\DoctrineSqlLoggerBundle\Logger;

use HalloVerden\DoctrineSqlLoggerBundle\Context\QueryExecutionTimeContext;
use HalloVerden\DoctrineSqlLoggerBundle\Event\QueryExecutionTimeEvent;

interface QueryExecutionTimeLoggerInterface {
  public function start(string $sql, array $params = [], array $types = []): ?QueryExecutionTimeEvent;
  public function stop(QueryExecutionTimeEvent $event): void;
  public function getDefaultContext(): QueryExecutionTimeContext;
  public function addContext(QueryExecutionTimeContext ...$contexts): void;
}
