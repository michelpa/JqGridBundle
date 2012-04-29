<?php

namespace Openify\Bundle\JqGridBundle\Grid;

use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\Query;

//use Doctrine\ORM\Query;


/**
 * Description of Grid
 *
 * @author pascal
 */
class Grid {
    
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
    private $navActions;
    private $navOptions;
    
    /**
     * @var string
     */
    private $hash;
    
    public function __construct($container, $paginator) {
        $this->container = $container;
        
        $this->router = $container->get ( 'router' );
        $this->request = $container->get ( 'request' );
        $this->session = $this->request->getSession ();
        $this->paginator = $paginator;
        $this->em = $container->get ( 'doctrine.orm.entity_manager' );
        $this->templating = $container->get ( 'templating' );
        $this->columns = array ();
        $this->setDefaultOptions ();
        $this->caption = '';
        $this->routeforced = '';
        $this->hideifempty = false;
        
        if ($this->request->isXmlHttpRequest ()) {
            $this->onlyData = true;
        }
        else {
            $this->onlyData = false;
        }
        
        //nom par defaut
        $now = new \DateTime ();
        $this->name = md5 ( $now->format ( 'Y-m-d H:i:s:u' ) );
        
        unset ( $this->routeParameters ['_route'] );
    }
    
    public function setSource(QueryBuilder $qb) {
        $this->qb = $qb;
        //generate hash
        $this->createHash ();
    }
    
    public function addColumn($name, $colmodel) {
        $col = new Column ();
        $col->setName ( $name );
        $col->setColModel ( $colmodel );
        $this->columns [] = $col;
        return $col;
    }
    
    public function getColumns() {
        return $this->columns;
    }
    
    public function getColumnsNames() {
        $tabColNames = array ();
        foreach ( $this->columns as $c ) {
            $tabColNames [] = '\'' . $c->getName () . '\'';
        }
        $colnames = implode ( ', ', $tabColNames );
        return $colnames;
    }
    
    public function getColumnsColModel() {
        $tabcolmodels = array ();
        
        foreach ( $this->columns as $c ) {
            $tabcolmodels [] = $c->getColModelJson ( $this->name );
        }
        
        $colmodels = implode ( ', ', $tabcolmodels );
        
        return $colmodels;
    }
    
    public function setName($name) {
        $this->name = $name;
    }
    
    public function getName() {
        return $this->name;
    }
    
    public function setHideIfEmpty($hideifempty) {
        $this->hideifempty = $hideifempty;
    }
    
    public function getHideIfEmpty() {
        return $this->hideifempty;
    }
    
    public function setCaption($caption) {
        $this->caption = $caption;
    }
    
    public function getCaption() {
        return $this->caption;
    }
    
    public function getRouteUrl() {
        if ($this->routeforced != '') {
            return $this->routeforced;
        }
        else {
            return $this->router->generate ( $this->request->get ( '_route' ) );
        }
    }
    
    public function setRouteForced($route) {
        $this->routeforced = $route;
    }
    
    public function isOnlyData() {
        return $this->onlyData;
    }
    
    public function createHash() {
        $this->hash = 'grid_' . md5 ( $this->request->get ( '_controller' ) . $this->getName () );
        $this->session->set ( $this->getHash (), 'Y' );
    }
    
    public function getHash() {
        return $this->hash;
    }
    
    public function render() {
        if ($this->isOnlyData ()) {
            $response = new Response ();
            $response->setContent ( json_encode ( $this->getData () ) );
            $response->headers->set ( 'Content-Type', 'application/json' );
            return $response;
        }
        else {
            return array ('grid' => $this );
        }
    }
    
