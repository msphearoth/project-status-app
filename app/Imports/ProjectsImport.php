<?php

namespace App\Imports;

use App\Http\Requests\StoreProjectRequest;
use App\Models\Project;
use App\Models\User;
use DateTimeImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Throwable;

/**
 * Imports new projects from a spreadsheet laid out like the import template
 * (one heading row, then one project per row in COLUMNS order). Every row is
 * validated with the same rules as the "New Project" form; if any row fails,
 * nothing is imported and the errors are reported per row number.
 */
class ProjectsImport implements ToCollection, WithStartRow
{
    /**
     * Spreadsheet columns, in order, mapped to project attributes.
     */
    public const COLUMNS = [
        'project_code',
        'year',
        'work_code',
        'deca_no',
        'on_road',
        'start_road',
        'end_road',
        'pipe_type',
        'pipe_diameter',
        'pipe_length',
        'received_date',
    ];

    /**
     * Text date formats accepted for the received date, besides Excel date cells.
     */
    private const DATE_FORMATS = ['Y-m-d', 'd-M-y', 'd-M-Y', 'd/m/Y'];

    private int $importedCount = 0;

    public function __construct(private readonly User $creator) {}

    public function startRow(): int
    {
        return 2;
    }

    /**
     * @param  Collection<int, Collection<int, mixed>>  $rows
     *
     * @throws ValidationException
     */
    public function collection(Collection $rows): void
    {
        $projectsByRowNumber = $rows
            ->mapWithKeys(fn (Collection $row, int $index) => [$index + $this->startRow() => $this->mapRow($row)])
            ->reject(fn (array $attributes) => collect($attributes)->every(fn (mixed $value) => $value === null || $value === ''));

        if ($projectsByRowNumber->isEmpty()) {
            throw ValidationException::withMessages([
                'file' => __('The file does not contain any projects.'),
            ]);
        }

        $errors = $this->validationErrors($projectsByRowNumber);

        if ($errors !== []) {
            throw ValidationException::withMessages(['file' => $errors]);
        }

        DB::transaction(function () use ($projectsByRowNumber) {
            foreach ($projectsByRowNumber as $attributes) {
                Project::create([...$attributes, 'created_by' => $this->creator->id]);
            }
        });

        $this->importedCount = $projectsByRowNumber->count();
    }

    public function importedCount(): int
    {
        return $this->importedCount;
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $projectsByRowNumber
     * @return array<int, string>
     */
    private function validationErrors(Collection $projectsByRowNumber): array
    {
        $formRequest = new StoreProjectRequest;
        $errors = [];
        $seenProjectCodes = [];

        foreach ($projectsByRowNumber as $rowNumber => $attributes) {
            $messages = Validator::make($attributes, $formRequest->rules(), $formRequest->messages())
                ->errors()
                ->all();

            $projectCode = $attributes['project_code'];

            if ($projectCode !== null && $projectCode !== '') {
                if (isset($seenProjectCodes[$projectCode])) {
                    $messages[] = __('The project code :code already appears in row :row.', [
                        'code' => $projectCode,
                        'row' => $seenProjectCodes[$projectCode],
                    ]);
                } else {
                    $seenProjectCodes[$projectCode] = $rowNumber;
                }
            }

            foreach ($messages as $message) {
                $errors[] = __('Row :row: :message', ['row' => $rowNumber, 'message' => $message]);
            }
        }

        return $errors;
    }

    /**
     * @param  Collection<int, mixed>  $row
     * @return array<string, mixed>
     */
    private function mapRow(Collection $row): array
    {
        $attributes = [];

        foreach (self::COLUMNS as $position => $column) {
            $value = $row->get($position);
            $attributes[$column] = is_string($value) ? trim($value) : $value;
        }

        foreach (['project_code', 'work_code', 'deca_no', 'on_road', 'start_road', 'end_road', 'pipe_type'] as $column) {
            if (is_int($attributes[$column]) || is_float($attributes[$column])) {
                $attributes[$column] = (string) $attributes[$column];
            }
        }

        $attributes['received_date'] = $this->normalizeDate($attributes['received_date']);

        return $attributes;
    }

    /**
     * Convert an Excel date cell or a text date into Y-m-d. Unrecognised values
     * are returned unchanged so validation reports them.
     */
    private function normalizeDate(mixed $value): mixed
    {
        if (is_int($value) || is_float($value)) {
            try {
                return ExcelDate::excelToDateTimeObject($value)->format('Y-m-d');
            } catch (Throwable) {
                return $value;
            }
        }

        if (! is_string($value) || $value === '') {
            return $value;
        }

        foreach (self::DATE_FORMATS as $format) {
            $date = DateTimeImmutable::createFromFormat('!'.$format, $value);

            if ($date !== false && $date->format($format) === $value) {
                return $date->format('Y-m-d');
            }
        }

        return $value;
    }
}
