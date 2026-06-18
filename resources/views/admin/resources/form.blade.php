@extends('admin.layout')
@section('content')
@php($fields=\App\AdminHub\ResourceSchema::fields($schema,$mode==='create'?'create':'update')) @php($label=$schema['label'] ?? \Illuminate\Support\Str::headline($resourceKey))
<section class="rounded bg-white p-6 shadow"><p class="text-sm text-slate-500"><a class="hover:underline" href="{{ route('admin.product.resources.table', [$productKey,$resourceKey]) }}">{{ $label }}</a> / {{ $mode }}</p><h1 class="text-2xl font-bold">{{ $mode === 'create' ? 'Create' : 'Edit' }} {{ $label }}</h1>
@if($errors->any())<div class="mt-4 rounded border border-red-200 bg-red-50 p-3 text-red-700">{{ $errors->first() }}</div>@endif
<form method="POST" action="{{ $mode === 'create' ? route('admin.product.resources.store', [$productKey,$resourceKey]) : route('admin.product.resources.update', [$productKey,$resourceKey,$id]) }}" class="mt-6 space-y-4">@csrf @if($mode==='edit')@method('PATCH')@endif
@forelse($fields as $field)@include('admin.components.form-field',['field'=>$field,'value'=>\App\AdminHub\ResourceSchema::value($item,$field)])@empty<div class="rounded bg-slate-50 p-4 text-slate-600">No editable fields are available for this resource.</div>@endforelse
<div class="flex gap-2"><button class="rounded bg-blue-600 px-4 py-2 text-white">{{ $mode === 'create' ? 'Create' : 'Save Changes' }}</button><a class="rounded bg-slate-100 px-4 py-2" href="{{ $mode === 'edit' ? route('admin.product.resources.show', [$productKey,$resourceKey,$id]) : route('admin.product.resources.table', [$productKey,$resourceKey]) }}">Cancel</a></div></form></section>
@endsection
