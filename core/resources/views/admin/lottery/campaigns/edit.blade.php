@extends('admin.layouts.app')
@section('panel')
    @include('admin.lottery.campaigns.form')
@endsection

@push('breadcrumb-plugins')
    <x-back route="{{ route('admin.lottery.campaigns') }}" />
@endpush
