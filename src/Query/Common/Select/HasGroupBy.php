<?php

declare(strict_types=1);

namespace Redstraw\Hooch\Query\Common\Select;


use Redstraw\Hooch\Query\Exception\InterfaceException;
use Redstraw\Hooch\Query\Field\FieldInterface;
use Redstraw\Hooch\Query\Sql;
use Redstraw\Hooch\Query\Statement\SelectInterface;

/**
 * Trait HasGroupBy
 * @package Redstraw\Hooch\Query\Common\Sql
 */
trait HasGroupBy
{
    /**
     * @param FieldInterface $column
     * @return SelectInterface
     * @throws InterfaceException
     */
    public function groupBy(FieldInterface $column): SelectInterface
    {
        if($this instanceof SelectInterface) {
            $column->setAccent($this->query()->accent());

            $this->query()->sql()
                ->ifThenAppend(empty($this->query()->hasClause(Sql::GROUP)), Sql::GROUP)
                ->ifThenAppend(!empty($this->query()->hasClause(Sql::GROUP)), ',', [], false)
                ->append($column->sql()->queryString());

            $this->query()->appendSqlToClause(Sql::GROUP);

            return $this;
        }else {
            throw new InterfaceException(sprintf("Must invoke SelectInterface in: %s.", get_class($this)));
        }
    }
}