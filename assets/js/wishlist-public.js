/**
 * The Birthday Wishbook — Public Wishlist JavaScript
 * Handles countdown for public wishlist page
 */

// ─── Public Countdown ───
function initPublicCountdown() {
    const container = document.getElementById('publicCountdown');
    if (!container) return;
    
    const targetTimestamp = parseInt(container.dataset.target);
    
    function update() {
        const now = Date.now();
        const diff = targetTimestamp - now;
        
        if (diff <= 0) {
            container.innerHTML = '<p style="font-size: 1.5rem; font-weight: 700;">It\'s their birthday today!</p>';
            return;
        }
        
        const days    = Math.floor(diff / (1000 * 60 * 60 * 24));
        const hours   = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((diff % (1000 * 60)) / 1000);
        
        const d = document.getElementById('pub-days');
        const h = document.getElementById('pub-hours');
        const m = document.getElementById('pub-mins');
        const s = document.getElementById('pub-secs');
        
        if (d) d.textContent = days;
        if (h) h.textContent = hours;
        if (m) m.textContent = minutes;
        if (s) s.textContent = seconds;
    }
    
    update();
    setInterval(update, 1000);
}

// ─── Initialize ───
document.addEventListener('DOMContentLoaded', function() {
    initPublicCountdown();
});
