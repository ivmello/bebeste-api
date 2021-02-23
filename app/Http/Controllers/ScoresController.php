<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Score;
use Carbon\Carbon;

class ScoresController extends Controller
{
    public function index(Request $request) {
        $scores = Score::all();
        return response()->json($scores);
    }

    public function create(Request $request) {
        $users = User::all();
        $price = rand(1,5);
        $date = date('Y-m-d');

        foreach($users as $user) {
            $exists = $user->scores()->where('date', $date)->get();
            $day = Carbon::now()->dayOfWeek;

            if ($exists->isEmpty()) {
                $score = new Score();
                $score->user_id = $user->id;
                $score->price = $price;
                $score->date = $date;
                $score->day = $day;
                $score->save();
            }
        }

        return response()->json([
            'msg' => 'ok'
        ]);
    }

    public function update(Request $request, User $user) {
        $params = $request->all();

        if (!empty($params['date'])) {

            $score = $user->scores()->where('date', $params['date'])->first();
            $score->drink = 1;
            $score->save();

            return response()->json($score);
        }

        // $user->scores()->
        return response()->json([
            'msg' => 'Date is missing',
        ]);
    }
}
