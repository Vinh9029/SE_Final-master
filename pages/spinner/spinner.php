<?php
session_start();
include_once __DIR__ . '/../../config.php';
$is_logged_in = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vòng Quay May Mắn</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --brand-brown: #4B2E05;
            --brand-gold: #C4A35A;
            --brand-light: #fdfaf6;
            --bg-color: #fdfaf6;
            --text-color: #222222;
        }

        body {
            background-color: var(--bg-color);
            font-family: 'Poppins', sans-serif;
        }

        .spinner-section {
            background: linear-gradient(rgba(255, 255, 255, 0.8), rgba(255, 255, 255, 0.8)), url('<?php echo $base_url; ?>/Photos/background.jpg') center/cover;
            background-attachment: fixed;
        }

        .spinner-container {
            position: relative;
            width: 450px;
            height: 550px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
        }

        .wheel-wrapper {
            position: relative;
            width: 450px;
            height: 450px;
        }

        #spinner-canvas {
            width: 100%;
            height: 100%;
            transition: transform 6s cubic-bezier(0.2, 0.8, 0.2, 1);
            filter: drop-shadow(0 10px 15px rgba(0, 0, 0, 0.2));
        }

        .pointer {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 50px;
            height: 60px;
            background-color: var(--brand-brown);
            clip-path: polygon(50% 0%, 100% 100%, 0% 100%);
            transform: translate(-50%, -100%) rotate(180deg);
            z-index: 10;
            border-bottom: 5px solid var(--brand-gold);
            box-sizing: border-box;
        }

        .wheel-center {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 80px;
            height: 80px;
            background: radial-gradient(circle, var(--brand-gold) 50%, var(--brand-brown) 100%);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            z-index: 11;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-family: 'Playfair Display', serif;
            font-size: 1.2rem;
        }

        #spin-btn {
            margin-top: 20px;
            padding: 15px 40px;
            font-size: 1.2rem;
            font-weight: 600;
            color: white;
            background: linear-gradient(135deg, var(--brand-brown) 0%, var(--brand-gold) 100%);
            border: none;
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        #spin-btn:hover:not(:disabled) {
            background: linear-gradient(135deg, var(--brand-gold) 0%, var(--brand-brown) 100%);
            transform: scale(1.05);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
        }

        #spin-btn:disabled {
            background: #999;
            cursor: not-allowed;
            opacity: 0.7;
        }
    </style>
</head>

