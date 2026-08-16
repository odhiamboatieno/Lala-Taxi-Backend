@extends('admin.template')

@section('main')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <div>
                    <h4 class="mb-1">{{ $pageTitle ?? 'Modules' }}</h4>
                    <p class="text-muted mb-0">{{ $pageSubtitle ?? 'Install, enable and manage WiTaxi add-on modules.' }}</p>
                </div>
                <div class="page-title-right">
                    <a href="{{ route('admin.modules.create') }}" class="btn btn-primary">
                        <i class="bi bi-cloud-upload"></i> Upload module
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row">
        @forelse($modules as $module)
            @php
                $installed = (bool) ($module['_installed'] ?? false);
                $enabled = (bool) ($module['_enabled'] ?? false);
                $record = $module['_record'] ?? null;
                $providers = $module['providers'] ?? [];
            @endphp
            <div class="col-md-6 col-xl-4">
                <div class="card module-card {{ $enabled ? 'is-enabled' : 'is-disabled' }}">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div>
                                <h5 class="card-title mb-1">
                                    {{ $module['name'] }}
                                    <span class="badge bg-light text-muted ms-1">v{{ $module['version'] ?? '1.0.0' }}</span>
                                </h5>
                                <p class="text-muted small mb-2">{{ $module['description'] ?? 'No description provided.' }}</p>
                                @if(!empty($module['author']))
                                    <p class="small text-muted mb-0">by <strong>{{ $module['author'] }}</strong></p>
                                @endif
                            </div>
                            <div class="module-status">
                                @if(! $installed)
                                    <span class="badge bg-secondary">Not installed</span>
                                @elseif($enabled)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-warning text-dark">Installed · off</span>
                                @endif
                            </div>
                        </div>

                        @if(!empty($providers))
                            <div class="mt-2">
                                <span class="text-muted small">Providers:</span>
                                @foreach($providers as $p)
                                    <code class="small d-inline-block me-1">{{ class_basename($p) }}</code>
                                @endforeach
                            </div>
                        @endif

                        <div class="mt-3 d-flex flex-wrap gap-2">
                            @if($installed)
                                @if($enabled)
                                    <form method="POST" action="{{ route('admin.modules.disable', $record) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-warning">
                                            <i class="bi bi-toggle-off"></i> Disable
                                        </button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('admin.modules.enable', $record) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class="bi bi-toggle-on"></i> Enable
                                        </button>
                                    </form>
                                @endif

                                @if($record && $record->has_migrations && ! $record->migrated)
                                    <form method="POST" action="{{ route('admin.modules.migrate', $record) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-database"></i> Run migrations
                                        </button>
                                    </form>
                                @endif

                                <form method="POST" action="{{ route('admin.modules.destroy', $record) }}" onsubmit="return confirm('Uninstall this module? Files will be removed.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i> Uninstall
                                    </button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('admin.modules.rediscover') }}">
                                    @csrf
                                    <input type="hidden" name="name" value="{{ $module['name'] }}">
                                    <button type="submit" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-arrow-repeat"></i> Register on disk
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-box-seam display-3 text-muted"></i>
                        <h5 class="mt-3">No modules found</h5>
                        <p class="text-muted">Drop a module zip into the Modules folder or upload one above to get started.</p>
                        <a href="{{ route('admin.modules.create') }}" class="btn btn-primary mt-2">
                            <i class="bi bi-cloud-upload"></i> Upload first module
                        </a>
                    </div>
                </div>
            </div>
        @endforelse
    </div>
</div>

<style>
.module-card { border-radius: 14px; transition: transform .15s ease, box-shadow .15s ease; }
.module-card:hover { transform: translateY(-2px); box-shadow: 0 18px 30px rgba(15,23,42,.08); }
.module-card.is-enabled { border-left: 4px solid #10b981; }
.module-card.is-disabled { border-left: 4px solid #f59e0b; }
</style>
@endsection
