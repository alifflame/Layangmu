<?php

namespace App\Http\Controllers;

use App\Document;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class DocumentController extends Controller
{
    private function belongsToUser()
    {
        $user = Auth::user();

        return function ($query) use ($user) {
            $query->where('from', 'LIKE', "%{$user->email}%")->orWhere('to', 'LIKE', "%{$user->email}%");
        };
    }

    public function index(Request $request)
    {
        $limit = (int) $request->input('limit');
        $searchTerm = $request->input('q');
        $doc_type = $request->input('doc_type');
        $order_by = $request->input('order_by');
        $order_type = $request->input('order_type');

        $all = (int)$request->input('all');
        $archive = $request->input('archive');


        try {

            $docQuery = Document::query();
            
            if ($archive === "yes") {
                // show archieved document only
                $docQuery = $docQuery->where("archived", "=", 1);
            } else if ($archive === "no") {
                // don't show archieved document
                $docQuery = $docQuery->where("archived", "=", 0);
            }

            if (!$all) {
                // show all document
                $docQuery = $docQuery->where($this->belongsToUser());
            }

            $statsQuery = clone $docQuery;

            $docQuery = $docQuery->orderBy($order_by ?: 'doc_date', $order_type ?: 'desc');

            if ($searchTerm) {
                $docQuery = $docQuery->where(function ($query) use ($searchTerm) {
                    $query->where(function ($query) use ($searchTerm) {
                        $query->where('doc_type', '=', '0')
                            ->where('from', 'LIKE', "%{$searchTerm}%");
                    })->orWhere(function ($query) use ($searchTerm) {
                        $query->where('doc_type', '=', '1')
                            ->where('to', 'LIKE', "%{$searchTerm}%");
                    })->orWhere('subject', 'LIKE', "%{$searchTerm}%");
                });
            }

            if ($doc_type === "in" or $doc_type === "out") {
                $code = $doc_type === "in" ? 0 : 1;

                $docQuery = $docQuery->where("doc_type", "=", "$code");
            }

            $pagedDocs = $docQuery->paginate($limit);

            $documentsTotal = $statsQuery->count();
            $docInTotal =  $statsQuery->where('doc_type', '=', '0')->count();

            return response()->json([
                'total_documents' => [
                    'all' => $documentsTotal,
                    'in' => $docInTotal,
                    'out' => $documentsTotal - $docInTotal,
                ],
                'total_pagged' => $pagedDocs->total(),
                'data' => $pagedDocs->toArray()['data'],
                'current_page' => $pagedDocs->currentPage(),
                'last_page' => $pagedDocs->lastPage(),
            ], 200);

        } catch (QueryException $exception) {            
            return response()->json(["message" => $exception->getMessage()], 404);
        }
    }

    public function show($id)
    {
        return Document::find($id) ?: response()->json([
            "message" => "NO_MATCHING_DOCUMENT_FOUND",
        ], 404);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            "file" => 'required|file|max:8192',
            "from" => 'required|email',
            "to" => 'required|email',
            "subject" => 'required',
            "doc_date" => 'required|date',
            "doc_type" => 'required|numeric|min:0|max:1',
            "doc_number" => 'required|numeric',
        ]);

        $file = $request->file('file');

        $fileName = $file->getClientOriginalName();

        $saved_name = uniqid() . '_' . $fileName;

        $path = "uploads" . DIRECTORY_SEPARATOR . "documents" . DIRECTORY_SEPARATOR;

        try {
            $file->move($path, $saved_name);
        } catch (\Exception $error) {
            return response()->json(['message' => $error->getMessage()], 500);
        }

        $document = new Document();

        $document->from = $request->input("from");
        $document->to = $request->input("to");
        $document->subject = $request->input("subject");
        $document->doc_date = $request->input("doc_date");
        $document->doc_type = $request->input("doc_type");
        $document->doc_number = $request->input("doc_number");
        $document->file_path = $path . $saved_name;

        $document->save();

        return response()->json(['document' => $document], 201);
    }

    public function file($id)
    {
        $document = Document::find($id);

        $filePath = $document->file_path;

        if (!file_exists($filePath)) {
            return response()->json(["message" => "FILE_DOES_NOT_EXISTS"], 404);
        }

        return response()->download($filePath);
    }

    public function update(Request $request, $id)
    {

        $document = Document::find($id);

        if (!$document) {
            return response()->json(["message" => "NO_MATCHING_DOCUMENT_FOUND"], 404);
        }

        $document->update($request->all());

        return response()->json($document, 200);
    }

    public function delete($id)
    {
        $document = Document::find($id);

        if ($document) {
            File::delete($document->file_path);
            $document->delete();
        }

        return response()->json(null, 204);
    }

    public function archive($id)
    {
        $document = Document::find($id);

        if (!$document) {
            return response()->json(["message" => "DOCUMENT_NOT_FOUND"], 404);
        }

        $document->archived = true;

        $document->save();

        return response()->json(["message" => "DOCUMENT_ARCHIVED"], 200);
    }

    public function unarchive($id)
    {
        $document = Document::find($id);

        $document->archived = false;

        $document->save();

        return response()->json(["message" => "DOCUMENT_UNARCHIVED"], 200);
    }

    public function multipleArchive(Request $request)
    {
        $this->validate($request, [
            "documents" => 'required',
        ]);

        $documents = json_decode($request->input('documents'));

        foreach ($documents as $doc_id) {

            $document = Document::find($doc_id);

            $document->archived = true;

            $document->save();
        }

        return response()->json(["message" => "MULTIPLE_ARCHIVE_SUCCESS"], 200);
    }

    public function multipleUnarchive(Request $request)
    {
        $this->validate($request, [
            "documents" => 'required',
        ]);

        $documents = json_decode($request->input('documents'));

        foreach ($documents as $doc_id) {

            $document = Document::find($doc_id);

            $document->archived = false;

            $document->save();
        }

        return response()->json(["message" => "MULTIPLE_UNARCHIVE_SUCCESS"], 200);
    }

    public function multipleDelete(Request $request)
    {
        $this->validate($request, [
            "documents" => 'required',
        ]);

        $documents = json_decode($request->input('documents'));

        $validatedDocuments = [];

        foreach ($documents as $doc_id) {
           $document = Document::find($doc_id);

           if(!$document) {
               return response()->json(["message" => "DOCUMENT_NOT_FOUND", "doc_id" => $doc_id], 404);
           }

          $validatedDocuments[] = $document;
        }

        foreach ($validatedDocuments as $document) {
            $document->delete();
        }

        return response()->json(["message" => "MULTIPLE_DELETE_SUCCESS"], 200);
    }
}
