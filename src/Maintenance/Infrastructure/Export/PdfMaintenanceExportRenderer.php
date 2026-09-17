<?php

declare(strict_types=1);

namespace App\Maintenance\Infrastructure\Export;

use App\Maintenance\Application\Port\MaintenanceExport;
use App\Maintenance\Application\Port\MaintenanceExportRecord;
use App\Maintenance\Application\Port\VehicleSummary;
use Dompdf\Dompdf;
use Dompdf\Options;

final readonly class PdfMaintenanceExportRenderer
{
    private const PAPER_SIZE = 'A4';
    private const PAPER_ORIENTATION = 'landscape';

    // Bundled with dompdf; unlike the PDF core fonts it covers the full UTF-8 range users can type.
    private const FONT_FAMILY = 'DejaVu Sans';

    private const DATE_FORMAT = 'Y-m-d';
    private const EMPTY_VALUE = '—';
    private const YES = 'Yes';
    private const NO = 'No';

    private const FOOTER_TEMPLATE = 'Generated on %s · Page {PAGE_NUM} of {PAGE_COUNT}';
    private const FOOTER_FONT_SIZE = 7;
    private const FOOTER_OFFSET_X = 36;
    private const FOOTER_OFFSET_Y = 28;
    private const FOOTER_COLOR = [0.4, 0.4, 0.4];

    private const STYLESHEET = <<<'CSS'
        @page { margin: 36pt 36pt 48pt 36pt; }
        body { font-family: "DejaVu Sans", sans-serif; font-size: 8.5pt; color: #222; }
        h1 { font-size: 15pt; margin: 0 0 12pt; }
        table { border-collapse: collapse; }
        .vehicle { margin-bottom: 18pt; }
        .vehicle th { text-align: left; font-weight: normal; color: #666; padding: 1.5pt 14pt 1.5pt 0; }
        .vehicle td { font-weight: bold; padding: 1.5pt 0; }
        .records { width: 100%; }
        .records thead { display: table-header-group; }
        .records th { text-align: left; font-size: 7.5pt; color: #444; border-bottom: 1pt solid #222; padding: 4pt; }
        .records td { vertical-align: top; border-bottom: 0.5pt solid #ccc; padding: 4pt; }
        .records tr { page-break-inside: avoid; }
        .records .totals td { font-weight: bold; border-bottom: none; padding-top: 8pt; }
        .records .number { text-align: right; white-space: nowrap; }
        .nowrap { white-space: nowrap; }
        .empty { color: #666; }
        CSS;

    public function render(MaintenanceExport $export): string
    {
        $options = new Options();
        $options->setDefaultFont(self::FONT_FAMILY);
        // The template is self-contained; never let user-provided text make dompdf fetch anything.
        $options->setIsRemoteEnabled(false);

        $dompdf = new Dompdf($options);
        $dompdf->setPaper(self::PAPER_SIZE, self::PAPER_ORIENTATION);
        $dompdf->loadHtml($this->html($export));
        $dompdf->render();

        $this->addFooter($dompdf);

        return (string) $dompdf->output();
    }

    private function addFooter(Dompdf $dompdf): void
    {
        $canvas = $dompdf->getCanvas();

        $canvas->page_text(
            self::FOOTER_OFFSET_X,
            $canvas->get_height() - self::FOOTER_OFFSET_Y,
            sprintf(self::FOOTER_TEMPLATE, (new \DateTimeImmutable())->format(self::DATE_FORMAT)),
            $dompdf->getFontMetrics()->getFont(self::FONT_FAMILY),
            self::FOOTER_FONT_SIZE,
            self::FOOTER_COLOR,
        );
    }

    private function html(MaintenanceExport $export): string
    {
        return sprintf(
            '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>%s</style></head><body><h1>Maintenance history</h1>%s%s</body></html>',
            self::STYLESHEET,
            $this->vehicleSection($export->vehicle),
            $this->recordsSection($export),
        );
    }

    private function vehicleSection(VehicleSummary $vehicle): string
    {
        $makeAndModel = trim(implode(' ', array_filter([$vehicle->make, $vehicle->model])));

        $rows = [
            'Plate' => $vehicle->plate,
            'Make / model' => '' !== $makeAndModel ? $makeAndModel : null,
            'Year' => (string) $vehicle->year,
        ];

        if (null !== $vehicle->vin) {
            $rows['VIN'] = $vehicle->vin;
        }

        $html = '';
        foreach ($rows as $label => $value) {
            $html .= sprintf('<tr><th>%s</th><td>%s</td></tr>', $label, $this->text($value));
        }

        return sprintf('<table class="vehicle">%s</table>', $html);
    }

    private function recordsSection(MaintenanceExport $export): string
    {
        if ([] === $export->records) {
            return '<p class="empty">No maintenance records.</p>';
        }

        $rows = implode('', array_map($this->recordRow(...), $export->records));

        return <<<HTML
            <table class="records">
                <thead>
                    <tr>
                        <th style="width: 9%">Service date</th>
                        <th style="width: 14%">Type</th>
                        <th style="width: 9%" class="number">Mileage (km)</th>
                        <th style="width: 8%" class="number">Cost</th>
                        <th style="width: 16%">Shop</th>
                        <th style="width: 9%">Next service</th>
                        <th style="width: 7%">Verified</th>
                        <th style="width: 28%">Notes</th>
                    </tr>
                </thead>
                <tbody>
                    {$rows}
                    {$this->totalsRow($export)}
                </tbody>
            </table>
            HTML;
    }

    private function recordRow(MaintenanceExportRecord $record): string
    {
        return sprintf(
            '<tr><td class="nowrap">%s</td><td>%s</td><td class="number">%s</td><td class="number">%s</td><td>%s</td><td class="nowrap">%s</td><td>%s</td><td>%s</td></tr>',
            $this->text($record->serviceDate),
            $this->text($record->type),
            $this->text(null !== $record->mileage ? (string) $record->mileage : null),
            $this->text($record->cost),
            $this->text($record->shopName),
            $this->text($record->nextServiceDate),
            $record->verified ? self::YES : self::NO,
            nl2br($this->text($record->notes)),
        );
    }

    private function totalsRow(MaintenanceExport $export): string
    {
        $count = count($export->records);

        return sprintf(
            '<tr class="totals"><td colspan="3">Total (%d %s)</td><td class="number">%s</td><td colspan="4"></td></tr>',
            $count,
            1 === $count ? 'record' : 'records',
            $this->text($export->totalCost),
        );
    }

    private function text(?string $value): string
    {
        if (null === $value || '' === $value) {
            return self::EMPTY_VALUE;
        }

        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
