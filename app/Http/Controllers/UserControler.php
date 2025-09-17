<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserControler extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->search;

        $users = User::orderBy('id')
            ->when($search, function ($q, $search) {
                return $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('role', 'like', "%{$search}%");
            })
            ->paginate();

        if($search) $users->appends(['search'=>$search]);
        return view('user.index', [
            'users' => $users
        ]);

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('user.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
{
    $request->validate([
        'nama' => [
            'required',
            'regex:/^(?![0-9]+$).+$/'
        ],
        'username' => [
            'required',
            'unique:users',
            'regex:/^(?![0-9]+$).+$/'
        ],
        'password' => 'required|min:6',
        'role' => 'required'
    ], [
        'nama.regex' => 'Nama tidak boleh hanya berupa angka.',
        'username.regex' => 'Username tidak boleh hanya berupa angka.',
    ]);

    User::create([
        'nama' => $request->nama,
        'username' => $request->username,
        'password' => bcrypt($request->password),
        'role' => $request->role,
    ]);

    return redirect()->route('user.index')->with('store', 'success');
}

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        abort(404);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        return view('user.edit',[
            'user'=>$user
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
{
    $request->validate([
        'nama' => [
            'required',
            'regex:/^(?![0-9]+$).+$/'
        ],
        'username' => [
            'required',
            'unique:users,username,' . $user->id,
            'regex:/^(?![0-9]+$).+$/'
        ],
        'role' => 'required'
    ], [
        'nama.regex' => 'Nama tidak boleh hanya berupa angka.',
        'username.regex' => 'Username tidak boleh hanya berupa angka.',
    ]);

    $user->update([
        'nama' => $request->nama,
        'username' => $request->username,
        'role' => $request->role,
    ]);

    if ($request->password) {
        $user->update([
            'password' => bcrypt($request->password)
        ]);
    }

    return redirect()->route('user.index')->with('update', 'success');
}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();

        return back()->with('destroy','success');

    }
}