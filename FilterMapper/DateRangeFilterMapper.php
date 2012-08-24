<?php
namespace EPS\JqGridBundle\FilterMapper;

class DateRangeFilterMapper extends AbstractFilterMapper
{
    /**
     * @param array $rule
     * @param string $groupOperator
     *
     * @return mixed
     */
    public function execute(array $rule, $groupOperator = 'OR')
    {
        $queryBuilder = $this->grid->getQueryBuilder();
        $date = \DateTime::createFromFormat($this->grid->getDatePickerPhpFormat(), $rule['data']);

        $queryBuilder
                ->andWhere(
                        $queryBuilder->expr()
                                     ->gte($this->column->getFieldIndex(), ":{$this->column->getFieldName()}_1"))
                ->setParameter("{$this->column->getFieldName()}_1", $date->format('Y-m-d 00:00:00'));

        $queryBuilder
                ->andWhere(
                        $queryBuilder->expr()
                                     ->lte($this->column->getFieldIndex(), ":{$this->column->getFieldName()}_2"))
                ->setParameter("{$this->column->getFieldName()}_2", $date->format('Y-m-d 23:59:59'));
    }
}
