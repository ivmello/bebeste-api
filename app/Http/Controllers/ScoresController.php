<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Price;
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
        $date = date('Y-m-d');
        $day_of_week = Carbon::now()->dayOfWeekIso;
        $week_of_year = Carbon::now()->weekOfYear;

        $user_id = $request->get('user_id');
        $drank = $request->get('drank');

        $price_db = Price::where('date', $date)->first();
        if (empty($price_db)) {
            $price_of_day = rand(1,5) * 100;
            $price_db = Price::create([
                'date' => $date,
                'value' => $price_of_day
            ]);
        } else {
            $price_of_day = $price_db->value;
        }

        $user = User::find($user_id);

        if (empty($user)) {
            return response()->json([
                'msg' => 'Usuário não existe',
            ]);
        }

        $score_exists = $user->scores()->where('date', $date)->get();

        if ($score_exists->isEmpty()) {
            $score = new Score();
            $score->user_id = $user_id;
            $score->total = $drank == 1 ? 0 : $price_of_day;
            $score->drank = $drank;
            $score->date = $date;
            $score->day_of_week = $day_of_week;
            $score->week_of_year = $week_of_year;
            $score->price_of_day = $price_of_day;
            $score->save();
        } else {
            return response()->json([
                'msg' => 'Você já atualizou sua conta hoje',
            ]);
        }

        return response()->json($score);
    }
}
