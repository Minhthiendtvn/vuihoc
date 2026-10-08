@if (session('success'))
    <div class="vh-alert vh-alert-success">{{ session('success') }}</div>
@endif

@if (session('error'))
    <div class="vh-alert vh-alert-error">{{ session('error') }}</div>
@endif

@if (session('info'))
    <div class="vh-alert vh-alert-info">{{ session('info') }}</div>
@endif

@if ($errors->any())
    <div class="vh-alert vh-alert-error">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
