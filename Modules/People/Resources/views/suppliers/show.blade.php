@extends('layouts.app')

@section('title', 'Detail Supplier')

@section('breadcrumb')
    <ol class="breadcrumb border-0 m-0">
        <li class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></li>
        <li class="breadcrumb-item"><a href="{{ route('suppliers.index') }}">Supplier</a></li>
        <li class="breadcrumb-item active">Detail</li>
    </ol>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <tr>
                                    <th>Nama Supplier</th>
                                    <td>{{ $supplier->supplier_name }}</td>
                                </tr>
                                <tr>
                                    <th>Email Supplier</th>
                                    <td>{{ $supplier->supplier_email }}</td>
                                </tr>
                                <tr>
                                    <th>No. Telepon</th>
                                    <td>{{ $supplier->supplier_phone }}</td>
                                </tr>
                                <tr>
                                    <th>Kota</th>
                                    <td>{{ $supplier->city }}</td>
                                </tr>
                                <tr>
                                    <th>Negara</th>
                                    <td>{{ $supplier->country }}</td>
                                </tr>
                                <tr>
                                    <th>Alamat</th>
                                    <td>{{ $supplier->address }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
