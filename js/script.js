// Main JavaScript for Online Bidding System

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all functionality
    initCountdownTimers();
    initBidForms();
    initImageUpload();
    initMobileMenu();
});

// Countdown timers for bidding products
function initCountdownTimers() {
    const countdownElements = document.querySelectorAll('.countdown-timer');
    
    countdownElements.forEach(element => {
        const endTime = new Date(element.dataset.endtime).getTime();
        
        const timer = setInterval(function() {
            const now = new Date().getTime();
            const distance = endTime - now;
            
            if (distance < 0) {
                clearInterval(timer);
                element.innerHTML = "BIDDING ENDED";
                element.classList.add('ended');
                return;
            }
            
            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);
            
            element.innerHTML = `${days}d ${hours}h ${minutes}m ${seconds}s`;
        }, 1000);
    });
}

// Bid form validation and submission
function initBidForms() {
    const bidForms = document.querySelectorAll('.bid-form');
    
    bidForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const bidAmount = parseFloat(this.querySelector('input[name="bidAmount"]').value);
            const minimumBid = parseFloat(this.querySelector('input[name="minimumBid"]').value);
            const currentPrice = parseFloat(this.querySelector('input[name="currentPrice"]').value);
            
            if (bidAmount < minimumBid) {
                alert(`Bid amount must be at least ${minimumBid}`);
                return false;
            }
            
            if (bidAmount <= currentPrice) {
                alert('Bid amount must be higher than current price');
                return false;
            }
            
            // Show confirmation
            if (confirm(`Are you sure you want to bid ${bidAmount}?`)) {
                this.submit();
            }
        });
    });
}

// Image upload preview
function initImageUpload() {
    const imageInputs = document.querySelectorAll('input[type="file"]');
    
    imageInputs.forEach(input => {
        input.addEventListener('change', function() {
            const preview = document.getElementById(this.dataset.preview);
            if (preview && this.files && this.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                
                reader.readAsDataURL(this.files[0]);
            }
        });
    });
}

// Mobile menu toggle
function initMobileMenu() {
    const menuToggle = document.querySelector('.menu-toggle');
    const nav = document.querySelector('nav ul');
    
    if (menuToggle) {
        menuToggle.addEventListener('click', function() {
            nav.classList.toggle('active');
        });
    }
}

// Auto-refresh for bidding pages
function startAutoRefresh(interval = 30000) {
    setInterval(() => {
        const activeBids = document.querySelectorAll('.status.in-progress');
        if (activeBids.length > 0) {
            window.location.reload();
        }
    }, interval);
}

// Format currency input
function formatCurrencyInput(input) {
    input.addEventListener('input', function() {
        let value = this.value.replace(/[^\d.]/g, '');
        const decimalCount = (value.match(/\./g) || []).length;
        
        if (decimalCount > 1) {
            value = value.substring(0, value.lastIndexOf('.'));
        }
        
        this.value = value;
    });
}

// Initialize currency formatting on all money inputs
document.querySelectorAll('input[type="number"]').forEach(input => {
    formatCurrencyInput(input);
});

// Search functionality
function initSearch() {
    const searchInput = document.querySelector('.search-input');
    const productCards = document.querySelectorAll('.product-card');
    
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            
            productCards.forEach(card => {
                const productName = card.querySelector('h3').textContent.toLowerCase();
                const productDesc = card.querySelector('p').textContent.toLowerCase();
                
                if (productName.includes(searchTerm) || productDesc.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }
}

// Filter products by category
function initCategoryFilter() {
    const categorySelect = document.querySelector('.category-filter');
    const productCards = document.querySelectorAll('.product-card');
    
    if (categorySelect) {
        categorySelect.addEventListener('change', function() {
            const selectedCategory = this.value;
            
            productCards.forEach(card => {
                const cardCategory = card.dataset.category;
                
                if (selectedCategory === 'all' || cardCategory === selectedCategory) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initSearch();
    initCategoryFilter();
});