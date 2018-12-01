<?php

namespace QueryMule\Builder\Sql\Mysql;


use QueryMule\Query\Sql\Operator\Comparison;
use QueryMule\Query\Sql\Operator\Logical;
use QueryMule\Query\Sql\Sql;
use QueryMule\Query\Sql\Statement\OnInterface;

/**
 * Class Join
 * @package QueryMule\Builder\Sql\Mysql
 */
class On extends Filter implements OnInterface
{
    private $on = false;

    /**
     * @param $column
     * @param null|Comparison $comparison
     * @return $this
     */
    public function on($column, ?Comparison $comparison)
    {
        $this->on = true;

        $sql = Sql::ON . Sql::SQL_SPACE;
        $sql .= $column . Sql::SQL_SPACE;
        $sql .= $comparison->build()->sql();

        $this->query()->add(Sql::JOIN,new Sql($sql,$comparison->build()->parameters()));

        return $this;
    }

    /**
     * @param $column
     * @param null|Comparison $comparison
     * @return $this|mixed
     */
    public function orOn($column, ?Comparison $comparison)
    {
        $this->orWhere($column,$comparison,null);

        return $this;
    }
}