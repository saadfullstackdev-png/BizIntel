<div id="duplicateErr" class="alert alert-danger display-hide">
    <button class="close" data-close="alert"></button>
    Dupliocate record found, please select another one.
</div>
<div id="emptyErr" class="alert alert-danger display-hide">
    <button class="close" data-close="alert"></button>
    Please select Application User.
</div>
<div class="row">
    <div class="col-md-10">
        <h4>Application User</h4>
        <select name="location_id" class="form-control select2" id="user_id" required>
            @foreach($users as $key => $user)
                <option value="{{$key}}">{{$user}}</option>
            @endforeach
        </select>
    </div>

    <input type="hidden" id="discount_id" class="form-control" name="discount_id" value="{{$discount->id}}">

    <div class="col-md-2">
        <button class="btn btn-success" type="submit" name="AddApproval" id="AddApproval" style="margin-top: 39px;">
            Add
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
            <th>User</th>
            <th>Discount</th>
            <th>Action</th>
        </tr>
        </thead>
        @if($discount_approvals)
            @foreach($discount_approvals as $discount_approval)
                <tr class="HR_{{$discount_approval->id}}">
                    <td><?php echo $discount_approval->user->name; ?></td>
                    <td><?php echo $discount_approval->discount->name  ?></td>
                    <td>
                        <button class="btn btn-xs btn-danger" onClick=deleteModel('{{$discount_approval->id}}')>
                            Delete
                        </button>
                    </td>
                </tr>
                <?php $count++; ?>
            @endforeach
        @endif
    </table>
</div>
<script src="{{ url('js/admin/discounts/approval.js') }}" type="text/javascript"></script>
<script src="{{ url('js/admin/discounts/fields.js') }}" type="text/javascript"></script>
