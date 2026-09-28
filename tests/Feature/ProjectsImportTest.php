<?php

namespace Tests\Feature;

use App\Enums\ProjectStatus;
use App\Exports\ProjectsImportTemplate;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class ProjectsImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('projects.import.create'))
            ->assertOk()
            ->assertSee(route('projects.import.template'));
    }

    public function test_template_can_be_downloaded(): void
    {
        Excel::fake();

        $user = User::factory()->create();

        $this->actingAs($user)->get(route('projects.import.template'))->assertOk();

        Excel::assertDownloaded('projects-import-template.xlsx', function (ProjectsImportTemplate $template) {
            return count($template->headings()) === 10 && $template->array() === [];
        });
    }

    public function test_projects_are_imported_as_pending_from_a_spreadsheet(): void
    {
        $user = User::factory()->create();

        $file = $this->spreadsheet([
            ['PRJ-IMP1', 2026, 'WRK-1', 'Main St', '1st Ave', '5th Ave', 'PVC', 100, 250.5, ExcelDate::PHPToExcel(new \DateTime('2026-01-15'))],
            [],
            ['PRJ-IMP2', '2025', 'WRK-2', 'Second St', 'A', 'B', 'HDPE', '90', '40', '15-Feb-25'],
        ]);

        $response = $this->actingAs($user)->post(route('projects.import.store'), ['file' => $file]);

        $response->assertRedirect(route('projects.index'))->assertSessionHasNoErrors();

        $first = Project::firstWhere('project_code', 'PRJ-IMP1');
        $this->assertSame(ProjectStatus::Pending, $first->status);
        $this->assertSame($user->id, (int) $first->created_by);
        $this->assertSame('2026-01-15', $first->received_date->toDateString());
        $this->assertSame('250.50', $first->pipe_length);

        $second = Project::firstWhere('project_code', 'PRJ-IMP2');
        $this->assertSame('2025-02-15', $second->received_date->toDateString());
        $this->assertSame(2, Project::count());
    }

    public function test_nothing_is_imported_when_any_row_is_invalid(): void
    {
        $user = User::factory()->create();
        Project::factory()->create(['project_code' => 'PRJ-EXISTS']);

        $file = $this->spreadsheet([
            ['PRJ-OK', 2026, 'WRK-1', 'Main St', 'A', 'B', 'PVC', 100, 50, '2026-01-15'],
            ['PRJ-EXISTS', 2026, 'WRK-2', 'Main St', 'A', 'B', 'PVC', 100, 50, '2026-01-15'],
            ['PRJ-OK', 2026, 'WRK-3', '', 'A', 'B', 'PVC', 'abc', 50, 'not a date'],
        ]);

        $response = $this->actingAs($user)
            ->from(route('projects.import.create'))
            ->post(route('projects.import.store'), ['file' => $file]);

        $response->assertRedirect(route('projects.import.create'));

        $response->assertSessionHasErrors(['file' => 'Row 3: The project code has already been taken.']);
        $response->assertSessionHasErrors(['file' => 'Row 4: The road field is required.']);
        $response->assertSessionHasErrors(['file' => 'Row 4: The pipe diameter field must be a number.']);
        $response->assertSessionHasErrors(['file' => 'Row 4: The project code PRJ-OK already appears in row 2.']);
        $this->assertDatabaseMissing('projects', ['project_code' => 'PRJ-OK']);
        $this->assertSame(1, Project::count());
    }

    public function test_an_empty_spreadsheet_is_rejected(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('projects.import.store'), [
            'file' => $this->spreadsheet([]),
        ]);

        $response->assertSessionHasErrors('file');
    }

    public function test_non_spreadsheet_files_are_rejected(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('projects.import.store'), [
            'file' => UploadedFile::fake()->create('projects.pdf', 10, 'application/pdf'),
        ]);

        $response->assertSessionHasErrors('file');
        $this->assertSame(0, Project::count());
    }

    /**
     * Build an .xlsx upload with the template heading row followed by the given rows.
     *
     * @param  array<int, array<int, mixed>>  $rows
     */
    private function spreadsheet(array $rows): UploadedFile
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray((new ProjectsImportTemplate)->headings());

        foreach ($rows as $index => $row) {
            if ($row !== []) {
                $sheet->fromArray($row, null, 'A'.($index + 2));
            }
        }

        $path = tempnam(sys_get_temp_dir(), 'import').'.xlsx';
        (new Xlsx($spreadsheet))->save($path);

        return new UploadedFile($path, 'projects.xlsx', null, null, true);
    }
}
