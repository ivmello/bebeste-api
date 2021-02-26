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

        $usersData = User::select('users.id', 'users.name', DB::raw('COALESCE(SUM(scores.total), 0) As total'))
        ->leftJoin('scores', 'scores.user_id', '=', 'users.id')
        ->groupBy('users.id')
        ->orderBY('total', 'DESC');

        $users = $usersData->get();

        $price = Price::where('date', date('Y-m-d'))->first();
        $result = array();

        if ($day_of_week == 4) {
            $this->solveDraw($users, $day_of_week);
        }

        $users = $usersData->get();

        if (!empty($users)) {
            foreach($users as $i => $user) {
                $frequency = $user->scores()->select('id', 'user_id', 'day_of_week', 'week_of_year', 'drank')->where('week_of_year', $week_of_year)->get();

                array_push($result, array(
                    'id' => $user->id,
                    'name' => $user->name,
                    'total' => $user->total ? $user->total : 0,
                    'winner' => $users[0]->id == $user->id ? 1 : 0,
                    'loser' => $users[count($users)-1]->id == $user->id ? 1 : 0,
                    'price_of_day' => !empty($price->value) ? $price->value : 0,
                    'day_of_week' => $day_of_week,
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
                    'winner' => $users[0]->id == $user->id ? 1 : 0,
                    'loser' => $users[count($users)-1]->id == $user->id ? 1 : 0,
                    'price_of_day' => 0,
                    'day_of_week' => $day_of_week,
                    'frequency' => $frequency,
                    'date' => date('Y-m-d'),
                ));
            }
        }

        return response()->json($result);
    }

    private function solveDraw($users, $day_of_week) {
        $user_winner_1 = $users[0];
        $user_winner_2 = $users[1];
        $user_loser_1 = $users[count($users) - 1];
        $user_loser_2 = $users[count($users) - 2];

        $tmp_winners = array();
        $tmp_losers = array();
        foreach($users as $i => $user) {
            if ($i < count($users) - 1 && $users[$i]->total == $users[$i + 1]->total) {
                $tmp_winners[$i] = $users[$i];
                $tmp_winners[$i+1] = $users[$i+1];

                shuffle($tmp_winners);

                $score = new Score();
                $score->user_id = $tmp_winners[0]->id;
                $score->total = rand(25,50);
                $score->date = date('Y-m-d');
                $score->save();

            } else {
                if ($i < count($users) - 2 && $users[($i+1)]->total == $users[($i+1) + 1]->total) {
                    $tmp_losers[($i+1)] = $users[($i+1)];
                    $tmp_losers[($i+1)+1] = $users[($i+1)+1];

                    shuffle($tmp_losers);

                    $score = new Score();
                    $score->user_id = $tmp_losers[0]->id;
                    $score->total = rand(1,25);
                    $score->date = date('Y-m-d');
                    $score->save();
                }
            }
        }
    }
}
