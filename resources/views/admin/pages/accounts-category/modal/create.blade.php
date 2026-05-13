<!-- CREATE CATEGORY MODAL -->
<div class="modal fade" id="createCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('admin.accounts-category.store') }}" class="modal-content">
            @csrf

            <div class="modal-header">
                <h5 class="modal-title">Create Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                {{-- Name --}}
                <div class="mb-2">
                    <label>Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>

                {{-- Status --}}
                <div class="mb-2">
                    <label>Status</label>
                    <select name="status" class="form-control" required>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>

            </div>

            <div class="modal-footer">
                <button class="btn btn-primary">Create</button>
            </div>

        </form>
    </div>
</div>
