<?php

declare(strict_types=1);

namespace Redstraw\Hooch\Query\Common\Filter;


use Redstraw\Hooch\Query\Exception\InterfaceException;
use Redstraw\Hooch\Query\Sql;
use Redstraw\Hooch\Query\Statement\FilterInterface;

/**
 * Trait HasOrWhereNotExists
 * @package Redstraw\Hooch\Query\Common\Sql
 */
trait HasOrWhereNotExists
{
    /**
     * @param Sql $subQuery
     * @return FilterInterface
     * @throws InterfaceException
     */
    public function orWhereNotExists(Sql $subQuery): FilterInterface
    {
        if($this instanceof FilterInterface) {
            $this->orWhereNot(
                null,
                $this->operator()->sql()->exists($subQuery)
            );

            return $this;
        }else {
            throw new InterfaceException(sprintf("Must invoke FilterInterface in: %s.", get_class($this)));
        }
    }
}
