<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    public function index()
    {
        return view('login');
    }

    public function authenticate(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required']
        ]);

        $response = Http::post(
            'https://identitytoolkit.googleapis.com/v1/accounts:signInWithPassword?key=AIzaSyD7Zrw9hhhJXABWI0vO7KEC2BUvgIMm26E',
            [
                'email' => $request->email,
                'password' => $request->password,
                'returnSecureToken' => true
            ]
        );

        if (!$response->successful()) {

            return back()
                ->withInput()
                ->withErrors([
                    'email' => 'Email atau password salah.'
                ]);
        }

        $firebaseUser = $response->json();

        Session::put('firebase_user', [
            'uid' => $firebaseUser['localId'],
            'email' => $firebaseUser['email'],
            'idToken' => $firebaseUser['idToken'],
            'role' => str_contains($firebaseUser['email'], 'admin') ? 'admin' : 'operator'
        ]);

        return redirect()
            ->route('admin.index')
            ->with('success', 'Berhasil login ke dashboard.');
    }

    public function logout()
    {
        Session::forget('firebase_user');

        return redirect()
            ->route('login.index')
            ->with('success', 'Berhasil logout.');
    }
}
