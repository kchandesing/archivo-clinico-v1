<?php

namespace App\Http\Controllers\Util;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class CatalogosController extends Controller
{
    /**
     * Helper interno para leer los JSON guardados en storage.
     */
    private function getJsonData(string $filename): array
    {
        $path = storage_path("app/catalogos/{$filename}.json");
        
        if (!File::exists($path)) {
            return ['datos' => []];
        }

        return json_decode(File::get($path), true);
    }

    public function getVialidades()
    {
        return response()->json($this->getJsonData('tipo_vialidad'));
    }

    public function getAfiliaciones()
    {
        return response()->json($this->getJsonData('afiliacion'));
    }

    public function getEstados()
    {
        return response()->json($this->getJsonData('inegi_estados'));
    }

    public function getMunicipios(Request $request)
    {
        $estadoId = $request->query('estado');
        $catalogos = $this->getJsonData('inegi_municipios');

        // Filtrar de forma veloz en la RAM de tu servidor usando el cve_ent de INEGI
        $filtrados = array_filter($catalogos['datos'] ?? [], function ($item) use ($estadoId) {
            return $item['cve_ent'] === $estadoId;
        });

        return response()->json(['datos' => array_values($filtrados)]);
    }

    public function getLocalidades(Request $request)
    {
        $estadoId = $request->query('estado');
        $municipioId = $request->query('municipio');
        $catalogos = $this->getJsonData('inegi_localidades');

        // Filtrar combinando Estado y Municipio para asegurar exactitud
        $filtrados = array_filter($catalogos['datos'] ?? [], function ($item) use ($estadoId, $municipioId) {
            return $item['cve_ent'] === $estadoId && $item['cve_mun'] === $municipioId;
        });

        return response()->json(['datos' => array_values($filtrados)]);
    }
}
