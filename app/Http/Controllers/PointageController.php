<?php

namespace App\Http\Controllers;

use App\Models\PointageModel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class PointageController extends Controller
{

   // Basculer le statut d'un employé (absent -> présent ou présent -> absent)
    public function toggleStatus($id)
    {
        \Log::info('Requête reçue pour l’utilisateur : ' . $id);

        $pointage = PointageModel::firstOrCreate(
            ['users_id' => $id, 'date' => today()],
            ['status' => 'absent', 'punch_in' => now()]
        );

        \Log::info('Statut actuel avant modification : ' . $pointage->status);

        if ($pointage->status === 'absent') {
            $pointage->status = 'present';
            $pointage->punch_in = now();
        } else {
            $pointage->punch_out = now();
            $pointage->status = 'absent';
        }

        $pointage->save();

        \Log::info('Statut mis à jour : ' . $pointage->status);

        return response()->json([
            'status' => $pointage->status,
            'punch_in' => $pointage->punch_in,
            'punch_out' => $pointage->punch_out,
        ], 200);
    }


    // Récupérer le statut actuel d'un employé pour aujourd'hui
    public function getStatus($id)
    {
        $pointage = PointageModel::where('users_id', $id)->whereDate('date', today())->first();

        if (!$pointage) {
            return response()->json(['status' => 'absent'], 200);
        }

        return response()->json(['status' => $pointage->status], 200);
    }
    public function index(Request $request)
    {
        try {
            $employee = auth()->user();

            // Pagination optionnelle (par défaut 10 entrées par page)
            $perPage = $request->get('per_page', 10);

            // Récupérer les pointages
            $punches = $employee->punches()
                ->orderBy('punch_in', 'desc')
                ->paginate($perPage);

            // Transformer les données pour inclure des formats de date lisibles
            $formattedPunches = $punches->getCollection()->map(function ($punch) {
                // Convertir punch_in et punch_out en objets Carbon
                $punchIn = $punch->punch_in ? \Carbon\Carbon::parse($punch->punch_in) : null;
                $punchOut = $punch->punch_out ? \Carbon\Carbon::parse($punch->punch_out) : null;

                return [
                    'id' => $punch->id,
                    'punch_in' => $punchIn ? [
                        'datetime' => $punchIn->toDateTimeString(),
                        'formatted' => $punchIn->format('d/m/Y H:i:s'),
                    ] : null,
                    'punch_out' => $punchOut ? [
                        'datetime' => $punchOut->toDateTimeString(),
                        'formatted' => $punchOut->format('d/m/Y H:i:s'),
                    ] : null,
                    'status' => $punch->status,
                    // Calculer la durée si punch_out existe
                    'duration' => $punch->punch_out
                        ? Carbon::parse($punch->punch_in)->diffForHumans(Carbon::parse($punch->punch_out), true)
                        : null,
                ];
            });

            // Mettre à jour les données paginées
            $punches->setCollection($formattedPunches);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'employee' => [
                        'id' => $employee->id,
                        'full_name' => $employee->prenom . ' ' . $employee->nom,
                    ],
                    'punches' => $punches->items(),
                    'pagination' => [
                        'total' => $punches->total(),
                        'per_page' => $punches->perPage(),
                        'current_page' => $punches->currentPage(),
                        'last_page' => $punches->lastPage(),
                        'from' => $punches->firstItem(),
                        'to' => $punches->lastItem(),
                    ],
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la récupération des pointages',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function store(Request $request)
    {
        try {
            $employee = auth()->user();
            $lastPunch = $employee->punches()->latest()->first();

            // Vérifier les heures autorisées (6h à 23h)
            if (now()->hour < 6 || now()->hour >= 23) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Les pointages ne sont autorisés qu\'entre 6h et 23h.'
                ], 403);
            }

            if ($lastPunch && $lastPunch->status === 'present' && $lastPunch->punch_out === null) {
                // Mettre à jour le pointage existant
                $lastPunch->update([
                    'punch_out' => now(),
                    'status' => 'absent',
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Pointage de sortie enregistré',
                    'data' => [
                        'punch' => $lastPunch,
                        'currentStatus' => 'absent'
                    ]
                ]);
            } else {
                // Créer un nouveau pointage
                $punch = $employee->punches()->create([
                    'punch_in' => now(),
                    'status' => 'present',
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Pointage d\'entrée enregistré',
                    'data' => [
                        'punch' => $punch,
                        'currentStatus' => 'present'
                    ]
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors du pointage',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getPunchStatus($employeeId)
    {
        try {
            $employee = User::findOrFail($employeeId);
            $lastPunch = $employee->punches()->latest()->first();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'employeeId' => $employeeId,
                    'currentStatus' => $lastPunch ? $lastPunch->status : 'absent',
                    'lastPunch' => $lastPunch
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Employé non trouvé',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    public function adminIndex(Request $request)
    {
        try {
            // Vérifier si l'utilisateur est admin
            if (!auth()->user()->is_admin) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Accès non autorisé'
                ], 403);
            }

            // Récupérer les paramètres de filtrage et pagination
            $perPage = $request->get('per_page', 10);
            $date = $request->get('date');
            $status = $request->get('status');
            $employeeId = $request->get('employee_id');

            // Requête de base avec les relations
            $query = Punch::with('employee')
                ->orderBy('punch_in', 'desc');

            // Appliquer les filtres si présents
            if ($date) {
                $query->whereDate('punch_in', $date);
            }
            if ($status) {
                $query->where('status', $status);
            }
            if ($employeeId) {
                $query->where('employee_id', $employeeId);
            }

            // Récupérer les pointages avec pagination
            $punches = $query->paginate($perPage);

            // Formater les données
            $formattedPunches = $punches->map(function ($punch) {
                return [
                    'id' => $punch->id,
                    'employee' => [
                        'id' => $punch->employee->id,
                        'name' => $punch->employee->name,
                        'email' => $punch->employee->email,
                    ],
                    'punch_in' => [
                        'datetime' => $punch->punch_in,
                        'formatted' => $punch->punch_in->format('d/m/Y H:i:s'),
                    ],
                    'punch_out' => $punch->punch_out ? [
                        'datetime' => $punch->punch_out,
                        'formatted' => $punch->punch_out->format('d/m/Y H:i:s'),
                    ] : null,
                    'status' => $punch->status,
                    'duration' => $punch->punch_out
                        ? $punch->punch_in->diffForHumans($punch->punch_out, true)
                        : null,
                    'created_at' => $punch->created_at->format('d/m/Y H:i:s'),
                ];
            });

            // Statistiques globales
            $stats = [
                'total_present' => Punch::where('status', 'present')->count(),
                'total_absent' => Punch::where('status', 'absent')->count(),
                'today_punches' => Punch::whereDate('punch_in', now())->count(),
            ];

            return response()->json([
                'status' => 'success',
                'data' => [
                    'punches' => $formattedPunches,
                    'stats' => $stats,
                    'pagination' => [
                        'total' => $punches->total(),
                        'per_page' => $punches->perPage(),
                        'current_page' => $punches->currentPage(),
                        'last_page' => $punches->lastPage(),
                        'from' => $punches->firstItem(),
                        'to' => $punches->lastItem(),
                    ],
                    'filters' => [
                        'date' => $date,
                        'status' => $status,
                        'employee_id' => $employeeId,
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la récupération des pointages',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
