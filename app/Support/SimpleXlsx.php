<?php

namespace App\Support;

use DateTimeInterface;
use RuntimeException;
use ZipArchive;

/**
 * Pembuat file .xlsx sederhana tanpa paket tambahan (hanya butuh ekstensi PHP "zip").
 *
 * $sheets = [
 *   ['name' => 'Pesanan', 'columns' => [['Judul', 'text|int|money|datetime', lebar], ...], 'rows' => [[...], ...]],
 * ]
 * Baris pertama dibekukan dan diberi filter. Teks selalu disimpan sebagai teks (rumus tidak dieksekusi).
 */
final class SimpleXlsx
{
    private const STYLE_HEADER = 1;
    private const STYLE_MONEY = 2;
    private const STYLE_DATETIME = 3;
    private const STYLE_INT = 4;

    public static function isAvailable(): bool
    {
        return class_exists(ZipArchive::class);
    }

    public static function write(string $path, array $sheets): void
    {
        if (! self::isAvailable()) {
            throw new RuntimeException('Ekstensi PHP "zip" belum aktif.');
        }
        if ($sheets === []) {
            throw new RuntimeException('Minimal satu sheet diperlukan.');
        }

        $zip = new ZipArchive();
        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Tidak bisa membuat file Excel.');
        }

        $overrides = '';
        $sheetTags = '';
        $relTags = '';
        foreach (array_values($sheets) as $i => $sheet) {
            $n = $i + 1;
            $zip->addFromString("xl/worksheets/sheet{$n}.xml", self::sheetXml($sheet));
            $overrides .= "<Override PartName=\"/xl/worksheets/sheet{$n}.xml\" ContentType=\"application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml\"/>";
            $sheetTags .= '<sheet name="' . self::esc(self::sheetName($sheet['name'], $n)) . "\" sheetId=\"{$n}\" r:id=\"rId{$n}\"/>";
            $relTags .= "<Relationship Id=\"rId{$n}\" Type=\"http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet\" Target=\"worksheets/sheet{$n}.xml\"/>";
        }
        $styleRel = count($sheets) + 1;
        $relTags .= "<Relationship Id=\"rId{$styleRel}\" Type=\"http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles\" Target=\"styles.xml\"/>";

        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
        $zip->addFromString('[Content_Types].xml', $xml
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            . $overrides . '</Types>');
        $zip->addFromString('_rels/.rels', $xml
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>');
        $zip->addFromString('xl/workbook.xml', $xml
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . "<sheets>{$sheetTags}</sheets></workbook>");
        $zip->addFromString('xl/_rels/workbook.xml.rels', $xml
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' . $relTags . '</Relationships>');
        $zip->addFromString('xl/styles.xml', $xml . self::stylesXml());

        if (! $zip->close()) {
            throw new RuntimeException('Gagal menyimpan file Excel.');
        }
    }

    private static function sheetXml(array $sheet): string
    {
        $columns = $sheet['columns'];
        $rows = $sheet['rows'];
        $lastCol = self::colName(count($columns) - 1);
        $lastRow = count($rows) + 1;

        $cols = '';
        foreach ($columns as $i => $col) {
            $w = (float) ($col[2] ?? 14);
            $cols .= '<col min="' . ($i + 1) . '" max="' . ($i + 1) . "\" width=\"{$w}\" customWidth=\"1\"/>";
        }

        $out = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<sheetViews><sheetView workbookViewId="0"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>'
            . "<cols>{$cols}</cols><sheetData>";

        $out .= '<row r="1">';
        foreach ($columns as $i => $col) {
            $out .= self::textCell(self::colName($i) . '1', (string) $col[0], self::STYLE_HEADER);
        }
        $out .= '</row>';

        foreach ($rows as $r => $row) {
            $rowNum = $r + 2;
            $out .= "<row r=\"{$rowNum}\">";
            foreach ($columns as $i => $col) {
                $out .= self::cell(self::colName($i) . $rowNum, $col[1], $row[$i] ?? null);
            }
            $out .= '</row>';
        }

        $out .= '</sheetData>';
        if ($rows !== []) {
            $out .= "<autoFilter ref=\"A1:{$lastCol}{$lastRow}\"/>";
        }

        return $out . '</worksheet>';
    }

    private static function cell(string $ref, string $type, mixed $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        return match ($type) {
            'money' => "<c r=\"{$ref}\" s=\"" . self::STYLE_MONEY . '"><v>' . (int) $value . '</v></c>',
            'int' => "<c r=\"{$ref}\" s=\"" . self::STYLE_INT . '"><v>' . (int) $value . '</v></c>',
            'datetime' => $value instanceof DateTimeInterface
                ? "<c r=\"{$ref}\" s=\"" . self::STYLE_DATETIME . '"><v>' . self::serial($value) . '</v></c>'
                : '',
            default => self::textCell($ref, (string) $value, 0),
        };
    }

    private static function textCell(string $ref, string $text, int $style): string
    {
        $s = $style ? " s=\"{$style}\"" : '';

        return "<c r=\"{$ref}\" t=\"inlineStr\"{$s}><is><t xml:space=\"preserve\">" . self::esc($text) . '</t></is></c>';
    }

    // Excel tidak mengenal zona waktu: pakai jam dinding sesuai zona waktu aplikasi.
    private static function serial(DateTimeInterface $dt): string
    {
        return rtrim(rtrim(number_format((($dt->getTimestamp() + $dt->getOffset()) / 86400) + 25569, 6, '.', ''), '0'), '.');
    }

    public static function colName(int $index): string
    {
        $name = '';
        for ($i = $index; $i >= 0; $i = intdiv($i, 26) - 1) {
            $name = chr(65 + $i % 26) . $name;
        }

        return $name;
    }

    private static function sheetName(string $name, int $n): string
    {
        $clean = trim(preg_replace('/[\\\\\/?*\[\]:]/', ' ', $name));

        return mb_substr($clean === '' ? "Sheet{$n}" : $clean, 0, 31);
    }

    private static function esc(string $text): string
    {
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $text) ?? '';

        return htmlspecialchars($text, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    private static function stylesXml(): string
    {
        return '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<numFmts count="1"><numFmt numFmtId="164" formatCode="yyyy\-mm\-dd\ hh:mm"/></numFmts>'
            . '<fonts count="2"><font><sz val="11"/><name val="Calibri"/></font><font><b/><sz val="11"/><name val="Calibri"/></font></fonts>'
            . '<fills count="3"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill>'
            . '<fill><patternFill patternType="solid"><fgColor rgb="FFDCE9F5"/><bgColor indexed="64"/></patternFill></fill></fills>'
            . '<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="5">'
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
            . '<xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1"/>'
            . '<xf numFmtId="3" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1"/>'
            . '<xf numFmtId="164" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1"/>'
            . '<xf numFmtId="1" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1"/>'
            . '</cellXfs><cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles></styleSheet>';
    }
}
