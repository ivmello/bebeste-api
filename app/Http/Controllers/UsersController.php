<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use App\Models\User;

class UsersController extends Controller
{
    public function index(Request $request) {
        return new UserResource(User::all());
    }
}
