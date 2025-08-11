<?php

namespace App\Imports;

use App\Models\{
    GradoTitulo,
    DocenteNombrado,
    DocenteContratado
};
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\{
    ToModel,
    WithHeadingRow,
    WithBatchInserts,
    WithChunkReading,
    Importable
};
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class GradosTitulosImport implements
    ToModel,
    WithHeadingRow,
    WithBatchInserts,
    WithChunkReading
{
    use Importable;

    /* ==========================================================
       CONFIGURACIÓN BÁSICA
       ========================================================== */

    /** La primera fila (1) contiene ya los encabezados reales  */
    public function headingRow(): int
    {
        return 1;
    }

    /** Procesar en lotes/chunks para optimizar memoria */
    public function batchSize(): int { return 200; }
    public function chunkSize(): int { return 200; }

    /* ==========================================================
       PROCESAR UNA FILA
       ========================================================== */
    public function model(array $row)
    {
        /* ---------- 1. Normalizar/validar DNI ------------------ */
        $dni = $this->normalizeDni($row['numero_documento'] ?? null);

        if ($dni === null) {
            Log::warning('[GT‑Import] fila sin “NUMERO DOCUMENTO”, omitida');
            return null;
        }

        /* ---------- 2. Buscar docente (nombrado / contratado) -- */
        $docNombrado   = DocenteNombrado  ::where('dni', $dni)->first();
        $docContratado = $docNombrado
                       ? null
                       : DocenteContratado::where('dni', $dni)->first();

        if (!$docNombrado && !$docContratado) {
            Log::info("[GT‑Import] DNI {$dni}: docente NO hallado → fila omitida");
            return null;
        }

        /* ---------- 3. Mapear columnas académicas -------------- */
        $gt = new GradoTitulo([
            'docente_nombrado_id'   => $docNombrado?->id,
            'docente_contratado_id' => $docContratado?->id,

            'sexo'                      => $row['sexo']            ?? null,
            'nacionalidad'              => $row['nacionalidad']    ?? null,
            'documento'                 => $row['documento']       ?? null,
            'numero_documento'          => $dni,

            'mayor_grado_academico'     => $row['mayor_grado_academico'] ?? null,

            'bachiller'                 => $row['bachiller']        ?? null,
            'universidad_bach'          => $row['universidad_bach'] ?? null,
            'pais_bach'                 => $row['pais_bach']        ?? null,

            'titulo'                    => $row['titulo']           ?? null,
            'universidad_titu'          => $row['universidad_titu'] ?? null,
            'pais_titu'                 => $row['pais_titu']        ?? null,
            'resolucion_reconocimiento' => $row['resolucion_reconocimiento'] ?? null,

            'maestria'                  => $row['maestria']         ?? null,
            'universidad_maes'          => $row['universidad_maes'] ?? null,
            'pais_maes'                 => $row['pais_maes']        ?? null,

            'doctorado'                 => $row['doctorado']        ?? null,
            'universidad_doc'           => $row['universidad_doc']  ?? null,
            'pais_doc'                  => $row['pais_doc']         ?? null,

            'segunda_especialidad'      => $row['segunda_especialidad'] ?? null,
            'universidad_espe'          => $row['universidad_espe'] ?? null,
            'pais_espe'                 => $row['pais_espe']        ?? null,
        ]);

        return $gt;   // siempre se inserta un nuevo registro
    }

    /* ==========================================================
       HELPERS
       ========================================================== */

    /**
     * Convierte el valor leído de “NUMERO DOCUMENTO” a un
     * string de 8 dígitos sin caracteres extra.
     *
     * @return string|null  DNI limpio o null si no válido
     */
    private function normalizeDni($valor): ?string
    {
        if ($valor === null || $valor === '') {
            return null;
        }

        // Caso: valor numérico (celda Excel en formato “Número”)
        if (is_numeric($valor)) {
            $valor = (string) (int) $valor; // quita .0
        }

        // Eliminar espacios y todo lo que no sea dígito
        $valor = preg_replace('/\D/', '', $valor);

        // Pad a la izquierda hasta 8 (si viniera 7 dígitos, etc.)
        $valor = str_pad($valor, 8, '0', STR_PAD_LEFT);

        return strlen($valor) === 8 ? $valor : null;
    }
}
