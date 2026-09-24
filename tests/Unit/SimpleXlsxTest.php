<?php

namespace Tests\Unit;

use App\Support\SimpleXlsx;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;
use ZipArchive;

class SimpleXlsxTest extends TestCase
{
    private function build(array $sheets): array
    {
        $path = tempnam(sys_get_temp_dir(), 'xlsxtest');
        SimpleXlsx::write($path, $sheets);
        $zip = new ZipArchive();
        $this->assertTrue($zip->open($path));
        $files = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $files[$zip->getNameIndex($i)] = $zip->getFromIndex($i);
        }
        $zip->close();
        unlink($path);

        return $files;
    }

    public function test_column_letters(): void
    {
        $this->assertSame('A', SimpleXlsx::colName(0));
        $this->assertSame('Z', SimpleXlsx::colName(25));
        $this->assertSame('AA', SimpleXlsx::colName(26));
        $this->assertSame('AB', SimpleXlsx::colName(27));
        $this->assertSame('AAA', SimpleXlsx::colName(702));
    }

    public function test_produces_valid_package_with_every_sheet_and_well_formed_xml(): void
    {
        $files = $this->build([
            ['name' => 'Satu', 'columns' => [['A', 'text'], ['B', 'int']], 'rows' => [['x', 1]]],
            ['name' => 'Dua', 'columns' => [['C', 'text']], 'rows' => []],
        ]);

        foreach (['[Content_Types].xml', '_rels/.rels', 'xl/workbook.xml', 'xl/_rels/workbook.xml.rels', 'xl/styles.xml', 'xl/worksheets/sheet1.xml', 'xl/worksheets/sheet2.xml'] as $part) {
            $this->assertArrayHasKey($part, $files);
            $doc = new \DOMDocument();
            $this->assertTrue($doc->loadXML($files[$part]), "$part bukan XML yang valid");
        }
        $this->assertStringContainsString('name="Satu"', $files['xl/workbook.xml']);
        $this->assertStringContainsString('name="Dua"', $files['xl/workbook.xml']);
        $this->assertStringNotContainsString('autoFilter', $files['xl/worksheets/sheet2.xml']);
    }

    public function test_cell_types_and_escaping(): void
    {
        $when = Carbon::create(2026, 9, 24, 12, 0, 0, 'UTC');
        $files = $this->build([[
            'name' => 'T',
            'columns' => [['Teks', 'text'], ['Uang', 'money'], ['Jumlah', 'int'], ['Waktu', 'datetime'], ['Kosong', 'text']],
            'rows' => [['A & B <x> "q" \'s\'', 15000, 3, $when, null], ['=SUM(1+1)', 0, 0, null, '']],
        ]]);
        $sheet = $files['xl/worksheets/sheet1.xml'];

        $this->assertTrue((new \DOMDocument())->loadXML($sheet));
        $this->assertStringContainsString('A &amp; B &lt;x&gt; &quot;q&quot; &apos;s&apos;', $sheet);
        $this->assertStringContainsString('<c r="B2" s="2"><v>15000</v></c>', $sheet);   // angka sungguhan, bukan teks
        $this->assertStringContainsString('<c r="C2" s="4"><v>3</v></c>', $sheet);
        $this->assertStringContainsString('<c r="D2" s="3"><v>46289.5</v></c>', $sheet);  // 2026-09-24 12:00 dalam serial Excel
        $this->assertStringNotContainsString('r="E2"', $sheet);                             // null = sel kosong
        $this->assertStringContainsString('t="inlineStr"><is><t xml:space="preserve">=SUM(1+1)', $sheet); // rumus tetap teks
        $this->assertStringContainsString('<autoFilter ref="A1:E3"/>', $sheet);
        $this->assertStringContainsString('state="frozen"', $sheet);
    }

    public function test_control_characters_are_stripped(): void
    {
        $files = $this->build([['name' => 'T', 'columns' => [['A', 'text']], 'rows' => [["halo\x00\x08\x0Bdunia"]]]]);

        $this->assertStringContainsString('halodunia', $files['xl/worksheets/sheet1.xml']);
        $this->assertTrue((new \DOMDocument())->loadXML($files['xl/worksheets/sheet1.xml']));
    }

    public function test_sheet_name_is_sanitized_and_truncated(): void
    {
        $files = $this->build([['name' => 'Laporan/2026:[Q3]? ' . str_repeat('x', 40), 'columns' => [['A', 'text']], 'rows' => []]]);

        preg_match('/<sheet name="([^"]*)"/', $files['xl/workbook.xml'], $m);
        $this->assertLessThanOrEqual(31, mb_strlen($m[1]));
        $this->assertDoesNotMatchRegularExpression('/[\\\\\/?*\[\]:]/', $m[1]);
    }
}
