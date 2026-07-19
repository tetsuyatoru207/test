<?php
/** @var array $data */

     $tableHeader = '
    <tr>
        <th><input type="checkbox" checkbox-multi></th>
        <th>STT</th>
        <th>Tên phòng</th>
        <th>Loại phòng</th>
        <th>Giá phòng</th>
        <th>Giảm giá</th>
        <th>Trạng thái</th>
        <th>Mô tả</th>
        <th>Ảnh</th>
        <th>Hành động</th>
    </tr>
    ';
    $tbodyId = "room-list";
?>

<div class="container py-4">
    <div class="text-center mb-4">
        <h1 class="h3 mb-2"><?php echo $data['title']; ?></h1>
        <p class="text-muted mb-0"><?php echo $data['description']; ?></p>  
    </div>
    <?php require_once __DIR__ .  '/../../components/filter.php';  ?>
    <?php require_once __DIR__ .  '/../../components/toolbar.php'; ?>
    
   
    <?php require_once __DIR__ .  '/../../components/table.php'; ?>
    
</div>
