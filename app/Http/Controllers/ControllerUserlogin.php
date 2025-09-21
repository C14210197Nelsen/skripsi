<?php

namespace App\Http\Controllers;

use App\Models\Userlogin;
use Illuminate\Http\Request;

class ControllerUserlogin extends Controller
{

    public function index() {
        $userlogins = Userlogin::all();
        return view('user.index', [
            'title' => 'User',
            'user' => 'Nama', // atau Auth::user()->name jika login
            'userlogins' => $userlogins
        ]);
    }
    

    public function create() {
        return view('user.create', [
            'title' => 'Create User',
            'user' => 'Nama'
        ]);
    }
    
    
    public function store(Request $request) {
        $validated = $request->validate([
            'username' => 'required|string|unique:userlogin,username|max:32',
            'password' => 'required|string|min:6|max:255',
            'fullName' => 'required|string|max:64',
            'role' => 'required|in:Owner,Purchase,Sales|max:16'
        ]);

        // Data akan otomatis ter-hash oleh setPasswordAttribute() di model
        \App\Models\Userlogin::create($validated);

        return redirect()->route('user.index')->with('success', 'User berhasil ditambahkan!');
    }


    public function show(Userlogin $userlogin) {
        //
    }

    public function edit(Userlogin $userlogin) {
        return view('user.edit', [
            'title' => 'Edit User',
            'user' => 'Nama Pengguna',
            'userlogin' => $userlogin
        ]);
    }


    public function update(Request $request, Userlogin $userlogin) {
        $validated = $request->validate([
            'username' => 'required|string|max:32|unique:userlogin,username,' . $userlogin->userID . ',userID',
            'password' => 'nullable|string|min:6|max:255',
            'fullName' => 'required|string|max:64',
            'role' => 'required|string|in:' . implode(',', Userlogin::ROLE_OPTIONS)
        ]);

        // Cek apakah password diisi, jika ya set dan hash otomatis via model
        if (!empty($validated['password'])) {
            $userlogin->password = $validated['password'];
        }

        // Update field lainnya
        $userlogin->username = $validated['username'];
        $userlogin->fullName = $validated['fullName'];
        $userlogin->role = $validated['role'];

        $userlogin->save();

        return redirect()->route('user.index')->with('success', 'User berhasil diperbarui!');
    }


    public function destroy(Userlogin $userlogin) {
        $userlogin->delete();

        return redirect()->route('user.index')
            ->with('success', 'Status diubah menjadi Tidak Aktif.');

    
    }
}
