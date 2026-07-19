<?php

class ClientroomsModel extends Database {
    public function getAvailableRooms(array $filters = []) {
        $priceSql = "(rt.ROOMTYPE_PRICE_PER_NIGHT - (rt.ROOMTYPE_PRICE_PER_NIGHT * rt.ROOMTYPE_DISCOUNT_PERCENTAGE / 100))";

        $sql = "SELECT
                    r.ROOM_ID,
                    r.ROOM_NUMBER,
                    r.ROOM_DESCRIPTION,
                    r.ROOM_STATUS,
                    rt.ROOMTYPE_ID,
                    rt.ROOMTYPE_NAME,
                    rt.ROOMTYPE_PRICE_PER_NIGHT,
                    rt.ROOMTYPE_DISCOUNT_PERCENTAGE,
                    rt.ROOMTYPE_THUMBNAIL,
                    rt.ROOMTYPE_MAX_GUESTS,
                    rt.ROOMTYPE_BED_TYPE,
                    $priceSql AS FINAL_PRICE
                FROM Room r
                INNER JOIN RoomType rt
                    ON r.ROOM_ROOMTYPE_ID = rt.ROOMTYPE_ID
                WHERE r.ROOM_STATUS = 'Available'
                AND rt.ROOMTYPE_STATUS = 'Active'";

        $params = [];

        if (!empty($filters['search'])) {
            $sql .= " AND (r.ROOM_NUMBER LIKE ? OR rt.ROOMTYPE_NAME LIKE ?)";
            $params[] = '%' . $filters['search'] . '%';
            $params[] = '%' . $filters['search'] . '%';
        }

        if (!empty($filters['room-type'])) {
            $sql .= " AND rt.ROOMTYPE_ID = ?";
            $params[] = $filters['room-type'];
        }

        if (!empty($filters['min-price'])) {
            $sql .= " AND $priceSql >= ?";
            $params[] = $filters['min-price'];
        }

        if (!empty($filters['max-price'])) {
            $sql .= " AND $priceSql <= ?";
            $params[] = $filters['max-price'];
        }

        if (!empty($filters['max-guests'])) {
            $sql .= " AND rt.ROOMTYPE_MAX_GUESTS >= ?";
            $params[] = $filters['max-guests'];
        }

        $sortMap = [
            'price_asc' => 'FINAL_PRICE ASC',
            'price_desc' => 'FINAL_PRICE DESC',
            'room_asc' => 'r.ROOM_NUMBER ASC',
            'room_desc' => 'r.ROOM_NUMBER DESC'
        ];

        if (!empty($filters['sort-by']) && isset($sortMap[$filters['sort-by']])) {
            $sql .= ' ORDER BY ' . $sortMap[$filters['sort-by']];
        } else {
            $sql .= ' ORDER BY r.ROOM_NUMBER ASC';
        }

        $stmt = $this->connect()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRoomTypes() {
        $sql = "SELECT ROOMTYPE_ID, ROOMTYPE_NAME
                FROM RoomType
                WHERE ROOMTYPE_STATUS = 'Active'
                ORDER BY ROOMTYPE_NAME ASC";

        $stmt = $this->connect()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
