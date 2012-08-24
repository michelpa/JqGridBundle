<?php

namespace EPS\JqGridBundle\Grid;
/**
 * Description of Column
 *
 * @author pascal
 */
class Column extends GridTools
{

    private $name;
    private $colmodel;
    private $router;

    public function __construct($router, $name = null)
    {

        $this->router = $router;
        if ($name != null) {
            $this->name = $name;
        }
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param array $columnModel
     */
    public function setColModel(array $colmodel)
    {
        $this->colmodel = $colmodel;
    }

    /**
     * @return array
     */
    public function getColModel()
    {
        return $this->colmodel;
    }

    /**
     * Generic getter for any jqgrid column model attribute
     *
     * @param string $fieldname
     * @return mixed
     */
    private function getField($fieldname)
    {
        if (array_key_exists($fieldname, $this->colmodel)) {
            return $this->colmodel[$fieldname];
        } else {
            return false;
        }
    }

    /**
     * @return string
     */
    public function getFieldName()
    {
        return $this->getField('name');
    }

    /**
     * @return mixed
     */
    public function getFieldValue()
    {
        return $this->getField('value');
    }

    /**
     * @return string
     */
    public function getFieldIndex()
    {
        return $this->getField('index');
    }

    /**
     * @return string
     */
    public function getFieldTwig()
    {
        return $this->getField('twig');
    }

    /**
     * @return string
     */
    public function getFieldHaving()
    {
        return $this->getField('having');
    }

    /**
     * @return string
     */
    public function getFieldAutocomplete()
    {
        return $this->getField('autocomplete');
    }

    /**
     * @return string
     */
    public function getFieldFormatter()
    {
        return $this->getField('formatter');
    }

    /**
     * Decorate specific column model attributes with
     * values expected to build the view
     *
     * @param string $prefix
     *
     * @return array
     */
    public function getColModelJson($prefix = '')
    {
        $model = $this->colmodel;
        $dp = '';

        if (array_key_exists('datepicker', $model) && $model['datepicker']) {
            $dp = ' ,"searchoptions" : {dataInit : datePick, "attr" : { "title": "Choisir une date" }}';
        }

        if (array_key_exists('autocomplete', $model)) {
            $route = $this->router->generate($model['autocomplete']);
            $dp = ' ,"searchoptions" : {dataInit : function(elem) { $(elem).autocomplete({source:\'' . $route . '\',minLength:2}) }}';
        }

        unset($model['twig']);
        unset($model['having']);
        unset($model['value']);
        unset($model['datepicker']);
        unset($model['autocomplete']);

        //prefix index
        if (array_key_exists('name', $model)) {
            $model['name'] = $prefix . $model['name'];
        }

        $models = $this->encode($model);

        $models = substr($models, 0, strlen($models) - 1) . $dp . '}';

        return $models;
    }

}

