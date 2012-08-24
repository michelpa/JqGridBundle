<?php
namespace EPS\JqGridBundle\FilterMapper;
use EPS\JqGridBundle\Grid\Grid;
use EPS\JqGridBundle\Grid\Column;

abstract class AbstractFilterMapper
{
    protected $grid;
    protected $column;

    /**
     * @param \EPS\JqGridBundle\Grid\Grid $grid
     * @param \EPS\JqGridBundle\Grid\Column $column
     */
    public function __construct(Grid $grid, Column $column)
    {
        $this->grid = $grid;
        $this->column = $column;
    }

    /**
     * @abstract
     *
     * @param array $rule
     * @param string $groupOperator
     *
     * @return mixed
     */
    abstract public function execute(array $rule, $groupOperator = 'OR');
}
