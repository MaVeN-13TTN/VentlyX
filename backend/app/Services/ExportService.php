<?php

namespace App\Services;

use League\Csv\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Collection;

class ExportService
{
    /**
     * Export data to CSV
     *
     * @param array $headers Column headers
     * @param Collection|array $data Data to export
     * @param string $filename Filename for the exported file
     * @return StreamedResponse
     */
    public function exportToCsv(array $headers, $data, string $filename): StreamedResponse
    {
        // Create CSV
        $csv = Writer::createFromString('');
        
        // Add headers
        $csv->insertOne($headers);
        
        // Add data
        if ($data instanceof Collection) {
            foreach ($data as $row) {
                $csv->insertOne($this->formatRow($row, $headers));
            }
        } else {
            foreach ($data as $row) {
                $csv->insertOne($this->formatRow($row, $headers));
            }
        }
        
        // Create response
        $response = new StreamedResponse(function() use ($csv) {
            echo $csv->toString();
        });
        
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');
        
        return $response;
    }
    
    /**
     * Export data to Excel
     *
     * @param array $headers Column headers
     * @param Collection|array $data Data to export
     * @param string $filename Filename for the exported file
     * @return StreamedResponse
     */
    public function exportToExcel(array $headers, $data, string $filename): StreamedResponse
    {
        // In a real implementation, this would use a library like PhpSpreadsheet
        // For now, we'll just use CSV as a fallback
        return $this->exportToCsv($headers, $data, str_replace('.csv', '.xlsx', $filename));
    }
    
    /**
     * Export data to PDF
     *
     * @param array $headers Column headers
     * @param Collection|array $data Data to export
     * @param string $filename Filename for the exported file
     * @return StreamedResponse
     */
    public function exportToPdf(array $headers, $data, string $filename): StreamedResponse
    {
        // In a real implementation, this would use a library like TCPDF or Dompdf
        // For now, we'll just use CSV as a fallback
        return $this->exportToCsv($headers, $data, str_replace('.csv', '.pdf', $filename));
    }
    
    /**
     * Format a data row based on headers
     *
     * @param array|object $row Data row
     * @param array $headers Column headers
     * @return array
     */
    private function formatRow($row, array $headers): array
    {
        $formattedRow = [];
        
        // Convert object to array if needed
        if (is_object($row)) {
            $row = (array) $row;
        }
        
        // Format each column based on headers
        foreach ($headers as $header) {
            $formattedRow[] = $row[$header] ?? '';
        }
        
        return $formattedRow;
    }
}