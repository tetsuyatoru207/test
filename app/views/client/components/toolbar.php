<div class="card mb-3">
    <div class="card-header">
        <h5>Chọn phòng đặt</h5>
    </div>

    <div class="card-body">
        <div class="row align-items-center g-2">
            <div class="col-md-6">
                <label class="form-check-label">
                    <input type="checkbox" class="form-check-input" checkbox-multi>
                    Chọn tất cả phòng đang hiển thị
                </label>
            </div>

            <div class="col-md-6 d-flex justify-content-end gap-2">
                <a href="<?php echo URLROOT; ?>/booking" class="btn btn-outline-primary">
                    Qua trang đặt phòng
                </a>

                <button type="submit" class="btn btn-success">
                    Đặt phòng đã chọn
                </button>
            </div>
        </div>
    </div>
</div>
