<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithEvents;

class ReportExport implements FromView, WithStyles, WithEvents, WithColumnWidths
{
    protected $report;
    protected $typeReport;
    protected $startDate;
    protected $endDate;

    public function __construct($report, $typeReport, $startDate, $endDate)
    {
        $this->report = $report;
        $this->typeReport = $typeReport;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function view(): View
    {
        return view('pages.apps.export.template.' . $this->typeReport, [
            'report' => $this->report
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        $lastColumn = $sheet->getHighestColumn();

        // Style header row (row 1)
        $sheet->getStyle("A1:{$lastColumn}1")->applyFromArray([
            'font' => [
                'name' => 'Calibri',
                'bold' => true,
                'size' => 11,             // Font size 11
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '228B22'], // green (ForestGreen)
            ],
        ]);

        // Add thin borders to all cells
        $sheet->getStyle('A1:' . $lastColumn . $sheet->getHighestRow())
            ->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ],
                ],
            ]);

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 50, // Marketing
            'B' => 50, // Team
            'C' => 50, // Start Kerja
            'D' => 50, // Member Daftar
            'E' => 50, // Total Deposit Amount
            'F' => 50, // Total Deposit Transactions
            // 'I' => 30, // Start Date
            // 'J' => 30, // End Date
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->getColumnDimension('I')->setWidth(20);
                $sheet->getColumnDimension('J')->setWidth(20);
                $lastRow = $sheet->getHighestRow() + 1;

                $sheet->setCellValue('I1', 'Start Date');
                $sheet->setCellValue('I2', $this->startDate);
                $sheet->setCellValue('J1', 'End Date');
                $sheet->setCellValue('J2', $this->endDate);

                // Style Start/End date cells
                $sheet->getStyle('I1:J1')->applyFromArray([
                    'font' => [
                        'name' => 'Calibri',
                        'bold' => true,
                        'size' => 11,
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '228B22'],
                    ],
                ]);



                // Insert new row 2 for TOTAL
                $sheet->insertNewRowBefore(2, 1);

                // Merge A2:C2
                $sheet->mergeCells('A2:B2');
                $sheet->setCellValue('A2', 'TOTAL');

                // D2 = sum of member daftar
                $sheet->setCellValue('C2', "=SUM(C3:C{$lastRow})");

                // E2 = sum of total deposit amount
                $sheet->setCellValue('D2', "=SUM(D3:D{$lastRow})");

                // E2 = sum of total deposit amount
                $sheet->setCellValue('E2', "=SUM(E3:E{$lastRow})");



                // Style row 2
                $sheet->getStyle('A2:D2')->applyFromArray([
                    'font' => [
                        'name' => 'Calibri',
                        'bold' => true,
                        'size' => 11,
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '228B22'],
                    ],
                ]);

                // Format numeric columns
                $sheet->getStyle("C3:C{$lastRow}")
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_NUMBER);

                $sheet->getStyle("D3:D{$lastRow}")
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

                $sheet->getStyle("E3:E{$lastRow}")
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_NUMBER);
            }
        ];
    }
}