    /*
     * http://www.trirand.com/jqgridwiki/doku.php?id=wiki:search_config
     */
    protected function generateFilters() {
        $filters = $this->request->query->get ( 'filters' );
        
        $filters = json_decode ( $filters, true );
        $rules = $filters ['rules'];
        $groupOp = $filters ['groupOp']; //AND or OR
        
        if ($rules) {
            foreach ( $rules as $rule ) {
                foreach ( $this->columns as $c ) {
                    if ($c->getFieldIndex () == $rule ['field']) {
                        
                        $op = $rule ['op'];
                        
                        $parameter = $rule ['data'];
                        
                        switch ($rule ['op']) {
                            case 'eq' :
                                $where = $this->qb->expr ()->eq ( $c->getFieldIndex (), ":{$c->getFieldName ()}" );
                                break;
                            case 'ne' :
                                $where = $this->qb->expr ()->neq ( $c->getFieldIndex (), ":{$c->getFieldName ()}" );
                                break;
                            case 'lt' :
                                $where = $this->qb->expr ()->lt ( $c->getFieldIndex (), ":{$c->getFieldName ()}" );
                                break;
                            case 'le' :
                                $where = $this->qb->expr ()->lte ( $c->getFieldIndex (), ":{$c->getFieldName ()}" );
                                break;
                            case 'gt' :
                                $where = $this->qb->expr ()->gt ( $c->getFieldIndex (), ":{$c->getFieldName ()}" );
                                break;
                            case 'ge' :
                                $where = $this->qb->expr ()->gte ( $c->getFieldIndex (), ":{$c->getFieldName ()}" );
                                break;
                            case 'bw' :
                                $where = $this->qb->expr ()->like ( $c->getFieldIndex (), ":{$c->getFieldName ()}" );
                                $parameter = $rule ['data'] . '%';
                                break;
                            case 'bn' :
                                $where = $c->getFieldIndex () . " NOT LIKE :{$c->getFieldName ()}";
                                $parameter = $rule ['data'] . '%';
                                break;
                            case 'nu' :
                                $where = $this->qb->expr ()->orX ( $this->qb->expr ()->eq ( $c->getFieldIndex (), ":{$c->getFieldName ()}" ), $c->getFieldIndex () . ' IS NULL' );
                                $parameter = '';
                                break;
                            case 'nn' :
                                $where = $this->qb->expr ()->andX ( $this->qb->expr ()->neq ( $c->getFieldIndex (), ":{$c->getFieldName ()}" ), $c->getFieldIndex () . ' IS NOT NULL' );
                                
                                $parameter = '';
                                break;
                            case 'in' :
                                if (false !== strpos ( $rule ['data'], ',' )) {
                                    $where = $this->qb->expr ()->in ( $c->getFieldIndex (), ":{$c->getFieldName ()}" );
                                    $parameter = explode ( ',', $rule ['data'] );
                                }
                                elseif (false !== strpos ( $rule ['data'], '-' )) {
                                    $where = $this->qb->expr ()->between ( $c->getFieldIndex (), ":start", ":end" );
                                    list ( $start, $end ) = explode ( '-', $rule ['data'] );
                                    $this->qb->setParameter ( 'start', $start );
                                    $this->qb->setParameter ( 'end', $end );
                                    unset ( $parameter );
                                }
                                break;
                            case 'ni' :
                                if (false !== strpos ( $rule ['data'], ',' )) {
                                    $where = $this->qb->expr ()->notIn ( $c->getFieldIndex (), ":{$c->getFieldName ()}" );
                                    $parameter = explode ( ',', $rule ['data'] );
                                }
                                elseif (false !== strpos ( $rule ['data'], '-' )) {
                                    $where = $this->qb->expr ()->orX ( $c->getFieldIndex () . "< :start", $c->getFieldIndex () . "> :end" );
                                    list ( $start, $end ) = explode ( '-', $rule ['data'] );
                                    $this->qb->setParameter ( 'start', $start );
                                    $this->qb->setParameter ( 'end', $end );
                                    unset ( $parameter );
                                }
                                
                                break;
                            case 'ew' :
                                $where = $this->qb->expr ()->like ( $c->getFieldIndex (), ":{$c->getFieldName ()}" );
                                $parameter = '%' . $rule ['data'];
                                break;
                            case 'en' :
                                $where = $c->getFieldIndex () . " NOT LIKE :{$c->getFieldName ()}";
                                $parameter = '%' . $rule ['data'];
                                break;
                            case 'nc' :
                                $where = $c->getFieldIndex () . " NOT LIKE :{$c->getFieldName ()}";
                                $parameter = '%' . $rule ['data'] . '%';
                                break;
                            default : //case 'cn'
                                $where = $this->qb->expr ()->like ( $c->getFieldIndex (), ":{$c->getFieldName ()}" );
                                $parameter = '%' . $rule ['data'] . '%';
                        }
                        //TODO : handle date field
                        

                        if ('OR' == $groupOp) {
                            $this->qb->orWhere ( $where );
                        }
                        else {
                            $this->qb->andWhere ( $where );
                        
                        }
                        
                        if (isset ( $parameter )) {
                            $this->qb->setParameter ( $c->getFieldName (), $parameter );
                        }
                    }
                }
            }
        }
    }
    
