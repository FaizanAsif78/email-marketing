<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class TenantController extends Controller
{
    public function index(Request $request)
    {
        $tenants = Tenant::query()
            ->with('admin')
            ->when($request->filled('search'), fn ($query) => $query
                ->where('company_name', 'like', '%'.$request->string('search').'%'))
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('dashboard.tenants.index', compact('tenants'));
    }

    public function create()
    {
        $plans = Tenant::SUBSCRIPTION_PLANS;
        $statuses = Tenant::STATUSES;

        return view('dashboard.tenants.create', compact('plans', 'statuses'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'company_logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp,svg', 'max:2048'],
            'company_website' => ['nullable', 'url', 'max:255'],
            'timezone' => ['required', 'string', 'max:255'],
            'country' => ['required', 'string', 'max:255'],
            'subscription_plan' => ['required', 'string', 'in:'.implode(',', Tenant::SUBSCRIPTION_PLANS)],
            'status' => ['required', 'string', 'in:'.implode(',', Tenant::STATUSES)],
            'admin_name' => ['required', 'string', 'max:255'],
            'admin_email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'admin_password' => ['required', 'string', 'min:8'],
        ]);

        if ($request->hasFile('company_logo')) {
            $data['company_logo'] = $request->file('company_logo')->store('tenant-logos', 'public');
        }

        $tenant = Tenant::create($data);

        $admin = User::create([
            'name' => $request->string('admin_name'),
            'email' => $request->string('admin_email'),
            'password' => Hash::make($request->string('admin_password')),
            'tenant_id' => $tenant->id,
        ]);
        $admin->assignRole('admin');

        return redirect()->route('tenants.index')->with('success', 'Tenant created successfully.');
    }

    public function edit(Tenant $tenant)
    {
        $plans = Tenant::SUBSCRIPTION_PLANS;
        $statuses = Tenant::STATUSES;

        return view('dashboard.tenants.edit', compact('tenant', 'plans', 'statuses'));
    }

    public function update(Request $request, Tenant $tenant)
    {
        $data = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'company_logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp,svg', 'max:2048'],
            'company_website' => ['nullable', 'url', 'max:255'],
            'timezone' => ['required', 'string', 'max:255'],
            'country' => ['required', 'string', 'max:255'],
            'subscription_plan' => ['required', 'string', 'in:'.implode(',', Tenant::SUBSCRIPTION_PLANS)],
            'status' => ['required', 'string', 'in:'.implode(',', Tenant::STATUSES)],
        ]);

        if ($request->hasFile('company_logo')) {
            if ($tenant->company_logo) {
                Storage::disk('public')->delete($tenant->company_logo);
            }

            $data['company_logo'] = $request->file('company_logo')->store('tenant-logos', 'public');
        }

        $tenant->update($data);

        return redirect()->route('tenants.index')->with('success', 'Tenant updated successfully.');
    }

    public function destroy(Tenant $tenant)
    {
        if ($tenant->company_logo) {
            Storage::disk('public')->delete($tenant->company_logo);
        }

        $tenant->delete();

        return redirect()->route('tenants.index')->with('success', 'Tenant deleted successfully.');
    }
}
