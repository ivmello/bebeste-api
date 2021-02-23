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
        $price_of_day = rand(1,5) * 100;
        $date = date('Y-m-d');

        $user_id = $request->get('user_id');
        $drank = $request->get('drank');

        $price_db = Price::where('date', $date)->get();
        if ($price_db->isEmpty()) {
            $price_db = Price::create([
                'date' => $date,
                'value' => $price_of_day
            ]);
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
            $score->total = $drank ? 0 : $price_of_day;
            $score->drank = $drank;
            $score->date = $date;
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
