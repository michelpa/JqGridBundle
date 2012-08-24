<?php
namespace EPS\JqGridBundle\FilterMapper;
use EPS\JqGridBundle\Grid\Grid;
use EPS\JqGridBundle\Grid\Column;

class FilterMapperFactory
{

    const FORMATTER_DATE = 'date';

    /**
     * @param \EPS\JqGridBundle\Grid\Grid $grid
     * @param \EPS\JqGridBundle\Grid\Column $column
     *
     * @return \EPS\JqGridBundle\FilterMapper\AbstractFilterMapper
     */
    public static function getFilterMapper(Grid $grid, Column $column)
    {
        if ($column->getFieldFormatter() == self::FORMATTER_DATE) {

            return new DateRangeFilterMapper($grid, $column);

        } elseif ($column->getFieldHaving()) {

            return new HavingFilterMapper($grid, $column);

        } else {

            return new ComparisionFilterMapper($grid, $column);
        }
    }
}
