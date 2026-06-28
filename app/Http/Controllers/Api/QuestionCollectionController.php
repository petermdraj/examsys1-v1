<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\QuestionCollection;
use Illuminate\Http\Request;

class QuestionCollectionController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:255']);

        $collection = QuestionCollection::create([
            'creator_id' => auth()->id(),
            'name'       => trim($data['name']),
        ]);

        return response()->json(['id' => $collection->id, 'name' => $collection->name]);
    }
}
