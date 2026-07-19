import { API } from '../api/api.js';

const roomList = document.querySelector('#client-room-list');
const filterForm = document.querySelector('[filter-form]');
const selectForm = document.querySelector('[form-select-multi]');
const checkAll = document.querySelector('[checkbox-multi]');
const resetButton = document.querySelector('[reset-filter]');

function getBedName(bedType) {
    if (bedType == 'singleBed') return 'Giường đơn';
    if (bedType == 'doubleBed') return 'Giường đôi';
    if (bedType == 'queenBed') return 'Giường Queen';
    if (bedType == 'kingBed') return 'Giường King';
    return bedType;
}

function renderRooms(rooms) {
    if (!Array.isArray(rooms) || rooms.length == 0) {
        roomList.innerHTML = `
            <div class="col-12">
                <div class="alert alert-secondary text-center">
                    Không tìm thấy phòng phù hợp.
                </div>
            </div>
        `;

        checkAll.checked = false;
        return;
    }

    let html = '';

    rooms.forEach(function(room) {
        let image = `
            <div class="client-room-image no-image">
                Chưa có ảnh
            </div>
        `;

        if (room.ROOMTYPE_THUMBNAIL) {
            image = `
                <img
                    class="client-room-image"
                    src="${APP_URLROOT}/public/uploads/roomtypes/${room.ROOMTYPE_THUMBNAIL}"
                    alt="Ảnh phòng ${room.ROOM_NUMBER}"
                >
            `;
        }

        let oldPrice = '';

        if (Number(room.ROOMTYPE_DISCOUNT_PERCENTAGE) > 0) {
            oldPrice = `
                <span class="old-price">
                    ${Number(room.ROOMTYPE_PRICE_PER_NIGHT).toLocaleString('vi-VN')} VNĐ
                </span>
            `;
        }

        html += `
            <div class="col-md-6 col-xl-4">
                <div class="client-room-card">
                    <div class="room-image-box">
                        ${image}

                        <label class="room-check-box">
                            <input type="checkbox" name="id" value="${room.ROOM_ID}">
                        </label>
                    </div>

                    <div class="client-room-body">
                        <div class="room-name-box">
                            <div>
                                <h2>Phòng ${room.ROOM_NUMBER}</h2>
                                <p>${room.ROOMTYPE_NAME}</p>
                            </div>

                            <span class="badge bg-success">Còn trống</span>
                        </div>

                        <div class="room-info-box">
                            <p><strong>Sức chứa:</strong> ${room.ROOMTYPE_MAX_GUESTS} khách</p>
                            <p><strong>Loại giường:</strong> ${getBedName(room.ROOMTYPE_BED_TYPE)}</p>
                            <p><strong>Giảm giá:</strong> ${room.ROOMTYPE_DISCOUNT_PERCENTAGE}%</p>
                        </div>

                        <p class="room-description">
                            ${room.ROOM_DESCRIPTION || 'Phòng chưa có mô tả.'}
                        </p>

                        <div class="room-price-box">
                            <div>
                                ${oldPrice}
                                <strong>
                                    ${Number(room.PRICE_AFTER_DISCOUNT).toLocaleString('vi-VN')} VNĐ
                                </strong>
                                <small>/ đêm</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });

    roomList.innerHTML = html;
    checkAll.checked = false;
}

async function loadRooms(queryString = '') {
    try {
        const rooms = await API.get('rooms/client/data' + queryString);
        renderRooms(rooms);
    } catch (error) {
        console.log(error);

        roomList.innerHTML = `
            <div class="col-12">
                <div class="alert alert-danger text-center">
                    Không tải được danh sách phòng.
                </div>
            </div>
        `;
    }
}

filterForm.addEventListener('submit', function(event) {
    event.preventDefault();

    const params = new URLSearchParams(new FormData(filterForm));
    loadRooms('?' + params.toString());
});

resetButton.addEventListener('click', function() {
    filterForm.reset();
    loadRooms();
});

checkAll.addEventListener('change', function() {
    const roomCheckboxes = document.querySelectorAll("input[name='id']");

    roomCheckboxes.forEach(function(checkbox) {
        checkbox.checked = checkAll.checked;
    });
});

roomList.addEventListener('change', function(event) {
    if (event.target.name != 'id') {
        return;
    }

    const roomCheckboxes = document.querySelectorAll("input[name='id']");
    const checkedRooms = document.querySelectorAll("input[name='id']:checked");

    checkAll.checked = roomCheckboxes.length > 0 &&
                       roomCheckboxes.length == checkedRooms.length;
});

selectForm.addEventListener('submit', async function(event) {
    event.preventDefault();

    const checkedRooms = document.querySelectorAll("input[name='id']:checked");

    if (checkedRooms.length == 0) {
        alert('Vui lòng chọn ít nhất một phòng!');
        return;
    }

    const roomIds = [];

    checkedRooms.forEach(function(checkbox) {
        roomIds.push(Number(checkbox.value));
    });

    try {
        const result = await API.post('booking/add', {
            room_ids: roomIds
        });

        alert(result.message);

        if (result.success) {
            window.location.href = APP_URLROOT + '/booking';
        }
    } catch (error) {
        console.log(error);
        alert('Không thể chọn phòng!');
    }
});

loadRooms();
