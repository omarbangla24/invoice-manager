<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\ClientProfile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use App\Services\AuditLogger;

class ClientController extends Controller
{
    public function index(Request $request): View
    {
        $perPage = min((int) $request->integer('per_page', 15), 100);

        return view('admin.clients.index', [
            'clients' => ClientProfile::with('user')
                ->withCount('invoices')
                ->when($request->filled('q'), function ($query) use ($request): void {
                    $q = $request->string('q');
                    $query->where(function ($query) use ($q): void {
                        $query->where('business_name', 'like', "%{$q}%")
                            ->orWhere('contact_name', 'like', "%{$q}%")
                            ->orWhere('webmail_address', 'like', "%{$q}%")
                            ->orWhereHas('user', fn ($query) => $query->where('email', 'like', "%{$q}%"));
                    });
                })
                ->latest()
                ->paginate($perPage)
                ->withQueryString(),
        ]);
    }

    public function create(): View
    {
        return view('admin.clients.create');
    }

    public function store(Request $request, AuditLogger $audit): RedirectResponse
    {
        $validated = $request->validate([
            'business_name' => ['required', 'string', 'max:255'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'phone' => ['nullable', 'string', 'max:255'],
            'tax_identifier' => ['nullable', 'string', 'max:255'],
            'webmail_address' => ['nullable', 'email', 'max:255', 'unique:client_profiles,webmail_address'],
            'details' => ['nullable', 'string'],
        ]);

        $client = DB::transaction(function () use ($validated): ClientProfile {
            $user = User::create([
                'name' => $validated['contact_name'] ?: $validated['business_name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => UserRole::Client,
            ]);

            return ClientProfile::create([
                'user_id' => $user->id,
                'business_name' => $validated['business_name'],
                'contact_name' => $validated['contact_name'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'tax_identifier' => $validated['tax_identifier'] ?? null,
                'webmail_address' => $validated['webmail_address'] ?? null,
                'details' => $validated['details'] ?? null,
                'storage_folder' => Str::slug($validated['business_name']).'-'.Str::lower(Str::random(6)),
            ]);
        });

        $audit->log('client.created', $client);

        return redirect()->route('admin.clients.show', $client)->with('status', 'Client profile created.');
    }

    public function show(ClientProfile $client): View
    {
        $request = request();
        $client->load('user');

        return view('admin.clients.show', [
            'client' => $client,
            'invoices' => $client->invoices()
                ->when($request->filled('q'), fn ($query) => $query->where('title', 'like', '%'.$request->string('q').'%'))
                ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
                ->latest()
                ->paginate(min((int) $request->integer('per_page', 15), 100))
                ->withQueryString(),
        ]);
    }

    public function edit(ClientProfile $client): View
    {
        return view('admin.clients.edit', ['client' => $client->load('user')]);
    }

    public function update(Request $request, ClientProfile $client, AuditLogger $audit): RedirectResponse
    {
        $validated = $request->validate([
            'business_name' => ['required', 'string', 'max:255'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($client->user_id)],
            'password' => ['nullable', 'string', 'min:8'],
            'phone' => ['nullable', 'string', 'max:255'],
            'tax_identifier' => ['nullable', 'string', 'max:255'],
            'webmail_address' => ['nullable', 'email', 'max:255', Rule::unique('client_profiles')->ignore($client->id)],
            'details' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($client, $validated): void {
            $client->user->forceFill([
                'name' => $validated['contact_name'] ?: $validated['business_name'],
                'email' => $validated['email'],
            ]);

            if (! empty($validated['password'])) {
                $client->user->password = Hash::make($validated['password']);
            }

            $client->user->save();
            $client->update([
                'business_name' => $validated['business_name'],
                'contact_name' => $validated['contact_name'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'tax_identifier' => $validated['tax_identifier'] ?? null,
                'webmail_address' => $validated['webmail_address'] ?? null,
                'details' => $validated['details'] ?? null,
            ]);
        });

        $audit->log('client.updated', $client);

        return redirect()->route('admin.clients.show', $client)->with('status', 'Client profile updated.');
    }

    public function destroy(ClientProfile $client, AuditLogger $audit): RedirectResponse
    {
        abort_if($client->invoices()->exists(), 422, 'Client has invoices. Delete blocked to protect accounting records.');

        $audit->log('client.deleted', $client, ['email' => $client->user->email]);
        $client->user->delete();

        return redirect()->route('admin.clients.index')->with('status', 'Client deleted.');
    }
}
