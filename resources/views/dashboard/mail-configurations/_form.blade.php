@php
    $configuration = $configuration ?? null;
@endphp

@csrf

<div class="row">
    <div class="mb-3 col-md-6">
        <label for="smtp_host" class="form-label">SMTP Host <span class="text-danger">*</span></label>
        <input
            type="text"
            class="form-control @error('smtp_host') is-invalid @enderror"
            id="smtp_host"
            name="smtp_host"
            value="{{ old('smtp_host', $configuration?->smtp_host) }}"
            placeholder="e.g. smtp.gmail.com"
            autofocus
        />
        @error('smtp_host')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3 col-md-6">
        <label for="smtp_port" class="form-label">SMTP Port <span class="text-danger">*</span></label>
        <input
            type="number"
            min="1"
            max="65535"
            class="form-control @error('smtp_port') is-invalid @enderror"
            id="smtp_port"
            name="smtp_port"
            value="{{ old('smtp_port', $configuration?->smtp_port) }}"
            placeholder="e.g. 587"
        />
        @error('smtp_port')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3 col-md-6">
        <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
        <input
            type="text"
            class="form-control @error('username') is-invalid @enderror"
            id="username"
            name="username"
            value="{{ old('username', $configuration?->username) }}"
            placeholder="e.g. noreply@yourdomain.com"
        />
        @error('username')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3 col-md-6">
        <label for="password" class="form-label">
            Password {{ $configuration ? '(leave blank to keep current)' : '' }} <span class="text-danger">*</span>
        </label>
        <input
            type="password"
            class="form-control @error('password') is-invalid @enderror"
            id="password"
            name="password"
            placeholder="{{ $configuration ? '••••••••' : 'SMTP password' }}"
        />
        @error('password')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3 col-md-6">
        <label for="encryption" class="form-label">Encryption <span class="text-danger">*</span></label>
        <select class="form-select @error('encryption') is-invalid @enderror" id="encryption" name="encryption">
            <option value="">Select encryption</option>
            @foreach (\App\Models\MailConfiguration::ENCRYPTIONS as $encryption)
                <option value="{{ $encryption }}" @selected(old('encryption', $configuration?->encryption) === $encryption)>
                    {{ strtoupper($encryption) }}
                </option>
            @endforeach
        </select>
        @error('encryption')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3 col-md-6">
        <label for="from_name" class="form-label">From Name <span class="text-danger">*</span></label>
        <input
            type="text"
            class="form-control @error('from_name') is-invalid @enderror"
            id="from_name"
            name="from_name"
            value="{{ old('from_name', $configuration?->from_name) }}"
            placeholder="e.g. Acme Support"
        />
        @error('from_name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3 col-md-6">
        <label for="from_email" class="form-label">From Email <span class="text-danger">*</span></label>
        <input
            type="email"
            class="form-control @error('from_email') is-invalid @enderror"
            id="from_email"
            name="from_email"
            value="{{ old('from_email', $configuration?->from_email) }}"
            placeholder="noreply@yourdomain.com"
        />
        @error('from_email')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3 col-md-6">
        <label for="reply_to_email" class="form-label">Reply-To Email</label>
        <input
            type="email"
            class="form-control @error('reply_to_email') is-invalid @enderror"
            id="reply_to_email"
            name="reply_to_email"
            value="{{ old('reply_to_email', $configuration?->reply_to_email) }}"
            placeholder="support@yourdomain.com"
        />
        @error('reply_to_email')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3 col-md-6 d-flex align-items-center">
        <div class="form-check">
            <input
                type="checkbox"
                class="form-check-input @error('is_default') is-invalid @enderror"
                id="is_default"
                name="is_default"
                value="1"
                @checked(old('is_default', $configuration?->is_default))
            />
            <label class="form-check-label" for="is_default">Set as default mail configuration</label>
            <div class="form-text">The default configuration is used when sending emails. Only one default is allowed per tenant.</div>
            @error('is_default')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="mt-2">
    <button type="submit" class="btn btn-primary me-2">
        {{ $configuration ? 'Update Mail Configuration' : 'Create Mail Configuration' }}
    </button>
    <a href="{{ route('mail-configurations.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>