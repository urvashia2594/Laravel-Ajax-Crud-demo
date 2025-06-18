<!DOCTYPE html>
<html>
<head>
    <title>Laravel Ajax CRUD Tutorial Example - ItSolutionStuff.com</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap.min.css" />
    <link href="https://cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.10.19/css/dataTables.bootstrap4.min.css" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>  
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.js"></script>
    <script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.19/js/dataTables.bootstrap4.min.js"></script>
    <style>
        .is-invalid {
            border-color: #dc3545;
        }
    </style>

</head>
<body>
      
<div class="container">
    <h1>Laravel Ajax CRUD Demo</h1>
    <div id="successMessage" class="alert alert-success" style="display:none;"></div>
    <a class="btn btn-success" href="javascript:void(0)" id="createNewContact"> Create New Contact</a>
    <button class="btn btn-warning" id="mergeContactsBtn">Merge Contacts</button>
    <table class="table table-bordered data-table">
        <thead>
            <tr>
                <th>No</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Gender</th>
                <th>Image</th>
                <th>Document</th>
                <th>Custom Field</th>
                <th><input type="checkbox" id="selectAll" /></th>
                <th width="280px">Action</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>
     
<div class="modal fade" id="ajaxModel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="modelHeading"></h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="formErrors" class="alert alert-danger d-none"></div>
                <form id="contactForm" name="contactForm" class="form-horizontal" enctype="multipart/form-data">
                   <input type="hidden" name="contact_id" id="contact_id">
                    <div class="form-group">
                        <label for="name" class="col-sm-2 control-label">Name</label>
                        <div class="col-sm-12">
                            <input type="text" class="form-control" id="name" name="name" placeholder="Enter Name"  >
                        </div>
                    </div>
       
                    <div class="form-group">
                        <label for="name" class="col-sm-2 control-label">Email</label>
                        <div class="col-sm-12">
                            <input type="text" class="form-control" id="email" name="email" placeholder="Enter Email"  >
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="name" class="col-sm-4 control-label">Phone No</label>
                        <div class="col-sm-12">
                            <input type="text" class="form-control" id="phone" name="phone" placeholder="Enter Phone No"  >
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="name" class="col-sm-2 control-label">Gender</label>
                        <div class="col-sm-12">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="gender" id="gender1" value="1">
                                <label class="form-check-label" for="gender1">Male</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="gender" id="gender2" value="2">
                                <label class="form-check-label" for="gender2">Female</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="gender" id="gender3" value="3">
                                <label class="form-check-label" for="gender3">Other</label>
                            </div>
                        </div>
                    </div>

                   

                    <div class="form-group">
                        <label for="image" class="col-sm-2 control-label">Image</label>
                        <div class="col-sm-12">
                            <input type="file" id="prof_img" name="prof_img" class="form-control">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="document">Upload Document</label>
                        <div class="col-sm-12">
                            <input type="file" name="doc" id="doc" class="form-control">
                        </div>
                    </div>

                    <!-- custom field -->
                    <div class="form-group">
                        <div id="customFieldsWrapper">
                            <!-- Will append dynamic fields here -->
                        </div>
                        <div class="text-left mt-2">
                        <button type="button" id="addCustomField" class="btn btn-sm btn-secondary">Add Field</button>
                        </div>
                    </div>
                    

                    
                    <div class="form-group text-left">
                     <button type="submit" class="btn btn-primary" id="saveBtn" value="create">Save changes
                     </button>
                     <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="mergeModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Merge Contacts</h5></div>
      <div class="modal-body">
        <p>Select the master contact:</p>
        <select id="masterContactSelect" class="form-control"></select>
      </div>
      <div class="modal-footer">
        <button type="button" id="confirmMerge" class="btn btn-primary">Confirm Merge</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="mergedContactsModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Merged Contact History</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
      </div>
      <div class="modal-body">
        <ul id="mergedContactList" class="list-group"></ul>
      </div>
    </div>
  </div>
</div>

      
</body>
      
