JqGridBundle
============

JqGrid implementation for Symfony2.


**Compatibility**: Tested with Symfony 2.0.12

[Full working demo](https://github.com/michelpa/demoJqGrid)


Installation
------------

1. **Add this bundle to your vendor/ dir**

    Add the following lines in your deps file:

    ```
      [OpenifyJqGridBundle]
        git=git://github.com/michelpa/JqGridBundle.git
        target=/bundles/Openify/JqGridBundle
    ```

    Run the vendor script:

    ```
      ./bin/vendors install
    ```

2. **Add the "Openify" namespace to your autoloader**

    ```php
       <?php
       // app/autoload.php
       $loader->registerNamespaces(array(
           'Openify' => __DIR__.'/../vendor/bundles',
       // your other namespaces
       ));
    ```

3. **Enable the bundle in the kernel**

    ```php
      <?php
         // app/ApplicationKernel.php
         public function registerBundles()
         {
             return array(
                 // ...
                 new Openify\Bundle\JqGridBundle\OpenifyJqGridBundle(),
                 // ...
             );
         }
    ```

4. **Add assets to your layout**

     *JS*

         bundles/openifyjqgrid/js/i18n/grid.locale-fr.js
         bundles/openifyjqgrid/js/jquery.jqGrid.min.js

     *CSS*

         bundles/openifyjqgrid/css/ui.jqgrid.css

Grid example
------------


[Full working demo](https://github.com/michelpa/demoJqGrid)
