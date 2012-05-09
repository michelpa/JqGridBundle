<?php

namespace EPS\JqGridBundle\Grid;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\Query;

//use Doctrine\ORM\Query;

/**
 * Description of Grid
 *
 * @author pascal
 */
class Grid
{

    /**
     * @var \Symfony\Component\DependencyInjection\Container
     */
    private $container;

    /**
     * @var \Symfony\Component\HttpFoundation\Session;
     */
    private $session;
    private $paginator;

    /**
     * @var \Symfony\Component\HttpFoundation\Request
     */
    private $request;
    private $onlyData;
    private $em;

    /**
     * @var \Symfony\Component\Routing\Router
     */
    private $router;
    private $templating;
    private $qb;
    private $name;
    private $caption;
    private $columns;
    private $options;
    private $routeforced;
    private $hideifempty;
    private $navOptions;

    /**
     * @var string
     */
    private $hash;

    public function __construct($container, $paginator)
    {
        $this->container = $container;

        $this->router = $container->get('router');
        $this->request = $container->get('request');
        $this->session = $this->request
                              ->getSession();
        $this->paginator = $paginator;
        $this->em = $container->get('doctrine.orm.entity_manager');
        $this->templating = $container->get('templating');
        $this->columns = array();
        $this->setDefaultOptions();
        $this->caption = '';
        $this->routeforced = '';
        $this->hideifempty = false;

        if ($this->request
                 ->isXmlHttpRequest()) {
            $this->onlyData = true;
        } else {
            $this->onlyData = false;
        }

        //nom par defaut
        $now = new \DateTime();
        $this->name = md5($now->format('Y-m-d H:i:s:u'));

        unset($this->routeParameters['_route']);
    }

    public function setSource(QueryBuilder $qb)
    {
        $this->qb = $qb;
        //generate hash
        $this->createHash();
    }

    public function addColumn($name, $colmodel)
    {
        $col = new Column();
        $col->setName($name);
        $col->setColModel($colmodel);
        $this->columns[] = $col;
        return $col;
    }

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

    public function setName($name)
    {
        $this->name = $name;
    }

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

    public function setCaption($caption)
    {
        $this->caption = $caption;
    }

    public function getCaption()
    {
        return $this->caption;
    }

    public function getRouteUrl()
    {
        if ($this->routeforced != '') {
            return $this->routeforced;
        } else {
            return $this->router
                        ->generate($this->request
                                        ->get('_route'));
        }
    }

    public function setRouteForced($route)
    {
        $this->routeforced = $route;
    }

    public function isOnlyData()
    {
        return $this->onlyData;
    }

    public function createHash()
    {
        $this->hash = 'grid_' . md5($this->request
                                         ->get('_controller') . $this->getName());
        $this->session
                ->set($this->getHash(), 'Y');
    }

    public function getHash()
    {
        return $this->hash;
    }

    public function render()
    {
        if ($this->isOnlyData()) {
            $response = new Response();
            $response->setContent(json_encode($this->getData()));
            $response->headers
                     ->set('Content-Type', 'application/json');
            return $response;
        } else {
            return array(
                'grid' => $this
            );
        }
    }

    public function getData()
    {
        if ($this->session
                 ->get($this->getHash()) == 'Y') {

            $page = $this->request
                         ->query
                         ->get('page');
            $limit = $this->request
                          ->query
                          ->get('rows');
            $sidx = $this->request
                         ->query
                         ->get('sidx');
            $sord = $this->request
                         ->query
                         ->get('sord');
            $search = $this->request
                           ->query
                           ->get('_search');

            if ($sidx != '') {
                $this->qb
                        ->orderBy($sidx, $sord);
            }

            if ($search == 'true') {
                $paramnumber = 1;
                $filters = json_decode($this->request
                                            ->query
                                            ->get('filters'), true);
                $rules = $filters['rules'];

                if ($rules) {
                    foreach ($rules as $rule) {
                        foreach ($this->columns as $c) {
                            if ($c->getFieldIndex() == $rule['field']) {
                                //est-ce une date
                                if ($c->getFieldFormatter() == 'date') {
                                    $tmp = explode("/", $rule['data']);
                                    $date = $tmp[2] . "-" . $tmp[1] . "-" . $tmp[0];
                                    $this->qb
                                            ->andWhere($c->getFieldIndex() . ' LIKE \'%' . $date . '%\'');
                                } elseif ($c->getFieldHaving()) {
                                    $this->qb
                                            ->having($c->getFieldHaving() . " =  ?$paramnumber");
                                    $this->qb
                                            ->setParameter($paramnumber, $rule['data']);
                                    $paramnumber++;
                                } else {
                                    $this->qb
                                            ->andWhere($c->getFieldIndex() . " LIKE  ?$paramnumber");
                                    $this->qb
                                            ->setParameter($paramnumber, '%' . $rule['data'] . '%');
                                    $paramnumber++;
                                }
                            }
                        }
                    }
                }
            }

            $pagination = $this->paginator
                               ->paginate($this->qb
                                               ->getQuery()
                                               ->setHydrationMode(Query::HYDRATE_ARRAY), $page/* page number */, $limit/* limit per page */
                               );

            $nbRec = $pagination->getTotalItemCount();

            if ($nbRec > 0) {
                $total_pages = ceil($nbRec / $limit);
            } else {
                $total_pages = 0;
            }

            $response = array();
            $response['page'] = $page;
            $response['total'] = $total_pages;
            $response['records'] = $nbRec;

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
                                      ->render($c->getFieldTwig(), array(
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
        if ($l = $this->request
                      ->get('_locale') != '') {
            return $l;
        } else {
            return \Locale::getDefault();
        }
    }

}
