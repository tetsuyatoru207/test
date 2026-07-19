<?php

class ClientBookingController extends Controller {

    public function index() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        $data = [
            'title' => 'Đặt phòng',
            'description' => 'Kiểm tra phòng đã chọn và nhập thông tin đặt phòng.',
            'view_content' => 'pages/booking/index',
            'page_script' => 'booking',
            'page_style' => 'booking'
        ];

        $this->view('client/layout/main_layout', $data);
    }

    public function add() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        header('Content-Type: application/json; charset=utf-8');

        $input = json_decode(file_get_contents('php://input'), true);
        $roomIds = $input['room_ids'] ?? [];

        if (!is_array($roomIds) || empty($roomIds)) {
            echo json_encode([
                'success' => false,
                'message' => 'Bạn chưa chọn phòng!'
            ]);
            return;
        }

        $roomIds = array_map('intval', $roomIds);
        $roomIds = array_values(array_unique($roomIds));

        $bookingModel = $this->model('bookings');
        $rooms = $bookingModel->getRoomsInCart($roomIds);

        if (count($rooms) != count($roomIds)) {
            echo json_encode([
                'success' => false,
                'message' => 'Có phòng không còn trống!'
            ]);
            return;
        }

        $_SESSION['booking_cart'] = $roomIds;

        echo json_encode([
            'success' => true,
            'message' => 'Đã chọn phòng thành công!'
        ]);
    }

    public function getData() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        $accountId = $_SESSION['user_id'] ?? 1;
        $roomIds = $_SESSION['booking_cart'] ?? [];

        $bookingModel = $this->model('bookings');

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'cart' => $bookingModel->getRoomsInCart($roomIds),
            'history' => $bookingModel->getBookingHistory($accountId)
        ]);
    }

    public function remove() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        header('Content-Type: application/json; charset=utf-8');

        $input = json_decode(file_get_contents('php://input'), true);
        $roomId = (int)($input['room_id'] ?? 0);

        $cart = $_SESSION['booking_cart'] ?? [];
        $cart = array_diff($cart, [$roomId]);

        $_SESSION['booking_cart'] = array_values($cart);

        echo json_encode([
            'success' => true,
            'message' => 'Đã xóa phòng!'
        ]);
    }

    public function process() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        header('Content-Type: application/json; charset=utf-8');

        $input = json_decode(file_get_contents('php://input'), true);

        $checkin = $input['checkin'] ?? '';
        $checkout = $input['checkout'] ?? '';
        $paymentMethod = $input['payment_method'] ?? 'Cash';
        $notes = trim($input['notes'] ?? '');

        $roomIds = $_SESSION['booking_cart'] ?? [];
        $accountId = $_SESSION['user_id'] ?? 1;

        if (empty($roomIds)) {
            echo json_encode([
                'success' => false,
                'message' => 'Bạn chưa chọn phòng!'
            ]);
            return;
        }

        if ($checkin == '' || $checkout == '') {
            echo json_encode([
                'success' => false,
                'message' => 'Vui lòng nhập đầy đủ ngày!'
            ]);
            return;
        }

        if ($paymentMethod != 'Cash' && $paymentMethod != 'Bank Transfer') {
            echo json_encode([
                'success' => false,
                'message' => 'Phương thức thanh toán không hợp lệ!'
            ]);
            return;
        }

        $checkinTime = strtotime($checkin);
        $checkoutTime = strtotime($checkout);
        $todayTime = strtotime(date('Y-m-d'));

        if ($checkinTime < $todayTime || $checkoutTime <= $checkinTime) {
            echo json_encode([
                'success' => false,
                'message' => 'Ngày nhận hoặc trả phòng không hợp lệ!'
            ]);
            return;
        }

        $bookingModel = $this->model('bookings');
        $rooms = $bookingModel->getRoomsInCart($roomIds);

        if (count($rooms) != count($roomIds)) {
            echo json_encode([
                'success' => false,
                'message' => 'Có phòng không còn trống!'
            ]);
            return;
        }

        foreach ($rooms as $room) {
            if (!$bookingModel->isRoomAvailable($room['ROOM_ID'], $checkin, $checkout)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Phòng ' . $room['ROOM_NUMBER'] . ' đã có người đặt ngày này!'
                ]);
                return;
            }
        }

        $numberOfNight = ($checkoutTime - $checkinTime) / 86400;

        foreach ($rooms as $room) {
            $totalPrice = $room['PRICE_PER_NIGHT'] * $numberOfNight;

            $result = $bookingModel->addBooking(
                $room['ROOM_ID'],
                $accountId,
                $checkin,
                $checkout,
                $totalPrice,
                $notes,
                $paymentMethod
            );

            if (!$result) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Không thể lưu đơn đặt phòng!'
                ]);
                return;
            }
        }

        unset($_SESSION['booking_cart']);

        echo json_encode([
            'success' => true,
            'message' => 'Đặt phòng thành công!'
        ]);
    }


    public function cancel() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        header('Content-Type: application/json; charset=utf-8');

        $input = json_decode(file_get_contents('php://input'), true);
        $bookingId = (int)($input['booking_id'] ?? 0);
        $accountId = $_SESSION['user_id'] ?? 1;

        $result = $this->model('bookings')->cancelBooking(
            $bookingId,
            $accountId
        );

        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Đã hủy đơn đặt phòng!'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Không thể hủy đơn này!'
            ]);
        }
    }
}