<script type="text/javascript">
  $(function () {
      
    /*------------------------------------------
     --------------------------------------------
     Pass Header Token
     --------------------------------------------
     --------------------------------------------*/ 
    $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          }
    });
      
    /*------------------------------------------
    --------------------------------------------
    Render DataTable
    --------------------------------------------
    --------------------------------------------*/
    var table = $('.data-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('contact.index') }}",
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
            {data: 'name', name: 'name'},
            {data: 'email', name: 'email'},
            {data: 'phone', name: 'phone'},
            {data: 'gender', name: 'gender'},
            { data: 'image', name: 'image', orderable: false, searchable: false },
            { data: 'document', name: 'document', orderable: false, searchable: false },
            { data: 'custom', name: 'custom', orderable: false, searchable: false },
            { 
                data: 'checkbox', 
                name: 'checkbox', 
                orderable: false, 
                searchable: false 
            },
            {data: 'action', name: 'action', orderable: false, searchable: false},
        ]
    });
      
    /*------------------------------------------
    --------------------------------------------
    Click to Button
    --------------------------------------------
    --------------------------------------------*/
    $('#createNewContact').click(function () {
        $('#saveBtn').val("create-product");
        $('#contact_id').val('');
        $('#contactForm').trigger("reset");
        $('#modelHeading').html("Create New Product");
        $('#ajaxModel').modal('show');

        $('#formErrors').addClass('d-none');
        $('#errorList').html('');
    });
      
    /*------------------------------------------
    --------------------------------------------
    Click to Edit Button
    --------------------------------------------
    --------------------------------------------*/
    $('body').on('click', '.editContact', function () {
      var contact_id = $(this).data('id');
      $.get("{{ route('contact.index') }}" +'/' + contact_id +'/edit', function (data) {
          $('#modelHeading').html("Edit Product");
          $('#saveBtn').val("edit-user");
          $('#ajaxModel').modal('show');
          $('#contact_id').val(data.id);
          $('#name').val(data.name);
          $('#email').val(data.email);
          $('#phone').val(data.phone);
          $('input[name="gender"][value="' + data.gender + '"]').prop('checked', true);
          populateCustomFields(data.custom_fields);
          $('#formErrors').addClass('d-none');
          $('#errorList').html('');
         

      })
    });
      
    /*------------------------------------------
    --------------------------------------------
    Create Product Code
    --------------------------------------------
    --------------------------------------------*/
    $('#saveBtn').click(function (e) {
        e.preventDefault();
        $(this).html('Sending..');
        
          // Clear old errors
        $('#formErrors').html('').addClass('d-none');
        $('.form-control').removeClass('is-invalid');

        var form = $('#contactForm')[0];
        var formData = new FormData(form);

        if ($('#customFieldsWrapper').children().length === 0) {
            formData.append('custom_fields', JSON.stringify([]));
        }

        $.ajax({
          data: formData,
          url: "{{ route('contact.store') }}",
          type: "POST",
          dataType: 'json',
          processData: false,
          contentType: false,
          headers: {
    'Accept': 'application/json' 
  },
          success: function (data) {
       
              $('#contactForm').trigger("reset");
              $('#ajaxModel').modal('hide');
              $('#saveBtn').html('Save Changes');
              table.draw();
              $('#successMessage').text(data.success).fadeIn().delay(3000).fadeOut();
           
          },
          error: function (xhr) {
            $('#saveBtn').html('Save Changes');
            $('#formErrors').html('').addClass('d-none');
            $('.form-control').removeClass('is-invalid');

            try {
                const response = JSON.parse(xhr.responseText);

                if (xhr.status === 422 && response.errors) {
                    $('#formErrors').removeClass('d-none');

                    $.each(response.errors, function (key, messages) {
                        messages.forEach(function (message) {
                            $('#formErrors').append('<div>' + message + '</div>');
                        });

                        // Highlight invalid field
                        $('#' + key).addClass('is-invalid');
                    });
                } else {
                    $('#formErrors')
                    .removeClass('d-none')
                    .html('<div class="text-danger">Unexpected validation error occurred.</div>');
                }

            } catch (e) {
                console.error("Error parsing response:", e);
                console.error("Raw response:", xhr.responseText);
                $('#formErrors')
                .removeClass('d-none')
                .html('<div class="text-danger">Unexpected error occurred. Please try again.</div>');
            }
        }
      });
    });
      
    /*------------------------------------------
    --------------------------------------------
    Delete Product Code
    --------------------------------------------
    --------------------------------------------*/
    $('body').on('click', '.deleteContact', function () {
     
        var contact_id = $(this).data("id");
        confirm("Are You sure want to delete !");
        if(contact_id)
        {
            $.ajax({
            type: "DELETE",
            url: "{{ route('contact.store') }}"+'/'+contact_id,
            success: function (data) {
                table.draw();
            },
            error: function (data) {
                console.log('Error:', data);
            }
        });
        }
       
    });

    let customFieldIndex = 0;

    // Add field
    $('#addCustomField').on('click', function () {
        $('#customFieldsWrapper').append(`
            <div class="custom-field-group" data-index="${customFieldIndex}">
                <input type="text" name="custom_fields[${customFieldIndex}][key]" placeholder="Field name">
                <input type="text" name="custom_fields[${customFieldIndex}][value]" placeholder="Field value">
                <button type="button" class="removeField">Remove</button>
            </div>
        `);
        customFieldIndex++;
    });

    // Remove field
    $(document).on('click', '.removeField', function () {
        $(this).closest('.custom-field-group').remove();
    });

    // Edit mode: populate custom fields
    function populateCustomFields(customFields) {
        $('#customFieldsWrapper').empty();
        customFieldIndex = 0;

        if (customFields) {
            for (const [key, value] of Object.entries(customFields)) {
                $('#customFieldsWrapper').append(`
                    <div class="custom-field-group" data-index="${customFieldIndex}">
                        <input type="text" name="custom_fields[${customFieldIndex}][key]" value="${value.key}" placeholder="Field name">
                        <input type="text" name="custom_fields[${customFieldIndex}][value]" value="${value.value}" placeholder="Field value">
                        <button type="button" class="removeField">Remove</button>
                    </div>
                `);
                customFieldIndex++;
            }
        }
    }

    
       
  });

  //Merge contact js

    let selectedIds = [];

    $('#selectAll').on('click', function () {
        $('.contactCheckbox').prop('checked', this.checked);
    });
   
    $('#mergeContactsBtn').on('click', function () {
    let selected = $('.contactCheckbox:checked').map(function () {
        return $(this).val();
    }).get();

    if (selected.length < 2) {
        alert('Select at least 2 contacts to merge.');
        return;
    }

    // Send as an array (jQuery will encode as ?ids[]=1&ids[]=2)
    $.get('/api/contacts', { ids: selected }, function(data) {
        let options = data.map(c => `<option value="${c.id}">${c.name} - (${c.email})</option>`).join('');
        $('#masterContactSelect').html(options);
        $('#mergeModal').modal('show');
    });
});

    $('#confirmMerge').on('click', function () {
        let master_id = $('#masterContactSelect').val();
        let ids = $('.contactCheckbox:checked').map(function () {
            return $(this).val();
        }).get().filter(id => id != master_id);

        $.post("{{ route('contact.merge') }}", {
            master_id,
            ids
        }, function (res) {
            $('#mergeModal').modal('hide');
            alert(res.success);
            $('.data-table').DataTable().ajax.reload();
        });
    });

    $(document).on('click', '.showMergedBtn', function () {
        let contactId = $(this).data('id');

        $.get(`/contacts/${contactId}/merged`, function (data) {
            console.log('Merged contact data:', data);
            if (data.length === 0) {
                alert("No merged contact data found.");
                return;
            }

            let html = data.map(item => {
            let mergedIds = item.merged_ids;

            let fields = item.custom_fields 
                ? Object.entries(item.custom_fields).map(([k, v]) => `<li>${v.key}: ${v.value}</li>`).join('')
                : '';

            return `
                <li class="list-group-item">
                    <strong>${item.name}</strong> (${item.email ?? 'No email'})<br/>
                    Phone: ${item.phone ?? 'N/A'}, Gender: ${item.gender ?? 'N/A'}<br/>
                    <strong>Merged IDs:</strong> ${mergedIds}<br/>
                    <strong>Custom Fields:</strong>
                    <ul>${fields}</ul>
                </li>
            `;
        }).join('');

            $('#mergedContactList').html(html);
            $('#mergedContactsModal').modal('show');
        });
    });


</script>
</html>