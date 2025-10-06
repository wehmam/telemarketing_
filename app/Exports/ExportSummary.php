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

class ExportSummary implements FromView, WithStyles, WithEvents, WithColumnWidths
{
    protected $report;
    protected $typeReport;
    protected $startDate;
    protected $endDate;

    public function __construct($report, $startDate, $endDate)
    {
        $this->report = $report;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function view(): View
    {
        return view('pages.apps.export.template.summary', [
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
                'size' => 11,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '228B22'], // green
            ],
        ]);

        // Add thin borders
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
            'A' => 30, // Marketing
            'B' => 30, // Team
            'C' => 20, // Start Kerja
            'D' => 20, // Member Daftar
            'E' => 25, // Deposit Amount
            'F' => 25, // Deposit Count
            'G' => 25, // Redeposit Amount
            'H' => 25, // Redeposit Count
            'I' => 20, // Total Followup
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->getColumnDimension('K')->setWidth(20);
                $sheet->getColumnDimension('L')->setWidth(20);
                $lastRow = $sheet->getHighestRow() + 1;

                // Start/End Date
                $sheet->setCellValue('K1', 'Start Date');
                $sheet->setCellValue('K2', \Carbon\Carbon::createFromFormat('Y-m-d', $this->startDate)->format('d F Y'));
                $sheet->setCellValue('L1', 'End Date');
                $sheet->setCellValue('L2', \Carbon\Carbon::createFromFormat('Y-m-d', $this->endDate)->format('d F Y'));

                $sheet->getStyle('K1:L1')->applyFromArray([
                    'font' => [
                        'name' => 'Calibri',
                        'bold' => true,
                        'size' => 11,
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                    'fill' => [
                        'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '228B22'],
                    ],
                ]);

                // Insert TOTAL row
                $sheet->insertNewRowBefore(2, 1);
                $sheet->mergeCells('A2:B2');
                $sheet->setCellValue('A2', 'TOTAL');

                // SUM each column
                $sheet->setCellValue('D2', "=SUM(D3:D{$lastRow})"); // Member Daftar
                $sheet->setCellValue('E2', "=SUM(E3:E{$lastRow})"); // Deposit Amount
                $sheet->setCellValue('F2', "=SUM(F3:F{$lastRow})"); // Deposit Count
                $sheet->setCellValue('G2', "=SUM(G3:G{$lastRow})"); // Redeposit Amount
                $sheet->setCellValue('H2', "=SUM(H3:H{$lastRow})"); // Redeposit Count

                // Style TOTAL row
                $sheet->getStyle('A2:H2')->applyFromArray([
                    'font' => [
                        'name' => 'Calibri',
                        'bold' => true,
                        'size' => 11,
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                    'fill' => [
                        'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '228B22'],
                    ],
                ]);

                // Format numeric columns
                // $sheet->getStyle("D3:H{$lastRow}")
                //     ->getNumberFormat()
                //     ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
                $sheet->getStyle("E2")
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

                $sheet->getStyle("G2")
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
                $sheet->getStyle("E3:E{$lastRow}")
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

                $sheet->getStyle("G3:G{$lastRow}")
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
            }
        ];
    }
}
