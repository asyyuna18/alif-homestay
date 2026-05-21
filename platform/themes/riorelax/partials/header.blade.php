@php($fullWidth = $fullWidth ?? false)
@php($hasSidebarMenu = $fullWidth && Menu::isLocationHasMenu('sidebar-menu'))
@php($buttonLabel = theme_option('header_button_label'))
@php($buttonUrl = theme_option('header_button_url'))
@php($buttonUrl = $buttonUrl ? riorelax_theme_url($buttonUrl) : null)
@php($hasHeaderButton = ! $hasSidebarMenu && $buttonLabel && $buttonUrl)

<div @if(theme_option('header_sticky_enabled', 'yes') == 'yes') id="header-sticky" @endif class="menu-area">
    <div @class(['container' => ! $fullWidth, 'container-fluid' => $fullWidth])>
        <div class="second-menu">
            <div class="row align-items-center">
                <div class="col-8 col-md-4 col-lg-2 col-xl-2">
                    @if ($logo = theme_option('logo'))
                        <div class="logo">
                            <a href="{{ route('public.index') }}"><img src="{{ RvMedia::getImageUrl($logo) }}" alt="{{ theme_option('site_name') }}"></a>
                        </div>
                    @endif
                </div>
                <div @class([
                    'col-4 col-md-8',
                    'col-lg-8 col-xl-8' => ! $fullWidth || $hasHeaderButton,
                    'col-lg-9 col-xl-9' => $fullWidth && $hasSidebarMenu,
                    'col-lg-10 col-xl-10' => $fullWidth && ! $hasSidebarMenu && ! $hasHeaderButton,
                ])>
                    <div class="main-menu text-center">
                        <nav id="mobile-menu">
                            {!! Menu::renderMenuLocation('main-menu', [
                                'options' => ['class' => 'main-menu'],
                                'view' => 'main-menu',
                            ]) !!}
                        </nav>
                    </div>
                    <button class="navbar-toggler text-white float-end d-lg-none btn btn-toggle-menu-mobile" type="button" data-bs-toggle="collapse" data-bs-target="#menu-mobile-nav" aria-controls="navbarTogglerDemo02" aria-expanded="false" aria-label="Toggle navigation">
                        <i class="fa fa-list"></i>
                    </button>
                </div>

                @if ($hasSidebarMenu)
                    <div class="d-none d-lg-block col-xl-1 col-lg-1 text-end">
                        <div class="search-top">
                            <ul>
                                <li>
                                    <div class="bar-humburger">
                                        <a href="#" class="menu-tigger" aria-label="{{ __('Open menu') }}">
                                            <i class="fas fa-bars"></i>
                                        </a>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                @elseif ($hasHeaderButton)
                    <div class="d-none d-lg-block col-xl-2 col-lg-2 text-end">
                        <a href="{{ $buttonUrl }}" class="top-btn mt-10 mb-10">{!! BaseHelper::clean($buttonLabel) !!}</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
    {!! Theme::partial('menu-mobile-collapse') !!}
</div>

@if ($hasSidebarMenu)
    {!!  Theme::partial('offcanvas-menu') !!}
@endif
