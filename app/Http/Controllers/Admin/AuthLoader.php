<?php

namespace App\Http\Controllers\Admin;

use Exception;
use Carbon\Carbon;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\AuthLoader as AuthLoaderModel;

class AuthLoader extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = AuthLoaderModel::query();

        if ($request->filled('search')) {
            $query->where('lang', 'like', '%' . $request->search . '%')->orWhere('loader_type', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('sort') && $request->filled('direction')) {
            $query->orderBy($request->sort, $request->direction);
        }

        $loaders = $query->latest()->paginate(20)->withQueryString();
        // return response()->json([
        //     'loaders' => $loaders,
        //     'filters' => $request->only(['search', 'sort', 'direction']),
        // ]);
        return Inertia::render('LoaderUpdates/Index', [
            'loaders' => $loaders,
            'filters' => $request->only(['search', 'sort', 'direction']),
        ]);
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'is_auto_version' => ['required', 'boolean'],
            'version' => ['required_if:is_auto_version,false', 'nullable', 'decimal:2'],
            'lang' => ['required', 'string'],
            'updateNote' => ['nullable', 'array'],
            'loader_type' => ['required', 'string'],
            'stage' => ['required', 'string', 'in:production,staging,development'],
            'file' => ['somitmes','file'],
        ]);
        $authloader = AuthLoaderModel::where('lang', $data['lang'])
            ->where('loader_type', $data['loader_type'])
            ->orderByDesc('version')
            ->first();

        if (!$data['is_auto_version'] && $authloader && $data['version'] <= $authloader->version) {
            return redirect()
                ->back()
                ->withErrors([
                    'version' => 'The version must be greater than the previous version (' . $authloader->version . ').',
                ])
                ->withInput();
        }

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filePath = $file->store('uploads/loaders', 'public');
            $fileHash = md5_file($file->getRealPath());
        } else {
            return redirect()
                ->back()
                ->withErrors(['file' => 'File upload failed.'])
                ->withInput();
        }

        $data['hash'] = $fileHash;
        $data['path'] = $filePath;

        if ($data['is_auto_version']) {
            $data['version'] = $authloader ? $authloader->version + 0.01 : 0.01;
        }

        try {
            $creation = AuthLoaderModel::create($data);
        } catch (Exception $e) {
            Storage::delete($filePath);
            return redirect()
                ->back()
                ->withErrors(['file' => 'There was a problem processing the request.'])
                ->withInput();
        }

        return redirect()->back()->with('created', $creation);
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(int $id, Request $request)
    {
        // Validate the request
        $data = $request->validate([
            'updateNote' => ['array'],
            'stage' => ['nullable', 'string', 'in:production,staging,development'],
            // 'file' => [ 'nullable','file'],
            'unsupported_at' => ['nullable'], // We'll handle the validation in code
        ]);

        // Retrieve the existing record
        $authloader = AuthLoaderModel::findOrFail($id);

        // Handle file upload if a new file is provided
        // if ($request->hasFile('file')) {
        //     $file = $request->file('file');
        //     $filePath = $file->store('uploads/loaders', 'public');
        //     $fileHash = md5_file($file->getRealPath());

        //     // Delete the old file if it exists
        //     if ($authloader->path) {
        //         Storage::delete($authloader->path);
        //     }

        //     // Update file details in the data array
        //     $data['hash'] = $fileHash;
        //     $data['path'] = $filePath;
        // }

        // Parse the `unsupported_at` date from the incoming request if present
        if (isset($data['unsupported_at']) && is_array($data['unsupported_at'])) {
            $unsupportedAt = $data['unsupported_at'];

            // Check that year, month, and day are present
            if (isset($unsupportedAt['year'], $unsupportedAt['month'], $unsupportedAt['day'])) {
                try {
                    // Create a Carbon instance from the parsed values
                    $unsupportedAtDate = Carbon::createFromDate($unsupportedAt['year'], $unsupportedAt['month'], $unsupportedAt['day'])->format('Y-m-d H:i:s');

                    // Assign the formatted date to the data array
                    $data['unsupported_at'] = $unsupportedAtDate;
                } catch (Exception $e) {
                    // Handle any exception (invalid date, etc.)
                    return redirect()
                        ->back()
                        ->withErrors(['unsupported_at' => 'Invalid date format provided.'])
                        ->withInput();
                }
            } else {
                return redirect()
                    ->back()
                    ->withErrors(['unsupported_at' => 'Date must include year, month, and day.'])
                    ->withInput();
            }
        }

        // Handle updateNote, keeping only non-null values (title and description)
        if (isset($data['updateNote']) && is_array($data['updateNote'])) {
            // Filter out null values from the updateNote array
            $data['updateNote'] = array_filter($data['updateNote'], function ($value) {
                return !is_null($value);
            });

            // If both title and description are null, remove the updateNote array entirely
            if (empty($data['updateNote'])) {
                unset($data['updateNote']);
            }
        }

        // Filter out other null values from the $data array
        $filteredData = array_filter($data, function ($value) {
            return !is_null($value);
        });

        try {
            // Update the existing record with only the non-null values
            $authloader->update($filteredData);
        } catch (Exception $e) {
            // If there's a problem, roll back the file upload
            if (isset($filteredData['path'])) {
                Storage::delete($filteredData['path']);
            }

            return redirect()
                ->back()
                ->withErrors(['file' => 'There was a problem processing the request.'])
                ->withInput();
        }

        return redirect()->back()->with('updated', 'Loader updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
