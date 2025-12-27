@extends('layouts.app')

@section('content')
    <h1>Add Subscription Charge</h1>
    <form action="{{ route('admin.subscription-charges.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="amount">Amount</label>
            <input type="number" name="amount" id="amount" class="form-control" required>
        </div>

        <div id="repeater">
            <div class="repeater-item">
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="category_id">Category</label>
                        {!! Form::select('category_id[]', $categories, null, ['class' => 'form-control select2 category-select']) !!}
                    </div>
                    <div class="form-group col-md-6">
                        <label for="offered_discount">Offered Discount</label>
                        <input type="number" name="offered_discount[]" id="offered_discount" class="form-control" required>
                        {{-- @error('offered_discount.*')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror --}}
                    </div>
                </div>
                <button type="button" class="btn btn-danger btn-sm remove-item" style="margin: 10px 0px 5px 0px">Remove</button>
            </div>
        </div>

        <button type="button" id="add-item" class="btn btn-success btn-sm" style="margin: 5px 0px 15px 0px">Add Another</button>
        <br>

        <button type="submit" class="btn btn-primary mt-3">Save</button>
        <a href="{{ route('admin.subscription-charges.index') }}" class="btn btn-secondary mt-3">Cancel</a>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const repeater = document.getElementById('repeater');
            const addItemBtn = document.getElementById('add-item');

            addItemBtn.addEventListener('click', function () {
                const newItem = document.querySelector('.repeater-item').cloneNode(true);
                newItem.querySelectorAll('input, select').forEach(field => field.value = '');
                repeater.appendChild(newItem);
            });

            repeater.addEventListener('click', function (e) {
                if (e.target.classList.contains('remove-item')) {
                    if (document.querySelectorAll('.repeater-item').length > 1) {
                        e.target.closest('.repeater-item').remove();
                    } else {
                        alert('You need at least one item.');
                    }
                }
            });
        });
    </script>
@endsection