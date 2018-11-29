<?php namespace Anomaly\PagesModule;

use Anomaly\PagesModule\Http\Controller\Admin\AssignmentsController;
use Anomaly\PagesModule\Http\Controller\Admin\FieldsController;
use Anomaly\PagesModule\Http\Controller\Admin\VersionsController;
use Anomaly\PagesModule\Page\Contract\PageInterface;
use Anomaly\PagesModule\Page\Contract\PageRepositoryInterface;
use Anomaly\PagesModule\Page\PageCollection;
use Anomaly\PagesModule\Page\PageModel;
use Anomaly\PagesModule\Page\PageRepository;
use Anomaly\PagesModule\Type\Contract\TypeRepositoryInterface;
use Anomaly\PagesModule\Type\TypeModel;
use Anomaly\PagesModule\Type\TypeRepository;
use Anomaly\Streams\Platform\Addon\AddonServiceProvider;
use Anomaly\Streams\Platform\Assignment\AssignmentRouter;
use Anomaly\Streams\Platform\Field\FieldRouter;
use Anomaly\Streams\Platform\Model\Pages\PagesPagesEntryModel;
use Anomaly\Streams\Platform\Model\Pages\PagesTypesEntryModel;
use Anomaly\Streams\Platform\Version\VersionRouter;
use Illuminate\Http\Request;

/**
 * Class PagesModuleServiceProvider
 *
 * @link          http://pyrocms.com/
 * @author        PyroCMS, Inc. <support@pyrocms.com>
 * @author        Ryan Thompson <ryan@pyrocms.com>
 */
class PagesModuleServiceProvider extends AddonServiceProvider
{

    /**
     * The addon plugins.
     *
     * @var array
     */
    protected $plugins = [
        PagesModulePlugin::class,
    ];

    /**
     * The class bindings.
     *
     * @var array
     */
    protected $bindings = [
        PagesPagesEntryModel::class => PageModel::class,
        PagesTypesEntryModel::class => TypeModel::class,
    ];

    /**
     * The singleton bindings.
     *
     * @var array
     */
    protected $singletons = [
        PageRepositoryInterface::class => PageRepository::class,
        TypeRepositoryInterface::class => TypeRepository::class,
    ];

    /**
     * The addon routes.
     *
     * @var array
     */
    protected $routes = [
        'admin/pages'                  => 'Anomaly\PagesModule\Http\Controller\Admin\PagesController@index',
        'admin/pages/create'           => 'Anomaly\PagesModule\Http\Controller\Admin\PagesController@create',
        'admin/pages/edit/{id}'        => 'Anomaly\PagesModule\Http\Controller\Admin\PagesController@edit',
        'admin/pages/view/{id}'        => 'Anomaly\PagesModule\Http\Controller\Admin\PagesController@view',
        'admin/pages/change/{id}'      => 'Anomaly\PagesModule\Http\Controller\Admin\PagesController@change',
        'admin/pages/delete/{id}'      => 'Anomaly\PagesModule\Http\Controller\Admin\PagesController@delete',
        'admin/pages/types'            => 'Anomaly\PagesModule\Http\Controller\Admin\TypesController@index',
        'admin/pages/types/create'     => 'Anomaly\PagesModule\Http\Controller\Admin\TypesController@create',
        'admin/pages/types/edit/{id}'  => 'Anomaly\PagesModule\Http\Controller\Admin\TypesController@edit',
        'admin/pages/ajax/choose_type' => 'Anomaly\PagesModule\Http\Controller\Admin\TypesController@choose',
        'admin/pages/settings'         => 'Anomaly\PagesModule\Http\Controller\Admin\SettingsController@index',
        'pages/preview/{id}'           => 'Anomaly\PagesModule\Http\Controller\PagesController@preview',
    ];

    /**
     * Map additional routes.
     *
     * @param FieldRouter $fields
     * @param VersionRouter $versions
     * @param AssignmentRouter $assignments
     * @param PageRepositoryInterface $pages
     * @param Request $request
     */
    public function map(
        FieldRouter $fields,
        VersionRouter $versions,
        AssignmentRouter $assignments,
        PageRepositoryInterface $pages,
        Request $request
    ) {
        $versions->route($this->addon, VersionsController::class);

        $fields->route($this->addon, FieldsController::class);
        $assignments->route($this->addon, AssignmentsController::class, 'admin/pages/types');

        // Route the exact match.
        if ($page = $pages->findByPath($request->getPathInfo())) {

            $extension = $page->getHandler();

            $extension->route($page);

            $this->app->bind(
                'anomaly.module.pages::pages.current',
                function () use ($page) {
                    return $page;
                }
            );
        }

        /* @var PageCollection $pages */
        $pages = $pages->routable();

        /* @var PageInterface $page */
        foreach ($pages as $page) {

            $extension = $page->getHandler();

            $extension->route($page);
        }
    }
}
