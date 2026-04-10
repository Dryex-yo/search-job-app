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

class ApplicationsExportWithFilters implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $filters;

    /**
     * Constructor to accept filters
     */
    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    /**
     * Get the collection of applications based on filters
     */
    public function collection()
    {
        $query = Application::with(['user', 'job'])->latest();

        // Apply search filter
        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function($q) use ($search) {
                $q->whereHas('user', function($subQ) use ($search) {
                    $subQ->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                })
                ->orWhereHas('job', function($subQ) use ($search) {
                    $subQ->where('title', 'like', "%{$search}%");
                });
            });
        }

        // Apply status filter
        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        // Apply AI score range filter
        if (!empty($this->filters['score_min'])) {
            $query->where('ai_match_score', '>=', $this->filters['score_min']);
        }
        if (!empty($this->filters['score_max'])) {
            $query->where('ai_match_score', '<=', $this->filters['score_max']);
        }

        // Apply date range filter
        if (!empty($this->filters['date_from'])) {
            $query->whereDate('created_at', '>=', $this->filters['date_from']);
        }
        if (!empty($this->filters['date_to'])) {
            $query->whereDate('created_at', '<=', $this->filters['date_to']);
        }

        return $query->get();
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
            sprintf('%.1f%%', $row->ai_match_score ?? 0),
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
            'AI Match Score %',
            'Notes',
        ];
    }

    /**
     * Style the worksheet
     */
    public function styles(Worksheet $sheet)
    {
        // Style header row
        $sheet->getStyle('A1:L1')->applyFromArray([
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
        $sheet->getStyle('A2:L' . ($sheet->getHighestRow()))
            ->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'E5E7EB'],
                    ],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
            ]);

        // Alternate row colors for better readability
        for ($i = 2; $i <= $sheet->getHighestRow(); $i++) {
            if ($i % 2 == 0) {
                $sheet->getStyle("A{$i}:L{$i}")
                    ->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'F9FAFB'],
                        ],
                    ]);
            }
        }

        // Center align numeric columns (ID, Status, Score)
        $sheet->getStyle('A2:A' . ($sheet->getHighestRow()))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('F2:F' . ($sheet->getHighestRow()))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('K2:K' . ($sheet->getHighestRow()))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        return $sheet;
    }
}
