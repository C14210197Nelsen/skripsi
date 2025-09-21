<?php

// namespace App\Http\Controllers;

// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Auth;

// class AuthController extends Controller {

//     public function showLogin() {
//         return view('auth.login', [
//             'title' => 'Login'
//         ]);
//     }

//     public function login(Request $request) {
//         $credentials = $request->validate([
//             'username' => 'required|string',
//             'password' => 'required|string',
//         ]);

//         if (Auth::attempt($credentials)) {
//             $request->session()->regenerate();

//             $user = Auth::user();

//             switch ($user->role) {
//                 case 'Owner':
//                     return redirect()->route('home'); // atau buat route khusus owner
//                 case 'Purchase':
//                     return redirect()->route('purchase.index');
//                 case 'Sales':
//                     return redirect()->route('sales.index');
//                 default:
//                     return redirect()->route('home');
//             }
//         }

//         return back()->withErrors([
//             'username' => 'Username atau password salah.',
//         ]);
//     }

//     public function logout(Request $request) {
//         Auth::logout();
//         $request->session()->invalidate();
//         $request->session()->regenerateToken();

//         return redirect()->route('login')->with('success', 'Anda sudah logout.');
//     }
// }
