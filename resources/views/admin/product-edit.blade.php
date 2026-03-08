@extends('layouts.admin')

@section('title', 'Ürünü Düzenle')
@section('page-title', 'Ürünü Düzenle')

@section('content')
    <livewire:product-create-form :product-id="$product->uuid" />
@endsection
