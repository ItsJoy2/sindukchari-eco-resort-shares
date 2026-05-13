<!-- CREATE MODAL -->
<div class="modal fade" id="createModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('admin.accounts.store') }}" class="modal-content">
            @csrf

            <div class="modal-header">
                <h5 class="modal-title">Create Items</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">


                {{-- Type --}}
                <div class="mb-2">
                    <label>Type</label>
                    <select name="type" class="form-control" required>
                        <option value="income">Income</option>
                        <option value="expense">Expense</option>
                    </select>
                </div>

                {{-- Category --}}
                <div class="mb-2">
                    <label>Category</label>
                    <select name="category_id" class="form-control" required>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Date --}}
                <div class="mb-2">
                    <label>Date</label>
                    <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}">
                </div>

                {{-- Amount --}}
                <div class="mb-2">
                    <label>Amount</label>
                    <input type="number" step="0.01" name="amount" class="form-control">
                </div>

                {{-- Note --}}
                <div class="mb-2">
                    <label>Note</label>
                    <textarea name="note" class="form-control"></textarea>
                </div>

            </div>

            <div class="modal-footer">
                <button class="btn btn-primary">Create</button>
            </div>

        </form>
    </div>
</div>
