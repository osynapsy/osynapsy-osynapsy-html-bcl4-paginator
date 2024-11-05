<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Bcl4\Paginator;

use Osynapsy\Html\Component\AbstractComponent;
use Osynapsy\Html\Component\PaginatorInterface;
use Osynapsy\Database\PaginatorSimple;
use Osynapsy\Database\Driver\DboInterface;

/**
 * Description of Pagination
 *
 * @author Pietro Celeste <p.celeste@osynapsy.net>
 */
class Paginator extends AbstractComponent implements PaginatorInterface
{
    private $entity = 'Record';
    protected $data = [];
    private $filters = [];
    private $loaded = false;
    private $orderBy = null;
    private $parentComponent;
    protected $paginator;
    private $position = 'center';
    protected $showPageDimension = true;
    protected $showPageInfo = true;
    private $meta = [
        //Dimension of the pag in row;
        'pageDimension' => 10,
        'pageTotal' => 1,
        'pageCurrent' => 1,
        'rowsTotal' => 0
    ];

    /**
     * Costructor of pager component.
     *
     * @param string $id Identify of component
     * @param int $pageDimension Page dimension in number of row
     * @param bool $showPageDimension enable o disabled visualization of field for choice page dim.
     * @param bool $showPageInfo enable o disabled visualization of label with page current and page total info
     * @param string $tag Tag of container
     */
    public function __construct($id, $pageDimension = 10, $showPageDimension = true, $showPageInfo = true, $tag = 'div')
    {
        parent::__construct($tag, $id);
        $this->requireJs('bcl4/pagination/script.js');
        $this->addClass('BclPagination');
        if ($tag == 'form') {
            $this->attribute('method', 'post');
        }
        $this->setPageDimension($pageDimension);
        $this->showPageDimension = $showPageDimension;
        $this->showPageInfo = $showPageInfo;
    }

    public function preBuild()
    {
        if (!$this->loaded) {
            $this->loadData();
        }
        $this->add(PaginationBuilder::build($this));
    }

    public function loadData($defaultPage = null)
    {
        $requestPage = filter_input(\INPUT_POST, $this->id) ?? $defaultPage;
        $sort = $this->getSort(filter_input(\INPUT_POST, $this->id.'OrderBy'));
        $pageDimension = $this->meta['pageDimension'];
        $this->data = $this->paginator->get($requestPage, $pageDimension, $sort);
        $this->meta = $this->paginator->getAllMeta();
        $this->loaded = true;
        return $this->data;
    }

    public function addFilter($field, $value = null)
    {
        $this->filters[$field] = $value;
    }

    public function getEntity()
    {
        return $this->entity;
    }

    public function getMeta($key = null)
    {
        return is_null($key) ? $this->meta : ($this->meta[$key] ?? null);
    }

    public function getOrderBy()
    {
        return $this->orderBy;
    }

    public function getPageDimension()
    {
        return $this->meta['pageDimension'];
    }

    public function getParentComponent()
    {
        return $this->parentComponent;
    }

    public function getSort($requestSort)
    {
        $this->orderBy = empty($requestSort) ? $this->orderBy : str_replace(['][', '[', ']'], [',' ,'' ,''], $requestSort);
        return $this->orderBy;
    }

    public function getTotal($key)
    {
        return $this->getMeta('total'.ucfirst($key));
    }

    public function setOrder($field)
    {
        $this->orderBy = str_replace(['][', '[', ']'], [',' ,'' ,''], $field);
        return $this;
    }

    public function setPageDimension($pageDimension)
    {
        $comboId = PaginationBuilder::getPageDimensionFieldId($this->id);
        $this->meta['pageDimension'] = $_REQUEST[$comboId] ?? $pageDimension;
    }

    public function setParentComponent($componentId)
    {
        $this->parentComponent = $componentId;
        $this->attribute('data-parent', $componentId);
        return $this;
    }

    public function setPosition($position)
    {
        $this->position = $position;
    }

    public function setSql(DboInterface $Dbo, $query, array $queryParameters = [])
    {
        $this->paginator = new PaginatorSimple($this->id.'Paginator', $Dbo, $query, $queryParameters);
        return $this;
    }

    /**
     * Returns the value of showPageDimension, indicating whether to show the page dimension label in pagination
     *
     * @return bool
     */
    public function showPageDimension()
    {
        return $this->showPageDimension;
    }

    /**
     * Returns the value of showPageInfo, indicating whether to show the page info in pagination
     *
     * @return bool
     */
    public function showPageInfo()
    {
        return $this->showPageInfo;
    }

    public function __invoke($k)
    {
        return $this->meta[$k] ?? null;
    }
}
