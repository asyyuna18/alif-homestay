@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    @unless (config('core.base.general.skip_license_check'))
        @include('core/setting::partials.license')
    @endunless

    {!! $form->renderForm() !!}
@stop
