JqGridBundle
============

JqGrid implementation for Symfony2.


**Compatibility**: Tested with Symfony 2.0.12

Installation
------------

+ Add this bundle to your vendor/ dir

Add the following lines in your deps file::

```
[EPSJqGridBundle]
  git=git://github.com/michelpa/JqGridBundle.git
  target=/bundles/EPS/JqGridBundle
```

 Run the vendor script:

```
./bin/vendors install
```

+ Add the "EPS" namespace to your autoloader:

```php
  <?php
  // app/autoload.php
  $loader->registerNamespaces(array(
      'EPS' => __DIR__.'/../vendor/bundles',
 // your other namespaces
  ));
```

+ Enable the bundle in the kernel

```php
<?php
        // app/ApplicationKernel.php
        public function registerBundles()
        {
            return array(
                // ...
                new EPS\JqGridBundle\JqGridBundle(),
                // ...
            );
        }
```

+ Add assets to your layout

JS
bundles/epsjqgrid/js/i18n/grid.locale-fr.js
bundles/epsjqgrid/js/jquery.jqGrid.min.js

CSS
bundles/epsjqgrid/css/ui.jqgrid.css

Grid example
------------

## Controller

```php
<?php

namespace EPS\MyBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use EPS\JqGridBundle\Grid\Grid;


/**
 *
 * @Route("/")
 * 
 */
class MyController extends Controller {

 /**
     * @Route("/testgrid", name="grid_test")
     * @Template()
     */
    public function cdegridAction() {
        $em = $this->getDoctrine()->getEntityManager();


        $qb = $em->createQueryBuilder()
                ->from('EPSMyBundle:Comment', 'c')
                ->leftJoin('c.post', 'p')
                ->select('p.post_title, c.id, c.created');
                

        $posts = $em->getRepository('EPSLcpBundle:Post')->findBy(array('actif' => true));

        $lstpost = array();
        $lstpost[''] = 'All';
        foreach ($posts as $p) {
            $lstpost[$p->getPostTitle()] = $m->getPostTitle();
        }

        $grid = $this->get('eps_jq_grid');

        //OPTIONAL
        $grid->setName('gridcomments');
        $grid->setCaption('list of comments');
        $grid->setOptions(array('height' => 'auto', 'width' => '910'));
        $grid->setRouteForced($this->get('router')->generate('grid_test'));
        $grid->setHideIfEmpty(false);

        //MANDATORY
        $grid->setSource($qb);

        //COLUMNS DEFINITION
        $grid->addColumn('Action', array('twig' => 'EPSMyBundle:Grid:_testgridaction.html.twig', 'name' => 'action', 'resize' => false, 'sortable' => false, 'search' => false, 'width' => '50'));
        $grid->addColumn('ID', array('name' => 'id', 'index' => 'c.id', 'hidden' => true, 'sortable' => false, 'search' => false));
        $grid->addColumn('Post', array('name' => 'post_title', 'index' => 'p.post_title', 'width' => '150', 'stype' => 'select', 'searchoptions' => array('value' => $lstpost)));
        $grid->addColumn('Date', array('name' => 'created', 'index' => 'c.created', 'formatter' => 'date', 'datepicker' => true));


         return ('mygrid' => $grid->render());

    }
}

```
## View
```twig
{{jqgrid_js(mygrid)}}
```
## Action column

Action is managed by a specific twig template in which each value of the line is available in a variable named "ligne". In this definition it is "_testgridaction.html.twig"

```twig
<a href="#" onclick="alert('Whatever'); return false;"><span original-title="See detail"></span>{{ligne.post_title}}</a>
```
