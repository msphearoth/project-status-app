<?php

namespace App\Http\Controllers;

use App\Exports\ProjectsImportTemplate;
use App\Http\Requests\ImportProjectsRequest;
use App\Imports\ProjectsImport;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProjectImportController extends Controller
{
    /**
     * Show the upload form for importing projects from Excel.
     */
    public function create(): View
    {
        $this->authorize('create', Project::class);

        return view('projects.import');
    }

    /**
     * Download an empty spreadsheet with the expected import columns.
     */
    public function template(): BinaryFileResponse
    {
        $this->authorize('create', Project::class);

        return Excel::download(new ProjectsImportTemplate, 'projects-import-template.xlsx');
    }

    /**
     * Create pending projects from every row of the uploaded spreadsheet.
     * Nothing is imported if any row is invalid.
     */
    public function store(ImportProjectsRequest $request): RedirectResponse
    {
        $import = new ProjectsImport($request->user());

        Excel::import($import, $request->file('file'));

        return redirect()->route('projects.index')
            ->with('status', __(':count projects imported successfully.', ['count' => $import->importedCount()]));
    }
}
