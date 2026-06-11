@extends('layouts.app')

@section('title', 'Laporan Item Produk')

@section('breadcrumb')
<ol class="breadcrumb border-0 m-0">
    <li class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></li>
    <li class="breadcrumb-item active">Laporan Produk</li>
</ol>
@endsection

@section('content')
<div class="container-fluid">
    <livewire:reports.product-report />
</div>
@endsection