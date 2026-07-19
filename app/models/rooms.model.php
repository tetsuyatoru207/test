<?php

class RoomsModel extends Database {

    // Phần admin của nhóm giữ nguyên
    public function getAllRooms(array $filters = []) {
        $sql = "SELECT * FROM `Room` WHERE 1 = 1";
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND ROOM_STATUS = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['room-type'])) {
            $sql .= " AND ROOM_ROOMTYPE_ID = ?";
            $params[] = $filters['room-type'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND ROOM_NUMBER LIKE ?";
            $params[] = '%' . $filters['search'] . '%';
        }

        $sortMap = [
            'price_asc' => 'ROOM_PRICE_PER_NIGHT ASC',
            'price_desc' => 'ROOM_PRICE_PER_NIGHT DESC',
            'room_number_asc' => 'ROOM_NUMBER ASC',
            'room_number_desc' => 'ROOM_NUMBER DESC'
        ];

        if (!empty($filters['sort-by']) && isset($sortMap[$filters['sort-by']])) {
            $sql .= " ORDER BY " . $sortMap[$filters['sort-by']];
        }

        $stmt = $this->connect()->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateRoomStatus(array $ids, string $newStatus) {
        if (empty($ids) || empty($newStatus)) {
            return false;
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        if ($newStatus == 'Deleted' || $newStatus == 'Maintenance' || $newStatus == 'Occupied') {
            $sql = "UPDATE `Room`
                    SET `ROOM_STATUS` = ?, `ROOM_DELETED` = 1
                    WHERE `ROOM_ID` IN ($placeholders)";
        } else {
            $sql = "UPDATE `Room`
                    SET `ROOM_STATUS` = ?, `ROOM_DELETED` = 0
                    WHERE `ROOM_ID` IN ($placeholders)";
        }

        $stmt = $this->connect()->prepare($sql);
        $stmt->bindValue(1, $newStatus, PDO::PARAM_STR);

        foreach ($ids as $index => $id) {
            $stmt->bindValue($index + 2, $id, PDO::PARAM_INT);
        }

        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    // Phần client viết đơn giản
    public function getClientRooms(array $filters = []) {
        $price = "(rt.ROOMTYPE_PRICE_PER_NIGHT *
                    (100 - rt.ROOMTYPE_DISCOUNT_PERCENTAGE) / 100)";

        $sql = "SELECT
                    r.ROOM_ID,
                    r.ROOM_NUMBER,
                    r.ROOM_DESCRIPTION,
                    rt.ROOMTYPE_ID,
                    rt.ROOMTYPE_NAME,
                    rt.ROOMTYPE_PRICE_PER_NIGHT,
                    rt.ROOMTYPE_DISCOUNT_PERCENTAGE,
                    rt.ROOMTYPE_MAX_GUESTS,
                    rt.ROOMTYPE_BED_TYPE,
                    rt.ROOMTYPE_THUMBNAIL,
                    $price AS PRICE_AFTER_DISCOUNT
                FROM Room r
                JOIN RoomType rt
                    ON r.ROOM_ROOMTYPE_ID = rt.ROOMTYPE_ID
                WHERE r.ROOM_STATUS = 'Available'
                    AND rt.ROOMTYPE_STATUS = 'Active'";

        $params = [];

        if (!empty($filters['search'])) {
            $sql .= " AND (r.ROOM_NUMBER LIKE ? OR rt.ROOMTYPE_NAME LIKE ?)";

            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
        }

        if (!empty($filters['room-type'])) {
            $sql .= " AND rt.ROOMTYPE_ID = ?";
            $params[] = $filters['room-type'];
        }

        if (($filters['min-price'] ?? '') != '') {
            $sql .= " AND $price >= ?";
            $params[] = $filters['min-price'];
        }

        if (($filters['max-price'] ?? '') != '') {
            $sql .= " AND $price <= ?";
            $params[] = $filters['max-price'];
        }

        if (($filters['sort-by'] ?? '') == 'price_asc') {
            $sql .= " ORDER BY $price ASC";
        } elseif (($filters['sort-by'] ?? '') == 'price_desc') {
            $sql .= " ORDER BY $price DESC";
        } else {
            $sql .= " ORDER BY r.ROOM_NUMBER ASC";
        }

        $stmt = $this->connect()->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
