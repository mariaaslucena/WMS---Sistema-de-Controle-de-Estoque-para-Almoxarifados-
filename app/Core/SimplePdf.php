<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Gerador PDF mínimo, sem dependências externas.
 * Suficiente para a guia de remessa de uma página do WMS.
 */
final class SimplePdf
{
    private array $commands = [];

    public function text(float $x, float $y, float $size, string $text, bool $bold = false): void
    {
        $font = $bold ? 'F2' : 'F1';
        $encoded = $this->encode($text);
        $this->commands[] = sprintf(
            "BT /%s %.2F Tf 1 0 0 1 %.2F %.2F Tm (%s) Tj ET",
            $font,
            $size,
            $x,
            $y,
            $this->escape($encoded)
        );
    }

    public function line(float $x1, float $y1, float $x2, float $y2, float $width = 0.8): void
    {
        $this->commands[] = sprintf('%.2F w %.2F %.2F m %.2F %.2F l S', $width, $x1, $y1, $x2, $y2);
    }

    public function rect(float $x, float $y, float $w, float $h, float $width = 0.8): void
    {
        $this->commands[] = sprintf('%.2F w %.2F %.2F %.2F %.2F re S', $width, $x, $y, $w, $h);
    }

    /** @return float Próxima coordenada Y. */
    public function wrappedText(float $x, float $y, float $size, string $text, float $maxWidth, float $lineHeight = 15, bool $bold = false): float
    {
        $text = trim(preg_replace('/\s+/u', ' ', $text) ?? $text);
        if ($text === '') {
            return $y;
        }

        $maxChars = max(10, (int) floor($maxWidth / max(1, $size * 0.52)));
        $words = preg_split('/\s+/u', $text) ?: [];
        $line = '';
        foreach ($words as $word) {
            $candidate = $line === '' ? $word : $line . ' ' . $word;
            if (mb_strlen($candidate, 'UTF-8') > $maxChars && $line !== '') {
                $this->text($x, $y, $size, $line, $bold);
                $y -= $lineHeight;
                $line = $word;
            } else {
                $line = $candidate;
            }
        }
        if ($line !== '') {
            $this->text($x, $y, $size, $line, $bold);
            $y -= $lineHeight;
        }
        return $y;
    }

    public function output(): string
    {
        $stream = implode("\n", $this->commands) . "\n";
        $objects = [];
        $objects[1] = '<< /Type /Catalog /Pages 2 0 R >>';
        $objects[2] = '<< /Type /Pages /Kids [3 0 R] /Count 1 >>';
        $objects[3] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 4 0 R /F2 5 0 R >> >> /Contents 6 0 R >>';
        $objects[4] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>';
        $objects[5] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>';
        $objects[6] = '<< /Length ' . strlen($stream) . " >>\nstream\n" . $stream . 'endstream';

        $pdf = "%PDF-1.4\n%\xE2\xE3\xCF\xD3\n";
        $offsets = [0];
        foreach ($objects as $number => $object) {
            $offsets[$number] = strlen($pdf);
            $pdf .= $number . " 0 obj\n" . $object . "\nendobj\n";
        }

        $xref = strlen($pdf);
        $count = count($objects) + 1;
        $pdf .= "xref\n0 {$count}\n";
        $pdf .= "0000000000 65535 f \n";
        for ($i = 1; $i < $count; $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }
        $pdf .= "trailer\n<< /Size {$count} /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF";
        return $pdf;
    }

    private function encode(string $text): string
    {
        if (function_exists('iconv')) {
            $converted = iconv('UTF-8', 'Windows-1252//TRANSLIT', $text);
            if ($converted !== false) {
                return $converted;
            }
        }
        return preg_replace('/[^\x20-\x7E]/', '?', $text) ?? $text;
    }

    private function escape(string $text): string
    {
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
    }
}
