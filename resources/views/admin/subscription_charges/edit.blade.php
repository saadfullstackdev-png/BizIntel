@extends('layouts.app')

@section('content')
    <h1>Edit Subscription Charge</h1>
    <form action="{{ route('admin.subscription-charges.update', $charge->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="amount">Amount</label>
            <input type="number" name="amount" id="amount" class="form-control" value="{{ $charge->amount }}" required>
        </div>

        <div id="repeater">
            @foreach($charge->categories as $category)
                <div class="repeater-item">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="category_id">Category</label>
                            {!! Form::select('category_id[]', $categories, $category->id, ['class' => 'form-control select2 category-select']) !!}
                        </div>
                        <div class="form-group col-md-6">
                            <label for="offered_discount">Offered Discount</label>
                            <input type="number" name="offered_discount[]" id="offered_discount" class="form-control" value="{{ $category->pivot->offered_discount }}" required>
                        </div>
                    </div>
                    <button type="button" class="btn btn-danger btn-sm remove-item">Remove</button>
                </div>
            @endforeach
        </div>
        
        <button type="button" id="add-item" class="btn btn-success btn-sm">Add Another</button>

        <button type="submit" class="btn btn-primary mt-3">Update</button>
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

                // Reinitialize Select2
                $(newItem).find('.select2').select2();
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

            // Initialize Select2
            $('.select2').select2();
        });
    </script>
@endsection
