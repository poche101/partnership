<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use App\Services\SemanticPartnerSearch;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index()
    {
        return view('search.index', ['results' => collect(), 'ran' => false, 'query' => '']);
    }

    public function search(Request $request, SemanticPartnerSearch $search)
    {
        $data = $request->validate(['query' => ['required', 'string', 'min:1']]);

        $ids = $search->search($data['query']);
        $results = $ids
            ? Partner::with('church.groupChurch')->whereIn('id', $ids)->get()->sortBy(fn ($p) => array_search($p->id, $ids))->values()
            : collect();

        return view('search.index', ['results' => $results, 'ran' => true, 'query' => $data['query']]);
    }
}
