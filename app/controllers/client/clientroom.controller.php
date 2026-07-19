<?php

class ClientRoomController extends Controller {

    public function index() {
        $roomTypeModel = $this->model('roomstype');

        $data = [
            'title' => 'Danh sách phòng',
            'description' => 'Chọn phòng phù hợp để đặt.',
            'view_content' => 'pages/room/index',
            'page_script' => 'room',
            'page_style' => 'room',
            'room_types' => $roomTypeModel->getAllRoomsType([
                'status' => 'Active'
            ])
        ];

        $this->view('client/layout/main_layout', $data);
    }

    public function getData() {
        $filter = [
            'search' => $_GET['search'] ?? '',
            'room-type' => $_GET['room-type'] ?? '',
            'min-price' => $_GET['min-price'] ?? '',
            'max-price' => $_GET['max-price'] ?? '',
            'sort-by' => $_GET['sort-by'] ?? ''
        ];

        $roomModel = $this->model('rooms');
        $rooms = $roomModel->getClientRooms($filter);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($rooms);
    }
}
