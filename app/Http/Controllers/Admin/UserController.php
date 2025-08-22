<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Normaliza teléfono: deja dígitos y +; quita espacios/guiones/paréntesis.
     */
    private function normalizePhone(?string $raw): ?string
    {
        if (! $raw) {
            return null;
        }

        // Permite + al inicio; quita todo lo demás no numérico
        $raw     = trim($raw);
        $hasPlus = str_starts_with($raw, '+');
        $digits  = preg_replace('/\D+/', '', $raw);
        return $hasPlus ? ('+' . ltrim($digits, '+')) : $digits;
    }

    public function index(Request $request)
    {
        // ===== ADMINES =====
        $searchAdmin = $request->input('search_admin');
        $branchAdmin = $request->input('branch_admin');
        $statusAdmin = $request->input('status_admin'); // active|inactive|null

        $admins = User::query()
            ->with(['roles', 'branch'])
            ->whereHas('roles', fn($q) => $q->where('name', 'admin'))
            ->when($searchAdmin, function ($q) use ($searchAdmin) {
                $q->where(function ($qq) use ($searchAdmin) {
                    $qq->where('name', 'like', "%{$searchAdmin}%")
                        ->orWhere('email', 'like', "%{$searchAdmin}%")
                        ->orWhere('phone', 'like', "%{$searchAdmin}%");
                });
            })
            ->when($branchAdmin, fn($q) => $q->where('branch_id', $branchAdmin))
            ->when($statusAdmin === 'active', fn($q) => $q->where('is_active', 1))
            ->when($statusAdmin === 'inactive', fn($q) => $q->where('is_active', 0))
            ->orderBy('name')
            ->paginate(10, ['*'], 'admins_page');

        // ===== GUARDIAS =====
        $searchGuard = $request->input('search_guard');
        $branchGuard = $request->input('branch_guard');
        $statusGuard = $request->input('status_guard'); // active|inactive|null

        $guards = User::query()
            ->with(['roles', 'branch'])
            ->whereHas('roles', fn($q) => $q->where('name', 'guard'))

            ->when($searchGuard, function ($q) use ($searchGuard) {
                $q->where(function ($qq) use ($searchGuard) {
                    $qq->where('name', 'like', "%{$searchGuard}%")
                        ->orWhere('email', 'like', "%{$searchGuard}%")
                        ->orWhere('phone', 'like', "%{$searchGuard}%");
                });
            })
            ->when($branchGuard, fn($q) => $q->where('branch_id', $branchGuard))
            ->when($statusGuard === 'active', fn($q) => $q->where('is_active', 1))
            ->when($statusGuard === 'inactive', fn($q) => $q->where('is_active', 0))
            ->orderBy('name')
            ->paginate(10, ['*'], 'guards_page');

        // ---- Adorna con "last_seen" (opcional) para mostrar en la ficha ----
        $decorateLastSeen = function ($paginator) {
            $ids = $paginator->getCollection()->pluck('id')->all();
            if (empty($ids)) {
                return;
            }

            $last = DB::table('sessions')
                ->select('user_id', DB::raw('MAX(last_activity) as ts'))
                ->whereIn('user_id', $ids)
                ->groupBy('user_id')
                ->pluck('ts', 'user_id');

            $paginator->getCollection()->transform(function ($u) use ($last) {
                $ts = $last[$u->id] ?? null;
                if ($ts) {
                    $u->last_seen_humans = Carbon::createFromTimestamp($ts)->diffForHumans();
                }
                return $u;
            });
        };

        $decorateLastSeen($admins);
        $decorateLastSeen($guards);

        $branches    = Branch::orderBy('name')->get();
        $adminsCount = User::role('admin')->count();

        return view('admin.users.index', compact(
            'admins', 'guards', 'branches', 'adminsCount',
            'searchAdmin', 'branchAdmin', 'statusAdmin',
            'searchGuard', 'branchGuard', 'statusGuard'
        ));
    }

    public function create()
    {
        $roles = Role::whereIn('name', ['guard', 'admin'])
            ->orderBy('name')->pluck('name', 'name');
        $branches = Branch::orderBy('name')->get();

        return view('admin.users.create', compact('roles', 'branches'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'      => ['required', 'string', 'max:120'],
            'email'     => ['required', 'email', 'max:120', 'unique:users,email'],
            'password'  => ['required', 'string', 'min:8', 'confirmed'],
            'role'      => ['required', 'string', 'exists:roles,name'],
            'branch_id' => ['nullable', 'required_if:role,guardia', 'exists:branches,id'],
            'is_active' => ['nullable'],
            'phone'     => ['nullable', 'string', 'max:25', 'regex:/^[0-9\-\+\s\(\)]+$/'],
            'avatar'    => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'], // <-- NUEVO
        ]);

        $avatarPath = null;
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
        }

        $user = User::create([
            'name'        => $data['name'],
            'email'       => $data['email'],
            'password'    => Hash::make($data['password']),
            'branch_id'   => $data['role'] === 'guardia' ? $data['branch_id'] : null,
            'is_active'   => $request->boolean('is_active'),
            'phone'       => $this->normalizePhone($request->input('phone')),
            'avatar_path' => $avatarPath,
        ]);

        $user->syncRoles([$data['role']]);

        return redirect()->route('admin.users.index')->with('success', 'Usuario creado.');
    }

    public function edit(User $user)
    {
        $roles       = Role::whereIn('name', ['guard', 'admin'])->orderBy('name')->pluck('name', 'name');
        $currentRole = $user->getRoleNames()->first();
        $branches    = Branch::orderBy('name')->get();

        return view('admin.users.edit', compact('user', 'roles', 'currentRole', 'branches'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'email'         => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'role'          => ['required', 'string', 'exists:roles,name'],
            'branch_id'     => ['nullable', 'required_if:role,guardia', 'exists:branches,id'],
            'is_active'     => ['nullable', 'boolean'],
            'phone'         => ['nullable', 'string', 'max:25', 'regex:/^[0-9\-\+\s\(\)]+$/'],
            'password'      => ['nullable', 'string', 'min:8', 'confirmed'],
            'avatar'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'], // <-- NUEVO
            'remove_avatar' => ['nullable', 'boolean'],                                      // <-- NUEVO
        ]);

        // proteger último admin y admin fijo (idéntico a tu lógica)
        $isRemovingAdmin = $user->hasRole('admin') && $data['role'] !== 'admin';
        if ($isRemovingAdmin) {
            $admins = User::role('admin')->count();
            if ($admins <= 1) {
                return back()->withErrors('No se puede quitar el rol admin del último administrador.')->withInput();
            }
        }
        if ($user->email === 'admin@admin.com') {
            if ($data['role'] !== 'admin') {
                return back()->withErrors('No se puede cambiar el rol de este administrador protegido.')->withInput();
            }
            if ($request->filled('is_active') && ! $request->boolean('is_active')) {
                return back()->withErrors('No se puede desactivar este administrador protegido.')->withInput();
            }
        }

        $user->name      = $data['name'];
        $user->email     = $data['email'];
        $user->branch_id = $data['role'] === 'guardia' ? $data['branch_id'] : null;

        if ($request->has('is_active')) {
            $user->is_active = (bool) $request->boolean('is_active');
        }

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->phone = $this->normalizePhone($request->input('phone'));

        // --- Avatar: borrar o reemplazar ---
        if ($request->boolean('remove_avatar') && $user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
            $user->avatar_path = null;
        }
        if ($request->hasFile('avatar')) {
            // si sube nuevo, borro el anterior
            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }
            $user->avatar_path = $request->file('avatar')->store('avatars', 'public');
        }

        $user->save();

        $user->syncRoles([$data['role']]);

        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('admin.users.index')->with('success', 'Usuario actualizado.');
    }

    public function destroy(User $user)
    {
        // 1) Proteger administrador “fijo”
        if ($user->email === 'admin@admin.com') {
            return back()->withErrors('No se puede eliminar este administrador protegido.');
        }

        // 2) Proteger al último admin
        if ($user->hasRole('admin')) {
            $admins = User::role('admin')->count();
            if ($admins <= 1) {
                return back()->withErrors('No se puede eliminar al último administrador.');
            }
        }

        // (Opcional) Proteger auto-eliminación si es el último admin
        if (auth()->id() === $user->id && $user->hasRole('admin')) {
            $admins = User::role('admin')->count();
            if ($admins <= 1) {
                return back()->withErrors('No te podés auto-eliminar: quedarías sin administradores.');
            }
        }

        $user->delete();
        return back()->with('success', 'Usuario eliminado correctamente.');
    }
}
