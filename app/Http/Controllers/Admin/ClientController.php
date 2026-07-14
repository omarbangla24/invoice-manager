<?php

namespace App\Http\Controllers\Admin;

use App\Enums\InvoiceStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\ClientProfile;
use App\Models\Invoice;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

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
                ->when($request->filled('q'), function ($query) use ($request): void {
                    $q = $request->string('q');
                    $query->where(function ($query) use ($q): void {
                        $query->where('original_filename', 'like', "%{$q}%")
                            ->orWhere('description', 'like', "%{$q}%")
                            ->orWhere('supplier_name', 'like', "%{$q}%")
                            ->orWhere('abn', 'like', "%{$q}%");
                    });
                })
                ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
                ->when($request->filled('source'), fn ($query) => $query->where('source', $request->string('source')))
                ->when($request->filled('date_from'), fn ($query) => $query->whereDate('created_at', '>=', $request->date('date_from')))
                ->when($request->filled('date_to'), fn ($query) => $query->whereDate('created_at', '<=', $request->date('date_to')))
                ->when($request->filled('supplier'), fn ($query) => $query->where('supplier_name', 'like', '%'.$request->string('supplier').'%'))
                ->when($request->filled('abn'), fn ($query) => $query->where('abn', 'like', '%'.$request->string('abn').'%'))
                ->when($request->filled('category'), fn ($query) => $query->where('category', $request->string('category')))
                ->when($request->filled('invoice_date_from'), fn ($query) => $query->whereDate('invoice_date', '>=', $request->date('invoice_date_from')))
                ->when($request->filled('invoice_date_to'), fn ($query) => $query->whereDate('invoice_date', '<=', $request->date('invoice_date_to')))
                ->when($request->filled('due_date_from'), fn ($query) => $query->whereDate('due_date', '>=', $request->date('due_date_from')))
                ->when($request->filled('due_date_to'), fn ($query) => $query->whereDate('due_date', '<=', $request->date('due_date_to')))
                ->when($request->filled('amount_min'), fn ($query) => $query->where('invoice_amount', '>=', $request->float('amount_min')))
                ->when($request->filled('amount_max'), fn ($query) => $query->where('invoice_amount', '<=', $request->float('amount_max')))
                ->when($request->filled('gst_min'), fn ($query) => $query->where('gst_amount', '>=', $request->float('gst_min')))
                ->when($request->filled('gst_max'), fn ($query) => $query->where('gst_amount', '<=', $request->float('gst_max')))
                ->latest()
                ->paginate(min((int) $request->integer('per_page', 20), 100))
                ->withQueryString(),
            'statuses' => InvoiceStatus::cases(),
            'categories' => Invoice::query()
                ->where('client_profile_id', $client->id)
                ->whereNotNull('category')
                ->distinct()
                ->orderBy('category')
                ->pluck('category'),
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
