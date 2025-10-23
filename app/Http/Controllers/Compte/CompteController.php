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

/**
 * @OA\Info(
 *     title="API de Gestion des Comptes",
 *     version="1.0.0",
 *     description="API pour la gestion des comptes bancaires"
 * )
 * @OA\Server(
 *     url="http://localhost:8000/api/v1",
 *     description="Serveur de développement"
 * )
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */
class CompteController extends Controller
{
    use ApiResponse;

    /**
     * @OA\Get(
     *     path="/comptes",
     *     summary="Lister les comptes",
     *     description="Récupère la liste des comptes avec pagination et filtres",
     *     operationId="getComptes",
     *     tags={"Comptes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Nombre d'éléments par page (max 100)",
     *         required=false,
     *         @OA\Schema(type="integer", default=10, maximum=100)
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Type de compte (epargne, cheque)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"epargne", "cheque"})
     *     ),
     *     @OA\Parameter(
     *         name="statut",
     *         in="query",
     *         description="Statut du compte",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Recherche par titulaire ou numéro de compte",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="Champ de tri",
     *         required=false,
     *         @OA\Schema(type="string", enum={"dateCreation", "solde", "titulaire"}, default="dateCreation")
     *     ),
     *     @OA\Parameter(
     *         name="order",
     *         in="query",
     *         description="Ordre de tri",
     *         required=false,
     *         @OA\Schema(type="string", enum={"asc", "desc"}, default="desc")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Liste des comptes récupérée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="succes", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Comptes récupérés avec succès"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(ref="#/components/schemas/Compte")
     *                 ),
     *                 @OA\Property(property="meta", ref="#/components/schemas/Meta")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non autorisé",
     *         @OA\JsonContent(
     *             @OA\Property(property="succes", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Non autorisé")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur",
     *         @OA\JsonContent(
     *             @OA\Property(property="succes", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Erreur interne du serveur")
     *         )
     *     )
     * )
     */
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
