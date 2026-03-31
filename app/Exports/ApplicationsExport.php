<?php

namespace App\Exports;

use App\Models\Application;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Font;

class ApplicationsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    /**
     * Get the collection of applications
     */
    public function collection()
    {
        return Application::with(['user', 'job'])
            ->latest()
            ->get();
    }

    /**
     * Map the data to columns
     */
    public function map($row): array
    {
        return [
            $row->id,
            $row->user->name ?? 'Unknown User',
            $row->user->email ?? '-',
            $row->job->title ?? 'Unknown Role',
            $row->job->location ?? '-',
            ucfirst($row->status),
            $row->created_at->format('d M Y H:i'),
            $row->updated_at->format('d M Y H:i'),
            $row->cover_letter ? 'Yes' : 'No',
            $row->resume_path ? 'Yes' : 'No',
            $row->notes ?? '-',
        ];
    }

    /**
     * Define the headings for the Excel file
     */
    public function headings(): array
    {
        return [
            'ID',
            'Candidate Name',
            'Email',
            'Applied Position',
            'Location',
            'Status',
            'Applied Date',
            'Last Updated',
            'Has Cover Letter',
            'Has Resume',
            'Notes',
        ];
    }

    /**
     * Style the worksheet
     */
    public function styles(Worksheet $sheet)
    {
        // Style header row
        $sheet->getStyle('A1:K1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 12,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1F2937'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'E5E7EB'],
                ],
            ],
        ]);

        // Style data rows with alternating colors
        $sheet->getStyle('A2:K' . ($sheet->getHighestRow()))
            ->applyFromArray([
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'E5E7EB'],
                    ],
                ],
            ]);

        // Alternating row colors
        for ($i = 2; $i <= $sheet->getHighestRow(); $i++) {
            if ($i % 2 == 0) {
                $sheet->getStyle("A{$i}:K{$i}")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setRGB('F9FAFB');
            }
        }

        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(8);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(25);
        $sheet->getColumnDimension('D')->setWidth(20);
        $sheet->getColumnDimension('E')->setWidth(15);
        $sheet->getColumnDimension('F')->setWidth(12);
        $sheet->getColumnDimension('G')->setWidth(18);
        $sheet->getColumnDimension('H')->setWidth(18);
        $sheet->getColumnDimension('I')->setWidth(15);
        $sheet->getColumnDimension('J')->setWidth(12);
        $sheet->getColumnDimension('K')->setWidth(30);

        return [];
    }
}
