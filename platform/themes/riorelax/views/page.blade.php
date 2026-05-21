@php
    $isHomepage = \Botble\Base\Facades\BaseHelper::isHomepage($page->getKey());
    $shouldRenderHomepageContent = $isHomepage && $page->template === 'full-menu';
    Theme::set('pageTitle', $page->name);
    Theme::set('pageDescription', $page->description);
    Theme::set('breadcrumbBackgroundImage', $page->getMetaData('breadcrumb_background', true));
    Theme::set('breadcrumb', $page->getMetaData('breadcrumb', true))
@endphp

@if ($isHomepage && ! $shouldRenderHomepageContent)
    @include(Theme::getThemeNamespace() . '::partials.homepage-simple', ['page' => $page])
@else
    {!!
        apply_filters(
            PAGE_FILTER_FRONT_PAGE_CONTENT,
            Html::tag('div', BaseHelper::clean($page->content), ['class' => 'ck-content'])->toHtml(),
            $page
        )
    !!}
@endif
