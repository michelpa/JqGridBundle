<?php

/*
 * This file is part of the DataGridBundle.
 *
 * (c) Stanislav Turza <sorien@mail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EPS\JqGridBundle\Twig;
use EPS\JqGridBundle\Grid\Grid;

class JqGridExtension extends \Twig_Extension
{

    const DEFAULT_TEMPLATE = 'EPSJqGridBundle::blocks.html.twig';

    /**
     * @var \Symfony\Component\Routing\Router
     */
    protected $router;

    /**
     * @var \Twig_Environment
     */
    protected $environment;

    /**
     * @var \Twig_TemplateInterface[]
     */
    protected $templates;

    public function __construct($router)
    {
        $this->router = $router;
    }

    public function initRuntime(\Twig_Environment $environment)
    {
        $this->environment = $environment;
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return [
                new \Twig_SimpleFunction('jqgrid', 'renderGrid',['is_safe' =>['html']]),
                new \Twig_SimpleFunction('jqgrid_js', 'renderGridJs',['is_safe' =>['html']]),
                new \Twig_SimpleFunction('jqgrid_html', 'renderGridHtml',['is_safe' =>['html']])
        ];
    }

    public function renderGrid(Grid $grid)
    {
        if (!$grid->isOnlyData()) {
            return $this->renderBlock('jqgrid', ['grid' => $grid]);
        }
    }

    public function renderGridJs(Grid $grid)
    {
        if (!$grid->isOnlyData()) {
            return $this->renderBlock('jqgrid_j', ['grid' => $grid]);
        }
    }

    public function renderGridHtml(Grid $grid)
    {
        if (!$grid->isOnlyData()) {
            return $this->renderBlock('jqgrid_h', ['grid' => $grid]);
        }
    }

    /**
     * Render block
     *
     * @param $name string
     * @param $parameters string
     * @return string
     */
    private function renderBlock($name, $parameters)
    {
        foreach ($this->getTemplates() as $template) {
            if ($template->hasBlock($name)) {
                return $template->renderBlock($name, $parameters);
            }
        }

        throw new \InvalidArgumentException(sprintf('Block "%s" doesn\'t exist in grid template.', $name));
    }

    /**
     * Has block
     *
     * @param $name string
     * @return boolean
     */
    private function hasBlock($name)
    {
        foreach ($this->getTemplates() as $template) {
            if ($template->hasBlock($name)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Template Loader
     *
     * @return \Twig_TemplateInterface[]
     * @throws \Exception
     */
    private function getTemplates()
    {
        if (empty($this->templates)) {
            $this->templates[] = $this->environment->loadTemplate($this::DEFAULT_TEMPLATE);
        }

        return $this->templates;
    }

    public function getBlockPrefix()
    {
        return $this->getName();
    }

    public function getName()
    {
        return 'eps_jq_grid_twig_extension';
    }

}
