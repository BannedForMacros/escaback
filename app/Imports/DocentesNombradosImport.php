<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\{ToCollection, WithHeadingRow};
use Carbon\CarbonImmutable;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

use App\Models\{
    DocenteNombrado,
    Categoria,
    Regimen,
    RegimenPensionario,
    Facultad,
    DocenteCatRegHist,
    DocenteCondicion,
    DocenteProcedencia,
    Renacyt
};

class DocentesNombradosImport implements ToCollection, WithHeadingRow
{
    /* =============================================================
       1. Recorremos cada fila del Excel
       ============================================================= */
    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {

            /* 0. Si la fila no tiene DNI, asumimos fin de datos  */
            if (empty($row['dni'])) break;

            /* 1. Normalizar DNI a 8 dígitos -------------------- */
            $dni = str_pad((string) $row['dni'], 8, '0', STR_PAD_LEFT);

            /* 2. Catálogos ------------------------------------- */
            $categoriaId = $this->idCategoria($row['categoria'] ?? null);
            $regimenId   = $this->idRegimen($row['regimen_de_dedicacion'] ?? null);
            $regPensId   = $this->idRegimenPens($row['regimen_pensionario'] ?? null);
            $facultadId  = $this->idFacultad($row['facultad'] ?? null);

            /* 3. Año de ingreso (maneja números Excel) --------- */
            $anioIngreso = $this->anio($row['ano_de_ingreso_a_la_unprg'] ?? null);

            /* 4. Insertar o actualizar docente ----------------- */
            $docente = DocenteNombrado::updateOrCreate(
                ['dni' => $dni],
                [
                    'apellido_paterno'       => $row['apellido_paterno']       ?? null,
                    'apellido_materno'       => $row['apellido_materno']       ?? null,
                    'nombres'                => $row['nombres']               ?? null,
                    'mayor_grado_academico'  => $row['mayor_grado_academico'] ?? null,

                    'categoria_id'           => $categoriaId,
                    'regimen_id'             => $regimenId,
                    'regimen_pensionario_id' => $regPensId,

                    'anio_ingreso_unprg'     => $anioIngreso,
                    'facultad_id'            => $facultadId,
                    'fecha_nacimiento'       => $this->fecha($row['fecha_de_nacimiento'] ?? null),

                    'direccion'              => $row['direccion']              ?? null,
                    'numero_celular'         => $row['numero_de_celular']      ?? null,
                    'correo_institucional'   => $row['correo_institucional']   ?? null,
                ]
            );

            /* 5. Historial categoría‑régimen 001‑006 ----------- */
                for ($i = 1; $i <= 6; $i++) {
                    $idx = sprintf('%03d', $i);
                    $raw = $row["categoria_y_regimen_$idx"] ?? null;
                    if (!$raw) continue;

                    // Limpieza
                    $cr = strtoupper(preg_replace('/\s+/', '', $raw));

                    // Al menos 2 caracteres: 2 para categoría, opcional régimen
                    if (strlen($cr) < 2) continue;          // ← cambiado

                    $catSig = substr($cr, 0, 2);
                    $regSig = substr($cr, 2);               // puede quedar ''

                    $catId = $this->idCategoria($catSig);

                    // Si no hay sigla de régimen, usamos/creamos 'SN' (= Sin Régimen)
                    if ($regSig) {
                        $regId = $this->idRegimen($regSig);
                    } else {
                        $regId = Regimen::firstOrCreate(['siglas' => 'SN'], ['descripcion' => 'Sin Régimen'])->id;
                    }

                    /* --- Resolución y fecha --- */
                    $res = $i === 1 ? $row['resolucion_de_nombramiento']
                                    : $row["resolucion_de_ascenso_$idx"] ?? null;
                    $fec = $i === 1 ? $row['fecha_de_nombramiento']
                                    : $row["fecha_de_ascenso_$idx"]      ?? null;

                    $res = $res ?: 'SIN RESOLUCION';

                    DocenteCatRegHist::updateOrCreate(
                        [
                            'docente_id'      => $docente->id,
                            'categoria_id'    => $catId,
                            'regimen_id'      => $regId,                  // ya nunca NULL
                            'tipo_resolucion' => $i === 1 ? 'NOMBRAMIENTO' : 'ASCENSO',
                        ],
                        [
                            'nro_resolucion'   => $res,
                            'fecha_resolucion' => $this->fecha($fec),
                        ]
                    );
                }


            /* 6. Condiciones 001‑003 --------------------------- */
            for ($i = 1; $i <= 3; $i++) {
                $idx  = sprintf('%03d', $i);
                $cond = $row["condicion_$idx"] ?? null;
                if (!$cond) continue;

                DocenteCondicion::updateOrCreate(
                    [
                        'docente_id' => $docente->id,
                        'condicion'  => $cond,
                    ],
                    [
                        'nro_resolucion'   => $row["resolucion_condicion_$idx"] ?? null,
                        'fecha_resolucion' => $this->fecha($row["fecha_condicion_$idx"] ?? null),
                    ]
                );
            }

            /* 7. Procedencias 001‑002 -------------------------- */
            for ($i = 1; $i <= 2; $i++) {
                $idx  = sprintf('%03d', $i);
                $proc = $row["procedencia_$idx"] ?? null;
                if (!$proc) continue;

                DocenteProcedencia::updateOrCreate(
                    [
                        'docente_id' => $docente->id,
                        'procedencia'=> $proc,
                    ],
                    [
                        'nro_resolucion'   => $row["resolucion_procedencia_$idx"] ?? null,
                        'fecha_resolucion' => $this->fecha($row["fecha_de_procedencia_$idx"] ?? null),
                        'comentario'       => $row["comentario_$idx"] ?? null,
                    ]
                );
            }

            /* 8. RENACYT -------------------------------------- */
            if (!empty($row['nivel_renacyt'])) {
                Renacyt::updateOrCreate(
                    ['docente_id' => $docente->id],
                    [
                        'nivel_renacyt'  => $row['nivel_renacyt'],
                        'nro_resolucion' => $row['resolucion_de_investigador'] ?? null,
                    ]
                );
            }
        }
    }

    /* =============================================================
       2. Helpers
       ============================================================= */

    private function idCategoria(?string $siglas): ?int
    {
        return $siglas
            ? Categoria::firstOrCreate(['siglas'=>strtoupper(trim($siglas))])->id
            : null;
    }

    private function idRegimen(?string $siglas): ?int
    {
        return $siglas
            ? Regimen::firstOrCreate(['siglas'=>strtoupper(trim($siglas))])->id
            : null;
    }

    private function idRegimenPens(?string $desc): ?int
    {
        return $desc
            ? RegimenPensionario::firstOrCreate(['descripcion'=>trim($desc)])->id
            : null;
    }

    private function idFacultad(?string $siglas): ?int
    {
        return $siglas
            ? Facultad::firstOrCreate(
                ['siglas'=>strtoupper(trim($siglas))],
                ['nombre'=>$siglas]
              )->id
            : null;
    }

    /** YYYY‑MM‑DD o null */
    private function fecha($valor): ?string
    {
        if (!$valor) return null;

        return is_numeric($valor)
            ? CarbonImmutable::instance(ExcelDate::excelToDateTimeObject($valor))->format('Y-m-d')
            : (function () use ($valor) {
                try { return CarbonImmutable::parse($valor)->format('Y-m-d'); }
                catch (\Throwable) { return null; }
            })();
    }

    /** Devuelve un entero Año manejando serial Excel o string */
    private function anio($valor): ?int
    {
        if (!$valor) return null;

        if (is_numeric($valor) && $valor > 1900 && $valor < 60000) {
            return (int) ExcelDate::excelToDateTimeObject($valor)->format('Y');
        }

        if (is_numeric($valor)) return (int) $valor;

        try   { return (int) CarbonImmutable::parse($valor)->format('Y'); }
        catch (\Throwable) { return null; }
    }
}
