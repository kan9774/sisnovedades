<?php

namespace App\Http\Controllers;

use App\Models\Guard;
use App\Models\Conductor;
use App\Models\Vehiculo;
use App\Models\SalidaVehiculo;
use App\Models\News;
use App\Models\Vuelo;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function __construct()
    {
        // Aseguramos que solo usuarios autenticados entren al dashboard
        $this->middleware('auth');
    }

    public function index()
    {
        // 1. Guardia del día
        $guardiaHoy = Guard::with(['capitan', 'oficial'])
            ->where('date', date('Y-m-d'))
            ->first();

        // 2. Conteos y Estadísticas del Box Superior
        $vehiculosEnRuta = SalidaVehiculo::whereNull('hora_entra')
            ->whereHas('guardia', function($q) {
                $q->where('status', 'open');
            })->count();

        $totalConductores = Conductor::where('activo', true)->count();
        
        // Vuelos activos (vuelos registrados en el día)
        $vuelosActivos = Vuelo::where('fecha', date('Y-m-d'))->count(); 

        // 3. Alertas de documentación de conductores (Vencen en los próximos 15 días o ya vencidos)
        $conductoresAlertas = Conductor::where('activo', true)
            ->where(function($q) {
                $q->where('fecha_vencimiento_licencia', '<=', now()->addDays(15))
                  ->orWhere('fecha_vencimiento_carne_salud', '<=', now()->addDays(15))
                  ->orWhere('fecha_vencimiento_carne_habilitante', '<=', now()->addDays(15));
            })->take(4)->get();

        // 4. Actividad Reciente para las Tablas del Dashboard
        $ultimasSalidas = SalidaVehiculo::with(['vehiculo', 'conductor'])
            ->latest()
            ->take(5)
            ->get();

        $ultimasNovedades = News::with('escribiente')
            ->latest()
            ->take(5)
            ->get();

        $ultimosVuelos = Vuelo::withCount('palomas')
            ->latest('fecha')
            ->take(4)
            ->get();

        // Retornamos tu vista original pasando toda la data empaquetada
        return view('admin.index', compact(
            'guardiaHoy', 
            'vehiculosEnRuta', 
            'totalConductores', 
            'vuelosActivos',
            'conductoresAlertas',
            'ultimasSalidas',
            'ultimasNovedades',
            'ultimosVuelos'
        ));
    }
}