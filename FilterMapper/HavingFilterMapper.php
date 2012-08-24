<?php
namespace EPS\JqGridBundle\FilterMapper;
use EPS\JqGridBundle\Grid\Column;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;

class HavingFilterMapper extends AbstractFilterMapper
{

    /**
     * @param array $rule
     * @param string $groupOperator
     *
     * @return mixed
     */
    public function execute(array $rule, $groupOperator = 'OR')
    {
        $this->grid->getQueryBuilder()->having($this->column->getFieldHaving() . " = :{$this->column->getFieldName()}")
                ->setParameter($this->column->getFieldName(), $rule['data']);
    }
}
