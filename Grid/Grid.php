<?php

namespace EPS\JqGridBundle\Grid;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\Query;
use EPS\JqGridBundle\FilterMapper\FilterMapperFactory;

//use Doctrine\ORM\Query;

/**
 * Description of Grid
 *
 * @author pascal
 */
class Grid extends GridTools
{

    /**
     * @var \Symfony\Component\DependencyInjection\Container
     */
    private $container;

    /**
     * @var \Knp\Component\Pager\Paginator
     */
    protected $paginator;

    /**
     * @var \Symfony\Component\Routing\Router
     */
    protected $router;

    /**
     * @var \Symfony\Component\HttpFoundation\Request
     */
    protected $request;

    /**
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    protected $em;

    /**
     * @var \Twig_TemplateInterface
     */
    protected $templating;

    /**
     * @var \Symfony\Component\HttpFoundation\Session;
     */
    private $session;

    /**
     * @var array
     */
    protected $columns;

    /**
     * @var string
     */
    protected $caption;

    private $onlyData;

    private $qb;
    private $name;
    private $options;
    private $routeforced;
    private $hideifempty;
    private $navOptions;

    /**
     * @var string
     */
    protected $datePickerFormat;

    /**
     * @var string
     */
    protected $datePickerPhpFormat;

    /**
     * @var string
     */
    private $hash;

    /**
     * @var \EPS\JqGridBundle\Grid\Grid
     */
    protected $subGrid;

    /**
     * @param \Symfony\Component\DependencyInjection\Container $container
     */
    public function __construct($container)
    {
        $this->container = $container;

        $this->router = $this->container->get('router');
        $this->request = $this->container->get('request');
        $this->session = $this->request->getSession();
        $this->paginator = $this->container->get('knp_paginator');
        $this->em = $this->container->get('doctrine.orm.entity_manager');
        $this->templating = $this->container->get('templating');
        $this->columns = array();
        $this->setDefaultOptions();
        $this->caption = '';
        $this->routeforced = '';
        $this->hideifempty = false;

        if ($this->request->isXmlHttpRequest()) {
            $this->onlyData = true;
        } else {
            $this->onlyData = false;
        }

        //nom par defaut
        $now = new \DateTime();
        $this->name = md5($now->format('Y-m-d H:i:s:u'));

        unset($this->routeParameters['_route']);
    }

    /**
     * @param string $format A Jquery Datepicker Plugin date format
     *
     * @see http://jqueryui.com/demos/datepicker/
     */
    public function setDatePickerFormat($format)
    {
        $this->datePickerFormat = $format;
    }

    /**
     * @return string A Jquery Datepicker Plugin date format
     *
     * @see http://jqueryui.com/demos/datepicker/
     */
    public function getDatePickerFormat()
    {
        return $this->datePickerFormat;
    }

    /**
     * @param string $format A PHP date format
     *
     * @see http://br2.php.net/manual/en/function.date.php
     */
    public function setDatePickerPhpFormat($format)
    {
        $this->datePickerPhpFormat = $format;
    }

    /**
     * @return string A PHP date format
     *
     * @see http://br2.php.net/manual/en/function.date.php
     */
    public function getDatePickerPhpFormat()
    {
        return $this->datePickerPhpFormat;
    }

    /**
     * Set the query builder that will be used to get data to the grid
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     */
    public function setSource(QueryBuilder $qb)
    {
        $this->qb = $qb;
        //generate hash
        $this->createHash();
    }

    public function addColumn($name, $colmodel)
    {
        $col = new Column($this->router);
        $col->setName($name);
        $col->setColModel($colmodel);
        $this->columns[] = $col;

        return $col;
    }

    /**
     * Return an array with column definitions
     *
     * @return array
     */
    public function getColumns()
    {
        return $this->columns;
    }

    public function getColumnsNames()
    {
        $tabColNames = array();
        foreach ($this->columns as $c) {
            $tabColNames[] = '\'' . $c->getName() . '\'';
        }
        $colnames = implode(', ', $tabColNames);

        return $colnames;
    }

