<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" id="editForm" class="modal-content">
            @csrf
            @method('PUT')

            <div class="modal-header">
                <h5>Edit Items</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">


                {{-- Type --}}
                <div class="mb-2">
                    <label>Type</label>
                    <select name="type" id="edit_type" class="form-control" required>
                        <option value="income">Income</option>
                        <option value="expense">Expense</option>
                    </select>
                </div>

                {{-- Category --}}
                <div class="mb-2">
                    <label>Category</label>
                    <select name="category_id" id="edit_category" class="form-control" required>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Date --}}
                <div class="mb-2">
                    <label>Date</label>
                    <input type="date" name="date" id="edit_date" class="form-control" required>
                </div>

                {{-- Amount --}}
                <div class="mb-2">
                    <label>Amount</label>
                    <input type="number" step="0.01" name="amount" id="edit_amount" class="form-control" required>
                </div>

                {{-- Note --}}
                <div class="mb-2">
                    <label>Note</label>
                    <textarea name="note" id="edit_note" class="form-control"></textarea>
                </div>

            </div>

            <div class="modal-footer">
                <button class="btn btn-success">Update</button>
            </div>

        </form>
    </div>
</div>

<script>
document.querySelectorAll('.editBtn').forEach(button => {
    button.addEventListener('click', function () {

        let id = this.dataset.id;

        document.getElementById('edit_type').value = this.dataset.type;
        document.getElementById('edit_category').value = this.dataset.category;
        document.getElementById('edit_date').value = this.dataset.date;
        document.getElementById('edit_amount').value = this.dataset.amount;
        document.getElementById('edit_note').value = this.dataset.note ?? '';

        // ✅ FIX ROUTE (important)
        document.getElementById('editForm').action = "{{ url('admin/accounts') }}/" + id;
    });
});
</script>
