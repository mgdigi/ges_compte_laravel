<?php

namespace App\Http\Controllers\Compte;

use App\Exceptions\DatabaseQueryException;
use App\Http\Controllers\Controller;
use App\Http\Resources\CompteRessource;
use App\Http\Resources\MetaRessource;
use Illuminate\Http\Request;
use App\Models\Compte;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;


class CompteController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        try {
            $user = Auth::user();

            $cacheKey =  'comptes_' . md5(json_encode($request->all()));

            $cacheData = Cache::get($cacheKey);

            if ($cacheData) {
                return $this->successResponse($cacheData);
            }


            $comptes = Compte::filtrerComptes($request->all(), $user)
                ->paginate(min($request->get('limit', 10), 100))
                ->appends($request->all());

            $data = [
                'data' => CompteRessource::collection($comptes),
                'meta' => new MetaRessource($comptes)
            ];

            Cache::put($cacheKey, CompteRessource::collection($comptes), now()->addMinutes(10));


            return $this->successResponse($data, "Comptes récupérés avec succès");

        } catch (\Exception $e) {
            throw new DatabaseQueryException($e->getMessage());
        }
    }
}
