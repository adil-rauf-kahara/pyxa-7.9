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
						{{ __('Name') }}
					</th>
					<th>
						{{ __('Email') }}
					</th>
				</tr>
			</x-slot:head>

			<x-slot:body
				class="table-tbody align-middle text-heading-foreground"
			>
				@if($records->count() > 0)
					@foreach($records as $record)
						<tr>
							<td>{{ $record->user->fullname() }}</td>
							<td>{{ $record->user->email }}</td>
						</tr>
					@endforeach
				@endif
			</x-slot:body>
		</x-table>
	</div>
@endsection

@push('script')

	<script src="{{ custom_theme_url('/assets/js/panel/settings.js') }}"></script>
	<script src="{{ custom_theme_url('/assets/libs/select2/select2.min.js') }}"></script>
@endpush
