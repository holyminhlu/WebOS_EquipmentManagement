// JavaScript cho hệ thống mượn trả thiết bị
document.addEventListener('DOMContentLoaded', function() {
    // Khởi tạo các chức năng
    
    // Hiệu ứng scroll cho header
    const header = document.querySelector('.header');
    window.addEventListener('scroll', function() {
        if (window.scrollY > 100) {
            header.style.background = 'rgba(255, 255, 255, 0.95)';
            header.style.backdropFilter = 'blur(10px)';
        } else {
            header.style.background = 'white';
            header.style.backdropFilter = 'none';
        }
    });

    // Hiệu ứng counter cho stats
    const statsSection = document.querySelector('.stats');
    const statNumbers = document.querySelectorAll('.stat-info h3');
    let counted = false;

    function startCounters() {
        if (counted) return;
        
        statNumbers.forEach(stat => {
            const target = parseInt(stat.textContent);
            const increment = target / 100;
            let current = 0;
            
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    stat.textContent = target + '+';
                    clearInterval(timer);
                } else {
                    stat.textContent = Math.floor(current) + '+';
                }
            }, 20);
        });
        
        counted = true;
    }

    // Intersection Observer cho stats
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                startCounters();
            }
        });
    }, { threshold: 0.5 });

    observer.observe(statsSection);

    // Hiệu ứng hover cho cards
    const cards = document.querySelectorAll('.equipment-card, .step-item, .news-card');
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });

    // Xử lý các nút hành động
    const borrowButtons = document.querySelectorAll('.btn-borrow');
    borrowButtons.forEach(button => {
        button.addEventListener('click', function() {
            const equipmentName = this.closest('.equipment-card').querySelector('h3').textContent;
            showNotification(`Đã thêm "${equipmentName}" vào giỏ mượn!`);
        });
    });

    const detailButtons = document.querySelectorAll('.btn-detail');
    detailButtons.forEach(button => {
        button.addEventListener('click', function() {
            const equipmentName = this.closest('.equipment-card').querySelector('h3').textContent;
            showNotification(`Đang mở trang chi tiết cho "${equipmentName}"`);
        });
    });

    // Hiển thị thông báo
    function showNotification(message) {
        // Tạo toast notification
        const toast = document.createElement('div');
        toast.className = 'toast-notification';
        toast.innerHTML = `
            <div class="toast-content">
                <i class="fas fa-check-circle"></i>
                <span>${message}</span>
            </div>
        `;
        
        // Thêm styles cho toast
        toast.style.cssText = `
            position: fixed;
            top: 100px;
            right: 20px;
            background: var(--success);
            color: white;
            padding: 1rem 1.5rem;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            z-index: 10000;
            transform: translateX(400px);
            transition: transform 0.3s ease;
        `;
        
        document.body.appendChild(toast);
        
        // Hiệu ứng xuất hiện
        setTimeout(() => {
            toast.style.transform = 'translateX(0)';
        }, 100);
        
        // Tự động ẩn sau 3 giây
        setTimeout(() => {
            toast.style.transform = 'translateX(400px)';
            setTimeout(() => {
                document.body.removeChild(toast);
            }, 300);
        }, 3000);
    }

    // Xử lý tìm kiếm
    const searchInput = document.querySelector('.search-box input');
    const searchButton = document.querySelector('.search-btn');

    searchButton.addEventListener('click', performSearch);
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            performSearch();
        }
    });

    function performSearch() {
        const query = searchInput.value.trim();
        if (query) {
            showNotification(`Đang tìm kiếm: "${query}"`);
            // Ở đây sẽ tích hợp với chức năng tìm kiếm thực tế
        }
    }

    // Smooth scroll cho các liên kết
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Preload images (nếu cần)
    const images = [
        'images/tvu-logo.png',
        'images/hero-equipment.png',
        'images/projector.jpg',
        'images/laptop.jpg',
        'images/camera.jpg'
    ];

    images.forEach(src => {
        const img = new Image();
        img.src = src;
    });

    console.log('Hệ thống mượn trả thiết bị đã được khởi tạo thành công!');
});