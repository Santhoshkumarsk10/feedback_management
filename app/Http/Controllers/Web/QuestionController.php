<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Question;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    public function index()
    {
        return view('questions.index', ['questions' => Question::orderBy('sort_order')->orderBy('id')->get()]);
    }

    public function create()
    {
        return view('questions.form', ['question' => new Question(['type' => 'rating', 'sort_order' => 0])]);
    }

    public function store(Request $request)
    {
        Question::create($this->data($request));

        return redirect()->route('questions.index')->with('success', 'Question added.');
    }

    public function edit(Question $question)
    {
        return view('questions.form', compact('question'));
    }

    public function update(Request $request, Question $question)
    {
        $question->update($this->data($request));

        return redirect()->route('questions.index')->with('success', 'Question updated.');
    }

    public function destroy(Question $question)
    {
        // keep historical answers: deactivate instead of delete when already answered
        if ($question->answers()->exists()) {
            $question->update(['is_active' => false]);

            return back()->with('success', 'Question already has answers, so it was deactivated instead of deleted.');
        }
        $question->delete();

        return back()->with('success', 'Question deleted.');
    }

    private function data(Request $r): array
    {
        $d = $r->validate([
            'question' => ['required', 'string', 'min:3', 'max:255', 'regex:~^[\p{L}\p{N}\s\-–—_&/,\.()?!\'’"“”:]+$~u'],
            'section' => ['nullable', 'string', 'min:2', 'max:150', 'regex:~^[\p{L}\p{N}\s\-–—_&/,\.()\'’:]+$~u'],
            'type' => 'required|in:rating,mcq,text',
            'options' => 'required_if:type,mcq|nullable|string|max:2000',
            'sort_order' => 'nullable|integer|min:0|max:9999',
        ], [
            'question.min' => 'The question text must be at least 3 characters.',
            'question.regex' => 'The question text contains invalid characters.',
            'section.regex' => 'The section name contains invalid characters.',
        ]);

        return [
            'question' => $d['question'],
            'section' => $r->input('section'),
            'type' => $d['type'],
            'options' => $d['type'] === 'mcq'
                ? array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $d['options'] ?? ''))))
                : null,
            'sort_order' => $d['sort_order'] ?? 0,
            'is_required' => $r->boolean('is_required'),
            'is_active' => $r->boolean('is_active'),
        ];
    }
}
