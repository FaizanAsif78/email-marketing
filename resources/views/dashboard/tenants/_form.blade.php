@php
    $tenant = $tenant ?? null;
@endphp

@csrf

@unless ($tenant)
    <h6 class="mb-3 text-primary">Tenant Admin</h6>
    <div class="row">
        <div class="mb-3 col-md-4">
            <label for="admin_name" class="form-label">Admin Name <span class="text-danger">*</span></label>
            <input
                type="text"
                class="form-control @error('admin_name') is-invalid @enderror"
                id="admin_name"
                name="admin_name"
                value="{{ old('admin_name') }}"
                placeholder="e.g. Sarah Khan"
            />
            @error('admin_name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3 col-md-4">
            <label for="admin_email" class="form-label">Admin Email <span class="text-danger">*</span></label>
            <input
                type="email"
                class="form-control @error('admin_email') is-invalid @enderror"
                id="admin_email"
                name="admin_email"
                value="{{ old('admin_email') }}"
                placeholder="admin@company.com"
            />
            @error('admin_email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3 col-md-4">
            <label for="admin_password" class="form-label">Admin Password <span class="text-danger">*</span></label>
            <input
                type="password"
                class="form-control @error('admin_password') is-invalid @enderror"
                id="admin_password"
                name="admin_password"
                placeholder="Minimum 8 characters"
            />
            @error('admin_password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <hr class="my-4" />
    <h6 class="mb-3 text-primary">Company Details</h6>
@endunless

<div class="row">
    <div class="mb-3 col-md-6">
        <label for="company_name" class="form-label">Company Name <span class="text-danger">*</span></label>
        <input
            type="text"
            class="form-control @error('company_name') is-invalid @enderror"
            id="company_name"
            name="company_name"
            value="{{ old('company_name', $tenant?->company_name) }}"
            placeholder="e.g. Acme Corp"
            autofocus
        />
        @error('company_name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3 col-md-6">
        <label for="company_website" class="form-label">Company Website</label>
        <input
            type="url"
            class="form-control @error('company_website') is-invalid @enderror"
            id="company_website"
            name="company_website"
            value="{{ old('company_website', $tenant?->company_website) }}"
            placeholder="https://example.com"
        />
        @error('company_website')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3 col-md-6">
        <label for="company_logo" class="form-label">Company Logo</label>
        <input
            type="file"
            class="form-control @error('company_logo') is-invalid @enderror"
            id="company_logo"
            name="company_logo"
            accept="image/png,image/jpeg,image/jpg,image/webp,image/svg+xml"
        />
        @error('company_logo')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        @if ($tenant?->company_logo)
            <div class="mt-2">
                <img
                    src="{{ Storage::url($tenant->company_logo) }}"
                    alt="{{ $tenant->company_name }}"
                    class="rounded"
                    height="60"
                    width="60"
                />
            </div>
        @endif
        <div class="form-text">Allowed PNG, JPG, JPEG, WEBP, SVG. Max 2MB.</div>
    </div>

    <div class="mb-3 col-md-6">
        <label for="timezone" class="form-label">Timezone <span class="text-danger">*</span></label>
        <input
            type="text"
            class="form-control @error('timezone') is-invalid @enderror"
            id="timezone"
            name="timezone"
            value="{{ old('timezone', $tenant?->timezone) }}"
            placeholder="e.g. America/New_York"
            list="timezone-options"
        />
        <datalist id="timezone-options">
            @foreach (['UTC', 'America/New_York', 'America/Chicago', 'America/Denver', 'America/Los_Angeles', 'Europe/London', 'Europe/Paris', 'Europe/Berlin', 'Asia/Karachi', 'Asia/Dubai', 'Asia/Kolkata', 'Asia/Singapore', 'Asia/Tokyo', 'Australia/Sydney'] as $tz)
                <option value="{{ $tz }}"></option>
            @endforeach
        </datalist>
        @error('timezone')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3 col-md-6">
        <label for="country" class="form-label">Country <span class="text-danger">*</span></label>
        <input
            type="text"
            class="form-control @error('country') is-invalid @enderror"
            id="country"
            name="country"
            value="{{ old('country', $tenant?->country) }}"
            placeholder="e.g. United States"
            list="country-options"
        />
        <datalist id="country-options">
            @foreach (['United States', 'United Kingdom', 'Canada', 'Pakistan', 'India', 'UAE', 'Germany', 'France', 'Australia', 'Singapore'] as $country)
                <option value="{{ $country }}"></option>
            @endforeach
        </datalist>
        @error('country')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3 col-md-6">
        <label for="subscription_plan" class="form-label">Subscription Plan <span class="text-danger">*</span></label>
        <select
            class="form-select @error('subscription_plan') is-invalid @enderror"
            id="subscription_plan"
            name="subscription_plan"
        >
            <option value="">Select a plan</option>
            @foreach ($plans as $plan)
                <option value="{{ $plan }}" @selected(old('subscription_plan', $tenant?->subscription_plan) === $plan)>
                    {{ ucfirst($plan) }}
                </option>
            @endforeach
        </select>
        @error('subscription_plan')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3 col-md-6">
        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
        <select class="form-select @error('status') is-invalid @enderror" id="status" name="status">
            <option value="">Select a status</option>
            @foreach ($statuses as $status)
                <option value="{{ $status }}" @selected(old('status', $tenant?->status) === $status)>
                    {{ ucfirst($status) }}
                </option>
            @endforeach
        </select>
        @error('status')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="mt-2">
    <button type="submit" class="btn btn-primary me-2">
        {{ $tenant ? 'Update Tenant' : 'Create Tenant' }}
    </button>
    <a href="{{ route('tenants.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>