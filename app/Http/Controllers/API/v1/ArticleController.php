<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;


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

    public function store(Request $request)
    {

        /**
         * Melakukan validasi menggunakan validator
         */
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'content' => 'required',
            'publish_date' => 'required'
        ]);

        /** Jika ada data yang tidak valid return error response berikut */
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        try {
            /** Jika validasi berhasil lakukan proses simpan data lalu return
             *  response JSON dibawah
             */
            Article::create([
                'title' => $request->title,
                'content' => $request->content,
                'publish_date' => Carbon::create($request->publish_date)->toDateString(),
            ]);

            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => 'Data stored to db'
            ], Response::HTTP_OK);
        } catch (Exception $e) {

            /** Jika proses simpan data gagal return response JSON berikut */
            Log::error('Error storing data :' .  $e->getMessage());

            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Failed stored data to db'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show($id)
    {
        /** Query untuk menampilkan data article berdasarkan id
         * yang didapat
         */
        $article = Article::where('id', $id)->first();

        /** Jika data article berdasarkan id ada pada tabel article
         * tampilkan response berisikan data title,content & publish_date
         */
        if ($article) {
            return response()->json([
                'status' => Response::HTTP_OK,
                'data' => [
                    'title' => $article->title,
                    'content' => $article->content,
                    'publish_date' => $article->publish_date
                ]
            ], Response::HTTP_OK);
        } else {
            /** Jika adta tidak ada tampilkan response berikut */
            return response()->json([
                'status' => Response::HTTP_NOT_FOUND,
                'message' => 'article not found'
            ], Response::HTTP_NOT_FOUND);
        }
    }

    public function update(Request $request, $id)
    {
        $article = Article::find($id);

        if (!$article) {
            return response()->json([
                'status' => Response::HTTP_NOT_FOUND,
                'message' => 'Article not found'
            ], Response::HTTP_NOT_FOUND);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'content' => 'required',
            'publish_date' => 'required'
        ]);

        /** Jika ada data yang tidak valid return error response berikut */
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        try {
            /** Jika data valid lakukan proses update data */
            $article->update([
                'title' => $request->title,
                'content' => $request->content,
                'publish_date' => Carbon::create($request->publish_date)->toDateString(),
            ]);

            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => 'Data updated'
            ], Response::HTTP_OK);
        } catch (Exception $e) {
        /** Jika proses update data gagal return response JSON berikut */
            Log::error('Error update data :' .  $e->getMessage());

            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Failed stored data to db'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
