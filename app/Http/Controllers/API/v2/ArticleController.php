<?php

namespace App\Http\Controllers\API\v2;

use App\Http\Controllers\Controller;
use App\Http\Resources\ArticleCollection;
use App\Http\Resources\ArticleResource;
use App\Models\Article;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class ArticleController extends Controller
{
    public function index(Request $request)
    {

        $query = Article::query()->latest('publish_date');
        $keyword = $request->input('title');

        if ($keyword) {
            $query->where('title', 'like', "%{$keyword}%");
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

    public function store(Request $request)
    {

        /**
         * Cek bearer token apakah ada atau tidak
         */
        $this->unauthenticated($request);
        // $token = $request->bearerToken();
        // if (!$token) {
        //     return response()->json([
        //         'message' => 'Token not exists, please login first'
        //     ], Response::HTTP_UNAUTHORIZED);
        // }

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
        $this->unauthenticated($request);
        // $token = $request->bearerToken();
        // if (!$token) {
        //     return response()->json([
        //         'message' => 'Token not exists update failed, please login first'
        //     ], Response::HTTP_UNAUTHORIZED);
        // }

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

    public function destroy(Request $request, $id)
    {
        $this->unauthenticated($request);
        // $token = $request->bearerToken();
        // if (!$token) {
        //     return response()->json([
        //         'message' => 'Token not exists, please login first'
        //     ], Response::HTTP_UNAUTHORIZED);
        // }

        /** Cari data berdasarkan data id yang di dapatkan */
        $article = Article::find($id);

        try {
            /** Lakukan proses delete data dan return response
             *  Dalam bentuk JSON
             */
            $article->delete();
            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => 'Article deleted'
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error('Error update data :' .  $e->getMessage());

            /** Jika gagal tampilkan response error berikut */
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Failed delete data'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /** Method yang akan dijalankan ketika user belum terautentikasi atau token
     *  tidak ada
     */
    public function unauthenticated(Request $request)
    {
        $token = $request->bearerToken();
        if (!$token) {
            return response()->json([
                'message' => 'Token not exists, please login first'
            ], Response::HTTP_UNAUTHORIZED);
        }
    }
}
