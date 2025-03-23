@php
    // Generate full menu
    $items = app(\App\Services\Common\MenuService::class)->generate();

    // Get authenticated user
    $user = \Auth::user();

    // Check if the user is an admin
    $isAdmin = $user?->isAdmin();

    // Decode user plan settings (Ensure they are arrays)
    $planAiTools = json_decode($user->plan_ai_tools ?? '{}', true);
    $planFeatures = json_decode($user->plan_features ?? '{}', true);
    $openAiItems = json_decode($user->open_ai_items ?? '{}', true);

    // Merge all enabled tools and features into one list
    $userAllowedItems = array_merge($planAiTools, $planFeatures, $openAiItems);

    // Function to check if a menu item should be displayed
    function canUserAccessMenu($menuKey, $userAllowedItems, $isAdmin) {
        // Admins see everything
        if ($isAdmin) {
            return true;
        }

        // Show menu if it is explicitly enabled in the user's plan
        if (isset($userAllowedItems[$menuKey]) && $userAllowedItems[$menuKey] === true) {
            return true;
        }

        // Show menu if it is NOT defined in the user's plan (default items)
        if (!array_key_exists($menuKey, $userAllowedItems)) {
            return true;
        }

        return false;
    }
@endphp

@foreach ($items as $key => $item)
    @if (canUserAccessMenu($key, $userAllowedItems, $isAdmin))
        @if (data_get($item, 'is_admin'))
            @if ($isAdmin)
                @if (data_get($item, 'show_condition', true) && data_get($item, 'is_active'))
                    @if ($item['children_count'])
                        @includeIf('default.components.navbar.partials.types.item-dropdown')
                    @else
                        @includeIf('default.components.navbar.partials.types.' . $item['type'])
                    @endif
                @endif
            @endif
        @else
            @if (data_get($item, 'show_condition', true) && data_get($item, 'is_active'))
                @if ($item['children_count'])
                    @includeIf('default.components.navbar.partials.types.item-dropdown')
                @else
                    @includeIf('default.components.navbar.partials.types.' . $item['type'])
                @endif
            @endif
        @endif
    @endif
@endforeach
