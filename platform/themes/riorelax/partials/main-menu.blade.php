<ul {!! BaseHelper::clean($options) !!}>
    @foreach ($menu_nodes as $row)
        @php
            $hideSubmenu = $row->has_child && (
                str($row->title)->lower()->trim()->toString() === 'home' ||
                collect($row->child)->every(fn ($child) => str($child->title)->lower()->startsWith('home page'))
            );
            $isDropdownOnlyParent = $row->has_child
                && ! $hideSubmenu
                && blank($row->reference_type)
                && blank($row->reference_id)
                && $row->url === '/';
            $menuUrl = $isDropdownOnlyParent ? 'javascript:void(0)' : $row->url;
        @endphp

        <li @class(['has-sub' => $row->has_child && ! $hideSubmenu, $row->css_class])>
            <a @class(['active' => $row->active]) href="{{ $menuUrl }}" target="{{ $row->target }}">
                @if($iconImage = $row->getMetaData('icon_image', true))
                    <img src="{{ RvMedia::getImageUrl($iconImage) }}" alt="{{ $row->title }}" loading="lazy"/>
                @elseif($row->icon_font)
                    <i class="{{ trim($row->icon_font) }}"></i>
                @endif

                {{ $row->title }}
            </a>
            @if($row->has_child && ! $hideSubmenu)
                {!! Menu::renderMenuLocation('main-menu', [
                    'menu_nodes' => $row->child,
                    'view' => 'main-menu',
                    'options' => ['class' => 'sub-menu'],
                ]) !!}
            @endif
        </li>
    @endforeach
</ul>
