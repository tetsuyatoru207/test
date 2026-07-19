<?php /** @var array $data */ ?>
<section class="container page-section">
    <div class="page-title">
        <h1>Lịch sử đặt phòng</h1>
        <p>Danh sách các phòng tài khoản đã đặt.</p>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="message success">Đặt phòng thành công.</div>
    <?php endif; ?>

    <?php if (empty($data['bookings'])): ?>
        <div class="message">Bạn chưa có lịch sử đặt phòng.</div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="booking-table">
                <thead>
                    <tr>
                        <th>Mã đặt</th>
                        <th>Phòng</th>
                        <th>Loại phòng</th>
                        <th>Ngày nhận</th>
                        <th>Ngày trả</th>
                        <th>Tổng tiền</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data['bookings'] as $booking): ?>
                        <tr>
                            <td><?php echo $booking['BOOKING_ID']; ?></td>
                            <td><?php echo $booking['ROOM_NUMBER']; ?></td>
                            <td><?php echo $booking['ROOMTYPE_NAME']; ?></td>
                            <td><?php echo $booking['BOOKING_CHECKIN']; ?></td>
                            <td><?php echo $booking['BOOKING_CHECKOUT']; ?></td>
                            <td><?php echo number_format($booking['BOOKING_TOTAL_PRICE'], 0, ',', '.'); ?> VNĐ</td>
                            <td>
                                <?php if ($booking['BOOKING_CHECKIN'] > date('Y-m-d')): ?>
                                    <form action="<?php echo URLROOT; ?>/booking/cancel" method="POST" onsubmit="return confirm('Bạn có muốn hủy phòng này không?')">
                                        <input type="hidden" name="booking_id" value="<?php echo $booking['BOOKING_ID']; ?>">
                                        <button type="submit" class="btn btn-danger">Hủy</button>
                                    </form>
                                <?php else: ?>
                                    <span>Không thể hủy</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
