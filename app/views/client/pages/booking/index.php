<?php /** @var array $data */ ?>

<div class="booking-page">
    <div class="page-title">
        <div>
            <h1><?php echo $data['title']; ?></h1>
            <p><?php echo $data['description']; ?></p>
        </div>

        <a href="<?php echo URLROOT; ?>/rooms" class="btn btn-outline-primary">
            Chọn lại
        </a>
    </div>

    <div class="booking-box">
        <h2>Phòng đã chọn</h2>
        <div id="cart-list"></div>

        <div class="booking-form">
            <div>
                <label>Ngày nhận phòng</label>
                <input type="date" id="checkin" class="form-control">
            </div>

            <div>
                <label>Ngày trả phòng</label>
                <input type="date" id="checkout" class="form-control">
            </div>

            <div>
                <label>Phương thức thanh toán</label>
                <select id="payment-method" class="form-select">
                    <option value="Cash">Thanh toán tại quầy</option>
                    <option value="Bank Transfer">Chuyển khoản ngân hàng</option>
                </select>
            </div>
        </div>

        <div class="note-box">
            <label>Ghi chú</label>
            <textarea
                id="booking-notes"
                class="form-control"
                rows="3"
                placeholder="Ví dụ: Cần phòng yên tĩnh..."
            ></textarea>
        </div>


        <div class="total-box">
            <div>
                <span>Tổng tiền:</span>
                <strong id="total-price">0 đ</strong>
            </div>

            <button id="btn-booking" class="btn btn-success">
                Xác nhận đặt phòng
            </button>
        </div>
    </div>

    <div class="booking-box history-box">
        <h2>Lịch sử đặt phòng</h2>
        <div id="history-list"></div>
    </div>
</div>
