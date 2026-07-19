import { API } from '../api/api.js';

const cartList = document.getElementById('cart-list');
const historyList = document.getElementById('history-list');
const checkin = document.getElementById('checkin');
const checkout = document.getElementById('checkout');
const paymentMethod = document.getElementById('payment-method');
const bookingNotes = document.getElementById('booking-notes');
const totalPrice = document.getElementById('total-price');
const btnBooking = document.getElementById('btn-booking');

let currentCart = [];

function formatDate(date) {
    if (!date) return '';
    const part = date.split('-');
    return part[2] + '/' + part[1] + '/' + part[0];
}

function getPaymentMethod(method) {
    if (method == 'Cash') return 'Thanh toán tại quầy';
    if (method == 'Bank Transfer') return 'Chuyển khoản';
    return method;
}


function getBookingStatus(status) {
    if (status == 'Pending') {
        return '<span class="badge bg-warning text-dark">Chờ xác nhận</span>';
    }
    if (status == 'Confirmed') {
        return '<span class="badge bg-success">Đã xác nhận</span>';
    }
    if (status == 'Cancelled') {
        return '<span class="badge bg-secondary">Đã hủy</span>';
    }
    if (status == 'Completed') {
        return '<span class="badge bg-primary">Hoàn thành</span>';
    }
    return status;
}

function renderCart(cart) {
    if (cart.length == 0) {
        cartList.innerHTML = '<p class="empty-text">Bạn chưa chọn phòng.</p>';
        btnBooking.disabled = true;
        return;
    }
    btnBooking.disabled = false;

    let html = `
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Phòng</th>
                        <th>Giá một đêm</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
    `;

    cart.forEach(function(room) {
        html += `
            <tr>
                <td>Phòng ${room.ROOM_NUMBER} - ${room.ROOMTYPE_NAME}</td>
                <td>${Number(room.PRICE_PER_NIGHT).toLocaleString('vi-VN')} đ</td>
                <td>
                    <button class="btn btn-danger btn-sm btn-remove"
                            data-id="${room.ROOM_ID}">
                        Xóa
                    </button>
                </td>
            </tr>
        `;
    });

    html += '</tbody></table></div>';
    cartList.innerHTML = html;
}

function renderHistory(history) {
    if (history.length == 0) {
        historyList.innerHTML = '<p class="empty-text">Chưa có lịch sử đặt phòng.</p>';
        return;
    }

    let html = `
        <div class="table-responsive">
            <table class="table table-bordered history-table">
                <thead>
                    <tr>
                        <th>Mã</th>
                        <th>Phòng</th>
                        <th>Ngày nhận</th>
                        <th>Ngày trả</th>
                        <th>Phương thức</th>
                        <th>Trạng thái</th>
                        <th>Ghi chú</th>
                        <th>Tổng tiền</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
    `;

    history.forEach(function(item) {
        html += `
            <tr>
                <td>${item.BOOKING_ID}</td>
                <td>${item.ROOM_NUMBER} - ${item.ROOMTYPE_NAME}</td>
                <td>${formatDate(item.BOOKING_CHECKIN)}</td>
                <td>${formatDate(item.BOOKING_CHECKOUT)}</td>
                <td>${getPaymentMethod(item.PAYMENT_METHOD)}</td>
                <td>${getBookingStatus(item.BOOKING_STATUS)}</td>
                <td>${item.BOOKING_NOTES || 'Không có'}</td>
                <td>${Number(item.BOOKING_TOTAL_PRICE).toLocaleString('vi-VN')} đ</td>
                <td>
                    ${
                        item.BOOKING_STATUS == 'Pending' ||
                        item.BOOKING_STATUS == 'Confirmed'
                            ? `<button class="btn btn-danger btn-sm btn-cancel"
                                       data-id="${item.BOOKING_ID}">
                                   Hủy
                               </button>`
                            : ''
                    }
                </td>
            </tr>
        `;
    });

    html += '</tbody></table></div>';
    historyList.innerHTML = html;
}

function calculateTotal() {
    if (checkin.value == '' || checkout.value == '') {
        totalPrice.innerText = '0 đ';
        return;
    }

    const date1 = new Date(checkin.value);
    const date2 = new Date(checkout.value);
    const n = (date2 - date1) / (1000*60*60*24);

    if (n <= 0) {
        totalPrice.innerText = '0 đ';
        return;
    }

    let pricePerNight = 0;

    currentCart.forEach(function(room) {
        pricePerNight += Number(room.PRICE_PER_NIGHT);
    });

    const total = pricePerNight * n;
    totalPrice.innerText = total.toLocaleString('vi-VN') + ' đ';
}

async function loadBooking() {
    try {
        const data = await API.get('booking/data');

        currentCart = data.cart;
        renderCart(data.cart);
        renderHistory(data.history);
        calculateTotal();
    } catch (error) {
        console.log(error);
        alert('Không tải được dữ liệu booking!');
    }
}

cartList.addEventListener('click', async function(event) {
    if (!event.target.classList.contains('btn-remove')) return;

    const result = await API.post('booking/remove', {
        room_id: event.target.dataset.id
    });

    if (result.success) {
        loadBooking();
    }
});


historyList.addEventListener('click', async function(event) {
    if (!event.target.classList.contains('btn-cancel')) return;

    if (!confirm('Bạn có chắc muốn hủy đơn này?')) {
        return;
    }

    const result = await API.post('booking/cancel', {
        booking_id: event.target.dataset.id
    });

    alert(result.message);

    if (result.success) {
        loadBooking();
    }
});

checkin.addEventListener('change', function() {
    checkout.min = checkin.value;
    calculateTotal();
});

checkout.addEventListener('change', calculateTotal);

btnBooking.addEventListener('click', async function() {
    if (checkin.value == '' || checkout.value == '') {
        alert('Vui lòng nhập đầy đủ ngày!');
        return;
    }

    if (new Date(checkout.value) <= new Date(checkin.value)) {
        alert('Ngày trả phải sau ngày nhận!');
        return;
    }

    const result = await API.post('booking/process', {
        checkin: checkin.value,
        checkout: checkout.value,
        payment_method: paymentMethod.value,
        notes: bookingNotes.value
    });

    alert(result.message);

    if (result.success) {
        checkout.value = '';
        bookingNotes.value = '';
        loadBooking();
    }
});

const today = new Date().toISOString().split('T')[0];
checkin.min = today;
checkout.min = today;

loadBooking();
