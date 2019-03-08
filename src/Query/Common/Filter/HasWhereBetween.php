<?php

declare(strict_types=1);

namespace Redstraw\Hooch\Query\Common\Filter;


use Redstraw\Hooch\Query\Exception\InterfaceException;
use Redstraw\Hooch\Query\Field\FieldInterface;
use Redstraw\Hooch\Query\Statement\FilterInterface;

/**
 * Trait HasWhereBetween
 * @package Redstraw\Hooch\Query\Common\Sql
 */
trait HasWhereBetween
{
    /**
     * @param FieldInterface $field
     * @param mixed $from
     * @param mixed $to
     * @return FilterInterface
     * @throws InterfaceException
     */
    public function whereBetween(FieldInterface $field, $from, $to): FilterInterface
    {
        if($this instanceof FilterInterface) {
            $this->where(
                $field,
                $this->operator()->param()->between($from, $to)
            );

            return $this;
        }else {
            throw new InterfaceException(sprintf("Must invoke FilterInterface in: %s.", get_class($this)));
        }
    }
}