<?php

namespace App\Http\Controllers;

use App\Exports\VentasExport;
use App\Jobs\DownloadExcelVentasAllJob;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportExcelController extends Controller
{
    /**
     * Exportar en EXCEL todas las ventas
     */
    public function exportExcelVentasAll(): BinaryFileResponse
    {
        $filename = 'ventas_' . now()->format('Y_m_d_His') . '.xlsx';
        return Excel::download(new VentasExport, $filename);
    }
}
