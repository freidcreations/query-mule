<?php

declare(strict_types=1);

namespace Redstraw\Hooch\Query\Common\Sql;


use Redstraw\Hooch\Query\Exception\SqlException;
use Redstraw\Hooch\Query\Sql\Field\FieldInterface;
use Redstraw\Hooch\Query\Sql\Operator\OperatorInterface;
use Redstraw\Hooch\Query\Sql\Sql;
use Redstraw\Hooch\Query\Sql\Statement\OnFilterInterface;

/**
 * Trait HasOn
 * @package Redstraw\Hooch\Query\Common\Sql
 */
trait HasOn
{
    private $on = false;

    /**
     * @param $field
     * @param OperatorInterface|null $operator
     * @return OnFilterInterface
     * @throws SqlException
     */
    public function on($field, ?OperatorInterface $operator): OnFilterInterface
    {
        if ($this instanceof OnFilterInterface) {
            if ($field instanceof \Closure) {
                $field->call($this);
            } else if ($field instanceof FieldInterface) {
                $field->setAccent($this->query()->accent());

                $this->query()->sql()
                    ->ifThenAppend(!$this->on, Sql::ON)
                    ->ifThenAppend($this->on, Sql:: AND)
                    ->append($field->sql()->string())
                    ->append($operator->build());

                $this->on = true;
            }

            $this->query()->appendSqlToClause(Sql::JOIN);

            return $this;
        } else {
            throw new SqlException(sprintf("Must invoke OnFilterInterface in: %s.", get_class($this)));
        }
    }
}
