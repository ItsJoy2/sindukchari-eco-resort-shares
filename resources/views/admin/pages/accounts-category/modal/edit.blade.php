<!-- EDIT CATEGORY MODAL -->
<div class="modal fade" id="editCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" id="editCategoryForm" class="modal-content">
            @csrf
            @method('PUT')

            <div class="modal-header">
                <h5 class="modal-title">Edit Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                {{-- Name --}}
                <div class="mb-2">
                    <label>Name</label>
                    <input type="text" name="name" id="edit_category_name" class="form-control" required>
                </div>

                {{-- Status --}}
                <div class="mb-2">
                    <label>Status</label>
                    <select name="status" id="edit_category_status" class="form-control" required>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>

            </div>

            <div class="modal-footer">
                <button class="btn btn-success">Update</button>
            </div>

        </form>
    </div>
</div>

<script>
document.querySelectorAll('.editCategoryBtn').forEach(button => {
    button.addEventListener('click', function () {

        let id = this.dataset.id;

        document.getElementById('edit_category_name').value = this.dataset.name;
        document.getElementById('edit_category_status').value = this.dataset.status;

        // dynamic route
        document.getElementById('editCategoryForm').action =
            "{{ url('admin/accounts-category') }}/" + id;
    });
});
</script>
