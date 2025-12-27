<div id="duplicateErr" class="alert alert-danger display-hide">
    <button class="close" data-close="alert"></button>
    Dupliocate record found, please select another one.
</div>
<div id="emptyErr" class="alert alert-danger display-hide">
    <button class="close" data-close="alert"></button>
    Please select both Centre and Services to continue.
</div>
<div class="row">
    <div class="col-md-5">
        <h4>Centre</h4>
        <select name="location_id" class="form-control select2" id="location_id" required>
            <option value="">Select Center</option>
            @foreach($location as $locaiton)
                <optgroup label="{{$locaiton['name']}}">
                    @foreach($locaiton['children'] as $child)
                        <option value="{{$child['id']}}"><?php echo $child['name']; ?></option>
                    @endforeach
                </optgroup>
            @endforeach
        </select>
    </div>
    <div class="col-md-5">
        <h4>Services</h4>
        <select class="service_id form-control select2" id="service_id_final" style="width:100% !important;">
            <option value="0" disabled="true" selected="true">Select Service</option>
        </select>
    </div>

    <input type="hidden" id="doctor_id" class="form-control" name="doctor_id" value="{{$doctor->id}}">

    <div class="col-md-2">
        <button class="btn btn-success" type="submit" name="AddService" id="AddService" style="margin-top: 39px;">Add
        </button>
    </div>
</div>
<br><br>
<div class="table-responsive">
    <table id="table" class="table table-striped table-bordered table-advance table-hover">
        {{ csrf_field() }}
        <?php $no = 1; $count = 0;?>
        <thead>
        <tr>
            <th>Location</th>
            <th>Service</th>
            <th>Action</th>
        </tr>
        </thead>
        @if($doctor_has_location)
            @foreach($doctor_has_location as $doctor_has_location)
                <tr class="HR_{{$doctor_has_location->id}}">
                    <td><?php echo $doctor_has_location->location->city->name; ?>-<?php echo $doctor_has_location->location->name; ?></td>
                    <td><?php echo $doctor_has_location->service->name  ?></td>
                    <td>
                        <button class="btn btn-xs btn-danger" onClick=deleteModel('{{$doctor_has_location->id}}')>
                            Delete
                        </button>
                    </td>
                </tr>
                <?php $count++; ?>
            @endforeach
        @endif
    </table>
</div>
<script>
    $(document).ready(function () {
        $(document).on('change', '#location_id', function () {
            var location_id = $(this).val();
            var div = $(this).parents();
            var op = " ";
            $.ajax({
                type: 'get',
                url: route('admin.doctors.get_service'),
                data: {'id': location_id},
                success: function (myarray) {

                    op += '<option value="0" selected disabled>Select Service</option>';

                    for (var i = 0; i < myarray.d.length; i++) {

                        op += '<option value="' + myarray.locaiton_id_1 + "," + myarray.d[i].id + '">' + myarray.d[i].name + '</option>';
                    }
                    div.find('.service_id').html("");
                    div.find('.service_id').append(op);
                },
                error: function () {
                }
            });
        });
    });
    $("#AddService").click(function () {
        $('#duplicateErr').hide();
        $('#emptyErr').hide();

        var e = document.getElementById("service_id_final");
        var productInfo = e.options[e.selectedIndex].value;

        if(productInfo.split(',').length < 2) {
            $('#emptyErr').show();
        } else {
            $.ajax({
                type: 'get',
                url: route('admin.doctors.save_service'),
                data: {
                    '_token': $('input[name=_token]').val(),
                    'doctor_id': $('input[name=doctor_id]').val(),
                    'id': productInfo
                },
                success: function (resposne) {
                    if (resposne.status == '1') {
                        var data = resposne.mydata;
                        $('#table').append("<tr id='HR_' class='HR_" + data.record.id + "'><td>" + data.record_locaiton_name + "</td><td>" + data.record_service_name + "</td><td><button class='btn btn-xs btn-danger' onClick='deleteModel(" + data.record.id + ")'>Delete</button></td></tr>");
                    } else {
                        $('#duplicateErr').show();
                    }
                }
            });
        }
    });

    function deleteModel(id) {
        $.ajax({
            type: 'post',
            url: route('admin.doctors.delete_service'),
            data: {
                '_token': $('input[name=_token]').val(),
                'id': id
            },
            success: function (data) {

                $('.HR_' + data).remove();
            }
        });
    }
</script>
<script src="{{ url('js/admin/doctors/fields.js') }}" type="text/javascript"></script>
