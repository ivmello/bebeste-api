<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\DB;
use App\Models\Price;
use App\Models\User;

class UsersController extends Controller
{
    public function index(Request $request) {
        $users = User::select('users.id', 'users.name', DB::raw('SUM(scores.total) As total'))
         ->leftJoin('scores', 'scores.user_id', '=', 'users.id')
         ->groupBy('users.id')
         ->get();

        $price = Price::where('date', date('Y-m-d'))->first();
        $result = array();

        if (!empty($users) && !empty($price)) {
            foreach($users as $user) {
                array_push($result, array(
                    'id' => $user->id,
                    'name' => $user->name,
                    'total' => $user->total ? $user->total : 0,
                    'price_of_day' => $price->value,
                    'date' => $price->date,
                ));
            }
        }

         return response()->json($result);
    }
}
