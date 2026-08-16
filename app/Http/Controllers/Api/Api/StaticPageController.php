<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pages;
use Illuminate\Http\Request;

class StaticPageController extends Controller
{
    public function show(Request $request, string $slug)
    {
        $aliases = [
            'privacy_policy' => ['privacy_policy', 'privacy-policy', 'privacy'],
            'privacy-policy' => ['privacy_policy', 'privacy-policy', 'privacy'],
            'terms_conditions' => ['terms_of_service', 'terms_conditions', 'terms-and-conditions', 'terms'],
            'terms-and-conditions' => ['terms_of_service', 'terms_conditions', 'terms-and-conditions', 'terms'],
            'terms_of_service' => ['terms_of_service', 'terms_conditions', 'terms-and-conditions', 'terms'],
        ];

        $slugs = $aliases[$slug] ?? [$slug];
        $page = Pages::whereIn('url', $slugs)->where('status', 'Active')->first();

        if (! $page) {
            return response()->json([
                'status_code' => '0',
                'status_message' => 'Content not found.',
            ], 404);
        }

        $content = str_replace(
            ['SITE_NAME', 'SITE_URL'],
            [SITE_NAME, url('/')],
            $page->description_lang ?: $page->content
        );

        return response()->json([
            'status_code' => '1',
            'status_message' => 'Listed Successfully',
            'page' => [
                'id' => $page->id,
                'title' => $page->name_lang ?: $page->name,
                'slug' => $page->url,
                'content' => $content,
            ],
        ]);
    }
}
