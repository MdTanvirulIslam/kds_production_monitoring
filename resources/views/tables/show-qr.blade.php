@extends('layouts.app')

@section('title', 'QR Code - ' . $table->table_number)

@section('content')
    <div class="layout-px-spacing">
        <div class="page-header">
            <h3>QR Code - {{ $table->table_number }}</h3>
            <div class="page-actions">
                <a href="{{ route('tables.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="widget">
                    <div class="widget-content text-center">
                        <h4>{{ $table->table_name }}</h4>
                        <p class="text-muted">{{ $table->table_number }}</p>

                        <div class="qr-code-display my-4">
                            {!! QrCode::size(300)->generate($table->qr_code) !!}
                        </div>

                        <div class="alert alert-info">
                            <strong>QR Code Content:</strong><br>
                            {{ $table->qr_code }}
                        </div>

                        <div class="btn-group mt-3">
                            <a href="{{ route('tables.qr.download', $table) }}" class="btn btn-primary">
                                <i class="fas fa-download"></i> Download PNG
                            </a>
                            <a href="{{ route('tables.qr.download-pdf', $table) }}" class="btn btn-success">
                                <i class="fas fa-file-pdf"></i> Download PDF
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
