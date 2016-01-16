<?php namespace Anomaly\PagesModule\Page\Command;

use Anomaly\PagesModule\Page\Contract\PageInterface;
use Anomaly\PagesModule\Page\PageCollection;
use Anomaly\Streams\Platform\Model\EloquentModel;
use Illuminate\Contracts\Bus\SelfHandling;

/**
 * Class SetParentRelations
 *
 * @page          http://anomaly.is/streams-platform
 * @author        AnomalyLabs, Inc. <hello@anomaly.is>
 * @author        Ryan Thompson <ryan@anomaly.is>
 * @package       Anomaly\PagesModule\Page\Command
 */
class SetParentRelations implements SelfHandling
{

    /**
     * The page collection.
     *
     * @var PageCollection
     */
    protected $pages;

    /**
     * Create a new SetParentRelations instance.
     *
     * @param PageCollection $pages
     */
    public function __construct(PageCollection $pages)
    {
        $this->pages = $pages;
    }

    /**
     * Handle the command.
     */
    public function handle()
    {
        /* @var PageInterface|EloquentModel $page */
        foreach ($this->pages as $page) {

            /* @var PageInterface $parent */
            if (($id = $page->getParentId()) && $parent = $this->pages->find($id)) {
                $page->setRelation('parent', $parent);
            }
        }
    }
}
