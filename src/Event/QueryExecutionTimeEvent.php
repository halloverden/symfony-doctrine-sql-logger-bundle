<?php

namespace HalloVerden\DoctrineSqlLoggerBundle\Event;

use HalloVerden\DoctrineSqlLoggerBundle\Context\QueryExecutionTimeContext;
use Symfony\Component\Stopwatch\StopwatchEvent;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\EventDispatcher\Event;

final class QueryExecutionTimeEvent extends Event {

  /**
   * QueryExecutionTimeEvent constructor.
   */
  public function __construct(
    public readonly StopwatchEvent            $stopwatchEvent,
    public readonly QueryExecutionTimeContext $context,
    public readonly string                    $sql,
    public readonly Uuid                      $uuid,
    public readonly array                     $params = [],
    public readonly array                     $types = [],
  ) {
  }

}
