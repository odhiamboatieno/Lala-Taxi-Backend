<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Help;
use Illuminate\Http\Request;

class HelpController extends Controller
{
    public function articles(Request $request)
    {
        $query = Help::where('status', 'Active');

        if ($request->filled('q')) {
            $term = $request->q;
            $query->where(function ($builder) use ($term) {
                $builder->where('question', 'like', "%{$term}%")
                    ->orWhere('answer', 'like', "%{$term}%");
            });
        }
        else {
            $query->where('suggested', 'yes');
        }

        $articles = $query->orderBy('id', 'desc')
            ->limit(20)
            ->get()
            ->map(function ($help) {
                return [
                    'id' => $help->id,
                    'question' => str_replace('SITE_NAME', SITE_NAME, $help->question_lang),
                    'answer' => str_replace('SITE_NAME', SITE_NAME, $help->answer_lang),
                    'category_id' => $help->category_id,
                    'subcategory_id' => $help->subcategory_id,
                    'category_name' => optional($help->category)->category_name_lang ?? '',
                    'subcategory_name' => $help->subcategory_name ?? '',
                ];
            })
            ->values();

        return response()->json([
            'status_code' => '1',
            'status_message' => 'Listed Successfully',
            'articles' => $articles,
        ]);
    }
}
