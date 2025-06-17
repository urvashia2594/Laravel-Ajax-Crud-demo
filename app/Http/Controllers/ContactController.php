<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contact;
use Yajra\DataTables\DataTables;
use App\Models\MergedContact;

class ContactController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //
        if ($request->ajax()) {
  
            $data = Contact::latest()->get();
  
            return Datatables::of($data)
                    ->addIndexColumn()
                    ->addColumn('gender', function ($row) {
                        if($row->gender == '1')
                        {
                            return "Male";
                        }elseif($row->gender == '2'){
                            return "Female";
                        }else{
                            return "Other";
                        }
                    })
                    ->addColumn('image', function($row){
                        if ($row->prof_img) {
                            return '<img src="' . asset('uploads/' . $row->prof_img) . '" width="50" height="50">';
                        }
                        return 'No Image';
                    })
                    ->addColumn('document', function($row){
                        if ($row->doc) {
                            return '<a href="' . asset('documents/' . $row->doc) . '" target="_blank">View</a>';
                        }
                        return 'No File';
                    })
                    ->addColumn('custom', function($row){
                        if ($row->custom_fields) {
                            $fields = json_decode($row->custom_fields, true);
                            return collect($fields)->map(fn($item) => $item['key'] . ': ' . $item['value'])->implode('<hr>');
                        }
                        return '—';
                    })
                    ->addColumn('checkbox', function ($row) {
                        return '<input type="checkbox" class="contactCheckbox" value="' . $row->id . '">';
                    })
                    ->addColumn('action', function($row){
   
                           $btn = '<a href="javascript:void(0)" data-toggle="tooltip"  data-id="'.$row->id.'" data-original-title="Edit" class="edit btn btn-primary btn-sm editContact">Edit</a>';
   
                           $btn = $btn.' <a href="javascript:void(0)" data-toggle="tooltip"  data-id="'.$row->id.'" data-original-title="Delete" class="btn btn-danger btn-sm deleteContact">Delete</a>';
    
                            return $btn;
                    })
                    ->rawColumns(['image','action','document','custom','checkbox'])
                    ->make(true);
        }
        
        return view('contact-ajax');
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
        $this->validateContact($request);
        $contactId = $request->contact_id;
        $data = $request->only('name', 'email', 'phone', 'gender');
        if ($request->hasFile('prof_img')) {
            $imageName = time() . '.' . $request->prof_img->extension();
            $request->prof_img->move(public_path('uploads'), $imageName);
            $data['prof_img'] = $imageName;
        }
        if ($request->hasFile('doc')) {
            $file = $request->file('doc');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('documents'), $filename);
            $data['doc'] = $filename;
        }

        // Handle custom fields
        if($contactId)
        {   
            $oldContact = Contact::find($contactId);
            $oldContact->custom_fields = null;
            $oldContact->save();
        }
        if ($request->has('custom_fields')) {
            $data['custom_fields'] = json_encode($request->custom_fields);
        }

        Contact::updateOrCreate(['id' => $contactId], $data);

        return response()->json(['success'=>'Contact saved successfully.']);
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
        $contact = Contact::find($id);
        $contact->custom_fields = json_decode($contact->custom_fields, true) ?? [];
        return response()->json($contact);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        Contact::find($id)->delete();
      
        return response()->json(['success'=>'Contact deleted successfully.']);
    }

    private function validateContact(Request $request)
    {
        $contactId = $request->contact_id;

        $rules = [
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:contacts,email,' . $contactId,
            'phone'     => 'required|digits:10',
            'gender'    => 'required|in:1,2,3',
            'prof_img'  => 'nullable|image|mimes:jpeg,png,jpg,gif',
            'doc'       => 'nullable|mimes:csv,pdf,doc,docx,jpg,png',
            'custom_fields.*.key'   => 'nullable|string|max:255',
            'custom_fields.*.value' => 'nullable|string|max:255',
        ];

        return $request->validate($rules);
    }

    public function fetchMergeContacts(Request $request)
    {
        $contacts = Contact::whereIn('id', $request->ids)->get(['id', 'name', 'email']);
        return response()->json($contacts);
    }

    public function merge(Request $request)
    {
        $request->validate([
            'master_id' => 'required|exists:contacts,id',
            'merge_ids' => 'required|array|min:1',
            'merge_ids.*' => 'exists:contacts,id',
        ]);

        $master = Contact::findOrFail($request->master_id);
        $mergeContacts = Contact::whereIn('id', $request->merge_ids)->get();

        foreach ($mergeContacts as $contact) {
            if ($contact->id == $master->id) continue;

            // Merge phones/emails only if different
            if ($contact->email && $contact->email !== $master->email) {
                $master->email .= ', ' . $contact->email;
            }

            if ($contact->phone && $contact->phone !== $master->phone) {
                $master->phone .= ', ' . $contact->phone;
            }

            // Merge custom fields
            $masterFields = json_decode($master->custom_fields, true) ?? [];
            $mergeFields = json_decode($contact->custom_fields, true) ?? [];

            foreach ($mergeFields as $field) {
                if (!collect($masterFields)->contains(fn($f) => $f['key'] === $field['key'] && $f['value'] === $field['value'])) {
                    $masterFields[] = $field;
                }
            }

            $master->custom_fields = json_encode($masterFields);
            $master->save();

            // Record merge
            MergedContact::create([
                'master_contact_id' => $master->id,
                'merged_contact_id' => $contact->id,
            ]);

            // Optionally delete or mark the merged contact
            $contact->delete();
        }

        return response()->json(['success' => 'Contacts merged successfully.']);
    }
}
