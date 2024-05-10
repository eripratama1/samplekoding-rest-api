<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ArticleController extends Controller
{
    public function index()
    {
        /**
         * Fetch data dari tabel artikel berdasarkan data
         * publish_date terbaru
         */
        $articles = Article::latest('publish_date')->get();

        /** Jika data kosong tampilkan hasil dan pesan dibawah */
        if ($articles->isEmpty()) {
            return response()->json([
                'status' => Response::HTTP_NOT_FOUND,
                'message' => 'Article empty'
            ], Response::HTTP_NOT_FOUND);
        } else {
            /** Jika data ada tampilkan data sesuai hasil return dari fungsi map */
            return response()->json([
                'data' => $articles->map(function ($article) {
                    return [
                        'title' => $article->title,
                        'content' => $article->content,
                        'publish_date' => $article->publish_date
                    ];
                }),
                'message' => 'List articles',
                'status' => Response::HTTP_OK
            ], Response::HTTP_OK);
        }
    }
}
