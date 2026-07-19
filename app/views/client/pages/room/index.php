<?php /** @var array $data */ ?>

<div class="container py-4">
    <div class="text-center mb-4">
        <h1 class="h3 mb-2"><?php echo $data['title']; ?></h1>
        <p class="text-muted mb-0"><?php echo $data['description']; ?></p>
    </div>

    <?php require_once __DIR__ . '/../../components/filter.php'; ?>

    <form form-select-multi>
        <?php require_once __DIR__ . '/../../components/toolbar.php'; ?>

        <div id="client-room-list" class="row g-3"></div>
    </form>
</div>
