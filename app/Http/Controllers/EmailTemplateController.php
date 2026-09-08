<?php

namespace App\Http\Controllers;

use App\Models\EmailTemplate;
use Illuminate\Http\Request;

class EmailTemplateController extends Controller
{
    public function index(Request $request)
    {
        $templates = EmailTemplate::where('user_id', auth()->id())
            ->when($request->filled('search'), fn ($query) => $query->where(fn ($query) => $query
                ->where('name', 'like', '%'.$request->string('search').'%')
                ->orWhere('subject', 'like', '%'.$request->string('search').'%')))
            ->orderByDesc('id')
            ->paginate(9)
            ->withQueryString();

        return view('dashboard.email-templates.index', compact('templates'));
    }

    public function create()
    {
        return view('dashboard.email-templates.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'subject' => ['nullable', 'string', 'max:255'],
            'content' => ['required', 'string'],
        ]);

        EmailTemplate::create([
            'user_id' => auth()->id(),
            'name' => $data['name'],
            'subject' => $data['subject'],
            'content' => $data['content'],
        ]);

        return redirect()->route('email-templates.index')
            ->with('success', 'Email template created successfully.');
    }

    public function edit(int $emailTemplate)
    {
        $template = $this->findScoped($emailTemplate);

        return view('dashboard.email-templates.edit', compact('template'));
    }

    public function update(Request $request, int $emailTemplate)
    {
        $template = $this->findScoped($emailTemplate);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'subject' => ['nullable', 'string', 'max:255'],
            'content' => ['required', 'string'],
        ]);

        $template->update($data);

        return redirect()->route('email-templates.index')
            ->with('success', 'Email template updated successfully.');
    }

    public function destroy(int $emailTemplate)
    {
        $this->findScoped($emailTemplate)->delete();

        return redirect()->route('email-templates.index')
            ->with('success', 'Email template deleted successfully.');
    }

    protected function findScoped(int $id): EmailTemplate
    {
        return EmailTemplate::where('user_id', auth()->id())->findOrFail($id);
    }
}
