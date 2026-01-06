<?php

namespace App\Http\Controllers;

use App\Models\Residente;
use App\Models\User;
use App\Models\Vivienda;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ResidenteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Residente::with(['user', 'vivienda'])
            ->where('activo', true);

        // Búsqueda
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nombres', 'like', "%{$search}%")
                  ->orWhere('apellido_paterno', 'like', "%{$search}%")
                  ->orWhere('apellido_materno', 'like', "%{$search}%")
                  ->orWhere('ci', 'like', "%{$search}%");
            });
        }

        // Filtro por tipo de residente
        if ($request->has('tipo') && $request->tipo != 'TODOS') {
            $query->where('tipo_residente', $request->tipo);
        }

        $residentes = $query->paginate(15)->withQueryString();

        return Inertia::render('Residentes/Index', [
            'residentes' => $residentes,
            'filters' => $request->only(['search', 'tipo'])
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $viviendas = Vivienda::where('activo', true)
            ->orderBy('numero')
            ->get();

        return Inertia::render('Residentes/Create', [
            'viviendas' => $viviendas
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'nombre_completo' => 'required|string|max:255',
            'vivienda_id' => 'required|exists:viviendas,id',
            'nombres' => 'required|string|max:100',
            'apellido_paterno' => 'required|string|max:100',
            'apellido_materno' => 'nullable|string|max:100',
            'ci' => 'required|string|max:20|unique:residentes,ci',
            'fecha_nacimiento' => 'nullable|date',
            'telefono' => 'nullable|string|max:20',
            'celular' => 'nullable|string|max:20',
            'tipo_residente' => 'required|in:PROPIETARIO,INQUILINO,FAMILIAR',
            'fotografia' => 'nullable|image|max:2048',
        ]);

        // Crear usuario
        $user = User::create([
            'name' => $validated['nombre_completo'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'rol' => $validated['tipo_residente'] === 'PROPIETARIO' ? 'PROPIETARIO' : 'RESIDENTE',
            'activo' => true,
        ]);

        // Manejar foto si existe
        $fotoPath = null;
        if ($request->hasFile('fotografia')) {
            $fotoPath = $request->file('fotografia')->store('residentes', 'public');
        }

        // Crear residente
        Residente::create([
            'user_id' => $user->id,
            'vivienda_id' => $validated['vivienda_id'],
            'nombres' => $validated['nombres'],
            'apellido_paterno' => $validated['apellido_paterno'],
            'apellido_materno' => $validated['apellido_materno'],
            'ci' => $validated['ci'],
            'fecha_nacimiento' => $validated['fecha_nacimiento'],
            'telefono' => $validated['telefono'],
            'celular' => $validated['celular'],
            'fotografia' => $fotoPath,
            'tipo_residente' => $validated['tipo_residente'],
            'activo' => true,
        ]);

        return redirect()->route('residentes.index')
            ->with('success', 'Residente creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Residente $residente)
    {
        $residente->load(['user', 'vivienda', 'reuniones']);

        return Inertia::render('Residentes/Show', [
            'residente' => $residente
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Residente $residente)
    {
        $residente->load('user');

        $viviendas = Vivienda::where('activo', true)
            ->orderBy('numero')
            ->get();

        return Inertia::render('Residentes/Edit', [
            'residente' => $residente,
            'viviendas' => $viviendas
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Residente $residente)
    {
        $validated = $request->validate([
            'email' => 'required|email|unique:users,email,' . $residente->user_id,
            'nombre_completo' => 'required|string|max:255',
            'vivienda_id' => 'required|exists:viviendas,id',
            'nombres' => 'required|string|max:100',
            'apellido_paterno' => 'required|string|max:100',
            'apellido_materno' => 'nullable|string|max:100',
            'ci' => 'required|string|max:20|unique:residentes,ci,' . $residente->id,
            'fecha_nacimiento' => 'nullable|date',
            'telefono' => 'nullable|string|max:20',
            'celular' => 'nullable|string|max:20',
            'tipo_residente' => 'required|in:PROPIETARIO,INQUILINO,FAMILIAR',
            'fotografia' => 'nullable|image|max:2048',
        ]);

        // Actualizar usuario
        $residente->user->update([
            'name' => $validated['nombre_completo'],
            'email' => $validated['email'],
            'rol' => $validated['tipo_residente'] === 'PROPIETARIO' ? 'PROPIETARIO' : 'RESIDENTE',
        ]);

        // Si hay contraseña nueva
        if ($request->filled('password')) {
            $request->validate(['password' => 'min:6']);
            $residente->user->update([
                'password' => Hash::make($request->password)
            ]);
        }

        // Manejar foto si existe
        if ($request->hasFile('fotografia')) {
            // Eliminar foto anterior si existe
            if ($residente->fotografia) {
                Storage::disk('public')->delete($residente->fotografia);
            }
            $validated['fotografia'] = $request->file('fotografia')->store('residentes', 'public');
        }

        // Actualizar residente
        $residente->update([
            'vivienda_id' => $validated['vivienda_id'],
            'nombres' => $validated['nombres'],
            'apellido_paterno' => $validated['apellido_paterno'],
            'apellido_materno' => $validated['apellido_materno'],
            'ci' => $validated['ci'],
            'fecha_nacimiento' => $validated['fecha_nacimiento'],
            'telefono' => $validated['telefono'],
            'celular' => $validated['celular'],
            'tipo_residente' => $validated['tipo_residente'],
            'fotografia' => $validated['fotografia'] ?? $residente->fotografia,
        ]);

        return redirect()->route('residentes.index')
            ->with('success', 'Residente actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Residente $residente)
    {
        // Soft delete
        $residente->update(['activo' => false]);
        $residente->user->update(['activo' => false]);

        return redirect()->route('residentes.index')
            ->with('success', 'Residente desactivado exitosamente.');
    }
}
