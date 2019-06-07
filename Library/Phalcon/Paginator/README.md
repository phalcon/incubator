# Phalcon\Paginator\Pager

Pager object is a navigation menu renderer based on doctrine1 pager object.

Initialize the paginator object in the controller:

```php
use Phalcon\Mvc\Controller;
use Phalcon\Paginator\Adapter\NativeArray as Paginator;
use Phalcon\Paginator\Pager;

class IndexController extends Controller
{
    public function indexAction()
    {
        $currentPage = abs(
            $this->request->getQuery('page', 'int', 1)
        );

        if ($currentPage == 0) {
            $currentPage = 1;
        }

        $pager = new Pager(
            new Paginator(
                [
                    'data'  => range(1, 200),
                    'limit' => 10,
                    'page'  => $currentPage,
                ]
            ),
            [
                // We will use Bootstrap framework styles
                'layoutClass' => 'Phalcon\Paginator\Pager\Layout\Bootstrap',
                // Range window will be 5 pages
                'rangeLength' => 5,
                // Just a string with URL mask
                'urlMask'     => '?page={%page_number}',
                // Or something like this
                // 'urlMask'     => sprintf(
                //     '%s?page={%%page_number}',
                //     $this->url->get(
                //         [
                //             'for'        => 'index:posts',
                //             'controller' => 'index',
                //             'action'     => 'index',
                //         ]
                //     )
                // ),
            ]
        );

        $this->view->setVar('pager', $pager);
    }
}
```

And use it in template:

```volt
{% if pager|length() == 0 %}
    <p>Sorry nothing found</p>
{% else %}
    <ul>
    {% for item in pager %}
        <li>{{ item }}</li>
    {% endfor %}
    </ul>

    {% if pager.haveToPaginate() %}
        {# Render the navigation #}
        {{ pager.getLayout() }}
    {% endif %}
{% endif %}
```
