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

    public function __construct($name = null)
    {
        if ($name != null) {
            $this->name = $name;
        }
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setColModel(array $colmodel)
    {
        $this->colmodel = $colmodel;
    }

    public function getColModel()
    {
        return $this->colmodel;
    }

    private function getField($fieldname)
    {
        if (array_key_exists($fieldname, $this->colmodel)) {
            return $this->colmodel[$fieldname];
        } else {
            return false;
        }
    }
    
    
    public function getFieldName()
    {
        return $this->getField('name');
    }

    public function getFieldValue()
    {
        return $this->getField('value');
    }

    public function getFieldIndex()
    {
        return $this->getField('index');
    }

    public function getFieldTwig()
    {
        return $this->getField('twig');
    }

    public function getFieldHaving()
    {
        return $this->getField('having');
    }

    public function getFieldFormatter()
    {
        return $this->getField('formatter');
    }

    public function getColModelJson($prefix = '')
    {
        $model = $this->colmodel;
        $dp = '';

        if (array_key_exists('datepicker', $model) && $model['datepicker']) {
            $dp = ' ,"searchoptions" : {dataInit : datePick, "attr" : { "title": "Choisir une date" }}';
        }

        unset($model['twig']);
        unset($model['having']);
        unset($model['value']);
        unset($model['datepicker']);

        //prefix index
        if (array_key_exists('name', $model)) {
            $model['name'] = $prefix . $model['name'];
        }

        $models = $this->encode($model);

        $models = substr($models, 0, strlen($models) - 1) . $dp . '}';

        return $models;
    }

}

