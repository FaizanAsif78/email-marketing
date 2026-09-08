<?php

namespace App\Http\Controllers;

use App\Models\MailConfiguration;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MailConfigurationController extends Controller
{
    public function index(Request $request)
    {
        $configurations = MailConfiguration::where('tenant_id', auth()->user()->tenant_id)
            ->when($request->filled('search'), fn ($query) => $query->where(fn ($query) => $query
                ->where('smtp_host', 'like', '%'.$request->string('search').'%')
                ->orWhere('username', 'like', '%'.$request->string('search').'%')
                ->orWhere('from_email', 'like', '%'.$request->string('search').'%')))
            ->orderByDesc('is_default')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('dashboard.mail-configurations.index', compact('configurations'));
    }

    public function create()
    {
        return view('dashboard.mail-configurations.create');
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request, null);

        $configuration = MailConfiguration::create([
            'tenant_id' => auth()->user()->tenant_id,
            'smtp_host' => $data['smtp_host'],
            'smtp_port' => $data['smtp_port'],
            'username' => $data['username'],
            'password' => $data['password'],
            'encryption' => $data['encryption'],
            'from_name' => $data['from_name'],
            'from_email' => $data['from_email'],
            'reply_to_email' => $data['reply_to_email'] ?? null,
            'is_default' => $data['is_default'],
        ]);

        if ($configuration->is_default) {
            $this->clearOtherDefaults($configuration);
        }

        return redirect()->route('mail-configurations.index')
            ->with('success', 'Mail configuration created successfully.');
    }

    public function edit(int $mailConfiguration)
    {
        $configuration = $this->findScoped($mailConfiguration);

        return view('dashboard.mail-configurations.edit', compact('configuration'));
    }

    public function update(Request $request, int $mailConfiguration)
    {
        $configuration = $this->findScoped($mailConfiguration);
        $data = $this->validatedData($request, $configuration);

        $attributes = [
            'smtp_host' => $data['smtp_host'],
            'smtp_port' => $data['smtp_port'],
            'username' => $data['username'],
            'encryption' => $data['encryption'],
            'from_name' => $data['from_name'],
            'from_email' => $data['from_email'],
            'reply_to_email' => $data['reply_to_email'] ?? null,
            'is_default' => $data['is_default'],
        ];

        if (! empty($data['password'])) {
            $attributes['password'] = $data['password'];
        }

        $configuration->update($attributes);

        if ($configuration->is_default) {
            $this->clearOtherDefaults($configuration);
        }

        return redirect()->route('mail-configurations.index')
            ->with('success', 'Mail configuration updated successfully.');
    }

    public function destroy(int $mailConfiguration)
    {
        $configuration = $this->findScoped($mailConfiguration);
        $configuration->delete();

        return redirect()->route('mail-configurations.index')
            ->with('success', 'Mail configuration deleted successfully.');
    }

    protected function validatedData(Request $request, ?MailConfiguration $configuration): array
    {
        $data = $request->validate([
            'smtp_host' => ['required', 'string', 'max:255'],
            'smtp_port' => ['required', 'integer', 'between:1,65535'],
            'username' => ['required', 'string', 'max:255'],
            'password' => [$configuration ? 'nullable' : 'required', 'string', 'max:255'],
            'encryption' => ['required', Rule::in(MailConfiguration::ENCRYPTIONS)],
            'from_name' => ['required', 'string', 'max:255'],
            'from_email' => ['required', 'email', 'max:255'],
            'reply_to_email' => ['nullable', 'email', 'max:255'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        $data['is_default'] = (bool) ($data['is_default'] ?? false);

        return $data;
    }

    protected function clearOtherDefaults(MailConfiguration $configuration): void
    {
        MailConfiguration::where('tenant_id', $configuration->tenant_id)
            ->where('id', '!=', $configuration->id)
            ->update(['is_default' => false]);
    }

    protected function findScoped(int $id): MailConfiguration
    {
        return MailConfiguration::where('tenant_id', auth()->user()->tenant_id)
            ->findOrFail($id);
    }
}
