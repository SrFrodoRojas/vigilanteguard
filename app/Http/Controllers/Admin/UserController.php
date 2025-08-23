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
use Illuminate\Validation\Rule;


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

    public function store(\Illuminate\Http\Request $request)
    {
        $data = $request->validate([
            'name'      => ['required', 'string', 'max:120'],
            'email'     => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone'     => ['nullable', 'string', 'max:25'],
            'branch_id' => ['nullable', 'integer'],
            'is_active' => ['nullable'], // checkbox
            'password'  => ['required', 'string', 'min:8', 'max:255'],
            'role'      => ['nullable', 'string', 'max:50'], // nombre del rol (Spatie)
            'avatar'    => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $payload = [
            'name'      => $data['name'],
            'email'     => $data['email'],
            'phone'     => $data['phone'] ?? null,
            'branch_id' => $data['branch_id'] ?? null,
            'is_active' => $request->boolean('is_active') ? '1' : '0', // tu columna es varchar NOT NULL
            'password'  => Hash::make($data['password']),
        ];

        if ($request->hasFile('avatar')) {
            $path                   = $request->file('avatar')->store('avatars', 'public');
            $payload['avatar_path'] = $path;
        }

        $user = User::create($payload);

        if (! empty($data['role']) && method_exists($user, 'syncRoles')) {
            $user->syncRoles([$data['role']]);
        }

        return redirect()->route('admin.users.index')->with('ok', 'Usuario creado.');
    }

    public function update(\Illuminate\Http\Request $request, User $user)
    {
        $data = $request->validate([
            'name'          => ['required', 'string', 'max:120'],
            'email'         => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone'         => ['nullable', 'string', 'max:25'],
            'branch_id'     => ['nullable', 'integer'],
            'is_active'     => ['nullable'],
            'password'      => ['nullable', 'string', 'min:8', 'max:255'],
            'role'          => ['nullable', 'string', 'max:50'],
            'avatar'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_avatar' => ['nullable', 'boolean'],
        ]);

        $payload = [
            'name'      => $data['name'],
            'email'     => $data['email'],
            'phone'     => $data['phone'] ?? null,
            'branch_id' => $data['branch_id'] ?? null,
            'is_active' => $request->boolean('is_active') ? '1' : '0',
        ];

        if (! empty($data['password'])) {
            $payload['password'] = Hash::make($data['password']);
        }

        // Avatar: reemplazo/limpieza
        if ($request->hasFile('avatar')) {
            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }
            $payload['avatar_path'] = $request->file('avatar')->store('avatars', 'public');
        } elseif ($request->boolean('remove_avatar') && $user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
            $payload['avatar_path'] = null;
        }

        $user->update($payload);

        if (method_exists($user, 'syncRoles')) {
            if (! empty($data['role'])) {
                $user->syncRoles([$data['role']]);
            } else {
                $user->syncRoles([]); // sin rol
            }
        }

        return redirect()->route('admin.users.index')->with('ok', 'Usuario actualizado.');
    }

    public function edit(User $user)
    {
        $roles       = Role::whereIn('name', ['guard', 'admin'])->orderBy('name')->pluck('name', 'name');
        $currentRole = $user->getRoleNames()->first();
        $branches    = Branch::orderBy('name')->get();

        return view('admin.users.edit', compact('user', 'roles', 'currentRole', 'branches'));
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
