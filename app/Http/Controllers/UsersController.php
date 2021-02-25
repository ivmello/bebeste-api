<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\DB;
use App\Models\Price;
use App\Models\Score;
use App\Models\User;
use Carbon\Carbon;

class UsersController extends Controller
{
    public function index(Request $request) {
        $week_of_year = Carbon::now()->weekOfYear;
        $day_of_week = Carbon::now()->dayOfWeekIso;

        $usersData = User::select('users.id', 'users.name', DB::raw('SUM(scores.total) As total'))
         ->leftJoin('scores', 'scores.user_id', '=', 'users.id')
         ->groupBy('users.id');

        $users = $usersData->orderBy('total', 'DESC')->get();

        $price = Price::where('date', date('Y-m-d'))->first();
        $result = array();

        $this->solveDraw($users, $day_of_week);

        $users = $usersData->orderBy('total', 'DESC')->get();

        if (!empty($users)) {
            foreach($users as $user) {
                $frequency = $user->scores()->select('id', 'user_id', 'day_of_week', 'week_of_year', 'drank')->where('week_of_year', $week_of_year)->get();

                array_push($result, array(
                    'id' => $user->id,
                    'name' => $user->name,
                    'total' => $user->total ? $user->total : 0,
                    'price_of_day' => !empty($price->value) ? $price->value : 0,
                    'frequency' => $frequency,
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

    private function solveDraw($users, $day_of_week) {
        if ($day_of_week == 4) {
            $user_winner_1 = $users[0];
            $user_winner_2 = $users[1];
            $user_loser_1 = $users[count($users) - 1];
            $user_loser_2 = $users[count($users) - 2];

            /**
             * Resolve empate entre os perdedores
             */
            if ($user_loser_1->total == $user_loser_2->total) {
                $arr_tmp = [$user_loser_1, $user_loser_2];
                shuffle($arr_tmp);
                $user_random = $arr_tmp[0];

                $score = new Score();
                $score->user_id = $user_random->id;
                $score->total = 50;
                $score->date = date('Y-m-d');
                $score->save();
            }

            /**
             * Resolve empate entre os vencedores
             */
            if ($user_winner_1->total == $user_winner_2->total) {
                $arr_tmp = [$user_winner_1, $user_winner_2];
                shuffle($arr_tmp);
                $user_random = $arr_tmp[0];

                $score = new Score();
                $score->user_id = $user_random->id;
                $score->total = 50;
                $score->date = date('Y-m-d');
                $score->save();
            }
        }
    }
}
