<?php

class BookingModel extends Database {
    public function getRoomsByIds(array $ids) {
        if (empty($ids)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $sql = "SELECT
                    r.ROOM_ID,
                    r.ROOM_NUMBER,
                    r.ROOM_DESCRIPTION,
                    rt.ROOMTYPE_NAME,
                    rt.ROOMTYPE_PRICE_PER_NIGHT,
                    rt.ROOMTYPE_DISCOUNT_PERCENTAGE,
                    (rt.ROOMTYPE_PRICE_PER_NIGHT -
                    (rt.ROOMTYPE_PRICE_PER_NIGHT * rt.ROOMTYPE_DISCOUNT_PERCENTAGE / 100)) AS FINAL_PRICE
                FROM Room r
                INNER JOIN RoomType rt
                    ON r.ROOM_ROOMTYPE_ID = rt.ROOMTYPE_ID
                WHERE r.ROOM_ID IN ($placeholders)
                AND r.ROOM_STATUS = 'Available'";

        $stmt = $this->connect()->prepare($sql);
        $stmt->execute(array_values($ids));
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createBookings($accountId, array $roomIds, $checkin, $checkout) {
        $db = $this->connect();
        $rooms = $this->getRoomsByIds($roomIds);

        if (count($rooms) !== count(array_unique($roomIds))) {
            return [
                'success' => false,
                'message' => 'Có phòng không còn khả dụng.'
            ];
        }

        $numberDays = (strtotime($checkout) - strtotime($checkin)) / 86400;
        if ($numberDays <= 0) {
            return [
                'success' => false,
                'message' => 'Ngày trả phòng không hợp lệ.'
            ];
        }

        try {
            $db->beginTransaction();

            $checkSql = "SELECT COUNT(*) FROM Booking
                         WHERE BOOKING_ROOM_ID = ?
                         AND NOT (BOOKING_CHECKOUT <= ? OR BOOKING_CHECKIN >= ?)";
            $checkStmt = $db->prepare($checkSql);

            $insertSql = "INSERT INTO Booking (
                            BOOKING_ROOM_ID,
                            BOOKING_ACCOUNT_ID,
                            BOOKING_CHECKIN,
                            BOOKING_CHECKOUT,
                            BOOKING_TOTAL_PRICE
                          ) VALUES (?, ?, ?, ?, ?)";
            $insertStmt = $db->prepare($insertSql);

            foreach ($rooms as $room) {
                $checkStmt->execute([
                    $room['ROOM_ID'],
                    $checkin,
                    $checkout
                ]);

                if ((int)$checkStmt->fetchColumn() > 0) {
                    $db->rollBack();
                    return [
                        'success' => false,
                        'message' => 'Phòng ' . $room['ROOM_NUMBER'] . ' đã được đặt trong thời gian này.'
                    ];
                }

                $totalPrice = $room['FINAL_PRICE'] * $numberDays;

                $insertStmt->execute([
                    $room['ROOM_ID'],
                    $accountId,
                    $checkin,
                    $checkout,
                    $totalPrice
                ]);
            }

            $db->commit();
            return [
                'success' => true,
                'message' => 'Đặt phòng thành công.'
            ];
        } catch (Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function getHistory($accountId) {
        $sql = "SELECT
                    b.BOOKING_ID,
                    b.BOOKING_DATE,
                    b.BOOKING_CHECKIN,
                    b.BOOKING_CHECKOUT,
                    b.BOOKING_TOTAL_PRICE,
                    r.ROOM_NUMBER,
                    rt.ROOMTYPE_NAME
                FROM Booking b
                INNER JOIN Room r
                    ON b.BOOKING_ROOM_ID = r.ROOM_ID
                INNER JOIN RoomType rt
                    ON r.ROOM_ROOMTYPE_ID = rt.ROOMTYPE_ID
                WHERE b.BOOKING_ACCOUNT_ID = ?
                ORDER BY b.BOOKING_ID DESC";

        $stmt = $this->connect()->prepare($sql);
        $stmt->execute([$accountId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function cancelBooking($bookingId, $accountId) {
        $sql = "DELETE FROM Booking
                WHERE BOOKING_ID = ?
                AND BOOKING_ACCOUNT_ID = ?
                AND BOOKING_CHECKIN > CURDATE()";

        $stmt = $this->connect()->prepare($sql);
        return $stmt->execute([$bookingId, $accountId]);
    }
}
