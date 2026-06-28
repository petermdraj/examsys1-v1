<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Quiz;
use App\Services\QuestionBank\QuestionBankService;
use Illuminate\Http\Request;

class QuestionBankController extends Controller
{
    public function index(Request $request)
    {
        $creatorId = auth()->id();

        $paginated = Question::whereNull('quiz_id')
            ->where('creator_id', $creatorId)
            ->with(['options', 'collection'])
            ->when($request->collection_id, fn ($q) => $q->where('collection_id', $request->collection_id))
            ->when($request->difficulty,    fn ($q) => $q->where('difficulty', $request->difficulty))
            ->when($request->type,          fn ($q) => $q->where('type', $request->type))
            ->when($request->search,        fn ($q) => $q->where('content', 'like', '%' . $request->search . '%'))
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return response()->json([
            'data' => $paginated->map(fn ($q) => [
                'id'         => $q->id,
                'content'    => $q->content,
                'type'       => $q->type,
                'difficulty' => $q->difficulty,
                'marks'      => (float) $q->marks,
                'collection' => $q->collection ? ['id' => $q->collection->id, 'name' => $q->collection->name] : null,
                'options'    => $q->options->map(fn ($o) => [
                    'content'    => $o->content,
                    'is_correct' => $o->is_correct,
                ])->all(),
            ]),
            'meta' => [
                'total'     => $paginated->total(),
                'page'      => $paginated->currentPage(),
                'per_page'  => $paginated->perPage(),
                'last_page' => $paginated->lastPage(),
            ],
        ]);
    }

    public function randomImport(Request $request, QuestionBankService $service)
    {
        $validated = $request->validate([
            'quiz_id'       => 'required|uuid|exists:quizzes,id',
            'count'         => 'required|integer|min:1|max:100',
            'collection_id' => 'nullable|uuid|exists:question_collections,id',
            'difficulty'    => 'nullable|in:easy,medium,hard',
            'type'          => 'nullable|in:mcq_single,mcq_multiple,fill_blank,true_false,short_answer',
        ]);

        $quiz = Quiz::where('id', $validated['quiz_id'])
            ->where('creator_id', auth()->id())
            ->firstOrFail();

        $imported = $service->randomImport(
            auth()->id(),
            $quiz,
            $validated['count'],
            $validated['collection_id'] ?? null,
            $validated['difficulty'] ?? null,
            $validated['type'] ?? null,
        );

        return response()->json([
            'imported' => $imported,
            'notice'   => $imported < $validated['count']
                ? "Only {$imported} questions matched your filters."
                : null,
        ]);
    }
}
