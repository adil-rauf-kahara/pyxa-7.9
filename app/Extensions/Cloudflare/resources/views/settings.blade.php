@extends('panel.layout.app')
@section('title', __('Cloudflare R2 Settings'))
@section('additional_css')
@endsection

@section('content')
    <!-- Page body -->
    <div class="page-body pt-6">
        <div class="container-xl">
            <div class="row">
                <div class="col-md-5 mx-auto">
                    <form
                        action="{{ route('dashboard.admin.settings.cloudflare-r2') }}"
                        method="post"
                    >
                        @csrf
                        <h3 class="mb-[25px] text-[20px]">{{ __('Cloudflare R2 Settings') }}</h3>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">{{__('ACCESS KEY ID')}}</label>
                                    <input type="text" class="form-control @error('CLOUDFLARE_R2_ACCESS_KEY_ID') border-red-400 @enderror" value="{{ config('filesystems.disks.r2.key') }}" id="CLOUDFLARE_R2_ACCESS_KEY_ID" name="CLOUDFLARE_R2_ACCESS_KEY_ID">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">{{__('SECRET ACCESS KEY')}}</label>
                                    <input type="text" class="form-control @error('CLOUDFLARE_R2_SECRET_ACCESS_KEY') border-red-400 @enderror" value="{{ config('filesystems.disks.r2.secret') }}" id="CLOUDFLARE_R2_SECRET_ACCESS_KEY" name="CLOUDFLARE_R2_SECRET_ACCESS_KEY">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">{{__('DEFAULT REGION')}}</label>
                                    <input type="text" class="form-control @error('CLOUDFLARE_R2_DEFAULT_REGION') border-red-400 @enderror" value="{{ config('filesystems.disks.r2.region') }}" id="CLOUDFLARE_R2_DEFAULT_REGION" name="CLOUDFLARE_R2_DEFAULT_REGION">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">{{__('BUCKET')}}</label>
                                    <input type="text" class="form-control @error('CLOUDFLARE_R2_BUCKET') border-red-400 @enderror" value="{{ config('file systems.disks.r2.bucket') }}" id="CLOUDFLARE_R2_BUCKET" name="CLOUDFLARE_R2_BUCKET">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">{{__('ENDPOINT')}}</label>
                                    <input type="text" class="form-control  @error('CLOUDFLARE_R2_ENDPOINT') border-red-400 @enderror" value="{{ config('filesystems.disks.r2.endpoint') }}" id="CLOUDFLARE_R2_ENDPOINT" name="CLOUDFLARE_R2_ENDPOINT">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">{{__('DOMAIN URL')}}</label>
                                    <input type="text" class="form-control  @error('CLOUDFLARE_R2_URL') border-red-400 @enderror" value="{{ config('filesystems.disks.r2.url') }}" id="CLOUDFLARE_R2_URL" name="CLOUDFLARE_R2_URL">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <button
                                    type="submit"
                                    class="btn btn-primary w-100 btn-block w-100">
                                    {{__('Save')}}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
