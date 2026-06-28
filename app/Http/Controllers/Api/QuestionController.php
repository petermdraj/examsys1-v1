<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Question;

class QuestionController extends Controller
{
    public function destroy(Question $question)
    {
        abort_if($question->quiz->creator_id !== auth()->id(), 403);

        $question->delete();

        return response()->json(['ok' => true]);
    }
}
