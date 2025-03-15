<?php

namespace App\Extensions\Menu\System\Http\Controllers;

use App\Helpers\Classes\Helper;
use App\Http\Controllers\Controller;
use App\Models\Common\Menu;
use App\Services\Common\MenuService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MenuController extends Controller
{
    public function __construct(
        public MenuService $service
    ) {}

    public function index()
    {
        $items = $this->service->generate(false);

        $dash_theme = setting('dash_theme');

        return view('menu::index', ['items' => $items, 'dash_theme' => $dash_theme]);
    }

    public function delete(Menu $menu)
    {
        if (Helper::appIsDemo()) {
            return response()->json([
                'status'  => 'error',
                'message' => trans('This feature is disabled in demo mode.'),
            ]);
        }

        if ($menu['custom_menu']) {

            Menu::query()->where('parent_id', $menu->id)->update([
                'parent_id' => null,
            ]);

            $menu->delete();
        }

        $this->service->regenerate();

        return back()->with([
            'type'    => 'success',
            'message' => trans('Item deleted'),
        ]);
    }

    public function update(Request $request, Menu $menu, $type)
    {
        if (Helper::appIsDemo()) {
            return response()->json([
                'status'  => 'error',
                'message' => trans('This feature is disabled in demo mode.'),
            ]);
        }

        $menu->update([
            $type => $request->get('value'),
        ]);

        $this->service->regenerate();

        return response()->json([
            'message' => trans('Menu updated'),
        ]);
    }

    public function order(Request $request)
    {
        if (Helper::appIsDemo()) {
            return response()->json([
                'status'  => 'error',
                'message' => trans('This feature is disabled in demo mode.'),
            ]);
        }

        $data = $request->get('menu');

        $parentMenuKeys = array_keys(
            array_filter($data, fn ($v) => $v == 'null')
        );

        $this->service->parentMenuOrderUpdate(
            $parentMenuKeys
        );

        $this->service->subMenuOrderUpdate(
            array_filter($data, fn ($v) => $v != 'null')
        );

        $this->service->regenerate();

        return response()->json([
            'message' => trans('Menu updated'),
        ]);
    }

    public function status(Request $request, Menu $menu)
    {
        if (Helper::appIsDemo()) {
            return response()->json([
                'status'  => 'error',
                'message' => trans('This feature is disabled in demo mode.'),
            ]);
        }

        $menu->update([
            'is_active' => ! $menu->getAttribute('is_active'),
        ]);

        $this->service->regenerate();

        return response()->json([
            'message' => trans('Menu updated'),
        ]);
    }

    public function boltMenu(Request $request, Menu $menu)
    {
        if (Helper::appIsDemo()) {
            return response()->json([
                'status'  => 'error',
                'message' => trans('This feature is disabled in demo mode.'),
            ]);
        }

        $menu->update([
            'bolt_menu' => ! $menu->getAttribute('bolt_menu'),
        ]);

        $this->service->regenerate();

        return response()->json([
            'message' => trans('Menu updated'),
        ]);
    }

    public function store(Request $request)
    {
        if (Helper::appIsDemo()) {
            return response()->json([
                'status'  => 'error',
                'message' => trans('This feature is disabled in demo mode.'),
            ]);
        }

        $data = $request->validate([
            'label' => 'required|string|max:30',
            'type'  => 'required|in:label,divider,item',
            'link'  => 'sometimes|nullable|string|max:255',
        ]);

        if ($data['type'] == 'item') {
            $data['icon'] = 'tabler-file-unknown';
            $data['route'] = $data['link'];

        }

        Menu::query()->create(array_merge($data, [
            'key'         => $this->keyGenerate($data['label']),
            'custom_menu' => true,
        ]));

        $this->service->regenerate();

        return response()->json([
            'message' => trans('Added the new item'),
        ]);
    }

    public function keyGenerate(string $label, $time = ''): string
    {
        $label = Str::slug($label) . $time;

        if (Menu::query()->where('key', $label)->exists()) {
            return $this->keyGenerate($label, rand(1000, 9999));
        }

        return $label;
    }
}
