<?php

declare(strict_types=1);

namespace QueryMule\Builder\Sql\Mysql;


use QueryMule\Builder\Sql\Common\Clause\HasCols;
use QueryMule\Builder\Sql\Common\Clause\HasFrom;
use QueryMule\Builder\Sql\Common\Clause\HasFullOuterJoin;
use QueryMule\Builder\Sql\Common\Clause\HasGroupBy;
use QueryMule\Builder\Sql\Common\Clause\HasInnerJoin;
use QueryMule\Builder\Sql\Common\Clause\HasJoin;
use QueryMule\Builder\Sql\Common\Clause\HasLeftJoin;
use QueryMule\Builder\Sql\Common\Clause\HasLimit;
use QueryMule\Builder\Sql\Common\Clause\HasOffset;
use QueryMule\Builder\Sql\Common\Clause\HasOrderBy;
use QueryMule\Builder\Sql\Common\Clause\HasRightJoin;
use QueryMule\Builder\Sql\Common\Clause\HasUnion;
use QueryMule\Query\QueryBuilderInterface;
use QueryMule\Query\Repository\RepositoryInterface;
use QueryMule\Query\Sql\Accent;
use QueryMule\Query\Sql\Operator\Comparison;
use QueryMule\Query\Sql\Operator\Logical;
use QueryMule\Query\Sql\Query;
use QueryMule\Query\Sql\Sql;
use QueryMule\Query\Sql\Statement\FilterInterface;
use QueryMule\Query\Sql\Statement\OnFilterInterface;
use QueryMule\Query\Sql\Statement\SelectInterface;

/**
 * Class Select
 * @package QueryMule\Builder\Sql\Sqlite
 */
class Select implements QueryBuilderInterface, SelectInterface
{
    use HasCols;
    use HasFrom;
    use HasGroupBy;
    use HasLimit;
    use HasOffset;
    use HasUnion;
    use HasOrderBy;
    use HasJoin;
    use HasLeftJoin;
    use HasRightJoin;
    use HasInnerJoin;
    use HasFullOuterJoin;

    /**
     * @var FilterInterface|QueryBuilderInterface
     */
    private $filter;

    /**
     * @var Query
     */
    private $query;

    /**
     * @var Logical
     */
    private $logical;

    /**
     * @var OnFilterInterface|QueryBuilderInterface
     */
    private $onFilter;

    /**
     * @var Accent
     */
    private $accent;

    /**
     * Select constructor.
     * @param Query $query
     * @param Logical $logical
     * @param Accent $accent
     * @param RepositoryInterface|null $table
     * @param array $cols
     */
    public function __construct(Query $query,
                                Logical $logical,
                                Accent $accent,
                                RepositoryInterface $table = null,
                                array $cols = [])
    {
        $this->query = $query;
        $this->logical = $logical;
        $this->accent = $accent;

        if (!empty($cols)) {
            $this->cols($cols);
        }

        if (!empty($table)) {
            $this->from($table);
        }

        $this->query->add(Sql::SELECT, new Sql(Sql::SELECT));
    }

    /**
     * @param array $clauses
     * @return Sql
     */
    public function build(array $clauses = [
        Sql::SELECT,   // DONE
        Sql::COLS,     // DONE
        Sql::FROM,     // DONE
        Sql::JOIN,     // DONE
        Sql::WHERE,    // DONE
        Sql::GROUP,    // DONE
        Sql::ORDER,    // DONE
        Sql::HAVING,   // <<<
        Sql::LIMIT,    // DONE
        Sql::OFFSET,   // DONE
        Sql::UNION     // DONE
    ]): Sql
    {
        if (in_array(Sql::WHERE, $clauses) && $this->filter) {
            $this->query->add(Sql::WHERE, $this->filter->build([Sql::WHERE]));
        }

        if (in_array(Sql::JOIN, $clauses) && $this->onFilter) {
            $this->query->add(Sql::JOIN, $this->onFilter->build([Sql::JOIN]));
        }

        $sql = $this->query->build($clauses);

        $this->query->reset($clauses);

        return $sql;
    }

    /**
     * @return FilterInterface|null
     */
    public function filter(): ?FilterInterface
    {
        return $this->filter;
    }

    /**
     * @return OnFilterInterface|null
     */
    public function onFilter(): ?OnFilterInterface
    {
        return $this->onFilter;
    }

    /**
     * @param bool $ignore
     * @return SelectInterface
     */
    public function ignoreAccent($ignore = true): SelectInterface
    {
        $this->accent->ignore($ignore);

        return $this;
    }

    /**
     * @param FilterInterface $filter
     */
    public function setFilter(FilterInterface $filter): void
    {
        $this->filter = $filter;
    }

    /**
     * @param OnFilterInterface $onFilter
     */
    public function setOnFilter(OnFilterInterface $onFilter): void
    {
        $this->onFilter = $onFilter;
    }

    /**
     * @return Accent
     */
    protected function accent(): Accent
    {
        return $this->accent;
    }

    /**
     * @return Logical
     */
    protected function logical(): Logical
    {
        return $this->logical;
    }

    /**
     * @return Query
     */
    protected function query(): Query
    {
        return $this->query;
    }
}