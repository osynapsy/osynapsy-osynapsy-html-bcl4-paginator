<?php
namespace Osynapsy\Bcl4\Paginator;

use Osynapsy\Html\Tag;
use Osynapsy\Html\Component\InputHidden;
use Osynapsy\Bcl4\ComboBox;

/**
 * Static class which build Bcl4 Pagination Object/Component
 *
 * @author Pietro Celeste <p.celeste@osynpasy.net>
 */
class PaginatorBuilder
{
    public static function build(Paginator $pagination)
    {
        $container = new Tag('div', null, 'd-flex justify-content-end mt-1');
        $container->append([
            new InputHidden($pagination->id, 'BclPaginationCurrentPage'),
            new InputHidden($pagination->id.'OrderBy', 'BclPaginationOrderBy')
        ]);
        if ($pagination->showPageDimension()) {            
            $container->append([
                sprintf('<small class="text-nowrap p-2">%s per pagina</small>', $pagination->getEntity()),
                self::fieldPageDimensionsFactory(
                    self::getPageDimensionFieldId($pagination->id),
                    $pagination->getParentComponent(),
                    $pagination('defaultPageDimension'),
                    $pagination('pageDimension')
                )
            ]);
        }
        if ($pagination->showPageInfo()) {
            $container->add(self::labelInfoFactory(
                $pagination->getMeta(),
                $pagination->getEntity()
            ));
        }
        $container->add(self::ulFactory(
            $pagination('pageCurrent'),
            $pagination('pageTotal'),
            $pagination->position
        ));
        return $container;
    }

    public static function labelInfoFactory($meta, $entity)
    {
        $end = min($meta['pageCurrent'] * $meta['pageDimension'], $meta['rowsTotal']);
        $start = ($meta['pageCurrent'] - 1) * $meta['pageDimension'] + 1;
        $label = sprintf('%s - %s di %s %s', min($start, $end), $end, $meta['rowsTotal'], strtolower($entity));
        return (new Tag('small', null, 'text-nowrap ml-3 mr-2 mt-2'))->add($label);
    }

    protected static function ulFactory($currentPage, $totalPages, $position)
    {
        $dim = min(7, $totalPages);
        $app = floor($dim / 2);
        $pageMax = max($dim, min($currentPage + $app, $totalPages));
        $pageMin = min(max(1, $currentPage - $app), $totalPages - $dim + 1);
        $ul = new Tag('ul', null, 'pagination pagination-sm justify-content-'.$position);
        $ul->add(self::liFactory('&laquo;', 'first', $currentPage < 2 ? 'disabled' : ''));
        for ($i = $pageMin; $i <= $pageMax; $i++) {
            $ul->add(self::liFactory($i, $i, $i == $currentPage ? 'active' : ''));
        }
        $ul->add(self::liFactory('&raquo;', 'last', $currentPage >= $totalPages ? 'disabled' : ''));
        return $ul;
    }

    protected static function liFactory($label, $value, $class)
    {
        $li = new Tag('li', null, trim('page-item '.$class));
        $li->add(new Tag('a', null, 'page-link'))
           ->attribute('data-value', $value)
           ->attribute('href','#')
           ->add($label);
        return $li;
    }

    protected static function fieldPageDimensionsFactory($fieldId, $parentComponentId, $defaultPageDimension, $pageDimension)
    {
        $Combo = new ComboBox($fieldId);
        $Combo->setPlaceholder(false);
        $Combo->setSmallSize();
        $Combo->setValue($pageDimension);
        $Combo->attribute('onchange', "Osynapsy.refreshComponents(['{$parentComponentId}'])");
        $Combo->setDataset(array_map(fn($v) => $v * ($defaultPageDimension ?: $pageDimension), [1,2,5,10,20]));
        return $Combo;
    }

    public static function getPageDimensionFieldId($paginationId)
    {
        return $paginationId . (strpos($paginationId, '_') ? '_page_dimension' : 'PageDimension');
    }
}
