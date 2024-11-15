<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class PointageController extends Controller
{
    public function index(Request $request)
    {
        try {
            $employee = auth()->user();

            // Pagination optionnelle (par défaut 10 entrées par page)
            $perPage = $request->get('per_page', 10);

            $punches = $employee->punches()
                ->orderBy('punch_in', 'desc')
                ->paginate($perPage);

            // Transformer les données pour inclure des formats de date lisibles
            $formattedPunches = $punches->map(function ($punch) {
                return [
                    'id' => $punch->id,
                    'punch_in' => [
                        'datetime' => $punch->punch_in,
                        'formatted' => $punch->punch_in->format('d/m/Y H:i:s'),
                    ],
                    'punch_out' => $punch->punch_out ? [
                        'datetime' => $punch->punch_out,
                        'formatted' => $punch->punch_out->format('d/m/Y H:i:s'),
                    ] : null,
                    'status' => $punch->status,
                    // Calculer la durée si punch_out existe
                    'duration' => $punch->punch_out
                        ? $punch->punch_in->diffForHumans($punch->punch_out, true)
                        : null,
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => [
                    'employee' => [
                        'id' => $employee->id,
                        'name' => $employee->name,
                    ],
                    'punches' => $formattedPunches,
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
