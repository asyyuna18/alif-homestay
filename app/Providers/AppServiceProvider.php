<?php

namespace App\Providers;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\DashboardMenu;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Collection;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (! class_exists(DashboardMenu::class) || ! class_exists(BaseHelper::class)) {
            return;
        }

        DashboardMenu::default()->beforeRetrieving(function (): void {
            $homepageId = BaseHelper::getHomepageId();

            DashboardMenu::make()
                ->registerItem([
                    'id' => 'cms-core-homepage',
                    'priority' => 3,
                    'name' => 'Homepage',
                    'icon' => 'ti ti-home',
                    'url' => fn () => $homepageId && Route::has('pages.edit')
                        ? route('pages.edit', $homepageId)
                        : (Route::has('pages.index') ? route('pages.index') : url(BaseHelper::getAdminPrefix())),
                    'permissions' => ['pages.index', 'pages.edit'],
                ]);
        });

        add_filter('dashboard_menu', function (Collection $items): Collection {
            return $this->removeDashboardMenuItems($items, [
                'cms-core-theme',
                'cms-core-plugins',
                'cms-core-plugins-installed',
                'cms-core-plugins-marketplace',
                'cms-plugins-gallery',
                'cms-plugins-place',
                'cms-plugins-team',
            ]);
        }, 999);

        app('events')->listen(RouteMatched::class, function (RouteMatched $event): void {
            if ($event->route->getName() !== 'theme.index' || ! Route::has('theme.options')) {
                return;
            }

            redirect()->route('theme.options')->send();
            exit;
        });
    }

    protected function removeDashboardMenuItems(Collection $items, array $hiddenIds): Collection
    {
        return $items
            ->reject(fn (array $item, string $id) => in_array($id, $hiddenIds, true))
            ->map(function (array $item) use ($hiddenIds): array {
                if ($item['children'] instanceof Collection) {
                    $item['children'] = $this->removeDashboardMenuItems($item['children'], $hiddenIds);
                }

                return $item;
            });
    }
}
