<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\DB;
use App\Models\Price;
use App\Models\User;
use Carbon\Carbon;

class UsersController extends Controller
{
    public function index(Request $request) {
        $week_of_year = Carbon::now()->weekOfYear;

        $users = User::select('users.id', 'users.name', DB::raw('SUM(scores.total) As total'))
         ->leftJoin('scores', 'scores.user_id', '=', 'users.id')
         ->groupBy('users.id')
         ->get();

        $price = Price::where('date', date('Y-m-d'))->first();
        $result = array();

        if (!empty($users)) {
            foreach($users as $user) {
                $frequency = $user->scores()->select('id', 'user_id', 'day_of_week', 'week_of_year', 'drank')->where('week_of_year', $week_of_year)->get();

                array_push($result, array(
                    'id' => $user->id,
                    'name' => $user->name,
                    'total' => $user->total ? $user->total : 0,
                    'price_of_day' => $price->value,
                    'frequency' => $frequency,
                    'date' => $price->date,
                ));
            }
        } else {
            foreach($users as $user) {
                $frequency = array();

                array_push($result, array(
                    'id' => $user->id,
                    'name' => $user->name,
                    'total' => 0,
                    'price_of_day' => 0,
                    'frequency' => $frequency,
                    'date' => date('Y-m-d'),
                ));
            }
        }

         return response()->json($result);
    }
}
