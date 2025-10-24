<?php
include_once __DIR__ . "/../config.php";
?>
<style>
    .return-home-btn {
        position: fixed;
        top: 20px;
        right: 20px;
        background: rgba(255, 255, 255, 0.2);
        color: #fff;
        padding: 10px 18px;
        border-radius: 50px;
        text-decoration: none;
        font-weight: 600;
        font-size: 14px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        backdrop-filter: blur(5px);
        border: 1px solid rgba(255, 255, 255, 0.3);
    }
    .return-home-btn:hover {
        background: rgba(255, 255, 255, 0.4);
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
    }
</style>
<a href="<?php echo $base_url; ?>/index.php" class="return-home-btn"><i class="fa-solid fa-house-chimney"></i> Return Home </a>