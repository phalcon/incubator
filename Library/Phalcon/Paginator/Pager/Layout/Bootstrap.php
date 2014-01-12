<?php
/**
 * Phalcon Framework
 * This source file is subject to the New BSD License that is bundled
 * with this package in the file docs/LICENSE.txt.
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@phalconphp.com so we can send you a copy immediately.
 *
 * @author Nikita Vershinin <endeveit@gmail.com>
 */
namespace Phalcon\Paginator\Pager\Layout;

use Phalcon\Paginator\Pager\Layout;

/**
 * \Phalcon\Paginator\Pager\Layout\Bootstrap
 * Pager layout that uses Twitter Bootstrap styles.
 */
class Bootstrap extends Layout
{

    /**
     * {@inheritdoc}
     *
     * @var string
     */
    protected $template = '<li><a href="{%url}">{%page}</a></li>';

    /**
     * {@inheritdoc}
     *
     * @var string
     */
    protected $selectedTemplate = '<li class="active"><span>{%page}</span></li>';

    /**
     * {@inheritdoc}
     *
     * @param  array  $options
     * @return string
     */
    public function getRendered(array $options = array())
    {
        $result = '<ul class="pagination">';

        $bootstrapSelected = '<li class="disabled"><span>{%page}</span></li>';
        $originTemplate = $this->selectedTemplate;
        $this->selectedTemplate = $bootstrapSelected;

        $this->addMaskReplacement('page', '&laquo;', true);
        $options['page_number'] = $this->pager->getPreviousPage();
        $result .= $this->processPage($options);

        $this->selectedTemplate = $originTemplate;
        $this->removeMaskReplacement('page');
        $result .= parent::getRendered($options);

        $this->selectedTemplate = $bootstrapSelected;

        $this->addMaskReplacement('page', '&raquo;', true);
        $options['page_number'] = $this->pager->getNextPage();
        $result .= $this->processPage($options);

        $this->selectedTemplate = $originTemplate;

        $result .= '</ul>';

        return $result;
    }
}
