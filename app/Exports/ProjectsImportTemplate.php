<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Empty spreadsheet with the column headings expected by ProjectsImport.
 */
class ProjectsImportTemplate implements FromArray, ShouldAutoSize, WithHeadings, WithStyles
{
    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return [
            __('Project Code'),
            __('Year'),
            __('Work Code'),
            __('On Road'),
            __('Start Road'),
            __('End Road'),
            __('Pipe Type'),
            __('Pipe Diameter'),
            __('Pipe Length'),
            __('Received Date').' (YYYY-MM-DD)',
        ];
    }

    /**
     * @return array<int, array<int, mixed>>
     */
    public function array(): array
    {
        return [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
