<?php

class BookingsModel extends Database {

    public function getRoomsInCart($roomIds) {
        if (empty($roomIds)) {
            return [];
        }

        $questionMark = implode(',', array_fill(0, count($roomIds), '?'));

        $sql = "SELECT
                    r.ROOM_ID,
                    r.ROOM_NUMBER,
                    rt.ROOMTYPE_NAME,
                    (rt.ROOMTYPE_PRICE_PER_NIGHT *
                    (100 - rt.ROOMTYPE_DISCOUNT_PERCENTAGE) / 100) AS PRICE_PER_NIGHT
                FROM Room r
                JOIN RoomType rt
                    ON r.ROOM_ROOMTYPE_ID = rt.ROOMTYPE_ID
                WHERE r.ROOM_ID IN ($questionMark)
                    AND r.ROOM_STATUS = 'Available'
                    AND rt.ROOMTYPE_STATUS = 'Active'";

        $stmt = $this->connect()->prepare($sql);
        $stmt->execute($roomIds);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getBookingHistory($accountId) {
        $sql = "SELECT
                    b.BOOKING_ID,
                    b.BOOKING_CHECKIN,
                    b.BOOKING_CHECKOUT,
                    b.BOOKING_TOTAL_PRICE,
                    b.BOOKING_NOTES,
                    b.PAYMENT_METHOD,
                    b.BOOKING_STATUS,
                    r.ROOM_NUMBER,
                    rt.ROOMTYPE_NAME
                FROM Booking b
                JOIN Room r ON b.BOOKING_ROOM_ID = r.ROOM_ID
                JOIN RoomType rt ON r.ROOM_ROOMTYPE_ID = rt.ROOMTYPE_ID
                WHERE b.BOOKING_ACCOUNT_ID = ?
                ORDER BY b.BOOKING_ID DESC";

        $stmt = $this->connect()->prepare($sql);
        $stmt->execute([$accountId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function isRoomAvailable($roomId, $checkin, $checkout) {
        $sql = "SELECT COUNT(*)
                FROM Booking
                WHERE BOOKING_ROOM_ID = ?
                    AND BOOKING_STATUS IN ('Pending', 'Confirmed')
                    AND BOOKING_CHECKIN < ?
                    AND BOOKING_CHECKOUT > ?";

        $stmt = $this->connect()->prepare($sql);
        $stmt->execute([$roomId, $checkout, $checkin]);

        return $stmt->fetchColumn() == 0;
    }

    public function addBooking(
        $roomId,
        $accountId,
        $checkin,
        $checkout,
        $totalPrice,
        $notes,
        $paymentMethod
    ) {
        $sql = "INSERT INTO Booking (
                    BOOKING_ROOM_ID,
                    BOOKING_ACCOUNT_ID,
                    BOOKING_CHECKIN,
                    BOOKING_CHECKOUT,
                    BOOKING_TOTAL_PRICE,
                    BOOKING_NOTES,
                    PAYMENT_METHOD,
                    BOOKING_STATUS
                ) VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending')";

        $stmt = $this->connect()->prepare($sql);

        return $stmt->execute([
            $roomId,
            $accountId,
            $checkin,
            $checkout,
            $totalPrice,
            $notes,
            $paymentMethod
        ]);
    }


    public function cancelBooking($bookingId, $accountId) {
        $sql = "UPDATE Booking
                SET BOOKING_STATUS = 'Cancelled'
                WHERE BOOKING_ID = ?
                    AND BOOKING_ACCOUNT_ID = ?
                    AND BOOKING_STATUS IN ('Pending', 'Confirmed')";

        $stmt = $this->connect()->prepare($sql);
        $stmt->execute([$bookingId, $accountId]);

        return $stmt->rowCount() > 0;
    }
}
