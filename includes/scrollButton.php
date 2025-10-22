<!-- Scroll to Top Button -->
<a href="#" id="scrollToTopBtn" class="scroll-to-top">
    <i class="fas fa-arrow-up"></i>
</a>

<style>
.scroll-to-top {
    position: fixed;
    bottom: 30px;
    right: 30px;
    display: none; /* Hidden by default */
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #4B2E05 0%, #C4A35A 100%);
    color: #ffffff;
    text-align: center;
    line-height: 50px;
    border-radius: 50%;
    font-size: 20px;
    z-index: 1000;
    transition: all 0.4s ease;
    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
}

.scroll-to-top:hover {
    background: linear-gradient(135deg, #C4A35A 0%, #4B2E05 100%);
    transform: translateY(-5px); /* Add a subtle lift effect */
    color: #ffffff;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const scrollToTopBtn = document.getElementById('scrollToTopBtn');

    // Function to show/hide button
    const toggleButtonVisibility = () => {
        if (window.pageYOffset > 300) { // Show button after scrolling 300px
            scrollToTopBtn.style.display = 'block';
        } else {
            scrollToTopBtn.style.display = 'none';
        }
    };

    // Smooth scroll to top on click
    const scrollToTop = (event) => {
        event.preventDefault();
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    };

    window.addEventListener('scroll', toggleButtonVisibility);
    scrollToTopBtn.addEventListener('click', scrollToTop);

    // Initial check
    toggleButtonVisibility();
});
</script>
