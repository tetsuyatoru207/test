<?php /** @var array $data */ ?>

<div class="card mb-3">
    <div class="card-header">
        <h5>Bộ lọc và Tìm kiếm</h5>
    </div>
    <div class="card-body">
        <form class="row g-3" filter-form>
            <div class="col-6"></div>
            <div class="col-6 d-flex justify-content-end">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Tìm theo số phòng hoặc loại phòng">
                    <button type="submit" class="btn btn-primary">Tìm kiếm</button>
                </div>
            </div>
            <div class="col-md-3">
                <label for="room-type" class="form-label">Loại phòng</label>
                <select id="room-type" name="room-type" class="form-select">
                    <option value="">Tất cả loại phòng</option>
                    <?php foreach ($data['room_types'] as $roomType): ?>
                        <option value="<?php echo $roomType['ROOMTYPE_ID']; ?>">
                            <?php echo htmlspecialchars($roomType['ROOMTYPE_NAME']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="min-price" class="form-label">Giá thấp nhất</label>
                <input type="number" id="min-price" name="min-price" class="form-control" min="0" placeholder="Ví dụ: 300000">
            </div>

            <div class="col-md-3">
                <label for="max-price" class="form-label">Giá cao nhất</label>
                <input type="number" id="max-price" name="max-price" class="form-control" min="0" placeholder="Ví dụ: 1000000">
            </div>
            <div class="col-md-3">
                <label for="sort-by" class="form-label">Sắp xếp theo</label>
                <select id="sort-by" name="sort-by" class="form-select">
                    <option value="">Số phòng tăng dần</option>
                    <option value="price_asc">Giá thấp đến cao</option>
                    <option value="price_desc">Giá cao đến thấp</option>
                </select>
            </div>

            <div class="col d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary">Lọc</button>
                <button type="button" class="btn btn-secondary" reset-filter>Làm lại</button>
            </div>
        </form>
    </div>
</div>
