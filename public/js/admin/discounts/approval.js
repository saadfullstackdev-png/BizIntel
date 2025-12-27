$("#AddApproval").click(function () {

    $('#duplicateErr').hide();
    $('#emptyErr').hide();

    var formData = {
        '_token': $('meta[name="csrf-token"]').attr('content'),
        'user_id': $("#user_id").val(),
        'discount_id': $("#discount_id").val(),
    };
    if ($("#user_id").val() && $("#discount_id").val()){
        $.ajax({
            type: 'post',
            url: route('admin.discounts.save_approval'),
            data: formData,
            success: function (resposne) {

                if (resposne.status == '1') {
                    var data = resposne.mydata;
                    $('#table').append("<tr id='HR_' class='HR_" + data.record.id + "'><td>" + data.user + "</td><td>" + data.discount + "</td><td><button onClick='deleteModel(" + data.record.id + ")' class='btn btn-xs btn-danger'>Delete</button></td></tr>");
                } else {
                    $('#duplicateErr').show();
                }
            }
        });
    } else {
        $('#emptyErr').show();
    }
});

function deleteModel(id) {
    $.ajax({
        type: 'post',
        url: route('admin.discounts.delete_approval'),
        data: {
            '_token': $('input[name=_token]').val(),
            'id': id
        },
        success: function (data) {

            $('.HR_' + data).remove();
        }
    });
}
