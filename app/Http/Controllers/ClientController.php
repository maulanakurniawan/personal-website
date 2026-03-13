<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClientController extends Controller
{
    public function index(Request $request): View
    {
        return view('clients.index', [
            'clients' => $request->user()->clients()->withCount('projects')->latest()->paginate(25),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:255'], 'notes' => ['nullable', 'string']]);
        $client = $request->user()->clients()->create($data);

        return back()
            ->with('success', 'Client added.')
            ->with('new_client_id', $client->id);
    }

    public function update(Request $request, int $clientId): RedirectResponse
    {
        $client = $request->user()->clients()->findOrFail($clientId);
        $client->update($request->validate(['name' => ['required', 'string', 'max:255'], 'notes' => ['nullable', 'string']]));

        return back()->with('success', 'Client updated.');
    }

    public function destroy(Request $request, int $clientId): RedirectResponse
    {
        $client = $request->user()->clients()->with('projects')->findOrFail($clientId);
        $client->projects()->delete();
        $client->delete();

        return back()->with('success', 'Client deleted.');
    }
}
