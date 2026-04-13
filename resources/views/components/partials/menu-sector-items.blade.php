@php
    $sectorMenu = getSectorMenuItems();
    $isHorizontal = $isHorizontal ?? false;
@endphp

@foreach($sectorMenu as $sectorKey => $sector)
    @php
        $sectorActive = isSectorActive($sector['items']);
        $hasVisibleItems = false;
        foreach ($sector['items'] as $item) {
            if (!isset($item['permissions']) && !isset($item['permission'])) {
                $hasVisibleItems = true; // Si no tiene restricción de permisos, es visible para todos
                break;
            } elseif (isset($item['permissions'])) {
                if (auth()->user()->canAny($item['permissions'])) {
                    $hasVisibleItems = true;
                    break;
                }
            } elseif (isset($item['permission'])) {
                if (auth()->user()->can($item['permission'])) {
                    $hasVisibleItems = true;
                    break;
                }
            }
        }
    @endphp

    @if($hasVisibleItems)
        @if(!$isHorizontal)
            <li class="menu-header mt-3">
                <span class="menu-header-text">{{ $sector['label'] }}</span>
            </li>
        @endif

        @foreach($sector['items'] as $item)
            @php
                $canAccess = true; // Por defecto asumimos que tiene acceso (para ítems sin restricción como Chat Interno)
                if (isset($item['permissions'])) {
                    $canAccess = auth()->user()->canAny($item['permissions']);
                } elseif (isset($item['permission'])) {
                    $canAccess = auth()->user()->can($item['permission']);
                }
            @endphp

            @if($canAccess)
                @if(isset($item['children']))
                    @php $itemActive = isMenuItemActive($item); @endphp
                    <li class="menu-item {{ $itemActive ? 'active open' : '' }}">
                        <a href="javascript:void(0);" class="menu-link menu-toggle">
                            <i class="menu-icon tf-icons ri {{ $item['icon'] }}"></i>
                            <div>{{ $item['label'] }}</div>
                        </a>
                        <ul class="menu-sub">
                            @foreach($item['children'] as $child)
                                @php
                                    $childCanAccess = true;
                                    if (isset($child['permission'])) {
                                        $childCanAccess = auth()->user()->can($child['permission']);
                                    }
                                    $childActive = isMenuItemActive($child);
                                @endphp
                                @if($childCanAccess)
                                    <li class="menu-item {{ $childActive ? 'active' : '' }}">
                                        <a href="{{ route($child['route']) }}" class="menu-link">
                                            <div>{{ $child['label'] }}</div>
                                        </a>
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    </li>
                @else
                    @php
                        $itemActive = isMenuItemActive($item);
                        $routeName = ($isHorizontal && isset($item['route_horizontal'])) ? $item['route_horizontal'] : $item['route'];
                    @endphp
                    <li class="menu-item {{ $itemActive ? 'active' : '' }}">
                        <a href="{{ route($routeName) }}" class="menu-link">
                            <i class="menu-icon tf-icons ri {{ $item['icon'] }}"></i>
                            <div>{{ $item['label'] }}</div>
                        </a>
                    </li>
                @endif
            @endif
        @endforeach
    @endif
@endforeach
