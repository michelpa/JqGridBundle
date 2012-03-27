JqGridBundle
============

JqGrid implementation for Symfony2.


**Compatibility**: Tested with Symfony 2.0.12

Installation
------------

1.Add this bundle to your vendor/ dir

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

2.Add the "EPS" namespace to your autoloader:

```php
  <?php
  // app/autoload.php
  $loader->registerNamespaces(array(
      'EPS' => __DIR__.'/../vendor/bundles',
 // your other namespaces
  ));
```

3.Add the bundle to your autoloader:

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


Grid example
------------

## Controller

## View
