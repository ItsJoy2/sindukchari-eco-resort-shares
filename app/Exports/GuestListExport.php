<?php

namespace App\Exports;

use App\Models\GuestList;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class GuestListExport implements FromCollection, WithHeadings, WithStyles, WithEvents
{
    public function collection()
    {
        return GuestList::select(
            'date',
            'name',
            'mobile',
            'address',
            'profession',
            'status',
            'reference',
            'note'
        )->get();
    }

    public function headings(): array
    {
        return [
            'Date',
            'Name',
            'Mobile',
            'Address',
            'Profession',
            'Status',
            'Reference',
            'Note'
        ];
    }

    // Style headings
    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                ]
            ],
        ];
    }

    // Header + formatting
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet->getDelegate();

                // Merge title row
                $sheet->insertNewRowBefore(1, 2);

                $sheet->mergeCells('A1:H1');

                $sheet->setCellValue('A1', 'GUEST LIST REPORT');

                // Title style
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 16,
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    ],
                ]);

                // Header row style
                $sheet->getStyle('A3:H3')->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    ],
                ]);

                // Auto size columns
                foreach (range('A', 'H') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
            }
        ];
    }
}