    public function getData() {
        if ($this->session->get ( $this->getHash () ) == 'Y') {
            
            $page = $this->request->query->get ( 'page' );
            $limit = $this->request->query->get ( 'rows' );
            $sidx = $this->request->query->get ( 'sidx' );
            $sord = $this->request->query->get ( 'sord' );
            $search = $this->request->query->get ( '_search' );
            
            if ($sidx != '') {
                $this->qb->orderBy ( $sidx, $sord );
            }
            
            if ($search) {
                $this->generateFilters ();
            }
            
            $pagination = $this->paginator->paginate ( $this->qb->getQuery ()->setHydrationMode ( Query::HYDRATE_ARRAY ), $page/* page number */, $limit/* limit per page */
            );
            
            $nbRec = $pagination->getTotalItemCount ();
            
            $total_pages = ($nbRec > 0) ? ceil ( $nbRec / $limit ) : 0;
            
            $response = array ('page' => $page, 'total' => $total_pages, 'records' => $nbRec );
            
            foreach ( $pagination as $key => $item ) {
                $row = $item;
                
                $val = array ();
                foreach ( $this->columns as $c ) {
                    if (array_key_exists ( $c->getFieldName (), $row )) {
                        $val [] = $row [$c->getFieldName ()];
                    }
                    elseif ($c->getFieldValue ()) {
                        $val [] = $c->getFieldValue ();
                    }
                    elseif ($c->getFieldTwig ()) {
                        $val [] = $this->templating->render ( $c->getFieldTwig (), array ('ligne' => $row ) );
                    }
                    else {
                        $val [] = ' ';
                    }
                }
                
                $response ['rows'] [$key] ['cell'] = $val;
            }
            
            return $response;
        }
        else {
            throw \Exception ( 'Invalid query' );
        }
    }
    
    public function setDefaultOptions() {
        $this->options = array ('height' => '100%', 'rowNum' => 10, 'rowList' => array (10, 20, 30 ), 'datatype' => 'json', 'viewrecords' => true );
        $this->navActions = array ('view' => false, 'search' => false, 'edit' => false, 'add' => false, 'del' => false );
        $this->navOptions = array ('edit' => '', 'add' => '', 'del' => '', 'search' => '' );
    }
    
    public function setAttributeOptions($attribute, array $options) {
        foreach ( $options as $k => $v ) {
            $this->{$attribute} [$k] = $options [$k];
        }
    }
    
    public function getAttributeOptions($attribute, $json = true) {
        if ($json) {
            $opts = json_encode ( $this->{$attribute} );
            $opts = trim ( $opts, '{}' );
            $opts .= ', ';
            
            return $opts;
        }
        else {
            return $this->{$attribute};
        }
    }
    
    public function getNavOptions($action) {
        return $this->navOptions [$action];
    }
    
    public function getCulture() {
        if ($l = $this->request->get ( '_locale' ) != '') {
            return $l;
        }
        else {
            return \Locale::getDefault ();
        }
    }

}
