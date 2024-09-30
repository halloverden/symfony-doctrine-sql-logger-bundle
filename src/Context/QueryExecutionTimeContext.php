<?php

namespace HalloVerden\DoctrineSqlLoggerBundle\Context;

readonly class QueryExecutionTimeContext {

  /**
   * QueryExecutionTimeContext constructor.
   */
  public function __construct(
    public int $threshold
  ) {
  }

  /**
   * @return array
   */
  public function toArray(): array {
    return [
      'threshold' => $this->threshold
    ];
  }

}
