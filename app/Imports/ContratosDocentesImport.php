<?php

namespace App\Imports;

use App\Models\{
    Facultad, Programa, CicloAcademico,
    DocenteContratado, ContratoDocente, TipoContratoDocente
};
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\{ToCollection, WithHeadingRow};
use PhpOffice\PhpSpreadsheet\Shared\Date as XLDate;
use Carbon\Carbon;

class ContratosDocentesImport implements ToCollection, WithHeadingRow
{
    private CicloAcademico $ciclo;

    public function __construct(int $anio, string $semestre)
    {
        $this->ciclo = CicloAcademico::firstOrCreate(
            ['anio' => $anio, 'semestre' => $semestre]
        );
    }

    public function collection(Collection $rows): void
    {
        DB::transaction(function () use ($rows) {

            foreach ($rows as $row) {
                /*------------------------------------------------------
                 | 1. FACULTAD
                 *-----------------------------------------------------*/
                $facultad = Facultad::firstOrCreate(
                    ['siglas' => trim($row['facultad'])],
                    ['nombre' => trim($row['facultad'])],                   
                );

                /* 2. PROGRAMA */
                $programa = Programa::firstOrCreate(
                    [
                        'facultad_id' => $facultad->id,
                        'nombre'      => trim($row['programa'])
                    ]
                );

                /* 3. DOCENTE */
                $docente = DocenteContratado::updateOrCreate(
                    ['dni' => trim($row['dni'])],
                    [
                        'apellido_paterno'      => trim($row['apellido_paterno']),
                        'apellido_materno'      => trim($row['apellido_materno']),
                        'nombres'               => trim($row['nombres']),
                        'sexo'                  => $row['sexo'] ?: null,
                        'mayor_grado_academico' => $row['mayor_grado_academico'] ?: null,
                    ]
                );

                /* 4. TIPO DE CONTRATO (puede venir vacío) */
                $tipoObj = null;
                if (!empty($row['tipo'])) {
                    $tipoObj = TipoContratoDocente::firstOrCreate(
                        ['tipo' => trim($row['tipo'])],
                        ['nombre' => null]
                    );
                }

                /* 5. CONTRATO */
                ContratoDocente::updateOrCreate(
                    [
                        'docente_id' => $docente->id,
                        'ciclo_id'   => $this->ciclo->id
                    ],
                    [
                        'categoria'          => trim($row['categoria']),
                        'regimen_dedicacion' => trim($row['regimen_de_dedicacion']),
                        'fecha_ingreso'      => $this->parseDate($row['fecha_de_ingreso']),
                        'fecha_fin'          => $this->parseDate($row['fecha_de_fin']),
                        'resolucion'         => trim($row['resolucion_contrato']),
                        'facultad_id'        => $facultad->id,
                        'programa_id'        => $programa->id,
                        'tipo_id'            => $tipoObj?->id,            // null si no hay tipo
                        'comentario'         => $row['comentario'] ?? null
                    ]
                );
            }
        });
    }

    /** Convierte fecha Excel (num) o texto dd/mm/yyyy a Y-m-d */
    private function parseDate($value): ?string
    {
        if ($value === null || trim((string)$value) === '') {
            return null;
        }
        if (is_numeric($value)) {
            return XLDate::excelToDateTimeObject($value)->format('Y-m-d');
        }
        return Carbon::createFromFormat('d/m/Y', trim($value))->format('Y-m-d');
    }
}
