@php use App\Domains\Entity\Enums\EntityEnum; @endphp
@extends('panel.layout.settings', ['layout' => 'wide'])
@section('title', __('Survey Results'))
@section('titlebar_actions', '')
@section('additional_css')
    <link
            href="{{ custom_theme_url('/assets/libs/select2/select2.min.css') }}"
            rel="stylesheet"
    />
@endsection

@section('content')
    <div class="py-10">
        <x-table class="table">
            <x-slot:head>
                <tr>
                    <th>
                        {{ __('Points') }}
                    </th>
                    <th>
                        {{ __('Total Count') }}
                    </th>
                    <th>
                        {{ __('Users') }}
                    </th>
                </tr>
            </x-slot:head>

            <x-slot:body
                    class="table-tbody align-middle text-heading-foreground"
            >
                @foreach([1, 2, 3, 4, 5] as $point)
                    @php
                        $result = $surveyResults->firstWhere('point', $point);
						$totalVotes = $result ? $result->total : 0;
                    @endphp
                    <tr>
                        <td>{{ $point }}</td>
                        <td>{{ $totalVotes }}</td>
						<td>
							<x-button
								class="size-9"
								hover-variant="primary"
								size="none"
								variant="ghost-shadow"
								href="{{ route('dashboard.admin.onboarding-pro.survey.result.point', ['id' => $id, 'point' => $point]) }}"
								title="{{ __('Users') }}"
							>
								<x-tabler-pencil-minus class="size-4" />
							</x-button>
						</td>
					</tr>
                @endforeach
            </x-slot:body>
        </x-table>
    </div>
@endsection

@push('script')

    <script src="{{ custom_theme_url('/assets/js/panel/settings.js') }}"></script>
    <script src="{{ custom_theme_url('/assets/libs/select2/select2.min.js') }}"></script>
@endpush
