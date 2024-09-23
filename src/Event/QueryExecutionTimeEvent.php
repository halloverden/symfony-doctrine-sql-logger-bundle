<?php

namespace HalloVerden\DoctrineSqlLoggerBundle\Event;

use Symfony\Component\Stopwatch\StopwatchEvent;
use Symfony\Contracts\EventDispatcher\Event;

final class QueryExecutionTimeEvent extends Event {

  /**
   * QueryExecutionTimeEvent constructor.
   */
  public function __construct(
    public readonly StopwatchEvent $stopwatchEvent,
    public readonly int            $threshold,
    public readonly string         $sql,
    public readonly array          $params = [],
    public readonly array          $types = []
  ) {
  }

}