    public function getColumnsColModel()
    {
        $tabcolmodels = array();

        foreach ($this->columns as $c) {
            $tabcolmodels[] = $c->getColModelJson($this->name);
        }

        $colmodels = implode(', ', $tabcolmodels);

        return $colmodels;
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

    public function setHideIfEmpty($hideifempty)
    {
        $this->hideifempty = $hideifempty;
    }

    public function getHideIfEmpty()
    {
        return $this->hideifempty;
    }

    /**
     * @param string $caption
     */
    public function setCaption($caption)
    {
        $this->caption = $caption;
    }

    /**
     * @return string
     */
    public function getCaption()
    {
        return $this->caption;
    }

    public function getRouteUrl()
    {
        if ($this->routeforced != '') {
            return $this->routeforced;
        } else {
            return $this->router->generate($this->request->get('_route'));
        }
    }

    public function setRouteForced($route)
    {
        $this->routeforced = $route;
    }

    /**
     * @return bool If true (Ajax Request), returns json. Else (Regular request), renders html
     */
    public function isOnlyData()
    {
        return $this->onlyData;
    }

    public function createHash()
    {
        $this->hash = 'grid_' . md5($this->request->get('_controller') . $this->getName());
        $this->session->set($this->getHash(), 'Y');
    }

    /**
     * @return string A hash that identifies the grid
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * @param \EPS\JqGridBundle\Grid $grid
     */
    public function setSubGrid(\EPS\JqGridBundle\Grid $grid)
    {
        $this->subGrid = $grid;
    }

    /**
     * @return \EPS\JqGridBundle\Grid
     */
    public function getSubGrid()
    {
        return $this->subGrid;
    }
    
    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getQueryBuilder()
    {
        return $this->qb;
    }
    
    

    public function render()
    {
        if ($this->isOnlyData()) {

            $content = $this->encode($this->getData());

            $response = new Response();
            $response->setContent($content);
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        } else {
            return array(
                'grid' => $this
            );
        }
    }

    public function getData()
    {
        if ($this->session->get($this->getHash()) == 'Y') {

            $page = $this->request->query->get('page');
            $limit = $this->request->query->get('rows');
            $sidx = $this->request->query->get('sidx');
            $sord = $this->request->query->get('sord');
            $search = $this->request->query->get('_search');

            if ($sidx != '') {
                $this->qb->orderBy($sidx, $sord);
            }

            if ($search) {
                $this->generateFilters();
            }

            $pagination = $this->paginator->paginate($this->qb->getQuery()->setHydrationMode(Query::HYDRATE_ARRAY), $page, $limit);

            $nbRec = $pagination->getTotalItemCount();

            if ($nbRec > 0) {
                $total_pages = ceil($nbRec / $limit);
            } else {
                $total_pages = 0;
            }

            $response = array(
                'page' => $page, 'total' => $total_pages, 'records' => $nbRec
            );

            foreach ($pagination as $key => $item) {
                $row = $item;

                $val = array();
                foreach ($this->columns as $c) {
                    if (array_key_exists($c->getFieldName(), $row)) {
                        $val[] = $row[$c->getFieldName()];
                    } elseif ($c->getFieldValue()) {
                        $val[] = $c->getFieldValue();
                    } elseif ($c->getFieldTwig()) {
                        $val[] = $this->templating
                                      ->render($c->getFieldTwig(),
                                        array(
                                            'ligne' => $row
                                        ));
                    } else {
                        $val[] = ' ';
                    }
                }

                $response['rows'][$key]['cell'] = $val;
            }

            return $response;
        } else {
            throw \Exception('Invalid query');
        }
    }

    public function setDefaultOptions()
    {
        $this->options = array(
                'height' => '100%', 'rowNum' => 10, 'rowList' => array(
                    10, 20, 30
                ), 'datatype' => 'json', 'viewrecords' => true,
        );

        $this->navOptions = array(
            'view' => false, 'search' => false, 'edit' => false, 'add' => false, 'del' => false,
        );
    }

    public function setOptions(array $options)
    {
        foreach ($options as $k => $v) {
            $this->options[$k] = $options[$k];
        }
    }

    public function setNavOptions(array $options)
    {
        foreach ($options as $k => $v) {
            $this->navOptions[$k] = $options[$k];
        }
    }

    public function getNavOptions($json = true)
    {
        if ($json) {
            $opts = json_encode($this->navOptions);
            $opts = substr($opts, 1);
            $opts = substr($opts, 0, strlen($opts) - 1);
            $opts = $opts . ', ';

            return $opts;
        } else {
            return $this->navOptions;
        }

    }

    public function getOptions($json = true)
    {
        if ($json) {
            $opts = json_encode($this->options);
            $opts = substr($opts, 1);
            $opts = substr($opts, 0, strlen($opts) - 1);
            $opts = $opts . ', ';

            return $opts;
        } else {
            return $this->options;
        }
    }

    public function getCulture()
    {
        if ($l = $this->request->get('_locale') != '') {
            return $l;
        } else {
            return \Locale::getDefault();
        }
    }

    /*
     * http://www.trirand.com/jqgridwiki/doku.php?id=wiki:search_config
     */
    protected function generateFilters()
    {

        $filters = $this->request->query->get('filters');

        $filters = json_decode($filters, true);
        $rules = $filters['rules'];
        $groupOp = $filters['groupOp']; //AND or OR

        if ($rules) {
            foreach ($rules as $rule) {
                foreach ($this->columns as $column) {
                    if ($column->getFieldIndex() == $rule['field']) {
                        $filterMapper = FilterMapperFactory::getFilterMapper($this, $column);
                        $filterMapper->execute($rule, $groupOp);
                    }
                }
            }
        }
    }

}
