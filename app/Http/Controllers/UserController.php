<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Permission;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct () {
        $this->middleware('has.permission:users.index')->only('index');
        $this->middleware('has.permission:users.create')->only('store');
        $this->middleware('has.permission:users.edit')->only('update');
        $this->middleware('has.permission:users.destroy')->only('destroy');
        $this->middleware('has.permission:users.permission')->only(['getPermissions', 'updatePermissions']);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return User::where('names', 'like', '%' . request()->search . '%')
                    ->orWhere('username', 'like', '%' . request()->search . '%')
                        ->paginate(7);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'names' => 'required|max:70|unique:users,names',
            'username' => 'required|max:20|unique:users,username',
            'password' => 'required|max:20',
            'official_position' => 'required|max:30',
            'technical_support' => 'required|boolean',
            'signature' => 'nullable|mimes:jpg,png',
        ], [], [
            'names' => 'Nombres',
            'username' => 'Nombre de usuario',
            'password' => 'Contraseña',
            'official_position' => 'Cargo',
            'technical_support' => 'Soporte técnico',
            'signature' => 'Firma'
        ]);

        $user = (new User)->fill($request->only(['names', 'username', 'official_position']));
        $user->password = bcrypt($data['password']);

        if ($request->hasFile('signature')) {
            $pathFile = Storage::putFile('signature-users', $request->signature);
            $user->signature = $pathFile;
        }
        
        $user->save();
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        return $user;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'names' => 'required|max:70|unique:users,names,' . $user->id,
            'username' => 'required|max:20|unique:users,username,' . $user->id,
            'password' => 'nullable|max:20',
            'official_position' => 'required|max:30',
            'technical_support' => 'required|boolean',
            'signature' => 'nullable|mimes:jpg,png',
        ], [], [
            'names' => 'Nombres',
            'username' => 'Nombre de usuario',
            'password' => 'Contraseña',
            'official_position' => 'Cargo',
            'technical_support' => 'Soporte técnico',
            'signature' => 'Firma'
        ]);

        $user->fill(['names' => $data['names'], 'username' => $data['username'],'technical_support' => $data['technical_support'], 'official_position' => $data['official_position'],]);

        if ($request->password) {

            $user->password = bcrypt($data['password']);
        }

        if ($request->hasFile('signature')) {
            if ($user->signature && Storage::exists($user->signature)) {
                Storage::delete($user->signature);
            }
            $pathFile = Storage::putFile('signature-users', $request->signature);
            $user->signature = $pathFile;
        }
        
        $user->update();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        //$user->delete();
    }

    public function getPermissions ($user) {
        $user = User::where('id', $user)
                ->with('permissions')
                ->firstOrFail(['id', 'names']);

        $permissions = Permission::orderBy('id', 'asc')->get();
        return [
            'user' => $user,
            'permissions' => $permissions
        ];
    }

    public function updatePermissions (Request $request, User $user) {
        $idsFromPermissions = collect($request->all())->pluck('id');    
        $user->permissions()->sync($idsFromPermissions);
    }
}