<body>
    <?php include_once __DIR__ . '/../../includes/header.php'; ?>

    <main>
        <section class="spinner-section py-12 md:py-20">
            <div class="container mx-auto px-4">
                <div class="text-center mb-8 md:mb-12">
                    <h1 class="text-4xl md:text-5xl font-bold text-gray-800" style="font-family: 'Playfair Display', serif;">Vòng Quay May Mắn</h1>
                    <p class="text-lg text-gray-600 mt-2">Thử vận may và nhận những phần quà hấp dẫn từ The Old Flavour!</p>
                </div>

                <div class="flex justify-center">
                    <div class="spinner-container">
                        <div class="wheel-wrapper">
                            <canvas id="spinner-canvas" width="500" height="500"></canvas>
                            <div class="pointer"></div>
                            <div class="wheel-center">Quay</div>
                        </div>
                        <button id="spin-btn">Quay ngay</button>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php include_once __DIR__ . '/../../includes/footer.php'; ?>

    <script>
        const canvas = document.getElementById('spinner-canvas');
        const ctx = canvas.getContext('2d');
        const spinBtn = document.getElementById('spin-btn');
        const isLoggedIn = <?php echo json_encode($is_logged_in); ?>;

        const prizes = [
            { text: 'Giảm 10%', color: '#E6D3B1' },
            { text: 'Chúc bạn\nmay mắn', color: '#FFFFFF' },
            { text: 'Giảm 20%', color: '#E6D3B1' },
            { text: 'Freeship', color: '#FFFFFF' },
            { text: 'Giảm 15%', color: '#E6D3B1' },
            { text: 'Thêm lượt', color: '#FFFFFF' },
            { text: 'Giảm 30%', color: '#E6D3B1' },
            { text: 'Mua 1 Tặng 1', color: '#FFFFFF' },
        ];

        const numSegments = prizes.length;
        const arcSize = (2 * Math.PI) / numSegments;
        const centerX = canvas.width / 2;
        const centerY = canvas.height / 2;
        const radius = canvas.width / 2 - 10;
        let currentRotation = 0;
        let isSpinning = false;

        function drawText(text, x, y, angle) {
            ctx.save();
            ctx.translate(x, y);
            ctx.rotate(angle);
            ctx.textAlign = 'center';
            ctx.fillStyle = '#4B2E05';
            ctx.font = "bold 18px 'Poppins', sans-serif";

            const lines = text.split('\n');
            if (lines.length > 1) {
                ctx.fillText(lines[0], 0, -8);
                ctx.fillText(lines[1], 0, 12);
            } else {
                ctx.fillText(text, 0, 0);
            }
            ctx.restore();
        }

        function drawWheel() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.strokeStyle = '#C4A35A';
            ctx.lineWidth = 5;

            prizes.forEach((prize, i) => {
                const angle = i * arcSize;

                // Draw segment
                ctx.beginPath();
                ctx.fillStyle = prize.color;
                ctx.moveTo(centerX, centerY);
                ctx.arc(centerX, centerY, radius, angle, angle + arcSize);
                ctx.closePath();
                ctx.fill();
                ctx.stroke();

                // Draw text
                const textAngle = angle + arcSize / 2;
                const textX = centerX + (radius * 0.65) * Math.cos(textAngle);
                const textY = centerY + (radius * 0.65) * Math.sin(textAngle);
                drawText(prize.text, textX, textY, textAngle);
            });
        }

        function spin() {
            if (isSpinning) return;

            if (!isLoggedIn) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Vui lòng đăng nhập',
                    text: 'Bạn cần đăng nhập để tham gia Vòng Quay May Mắn!',
                    confirmButtonColor: '#4B2E05',
                });
                return;
            }

            isSpinning = true;
            spinBtn.disabled = true;

            // Random spins and final angle
            const randomSpins = Math.floor(Math.random() * 5) + 5; // 5 to 9 full spins
            const randomStopAngle = Math.random() * 2 * Math.PI; // Random stop position
            const totalRotation = (randomSpins * 2 * Math.PI) + randomStopAngle;

            // Apply rotation
            currentRotation += totalRotation;
            canvas.style.transform = `rotate(${currentRotation}rad)`;

            // Determine winning prize after animation
            setTimeout(() => {
                const actualAngle = currentRotation % (2 * Math.PI);
                // The pointer is at the top (pointing down), which is 270 degrees or 1.5 * PI
                // We adjust to find the winning segment
                const pointerAngle = (1.5 * Math.PI);
                let winningIndex = Math.floor(numSegments - (actualAngle / (2 * Math.PI) * numSegments)) % numSegments;
                
                // The above calculation can be tricky. Let's simplify.
                // The wheel stops. We need to find which segment is under the pointer (top-center).
                // Pointer is at 270deg (3*PI/2).
                const finalAngle = currentRotation % (2 * Math.PI);
                const degrees = finalAngle * 180 / Math.PI;
                const correctedDegrees = (360 - degrees + 270) % 360; // Adjust for pointer position
                const segmentAngle = 360 / numSegments;
                winningIndex = Math.floor(correctedDegrees / segmentAngle);

                handleResult(winningIndex);

            }, 6500); // Match CSS transition duration + buffer
        }

        async function handleResult(prizeIndex) {
            try {
                const response = await fetch('handle_spin.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ prize_index: prizeIndex })
                });

                const result = await response.json();

                if (result.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Chúc Mừng!',
                        html: result.message,
                        confirmButtonColor: '#4B2E05',
                    });
                    // Send message to parent window
                    // window.parent.postMessage({ type: 'SPIN_SUCCESS', message: result.message }, '*'); // No longer in iframe

                    if (result.retry) {
                        // Allow to spin again
                        isSpinning = false;
                        spinBtn.disabled = false;
                    }
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Rất tiếc...',
                        text: result.message,
                        confirmButtonColor: '#4B2E05',
                    });
                    // window.parent.postMessage({ type: 'SPIN_ERROR', message: result.message }, '*'); // No longer in iframe
                }

            } catch (error) {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Có lỗi xảy ra, vui lòng thử lại sau!',
                    confirmButtonColor: '#4B2E05',
                });
                isSpinning = false;
                spinBtn.disabled = false;
            }
        }

        spinBtn.addEventListener('click', spin);

        // Initial draw
        drawWheel();
    </script>
</body>

</html>