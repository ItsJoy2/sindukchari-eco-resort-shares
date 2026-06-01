<!-- Create Guest Modal -->

<div class="modal fade" id="createGuestModal" tabindex="-1">

```
<div class="modal-dialog modal-lg">

    <form action="{{ route('admin.guest-list.store') }}"
          method="POST">

        @csrf

        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">
                    Add Guest
                </h5>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal">
                </button>
            </div>

            <div class="modal-body">

                <div class="row">

                    <div class="col-md-6 mb-3">
                        <label>Date</label>

                        <input type="date"
                               name="date"
                               class="form-control"
                               value="{{ date('Y-m-d') }}"
                               required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Name</label>

                        <input type="text"
                               name="name"
                               class="form-control"
                               required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Mobile</label>

                        <input type="text"
                               name="mobile"
                               class="form-control">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Address</label>

                        <input type="text"
                               name="address"
                               class="form-control">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Profession</label>

                        <input type="text"
                               name="profession"
                               class="form-control">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Status</label>

                        <select name="status"
                                class="form-control">

                            <option value="Interested">
                                Interested
                            </option>

                            <option value="Highly Motivated">
                                Highly Motivated
                            </option>

                            <option value="Not Interested">
                                Not Interested
                            </option>

                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Reference</label>

                        <input type="text"
                               name="reference"
                               class="form-control">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Note</label>

                        <textarea name="note"
                                  rows="3"
                                  class="form-control"></textarea>
                    </div>

                </div>

            </div>

            <div class="modal-footer">

                <button type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">
                    Close
                </button>

                <button type="submit"
                        class="btn btn-success">
                    Save Guest
                </button>

            </div>

        </div>

    </form>

</div>
```

</div>
