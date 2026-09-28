<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Exports the dashboard's "in-progress projects by year and assignee" table,
 * merging the Year column across each year's assignee rows to mirror the
 * rowspan grouping shown on the dashboard page.
 */
class DashboardExport implements FromArray, ShouldAutoSize, WithEvents, WithHeadings, WithStyles
{
    /**
     * @param  Collection<int, array<string, mixed>>  $rows
     */
    public function __construct(private readonly Collection $rows) {}

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return [
            __('Year'),
            __('Assignee'),
            __('Less than 5 Days'),
            __('5 to 10 Days'),
            __('10 Days or More'),
            __('Total'),
        ];
    }

    /**
     * @return array<int, array<int, mixed>>
     */
    public function array(): array
    {
        $data = $this->rows->map(fn (array $row) => [
            $row['year'],
            $row['assignee']?->name ?? __('Unassigned'),
            $row['under5'],
            $row['between5and10'],
            $row['over10'],
            $row['total'],
        ])->all();

        $data[] = [
            __('Total'),
            '',
            $this->rows->sum('under5'),
            $this->rows->sum('between5and10'),
            $this->rows->sum('over10'),
            $this->rows->sum('total'),
        ];

        return $data;
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

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();

                $rowNumber = 2;

                foreach ($this->rows as $row) {
                    if ($row['yearRowspan']) {
                        $endRow = $rowNumber + $row['yearRowspan'] - 1;

                        if ($row['yearRowspan'] > 1) {
                            $sheet->mergeCells("A{$rowNumber}:A{$endRow}");
                        }

                        $sheet->getStyle("A{$rowNumber}")->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
                    }

                    $rowNumber++;
                }

                $totalRow = $rowNumber;
                $sheet->mergeCells("A{$totalRow}:B{$totalRow}");
                $sheet->getStyle("A{$totalRow}:F{$totalRow}")->getFont()->setBold(true);
            },
        ];
    }
}
