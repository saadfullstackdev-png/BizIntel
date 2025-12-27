$(document).ready(function () {
    $(document).on('change', '#location_id', function () {
        if ($(this).val() != '') {
            var location_id = $(this).val();
            var div = $(this).parents();
            var op = " ";
            $.ajax({
                type: 'get',
                url: route('admin.resources.get_machinetype'),
                data: {'id': location_id},
                success: function (resposne) {
                    if (resposne.status == '1') {
                        $('#machine_type_id_dropdown').html('');
                        $('#machine_type_id_dropdown').html(resposne.d);
                        $('#machine_type_id').select2({
                            // placeholder: "Select Machine Type",
                        });
                        $('#machinenotexist').hide();
                    } else {
                        $('#machinenotexist').show();
                        var dropdown = '<select id="machine_type_id" class="form-control select2" name="machine_type_id" style="width:100% !important;" required></select>';
                        $('#machine_type_id_dropdown').html(dropdown);
                        $('#machine_type_id').select2({
                            // placeholder: "Select Machine Type",
                        });
                    }
                },
            });
        } else {
            var dropdown = '<select id="machine_type_id" class="form-control select2" name="machine_type_id" style="width:100% !important;" required></select>';
            $('#machine_type_id_dropdown').html('');
            $('#machine_type_id_dropdown').html(dropdown);
            $('#machine_type_id').select2({
                placeholder: "Select Machine Type",
            });
        }
    });
});