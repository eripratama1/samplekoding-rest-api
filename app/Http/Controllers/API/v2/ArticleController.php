<?php

namespace App\Http\Controllers\API\v2;

use App\Http\Controllers\Controller;
use App\Http\Resources\ArticleCollection;
use App\Http\Resources\ArticleResource;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ArticleController extends Controller
{
    public function index(Request $request)
    {

        $query = Article::query()->latest('publish_date');
        $keyword = $request->input('title');

        if ($keyword) {
            $query->where('title','like',"%{$keyword}%");
        }

        $articles = $query->paginate(2);

        /** Jika data kosong tampilkan hasil dan pesan dibawah */
        if ($articles->isEmpty()) {
            return response()->json([
                'status' => Response::HTTP_NOT_FOUND,
                'message' => 'Article empty'
            ], Response::HTTP_NOT_FOUND);
        } else {

            return new ArticleCollection($articles);

            /** Jika ingin menggunakan resources tanpa menampilkan paginate gunakan kode di bawah ini */
            // return response()->json([
            //     'status' => Response::HTTP_OK,
            //     'message' => 'List article',
            //     'data' => ArticleResource::collection($articles),
            // ]);
        }
    }
}
