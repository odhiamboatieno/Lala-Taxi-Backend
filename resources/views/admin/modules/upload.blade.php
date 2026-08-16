@extends('admin.template')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <div>
                    <h4 class="mb-1">{{ $pageTitle ?? 'Upload module' }}</h4>
                    <p class="text-muted mb-0">{{ $pageSubtitle ?? 'Upload a module zip and install it into LalaTaxi.' }}</p>
                </div>
                <div class="page-title-right">
                    <a href="{{ route('admin.modules.index') }}" class="btn btn-soft-secondary">
                        <i class="bi bi-arrow-left"></i> Back to modules
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <form method="POST" action="{{ route('admin.modules.store') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-4">
                            <label class="form-label">Module package (.zip)</label>
                            <div class="upload-drop" id="module-drop">
                                <input type="file" name="module" id="module-input" accept=".zip,application/zip" required>
                                <div class="upload-drop__icon"><i class="bi bi-cloud-arrow-up"></i></div>
                                <div class="upload-drop__title">Drop a module zip here</div>
                                <div class="upload-drop__hint">or click to browse · max 50 MB</div>
                                <div class="upload-drop__filename" id="module-filename"></div>
                            </div>
                            @error('module') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" name="enable" id="enable" value="1" checked>
                            <label class="form-check-label" for="enable">Enable immediately after install</label>
                        </div>

                        <div class="form-check form-switch mb-4">
                            <input class="form-check-input" type="checkbox" name="run_migrations" id="run_migrations" value="1" checked>
                            <label class="form-check-label" for="run_migrations">Run any migrations shipped in this module</label>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-rocket-takeoff"></i> Install module
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-info-circle"></i> Module package format</h5>
                    <p class="text-muted small">A valid module zip must include a <code>module.json</code> at the root or inside a top-level folder.</p>

<pre class="mb-3" style="background:var(--color-as-0f172a);color:var(--color-as-e2e8f0);padding:14px;border-radius:10px;font-size:12px;">
MyModule.zip
└── MyModule/
    ├── module.json
    ├── Providers/
    │   └── MyModuleServiceProvider.php
    ├── Http/Controllers/...
    ├── Database/Migrations/...
    ├── Helpers/helpers.php
    └── Resources/views/...
</pre>

                    <p class="small text-muted">Example <code>module.json</code>:</p>
<pre style="background:var(--color-as-0f172a);color:var(--color-as-e2e8f0);padding:14px;border-radius:10px;font-size:12px;">
{
  "name": "MyModule",
  "alias": "mymodule",
  "version": "1.0.0",
  "description": "What this module does",
  "author": "Your Name",
  "providers": [
    "Modules\\MyModule\\Providers\\MyModuleServiceProvider"
  ]
}
</pre>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.upload-drop {
    position: relative;
    border: 2px dashed var(--color-as-cbd5e1);
    border-radius: 16px;
    padding: 36px 20px;
    text-align: center;
    transition: border-color .15s ease, background .15s ease;
    background: var(--color-as-f8fafc);
    cursor: pointer;
}
.upload-drop.is-dragover { border-color: var(--color-as-214ba2); background: var(--color-as-eef3ff); }
.upload-drop input[type=file] {
    position: absolute; inset: 0; opacity: 0; cursor: pointer;
}
.upload-drop__icon { font-size: 36px; color: var(--color-as-214ba2); }
.upload-drop__title { font-weight: 600; margin-top: 8px; color: var(--color-as-0f172a); }
.upload-drop__hint { color: var(--color-as-64748b); font-size: 13px; }
.upload-drop__filename { color: var(--color-as-214ba2); font-weight: 600; margin-top: 10px; }
</style>

<script>
(function () {
    var drop = document.getElementById('module-drop');
    var input = document.getElementById('module-input');
    var name = document.getElementById('module-filename');
    if (!drop || !input) return;

    ['dragenter','dragover'].forEach(function (e) {
        drop.addEventListener(e, function (ev) { ev.preventDefault(); drop.classList.add('is-dragover'); });
    });
    ['dragleave','drop'].forEach(function (e) {
        drop.addEventListener(e, function (ev) { ev.preventDefault(); drop.classList.remove('is-dragover'); });
    });
    input.addEventListener('change', function () {
        if (input.files && input.files[0]) {
            name.textContent = input.files[0].name + ' · ' + Math.round(input.files[0].size / 1024) + ' KB';
        }
    });
})();
</script>
@endsection
