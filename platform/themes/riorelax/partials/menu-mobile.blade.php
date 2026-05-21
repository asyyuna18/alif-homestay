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

        <li class="nav-item">
            <a href="{{ $menuUrl }}"
               @class(['nav-link collapsed', 'has-sub' => $row->has_child && ! $hideSubmenu, 'active' => $row->active])
               target="{{ $row->target }}"
               @if($row->has_child && ! $hideSubmenu)
                   data-bs-toggle="collapse"
                   data-bs-target="#menu-collapse-{{ $row->id }}"
                   aria-expanded="false"
                   aria-controls="menu-collapse-{{ $row->id }}"
               @endif
            >{{ $row->title }}</a>
        </li>

        @if ($row->has_child && ! $hideSubmenu)
            <div class="collapse" id="menu-collapse-{{ $row->id }}">
                {!! Menu::renderMenuLocation('main-menu', [
                    'menu_nodes' => $row->child,
                    'view' => 'menu-mobile',
                    'options' => ['class' => 'navbar-nav me-auto mb-2 mb-lg-0 ms-3'],
                ]) !!}
            </div>
        @endif
    @endforeach
</ul>
