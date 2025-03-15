<?php

namespace App\Extensions\OnboardingPro\System\Http\Controllers;

use App\Extensions\OnboardingPro\System\Models\Banner;
use App\Extensions\OnboardingPro\System\Models\BannerUser;
use App\Extensions\OnboardingPro\System\Models\IntroductionStyle;
use App\Extensions\OnboardingPro\System\Models\Survey;
use App\Extensions\OnboardingPro\System\Models\SurveyUser;
use App\Http\Controllers\Controller;
use App\Models\Extensions\Introduction;
use App\Services\Common\MenuService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OnboardingProController extends Controller
{
    public function index(): View
    {
        return view('onboarding-pro::index');
    }

    public function banners(): View
    {
        $banners = Banner::all();

        return view('onboarding-pro::banners', compact(['banners']));
    }

    public function surveys(): View
    {
        $surveys = Survey::all();

        return view('onboarding-pro::surveys', compact(['surveys']));
    }

    public function customization(): View
    {
        $introduction = IntroductionStyle::query()->first();

        return view('onboarding-pro::introductionCustom', compact(['introduction']));
    }

    public function ImageDelete($key)
    {
        try {
            $introductionKey = explode('-', $key)[0];

            if (isset(explode('-', $key)[1])) {

                $order = explode('-', $key)[1];
                $initialize = Introduction::query()->where('key', $introductionKey)->first();
                $model = Introduction::query()->where('key', $introductionKey)
                    ->where('parent_id', $initialize->id)
                    ->skip($order - 1)
                    ->take(1)
                    ->first();

                $model->update([
                    'file_url'  => null,
                    'file_type' => null,
                ]);
            } else {
                Introduction::query()->where('key', $introductionKey)->update([
                    'file_url'  => null,
                    'file_type' => null,
                    'file_path' => null,
                ]);
            }

            return response()->json(['success' => true]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function customizationSave(Request $request): RedirectResponse
    {
        $model = IntroductionStyle::query()->first();

        if (! $model) {
            $model = new IntroductionStyle;
        }

        $model->forceFill([
            'title_size'             => $request->get('title_size'),
            'description_size'       => $request->get('description_size'),
            'background_color'       => $request->get('background_color_value'),
            'title_color'            => $request->get('title_color_value'),
            'description_color'      => $request->get('description_color_value'),
            'dark_background_color'  => $request->get('dark_background_color_value'),
            'dark_title_color'       => $request->get('dark_title_color_value'),
            'dark_description_color' => $request->get('dark_description_color_value'),
        ])->save();

        return redirect()->back()->with([
            'message' => 'Updated Successfully',
            'type'    => 'success',
        ]);
    }

    public function introduction(): View
    {
        // Ana "initialize" kaydı ve ilişkili tüm children kayıtlarını tek seferde çekiyoruz
        $initializeRecord = Introduction::with('children')
            ->where('key', 'initialize')
            ->whereNull('parent_id')
            ->first();

        // Eğer tüm kayıtları da kullanacaksanız:
        $list = Introduction::with('children')->get()->sortBy('order');

        // Menüler
        $menus = collect(app(MenuService::class)->regenerate())
            ->where('is_admin', false)
            ->where('show_condition', true)
            ->whereNotNull('data-name');

        // İstediğiniz değişkenleri Blade'e gönderiyoruz
        return view('onboarding-pro::introduction', [
            'introductions'    => Introduction::all()->sortBy('order'),
            'initializeRecord' => $initializeRecord,
            'list'             => $list,
            'menus'            => $menus,
        ]);
    }

    public function introductionSave(Request $request): RedirectResponse
    {
        $data = $request->except('_token', '_method');
        $order = json_decode($request->input('order'), true);
        $activeItems = json_decode($request->input('active_items'), true);
        $position = 1;

        if (isset($data['introductions'])) {
            $data = $data['introductions'];
        }

        // Tüm kayıtları önce inactive yap
        Introduction::query()->update(['status' => false]);

        // Initialize işleme
        if (isset($data['initialize'])) {
            $titles = $data['initialize']['title'] ?? [];
            $intros = $data['initialize']['intro'] ?? [];

            // Ana initialize kaydını oluştur/güncelle
            $initializeRecord = Introduction::updateOrCreate(
                [
                    'key'       => 'initialize',
                    'parent_id' => null,
                ],
                [
                    'intro'  => $intros[0] ?? null,
                    'title'  => $titles[0] ?? null,
                    'order'  => $position++,
                    'status' => in_array('initialize', $activeItems),
                ]
            );

            // Ana kayıt için dosya işleme
            if ($request->hasFile('introductions.initialize.file.0')) {
                $file = $request->file('introductions.initialize.file.0');
                $initializeRecord->update([
                    'file_url'  => '/uploads/' . $file->store('introductions', 'public'),
                    'file_type' => $file->getMimeType(),
                    'file_path' => $file->getPathname(),
                ]);
            }

            // Alt kayıtları güncelle (silmeden)
            if (count($titles) > 1) {
                for ($i = 1; $i < count($titles); $i++) {
                    if (! empty($titles[$i]) || ! empty($intros[$i])) {
                        // Mevcut alt kaydı bul veya yeni oluştur
                        $childRecord = Introduction::firstOrNew([
                            'key'       => 'initialize',
                            'parent_id' => $initializeRecord->id,
                            'order'     => $position++,
                        ]);

                        $childRecord->forceFill([
                            'intro'  => $intros[$i] ?? null,
                            'title'  => $titles[$i] ?? null,
                            'status' => true,
                        ])->save();

                        if ($request->hasFile("introductions.initialize.file.$i")) {
                            $file = $request->file("introductions.initialize.file.$i");
                            $childRecord->update([
                                'file_url'  => '/uploads/' . $file->store('introductions', 'public'),
                                'file_type' => $file->getMimeType(),
                                'file_path' => $file->getPathname(),
                            ]);
                        }
                    }
                }
            }
        }

        // Last kaydını işle
        if (isset($data['last'])) {
            $lastData = [
                'intro'  => $data['last']['intro'][0] ?? null,
                'title'  => $data['last']['title'][0] ?? null,
                'order'  => 99,
                'status' => in_array('last', $activeItems),
            ];

            if ($request->hasFile('introductions.last.file')) {
                $file = $request->file('introductions.last.file.0');
                $lastData['file_url'] = '/uploads/' . $file->store('introductions', 'public');
                $lastData['file_type'] = $file->getMimeType();
                $lastData['file_path'] = $file->getPathname();
            }

            Introduction::updateOrCreate(
                ['key' => 'last', 'parent_id' => null],
                $lastData
            );
        }

        // Diğer tüm kayıtları işle
        foreach ($order as $key) {
            if ($key !== 'initialize' && $key !== 'last' && isset($data[$key])) {
                $itemData = [
                    'intro'     => $data[$key]['intro'][0] ?? null,
                    'title'     => $data[$key]['title'][0] ?? null,
                    'order'     => $position++,
                    'status'    => in_array($key, $activeItems),
                    'parent_id' => null,
                ];

                if ($request->hasFile("introductions.$key.file")) {
                    $file = $request->file("introductions.$key.file.0");
                    $itemData['file_url'] = '/uploads/' . $file->store('introductions', 'public');
                    $itemData['file_type'] = $file->getMimeType();
                    $itemData['file_path'] = $file->getPathname();
                }
                Introduction::updateOrCreate(
                    ['key' => $key, 'parent_id' => null],
                    $itemData
                );
            }
        }

        // Temizlik: Artık kullanılmayan alt kayıtları sil
        if (isset($data['initialize'])) {
            if (! is_array($data['initialize']['title'])) {
                $data['initialize']['title'] = [$data['initialize']['title']];
            }

            $validOrders = range(1, count($data['initialize']['title']));

            Introduction::where('key', 'initialize')
                ->where('parent_id', $initializeRecord->id)
                ->whereNotIn('order', $validOrders)
                ->delete();
        }

        return redirect()->back()->with([
            'message' => 'Updated Successfully',
            'type'    => 'success',
        ]);
    }

    public function bannerCreate(): View
    {
        return view('onboarding-pro::bannerCreate');
    }

    public function surveyCreate(): View
    {
        return view('onboarding-pro::surveyCreate');
    }

    public function bannerPost(Request $request): RedirectResponse
    {
        $request->validate([
            'description' => 'required',
            'status'      => 'required',
            'permanent'   => 'required',
        ]);

        $model = new Banner;
        $model->fill([
            'description'      => $request->get('description'),
            'background_color' => $request->get('background_color_value'),
            'text_color'       => $request->get('text_color_value'),
            'status'           => $request->get('status'),
            'permanent'        => $request->get('permanent'),
        ])->save();

        return redirect()->route('dashboard.admin.onboarding-pro.banners')->with([
            'message' => 'Created Successfully',
            'type'    => 'success',
        ]);
    }

    public function surveyPost(Request $request): RedirectResponse
    {
        $request->validate([
            'description' => 'required',
            'status'      => 'required',
        ]);

        $model = new Survey;
        $model->fill([
            'description'      => $request->get('description'),
            'background_color' => $request->get('background_color_value'),
            'text_color'       => $request->get('text_color_value'),
            'status'           => $request->get('status'),
        ])->save();

        return redirect()->route('dashboard.admin.onboarding-pro.surveys')->with([
            'message' => 'Created Successfully',
            'type'    => 'success',
        ]);
    }

    public function bannerEdit($id): View
    {
        $banner = Banner::query()->findOrFail($id);

        return view('onboarding-pro::bannerEdit', compact('banner'));
    }

    public function surveyEdit($id): View
    {
        $survey = Survey::query()->findOrFail($id);

        return view('onboarding-pro::surveyEdit', compact('survey'));
    }

    public function surveyDelete($id): RedirectResponse
    {
        $survey = Survey::query()->findOrFail($id);
        $survey->delete();

        return redirect()->back()->with([
            'message' => 'Deleted Successfully',
            'type'    => 'success',
        ]);
    }

    public function bannerDelete($id): RedirectResponse
    {
        $survey = Banner::query()->findOrFail($id);
        $survey->delete();

        return redirect()->back()->with([
            'message' => 'Deleted Successfully',
            'type'    => 'success',
        ]);
    }

    public function surveyResult($id): View
    {
        $surveyResults = SurveyUser::query()
            ->selectRaw('point, COUNT(*) as total')
            ->where('survey_id', $id)
            ->groupBy('point')
            ->orderBy('point', 'asc')
            ->get();

        return view('onboarding-pro::surveyResult', compact(['surveyResults', 'id']));
    }

    public function surveyResultPoint($id, $point): View
    {
        $records = SurveyUser::query()->where('survey_id', $id)->where('point', $point)->get();

        return view('onboarding-pro::surveyResultUsers', compact(['records']));
    }

    public function bannerUpdate(Request $request, $id): RedirectResponse
    {
        $request->validate([
            'description' => 'required',
            'status'      => 'required',
            'permanent'   => 'required',
        ]);

        $model = Banner::query()->findOrFail($id);
        $model->fill([
            'description'      => $request->get('description'),
            'background_color' => $request->get('background_color_value'),
            'text_color'       => $request->get('text_color_value'),
            'status'           => $request->get('status'),
            'permanent'        => $request->get('permanent'),
        ])->save();

        return redirect()->route('dashboard.admin.onboarding-pro.banners')->with([
            'message' => 'Created Successfully',
            'type'    => 'success',
        ]);
    }

    public function surveyUpdate(Request $request, $id): RedirectResponse
    {
        $request->validate([
            'description' => 'required',
            'status'      => 'required',
        ]);

        $model = Survey::query()->findOrFail($id);
        $model->fill([
            'description'      => $request->get('description'),
            'background_color' => $request->get('background_color_value'),
            'text_color'       => $request->get('text_color_value'),
            'status'           => $request->get('status'),
        ])->save();

        return redirect()->route('dashboard.admin.onboarding-pro.surveys')->with([
            'message' => 'Created Successfully',
            'type'    => 'success',
        ]);
    }

    public function bannerDisplay($bannerId): JsonResponse
    {
        BannerUser::query()->create([
            'banner_id' => $bannerId,
            'user_id'   => auth()->user()->id,
        ]);

        return response()->json([
            'message' => 'Created Successfully',
            'type'    => 'success',
        ]);
    }

    public function surveyDisplay($point, $surveyId): JsonResponse
    {
        SurveyUser::query()->create([
            'point'     => $point,
            'survey_id' => $surveyId,
            'user_id'   => auth()->user()->id,
        ]);

        return response()->json([
            'message' => 'Created Successfully',
            'type'    => 'success',
        ]);
    }
}
