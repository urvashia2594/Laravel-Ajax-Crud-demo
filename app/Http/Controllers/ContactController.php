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
  
            $data = Contact::with('merged')->latest()->get();
  
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
                            return '<img src="' . asset('storage/' . $row->prof_img) . '" width="50" height="50">';
                        }
                        return 'No Image';
                    })
                    ->addColumn('document', function($row){
                        if ($row->doc) {
                            return '<a href="' . asset('storage/' . $row->doc) . '" target="_blank">View</a>';
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

                            if($row->merged->count())
                                $btn.=   '<button class="btn btn-sm btn-info showMergedBtn" data-id=" '. $row->id .'">Show Merged</button>';
                            
    
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
            $imagePath = $request->file('prof_img')->store('ProfileImage', 'public');
            $data['prof_img'] = $imagePath; 
        }

        if ($request->hasFile('doc')) {
            $docPath = $request->file('doc')->store('documents', 'public');
            $data['doc'] = $docPath; // Save 'documents/filename.pdf' in DB
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
            'ids' => 'required|array|min:2',
            'master_id' => 'required|integer|exists:contacts,id',
        ]);
        
        $ids = $request->ids;
        $masterId = $request->master_id;
    
        $master = Contact::findOrFail($masterId);
        $others = Contact::whereIn('id', $ids)->where('id', '!=', $masterId)->get();
    
        $mergedEmails = [$master->email];
        $mergedPhones = [$master->phone];
        $mergedFields = json_decode($master->custom_fields, true) ?? [];
    
        foreach ($others as $contact) {
            if ($contact->email) $mergedEmails[] = $contact->email;
            if ($contact->phone) $mergedPhones[] = $contact->phone;
    
            $otherFields = json_decode($contact->custom_fields, true) ?? [];
            foreach ($otherFields as $field) {
                if (!in_array($field, $mergedFields)) {
                    $mergedFields[] = $field;
                }
            }
        }
    
        // Save merged result in new table
        MergedContact::create([
            'merged_ids' =>implode(',', $ids),
            'master_id' => $masterId,
            'name' => $master->name,
            'email' => implode(',', array_unique($mergedEmails)),
            'phone' => implode(',', array_unique($mergedPhones)),
            'gender' => $master->gender,
            'image' => $master->prof_img,
            'document' => $master->doc,
            'custom_fields' => $mergedFields,
        ]);
    
        // Delete original records (or flag them as merged)
        Contact::whereIn('id', $ids)->delete();
    
        return response()->json(['success' => 'Contacts merged and saved to merged_contacts table.']);
    }

    public function showMergedContacts($id)
    {
        $contact = Contact::with('merged')->findOrFail($id);
        return response()->json($contact->merged);
    }
}
